<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$row = DB::connection('sqlsrv')->table('DeviceUsers')->first();
if ($row) {
    foreach ($row as $key => $val) {
        echo "$key: $val\n";
    }
} else {
    echo "DeviceUsers table is empty (or query failed).\n";
}
