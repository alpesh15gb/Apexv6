<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

$table = 'DeviceLogs_1_2026';
$logUserIds = DB::connection('sqlsrv')->table($table)
    ->where('LogDate', '>=', \Carbon\Carbon::today()->subDays(30))
    ->distinct()
    ->pluck('UserId')
    ->toArray();

echo "Total Unique Punching IDs in Logs: " . count($logUserIds) . "\n";

$mappedCount = 0;
$unmappedIds = [];

foreach ($logUserIds as $id) {
    // Check if we can map this ID
    $user = User::where('device_employee_id', $id)
        ->orWhere('employee_id', (string) $id)
        ->first();

    if ($user) {
        $mappedCount++;
    } else {
        $unmappedIds[] = $id;
    }
}

echo "Successfully Mapped Users: $mappedCount\n";
echo "Unmapped (Orphan) IDs: " . count($unmappedIds) . "\n";

if (count($unmappedIds) > 0) {
    echo "Sample Unmapped IDs: " . implode(', ', array_slice($unmappedIds, 0, 10)) . "\n";

    // Check if these IDs exist in Employees table with different code?
    echo "\nChecking first 5 unmapped IDs in Employees table:\n";
    foreach (array_slice($unmappedIds, 0, 5) as $orphanId) {
        $emp = DB::connection('sqlsrv')->table('Employees')->where('EmployeeId', $orphanId)->first();
        if ($emp) {
            echo "ID $orphanId Found in Employees. Code: " . $emp->EmployeeCode . ", Name: " . $emp->EmployeeName . "\n";
        } else {
            echo "ID $orphanId NOT found in Employees table.\n";
        }
    }
}
