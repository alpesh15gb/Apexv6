<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Use 'LIKE' to match naming with potential spaces
$users = DB::connection('sqlsrv')->table('Employees')
    ->where('EmployeeName', 'LIKE', '%123%')
    ->orWhere('EmployeeName', 'LIKE', '%Kandukuri%')
    ->get();

foreach ($users as $u) {
    echo "Name: '{$u->EmployeeName}'\n";
    echo "ID: {$u->EmployeeId}\n";
    echo "Code: '{$u->EmployeeCode}'\n";
    echo "Status: {$u->RecordStatus}\n";  // Check if active
    echo "CompanyId: {$u->CompanyId}\n";
    echo "DepartmentId: {$u->DepartmentId}\n";
    echo "--------------------------\n";
}
