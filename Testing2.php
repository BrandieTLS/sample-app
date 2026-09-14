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
if ($_SERVER["REQUEST_METHOD"] == "POST"){
$name = $_POST["name"]; //varchar(100)
$phone = $_POST["phone"]; //varchar(30)
$imei = $_POST["imei"]; //varchar(100)
$phone_model = $_POST["phone_model"]; //varchar(100)
$repair_description = $_POST["repair_description"]; // text
$start_date = $_POST["start"]; // date
$plan = $_POST["plan"]; // text
$warranty_expiry = $_POST["warranty_expiry"]; //date
$notes = $_POST["notes"]; 

}


// Calculate warranty expiry
if ($_SERVER["REQUEST_METHOD"] == "POST"){
$expiry_date = date(
    "Y-m-d",
    strtotime("+$plan months", strtotime($start_date))
);}




// Insert into database
$sql = "INSERT INTO warranties
        (name, phone,imei, phone_model, repair_description,
         start_date, plan, warranty_expiry, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
  if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
$stmt->bind_param(
    "sssssssss",
    $name,
    $phone,
    $imei,
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
    if ($stmt->errno == 1062) {
        echo "This IMEI already has a warranty record.";
    } else {
        echo "Error adding warranty: " . $stmt->error;
    }
}



$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Extended Warranty</title>

    <style>
        body {
            font-family: Arial;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }

        textarea {
            height: 80px;
        }

        .plan {
            margin-top: 10px;
        }

        .plan input {
            width: auto;
        }

        button {
            margin-top: 20px;
            padding: 12px 20px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <h1>Extended Warranty</h1>

    <form  action="Testing2.php" method="POST">

        <label>Customer Name</label>
        <input type="text" name="name" required>

        <label>Phone Number</label>
        <input type="tel" name="phone" required>

        <label>Phone Model</label>
        <input type="text" name="phone_model" required>

        <label>imei</label>
        <input type="text" name="imei" required>

        <label>Repair Description</label>
        <textarea name="repair_description" ></textarea>

        <label>Start Date</label>
        <input type="date" name="start" id="start" required>

        <label>Warranty Plan</label>

        <div class="plan">
            <input type="radio" name="plan" value="30" onclick="calculateExpiry()" required>
            Plan A - 30 days
        </div>

        <div class="plan">
            <input type="radio" name="plan" value="6" onclick="calculateExpiry()">
            Plan B - 6 months
        </div>

        <div class="plan">
            <input type="radio" name="plan" value="12" id="C" onclick="calculateExpiry()">
            Plan C - 12 months
        </div>

        <label>Warranty Expiry</label>
        <input type="date" name="warranty_expiry" id="warranty_expiry" readonly>

        <label>Notes</label>
<textarea
    name="notes"
    placeholder="Add any additional information..."
></textarea>

        <button type="submit">Add Warranty</button>

    </form>

    <script>
function calculateExpiry() {

    const startValue = document.getElementById("start").value;
    const plan = document.querySelector('input[name="plan"]:checked');

    if (!startValue || !plan) {
        return;
    }

    const date = new Date(startValue + "T00:00:00");

    if (plan.value === "30") {
        date.setDate(date.getDate() + 30);
    } 
    else if (plan.value === "6") {
        date.setMonth(date.getMonth() + 6);
    } 
    else if (plan.value === "12") {
        date.setFullYear(date.getFullYear() + 1);
    }

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    document.getElementById("warranty_expiry").value =
        `${year}-${month}-${day}`;
}

document.getElementById("start").addEventListener("change", calculateExpiry);

document.querySelectorAll('input[name="plan"]').forEach(function(radio) {
    radio.addEventListener("change", calculateExpiry);
});

document.getElementById("start").value =
    new Date().toISOString().split("T")[0];

calculateExpiry();
</script>
</body>
</html>


