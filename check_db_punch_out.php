<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Attendance;

// Find User 10's ID in local DB
$u = User::where('device_employee_id', 10)->first();
if (!$u)
    die("User 10 not found locally.\n");

// Check Jan 01
$att = Attendance::where('user_id', $u->id)->whereDate('date', '2026-01-01')->first();

echo "\n--- JAN 01 ---\n";
if ($att) {
    echo "ID: " . $att->id . "\n";
    echo "In: " . $att->punch_in_time . "\n";
    echo "Out: " . $att->punch_out_time . "\n";
    echo "Out: " . $att->punch_out_time . "\n";
} else {
    echo "Rec Not Found.\n";
}

$att2 = Attendance::where('user_id', $u->id)->whereDate('date', '2026-01-18')->first();
echo "\n--- JAN 18 ---\n";
if ($att2) {
    echo "ID: " . $att2->id . "\n";
    echo "In: " . $att2->punch_in_time . "\n";
    echo "Out: " . $att2->punch_out_time . "\n";
} else {
    echo "Rec Not Found.\n";
}
