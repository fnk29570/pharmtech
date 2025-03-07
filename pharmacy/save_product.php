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

// Check if the user is logged in and is a patient
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Retrieve data from POST request
$product_id = $_POST['product_id'];
$name = $_POST['name'];
$description = $_POST['description'];
$image = $_POST['image']; // Expecting Base64 encoded image

// Convert Base64 image back to binary
$image = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $image));

// Prepare the SQL statement
$sql = "INSERT INTO saved_products (user_id, product_id, name, description, image) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

// Bind parameters (use 'b' for binary data in PDO, 's' for string in MySQLi)
$stmt->bind_param("iisss", $user_id, $product_id, $name, $description, $image);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Product saved successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save product: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
