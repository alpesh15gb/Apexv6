<?php
echo "Testing SQL Server Connection...\n";

$tests = [
    ['server' => 'localhost, 1433', 'encrypt' => false],
    ['server' => '127.0.0.1, 1433', 'encrypt' => false],
    ['server' => 'localhost\SQLEXPRESS', 'encrypt' => false],
    ['server' => '.\SQLEXPRESS', 'encrypt' => false],
    ['server' => 'localhost, 1433', 'encrypt' => true, 'trust' => true],
];

$uid = 'essl';
$pwd = 'Keystone@456';
$db = 'etimetracklite1';

foreach ($tests as $test) {
    echo "------------------------------------------------\n";
    echo "Testing Server: " . $test['server'] . "\n";

    $connectionInfo = [
        "Database" => $db,
        "UID" => $uid,
        "PWD" => $pwd,
        "Encrypt" => $test['encrypt'],
        "TrustServerCertificate" => $test['trust'] ?? false
    ];

    $conn = sqlsrv_connect($test['server'], $connectionInfo);

    if ($conn) {
        echo "SUCCESS! Connected.\n";
        sqlsrv_close($conn);
        exit(0);
    } else {
        echo "FAILED.\n";
        // print_r(sqlsrv_errors());
        foreach (sqlsrv_errors() as $error) {
            echo "Error: " . $error['message'] . "\n";
        }
    }
}
