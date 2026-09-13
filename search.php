<?php

header("Content-Type: application/json");

$phone = $_GET['phone'] ?? '';

if ($phone == '') {
    echo json_encode([
        "success" => false,
        "message" => "Please enter a phone number."
    ]);
    exit;
}

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

$stmt = $conn->prepare(
    "SELECT plan, start_date, warranty_expiry, phone_model, name
     FROM warranties
     WHERE phone = ?"
);

if ($stmt === false) {
    echo json_encode([
        "success" => false,
        "message" => "SQL error: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("s", $phone);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

if (count($data) == 0) {
    echo json_encode([
        "success" => false,
        "message" => "No warranty record found."
    ]);
} else {
    echo json_encode([
        "success" => true,
        "data" => $data
    ]);
}

$stmt->close();
$conn->close();

?>