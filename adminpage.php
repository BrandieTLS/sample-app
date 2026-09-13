<?php

header("Content-Type: application/json");

$host = "serverless-eastus.sysp0000.db3.skysql.com";
$username = "dbpbf12883888";
$password = "nyy6hj6v0#s61c2fTw%i";
$dbname = "warranty_db";
$port = 4003;

$conn = mysqli_init();

mysqli_ssl_set(
    $conn,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL
);

if (!mysqli_real_connect(
    $conn,
    $host,
    $username,
    $password,
    $dbname,
    $port,
    NULL,
    MYSQLI_CLIENT_SSL
)) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit;
}

$sql = "
    SELECT
        name,
        phone,
        COUNT(*) AS warranty_count
    FROM warranties
    GROUP BY name, phone
    ORDER BY name
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "SQL error: " . $conn->error
    ]);
    exit;
}

$customers = [];

while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $customers
]);

$conn->close();

?>
