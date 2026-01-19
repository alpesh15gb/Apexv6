<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$deviceId = 106;
echo "Tracing Device ID: $deviceId\n";

// 1. Check DeviceUsers mapping
$du = DB::connection('sqlsrv')->table('DeviceUsers')->where('DeviceUserId', $deviceId)->first();
if ($du) {
    echo "[DeviceUsers] Maps 106 -> EmpID: {$du->EmployeeId}\n";
    $e = DB::connection('sqlsrv')->table('Employees')->where('EmployeeId', $du->EmployeeId)->first();
    if ($e) {
        echo "[DeviceUsers] EmpID {$du->EmployeeId} -> Code: '{$e->EmployeeCode}', Name: '{$e->EmployeeName}'\n";
    } else {
        echo "[DeviceUsers] EmpID {$du->EmployeeId} NOT FOUND in Employees table.\n";
    }
} else {
    echo "[DeviceUsers] 106 NOT FOUND in DeviceUsers table.\n";
}

// 2. Check Direct Code Match (106)
$eDirect = DB::connection('sqlsrv')->table('Employees')->where('EmployeeCode', (string) $deviceId)->first();
if ($eDirect) {
    echo "[Direct Code] Code '106' Found! ID: {$eDirect->EmployeeId}, Name: '{$eDirect->EmployeeName}'\n";
} else {
    echo "[Direct Code] Code '106' NOT FOUND.\n";
}

// 3. Check Suffix Match (KSDK106)
$eSuffix = DB::connection('sqlsrv')->table('Employees')->where('EmployeeCode', 'LIKE', "%$deviceId")->first();
if ($eSuffix) {
    echo "[Suffix Match] Code '%106' Found! Code: '{$eSuffix->EmployeeCode}', ID: {$eSuffix->EmployeeId}, Name: '{$eSuffix->EmployeeName}'\n";
} else {
    echo "[Suffix Match] Code '%106' NOT FOUND.\n";
}
