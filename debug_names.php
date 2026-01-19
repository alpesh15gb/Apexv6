<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$names = DB::connection('sqlsrv')->table('Employees')->take(20)->pluck('EmployeeName');
echo "Found " . $names->count() . " names:\n";
foreach ($names as $n) {
    echo "- $n\n";
}

$tables = DB::connection('sqlsrv')->select("SELECT table_name FROM information_schema.tables");
echo "\nTables in DB:\n";
foreach ($tables as $t) {
    echo "- " . $t->table_name . "\n";
}
