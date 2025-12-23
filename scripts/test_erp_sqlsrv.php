<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Load .env manually (because this script is outside Laravel bootstrap)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "== ERP SQL Server connection test ==\n";

$server   = $_ENV['DB_ERP_HOST'] ?? getenv('DB_ERP_HOST') ?? '';
$database = $_ENV['DB_ERP_DATABASE'] ?? getenv('DB_ERP_DATABASE') ?? '';
$user     = $_ENV['DB_ERP_USERNAME'] ?? getenv('DB_ERP_USERNAME') ?? '';
$pass     = $_ENV['DB_ERP_PASSWORD'] ?? getenv('DB_ERP_PASSWORD') ?? '';

echo "Server: {$server}\nDB: {$database}\nUser: {$user}\n\n";

// 1) Test using sqlsrv_* (NO PDO)
echo "-- sqlsrv_connect test --\n";
$connectionInfo = [
    "Database" => $database,
    "UID" => $user,
    "PWD" => $pass,
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => true,
];

$conn = @sqlsrv_connect($server, $connectionInfo);

if ($conn === false) {
    echo "sqlsrv_connect: FAILED\n";
    print_r(sqlsrv_errors());
} else {
    echo "sqlsrv_connect: OK\n";

    $stmt = sqlsrv_query($conn, "SELECT TOP 3 Geha_Code, Geha_Name FROM dbo.Geha_Data");

    if ($stmt === false) {
        echo "Query FAILED\n";
        print_r(sqlsrv_errors());
    } else {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            print_r($row);
        }
    }

    sqlsrv_close($conn);
}

echo "\n-- PDO sqlsrv test --\n";

// 2) Test using PDO
try {
    $dsn = "sqlsrv:Server={$server};Database={$database}";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $rows = $pdo->query("SELECT TOP 3 Geha_Code, Geha_Name FROM dbo.Geha_Data")
        ->fetchAll(PDO::FETCH_ASSOC);

    echo "PDO: OK\n";
    print_r($rows);
} catch (Throwable $e) {
    echo "PDO: FAILED\n";
    echo $e->getMessage() . "\n";
}
