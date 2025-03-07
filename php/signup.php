<?php
// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to validate email format
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to hash passwords
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Form submission handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $username = isset($_POST["username"]) ? sanitizeInput($_POST["username"]) : "";
    $email = isset($_POST["email"]) ? sanitizeInput($_POST["email"]) : "";
    $password = isset($_POST["password"]) ? sanitizeInput($_POST["password"]) : "";
    $full_name = isset($_POST["full_name"]) ? sanitizeInput($_POST["full_name"]) : "";
    $gender = isset($_POST["gender"]) ? sanitizeInput($_POST["gender"]) : null;
    $birthdate = isset($_POST["birthdate"]) ? $_POST["birthdate"] : "";

    // Validate email format
    if (!validateEmail($email)) {
        echo "Invalid email format";
        exit();
    }

    // Database connection parameters
    $host = 'localhost';
    $dbUsername = 'root'; // Default username in XAMPP
    $dbPassword = '';     // Default password in XAMPP is empty
    $dbName = 'pharmtech3_db'; // Name of your database

    try {
        // Connect to database
        $pdo = new PDO("mysql:host=$host;dbname=$dbName", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM patients WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            // Username or email already exists
            echo "Username or email already exists. Please choose another.";
        } else {
            // Insert new user into database
            $hashed_password = hashPassword($password);
            $timestamp = date('Y-m-d H:i:s');
            $insert_stmt = $pdo->prepare("INSERT INTO patients (username, password, email, full_name, gender, birthdate) VALUES (:username, :password, :email, :full_name, :gender, :birthdate)");
            $insert_stmt->bindParam(':username', $username);
            $insert_stmt->bindParam(':password', $hashed_password);
            $insert_stmt->bindParam(':email', $email);
            $insert_stmt->bindParam(':full_name', $full_name);
            $insert_stmt->bindParam(':gender', $gender);
            $insert_stmt->bindParam(':birthdate', $birthdate);
            $insert_stmt->execute();
           
            // User successfully registered
            echo "Registration successful!";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
// Redirect to a success page after registration
header("Location: ./patient_dashboard.php");
exit();


