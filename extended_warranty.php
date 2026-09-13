<?php

// connection
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
    die("Database connection failed: " . mysqli_connect_error());
}

echo "Database connected successfully!";


echo "Connected!<br>";
echo "Database: " . $conn->query("SELECT DATABASE()")->fetch_row()[0] . "<br>";

// Get information from the form
$name = $_POST["name"];
$phone = $_POST["phone"];
$phone_model = $_POST["phone_model"];
$repair_description = $_POST["repair_description"];
$start_date = $_POST["start"];
$plan = $_POST["plan"];
$warranty_expiry = $_POST["warranty_expiry"];
$notes = $_POST["notes"];


// Calculate warranty expiry
$expiry_date = date(
    "Y-m-d",
    strtotime("+$plan months", strtotime($start_date))
);


// Insert into database

$sql = "INSERT INTO warranties
        (name, phone, phone_model, repair_description,
         start_date, plan, warranty_expiry, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
  if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
$stmt->bind_param(
    "ssssssss",
    $name,
    $phone,
    $phone_model,
    $repair_description,
    $start_date,
    $plan,
    $expiry_date,
    $notes
);


if ($stmt->execute()) {

    echo "Warranty added successfully!";

} else {

    echo "Error adding warranty.";

}



$stmt->close();
$conn->close();

?>