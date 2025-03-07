<?php
session_start();

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

// Check if form data is posted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $amount = $_POST['amount'];
    $address = $_POST['address'];
    $user_id = $_SESSION['user_id']; // Get user ID from session

    // Prepare and execute SQL statement to insert transaction
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, phone, address, date, time) VALUES (?, ?, ?, ?, CURDATE(), CURTIME())");
    $stmt->bind_param("idss", $user_id, $amount, $phone, $address);
    
    if ($stmt->execute()) {
        // Redirect to thank_you.html after successful insertion
        header('Location: thank_you.html');
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>
