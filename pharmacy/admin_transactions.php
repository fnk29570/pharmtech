<?php
session_start();

// Set session timeout duration
$timeout_duration = 1800; // 30 minutes

// Check if the user is logged in and handle session expiration
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit;
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Session expired, clear the session
    session_unset();
    session_destroy();
    header('Location: ../login.html');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

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

// Function to check if the user is an admin
function checkAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Prepare and bind
    if ($stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?")) {
        $stmt->bind_param("i", $delete_id);
        
        // Execute the statement
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Transaction deleted successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error deleting transaction: " . $stmt->error . "</div>";
        }
        
        // Close the statement
        $stmt->close();
    } else {
        echo "<div class='alert alert-danger'>Failed to prepare statement: " . $conn->error . "</div>";
    }
}

// Fetch transactions from database
$sql = "SELECT t.id, t.date, t.time, t.amount, u.username 
        FROM transactions t 
        JOIN patients u ON t.user_id = u.id 
        ORDER BY t.date DESC, t.time DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Manage Transactions</h2>

        <?php if (checkAdmin()): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["date"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["time"]) . "</td>";
                        echo "<td>$" . number_format($row["amount"], 2) . "</td>";
                        echo "<td>
                                <a href='?delete_id=" . htmlspecialchars($row["id"]) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this transaction?\")'>Delete</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No transactions found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="alert alert-danger">You do not have permission to view this page.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>
