<?php
// Database connection parameters
$host = 'localhost';
$dbUsername = 'root'; // Default username in XAMPP
$dbPassword = '';     // Default password in XAMPP is empty
$dbName = 'pharmtech3_db'; // Name of your database

// Admin user details
$username = 'admin';
$password = 'password'; // Change this to your desired initial password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$full_name = 'Admin User';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbName", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert admin user into admins table
    $stmt = $pdo->prepare("INSERT INTO admins (username, password, full_name) VALUES (:username, :password, :full_name)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->execute();

    echo "Admin user created successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

