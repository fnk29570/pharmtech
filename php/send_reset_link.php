<?php
// Database connection parameters
$host = 'localhost';
$dbUsername = 'root'; // Default username in XAMPP
$dbPassword = '';     // Default password in XAMPP is empty
$dbName = 'pharmtech3_db'; // Name of your database

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);            // Trim whitespace
    $data = stripslashes($data);    // Remove backslashes
    $data = htmlspecialchars($data);// Convert special characters to HTML entities
    return $data;
}

// Function to generate a random token
function generateToken($length = 20) {
    return bin2hex(random_bytes($length));
}

// Start session
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $email = isset($_POST["email"]) ? sanitizeInput($_POST["email"]) : "";

    try {
        // Connect to database
        $pdo = new PDO("mysql:host=$host;dbname=$dbName", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if email exists in any user table (admins, pharmacists, patients)
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email UNION 
                               SELECT * FROM pharmacists WHERE email = :email UNION
                               SELECT * FROM patients WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate unique token
            $token = generateToken();

            // Store token and email in session (for verification in reset_password.php)
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_token'] = $token;

            // Send password reset link to user's email (You need to implement this part)
            // Example: Send email using PHP's mail function or a third-party library like PHPMailer
            // Replace placeholders with your actual email sending logic
            $reset_link = "http://localhost/project/reset_password.php?token=$token";
            $to = $email;
            $subject = "Password Reset Link";
            $message = "Dear user,\n\nPlease click on the following link to reset your password:\n$reset_link\n\nRegards,\nPharmTech Team";
            $headers = "From: PharmTech224@gmail.com"; // Replace with your email address

            // Uncomment the following lines when ready to send emails
            // mail($to, $subject, $message, $headers);

            // Redirect to a success page or show a success message
            header("Location: ../reset_password_success.html"); // Adjust the path as needed
            exit();
        } else {
            // Email not found in database
            echo "Email address not found.";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

