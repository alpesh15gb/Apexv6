<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// ID 2601 was the Suffix Match for device log 106
// Let's see if '106' appears purely in any column
$u = DB::connection('sqlsrv')->table('Employees')->where('EmployeeId', 2601)->first();

if ($u) {
    echo "Dumping Employee 2601:\n";
    echo "EmployeeId: {$u->EmployeeId}\n";
    echo "EmployeeCode: {$u->EmployeeCode}\n";
    echo "EmployeeName: {$u->EmployeeName}\n";
    echo "CardNumber: " . ($u->CardNumber ?? 'N/A') . "\n";

    echo "\nSearching for '106' in all columns:\n";
    foreach ((array) $u as $key => $val) {
        if (is_string($val) && strpos($val, '106') !== false) {
            echo "MATCH: [$key] => '$val'\n";
        }
    }
} else {
    echo "Employee 2601 not found.\n";
}
