<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    // Database connection
    $servername = "localhost";
    $username = "root"; // Default XAMPP username
    $password = "";     // Default XAMPP password is empty
    $dbname = "pharmtech3_db"; // Use the database name you created

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    // Update login status to 'offline'
    $update_stmt = $conn->prepare("UPDATE {$role}s SET login_status = 'offline' WHERE id = ?");
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();
    $update_stmt->close();

    $conn->close();
}

// Clear all session variables and destroy the session
session_unset(); 
session_destroy(); 

// Prevent caching of the page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

// Redirect to login page
header("Location: ../index.html"); 
exit;

