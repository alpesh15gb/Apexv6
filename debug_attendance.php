<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$atts = App\Models\Attendance::with('user')->orderBy('id', 'desc')->take(10)->get();

if ($atts->isEmpty()) {
    echo "No attendance records found.\n";
}

foreach ($atts as $a) {
    $userName = $a->user->name ?? 'Unknown User (' . $a->user_id . ')';
    $date = $a->date ? $a->date->format('Y-m-d') : 'No Date';

    echo "User: $userName\n";
    echo "Date: $date\n";
    echo "Time: " . ($a->punch_in_time ?? 'NULL') . " - " . ($a->punch_out_time ?? 'NULL') . "\n";
    echo "Status: $a->status\n";
    echo "-------------------\n";
}
