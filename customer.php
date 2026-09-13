<?php

// ==============================
// DATABASE CONNECTION
// ==============================

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
    die("Database connection failed.");
}


// ==============================
// GET CUSTOMER PHONE
// ==============================

$phone = $_GET["phone"] ?? "";

if ($phone == "") {
    die("No customer phone number provided.");
}


// ==============================
// EXTEND WARRANTY
// ==============================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $warranty_id = $_POST["warranty_id"] ?? "";
    $plan = $_POST["plan"] ?? "";

    if ($warranty_id == "" || $plan == "") {
        die("Missing warranty information.");
    }


    // ------------------------------
    // Get current expiry date
    // ------------------------------

    $sql = "SELECT warranty_expiry
            FROM warranties
            WHERE warranty_id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param("i", $warranty_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("Warranty not found.");
    }

    $warranty = $result->fetch_assoc();

    $current_expiry = $warranty["warranty_expiry"];

    $stmt->close();


    // ------------------------------
    // Add selected period
    // to CURRENT expiry date
    // ------------------------------

    if ($plan == "30") {

        $new_expiry = date(
            "Y-m-d",
            strtotime("+30 days", strtotime($current_expiry))
        );

    } elseif ($plan == "6") {

        $new_expiry = date(
            "Y-m-d",
            strtotime("+6 months", strtotime($current_expiry))
        );

    } elseif ($plan == "12") {

        $new_expiry = date(
            "Y-m-d",
            strtotime("+1 year", strtotime($current_expiry))
        );

    } else {

        die("Invalid warranty period.");
    }


    // ------------------------------
    // Update expiry date
    // ------------------------------

    $sql = "UPDATE warranties
            SET warranty_expiry = ?
            WHERE warranty_id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param(
        "si",
        $new_expiry,
        $warranty_id
    );

    if (!$stmt->execute()) {
        die("Failed to update warranty.");
    }

    $stmt->close();


    // ------------------------------
    // Redirect back to customer page
    // ------------------------------

    header(
        "Location: customer.php?phone="
        . urlencode($phone)
        . "&updated=1"
    );

    exit;
}


// ==============================
// GET ALL CUSTOMER WARRANTIES
// ==============================

$sql = "SELECT
            warranty_id,
            name,
            phone,
            imei,
            phone_model,
            repair_description,
            start_date,
            plan,
            warranty_expiry,
            notes
        FROM warranties
        WHERE phone = ?
        ORDER BY warranty_id DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL error: " . $conn->error);
}

$stmt->bind_param("s", $phone);
$stmt->execute();

$result = $stmt->get_result();


// ==============================
// CHECK CUSTOMER EXISTS
// ==============================

if ($result->num_rows == 0) {
    die("No warranty records found for this customer.");
}


// Get first row for customer information
$firstWarranty = $result->fetch_assoc();

// Put it back by storing all records
$warranties = [$firstWarranty];

while ($row = $result->fetch_assoc()) {
    $warranties[] = $row;
}

$stmt->close();


// ==============================
// CUSTOMER INFORMATION
// ==============================

$customerName = $warranties[0]["name"];
$customerPhone = $warranties[0]["phone"];

?>

<!DOCTYPE html>
<html>

<head>

    <title>Customer Warranty</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }

        h1 {
            margin-bottom: 10px;
        }

        .customer-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .warranty {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .warranty h2 {
            margin-top: 0;
        }

        .field {
            margin: 10px 0;
        }

        .label {
            font-weight: bold;
        }

        .edit-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        select {
            padding: 8px;
            margin-left: 10px;
        }

        button {
            padding: 10px 18px;
            margin-top: 15px;
            cursor: pointer;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>


    <h1>Customer Warranty</h1>


    <!-- ============================== -->
    <!-- SUCCESS MESSAGE -->
    <!-- ============================== -->

    <?php if (isset($_GET["updated"])): ?>

        <div class="success">
            Warranty expiry date updated successfully.
        </div>

    <?php endif; ?>


    <!-- ============================== -->
    <!-- CUSTOMER INFORMATION -->
    <!-- ============================== -->

    <div class="customer-info">

        <h2>Customer Information</h2>

        <div class="field">
            <span class="label">Name:</span>

            <?php
            echo htmlspecialchars($customerName);
            ?>
        </div>

        <div class="field">
            <span class="label">Phone:</span>

            <?php
            echo htmlspecialchars($customerPhone);
            ?>
        </div>

        <div class="field">
            <span class="label">Total Warranties:</span>

            <?php
            echo count($warranties);
            ?>
        </div>

    </div>


    <!-- ============================== -->
    <!-- WARRANTY RECORDS -->
    <!-- ============================== -->

    <h2>Warranty Records</h2>


    <?php foreach ($warranties as $warranty): ?>

        <div class="warranty">


            <h2>
                <?php
                echo htmlspecialchars($warranty["phone_model"]);
                ?>
            </h2>


            <!-- Warranty ID -->

            <div class="field">

                <span class="label">
                    Warranty ID:
                </span>

                <?php
                echo htmlspecialchars($warranty["warranty_id"]);
                ?>

            </div>


            <!-- IMEI -->

            <div class="field">

                <span class="label">
                    IMEI:
                </span>

                <?php
                echo htmlspecialchars($warranty["imei"] ?? "N/A");
                ?>

            </div>


            <!-- Phone Model -->

            <div class="field">

                <span class="label">
                    Phone Model:
                </span>

                <?php
                echo htmlspecialchars($warranty["phone_model"]);
                ?>

            </div>


            <!-- Repair Description -->

            <div class="field">

                <span class="label">
                    Repair:
                </span>

                <?php
                echo htmlspecialchars(
                    $warranty["repair_description"] ?? "N/A"
                );
                ?>

            </div>


            <!-- Start Date -->

            <div class="field">

                <span class="label">
                    Warranty Start:
                </span>

                <?php
                echo htmlspecialchars($warranty["start_date"]);
                ?>

            </div>


            <!-- Current Plan -->

            <div class="field">

                <span class="label">
                    Current Plan:
                </span>

                <?php

                if ($warranty["plan"] == "30") {

                    echo "30 Days";

                } elseif ($warranty["plan"] == "6") {

                    echo "6 Months";

                } elseif ($warranty["plan"] == "12") {

                    echo "1 Year";

                } else {

                    echo htmlspecialchars($warranty["plan"]);

                }

                ?>

            </div>


            <!-- Current Expiry -->

            <div class="field">

                <span class="label">
                    Current Expiry:
                </span>

                <?php
                echo htmlspecialchars(
                    $warranty["warranty_expiry"]
                );
                ?>

            </div>


            <!-- Notes -->

            <div class="field">

                <span class="label">
                    Notes:
                </span>

                <?php
                echo htmlspecialchars(
                    $warranty["notes"] ?? "N/A"
                );
                ?>

            </div>


            <!-- ============================== -->
            <!-- EXTEND WARRANTY -->
            <!-- ============================== -->

            <div class="edit-section">

                <h3>Extend Warranty</h3>

                <form method="POST">


                    <!-- Warranty ID -->

                    <input
                        type="hidden"
                        name="warranty_id"
                        value="<?php
                        echo htmlspecialchars(
                            $warranty["warranty_id"]
                        );
                        ?>"
                    >


                    <!-- Select extension -->

                    <label>

                        Extend by:

                        <select name="plan" required>

                            <option value="">
                                Select period
                            </option>

                            <option value="30">
                                30 Days
                            </option>

                            <option value="6">
                                6 Months
                            </option>

                            <option value="12">
                                1 Year
                            </option>

                        </select>

                    </label>


                    <br>


                    <button type="submit">
                        Extend Warranty
                    </button>

                </form>

            </div>


        </div>

    <?php endforeach; ?>


</body>

</html>

<?php

$conn->close();

?>