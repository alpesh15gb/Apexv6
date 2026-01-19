<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $ids = Illuminate\Support\Facades\DB::connection('sqlsrv')->table('DeviceLogs_1_2026')->distinct()->pluck('UserId')->take(10)->toArray();
    echo "Log IDs: " . implode(', ', $ids) . "\n";

    $emps = Illuminate\Support\Facades\DB::connection('sqlsrv')->select("SELECT TOP 5 EmployeeId, EmployeeCode FROM Employees");
    echo "Employees: ";
    foreach ($emps as $e)
        echo $e->EmployeeId . ':' . $e->EmployeeCode . ', ';
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
