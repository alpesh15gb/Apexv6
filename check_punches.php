<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Find a user with > 1 punch on any recent day
$results = DB::connection('sqlsrv')->select("
    SELECT TOP 1 UserId, CONVERT(date, LogDate) as Date, COUNT(*) as Count
    FROM DeviceLogs_1_2026
    GROUP BY UserId, CONVERT(date, LogDate)
    HAVING COUNT(*) > 1
");

if (empty($results)) {
    echo "No multiple punches found for any user in Jan 2026.\n";
    exit;
}

$r = $results[0];
$userId = $r->UserId;
$date = $r->Date;

echo "Found User $userId with {$r->Count} punches on $date.\n";

$logs = DB::connection('sqlsrv')->table('DeviceLogs_1_2026')
    ->where('UserId', $userId)
    ->whereRaw("CONVERT(date, LogDate) = ?", [$date])
    ->orderBy('LogDate', 'asc')
    ->get();

echo "Raw Logs:\n";
foreach ($logs as $log) {
    echo $log->LogDate . "\n";
}

// Check Local
$att = App\Models\Attendance::where('user_id', function ($q) use ($userId) {
    $q->select('id')->from('users')->where('device_employee_id', $userId);
})->whereDate('date', $date)->first();

echo "\nLocal Attendance:\n";
if ($att) {
    echo "In: " . $att->punch_in_time . "\n";
    echo "Out: " . $att->punch_out_time . "\n";
} else {
    echo "No Attendance Found in Local DB.\n";
}
