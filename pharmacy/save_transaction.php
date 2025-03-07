<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pharmtech3_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if POST variable is set
if (isset($_POST['amount'])) {
    // Sanitize and validate amount
    $amount = filter_var($_POST['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // Validate amount (ensure it's a valid number)
    if (!is_numeric($amount)) {
        die("Invalid amount.");
    }

    // Get current date and time
    $date = date('Y-m-d');
    $time = date('H:i:s');

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO transactions (date, time, amount) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $date, $time, $amount);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Transaction saved successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement
    $stmt->close();
} else {
    echo "Amount is required.";
}

// Close connection
$conn->close();

