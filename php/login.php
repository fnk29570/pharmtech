<?php
session_start();

// Database configuration
$host = 'localhost'; // Database host
$dbname = 'pharmtech3_db'; // Database name
$dbUsername = 'root'; // Database username
$dbPassword = ''; // Database password

// Create a new PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $inputUsername = $_POST['username'] ?? '';
    $inputPassword = $_POST['password'] ?? '';

    // Validate inputs
    if (!empty($inputUsername) && !empty($inputPassword)) {
        // Check credentials for admins
        $stmtAdmin = $pdo->prepare('SELECT id, password FROM admins WHERE username = :username');
        $stmtAdmin->bindParam(':username', $inputUsername, PDO::PARAM_STR);
        $stmtAdmin->execute();
        $userAdmin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

        if ($userAdmin && password_verify($inputPassword, $userAdmin['password'])) {
            // Admin login successful
            $_SESSION['user_id'] = $userAdmin['id'];
            $_SESSION['username'] = $inputUsername;
            header('Location: ../administrator_dashboard.php');
            exit;
        }

        // Check credentials for pharmacists
        $stmtPharmacist = $pdo->prepare('SELECT id, password FROM pharmacists WHERE username = :username');
        $stmtPharmacist->bindParam(':username', $inputUsername, PDO::PARAM_STR);
        $stmtPharmacist->execute();
        $userPharmacist = $stmtPharmacist->fetch(PDO::FETCH_ASSOC);

        if ($userPharmacist && password_verify($inputPassword, $userPharmacist['password'])) {
            // Pharmacist login successful
            $_SESSION['user_id'] = $userPharmacist['id'];
            $_SESSION['username'] = $inputUsername;
            header('Location: ../pharmacists_dashboard.php');
            exit;
        }

        // Check credentials for patients
        $stmtPatient = $pdo->prepare('SELECT id, password FROM patients WHERE username = :username');
        $stmtPatient->bindParam(':username', $inputUsername, PDO::PARAM_STR);
        $stmtPatient->execute();
        $userPatient = $stmtPatient->fetch(PDO::FETCH_ASSOC);

        if ($userPatient && password_verify($inputPassword, $userPatient['password'])) {
            // Patient login successful
            $_SESSION['user_id'] = $userPatient['id'];
            $_SESSION['username'] = $inputUsername;
            header('Location: ../patient_dashboard.php');
            exit;
        }

        // Invalid credentials
        $error = 'Invalid username or password.';
        header('Location: ../login.html?error=' . urlencode($error));
        exit;
    } else {
        // Missing credentials
        $error = 'Please enter both username and password.';
        header('Location: ../login.html?error=' . urlencode($error));
        exit;
    }
} else {
    // Redirect to login page if not a POST request
    header('Location: ../login.html');
    exit;
}

