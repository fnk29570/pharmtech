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

// Initialize variables for form inputs and messages
$id = "";
$formUsername = "";
$formPassword = "";
$formEmail = "";
$formFullName = "";
$formGender = "";
$searchQuery = "";
$errorMsg = "";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add New Pharmacist
    if (isset($_POST["addPharmacistBtn"])) {
        $formUsername = $_POST['username'];
        $formPassword = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
        $formEmail = $_POST['email'];
        $formFullName = $_POST['full_name'];
        $formGender = $_POST['gender'];

        // Check for duplicate username or email
        $stmt = $conn->prepare("SELECT id FROM pharmacists WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $formUsername, $formEmail);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errorMsg = "Account with this username or email already exists.";
        } else {
            // Prepare and bind the SQL statement
            $stmt = $conn->prepare("INSERT INTO pharmacists (username, password, email, full_name, gender) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $formUsername, $formPassword, $formEmail, $formFullName, $formGender);

            // Execute the statement
            if ($stmt->execute()) {
                // Redirect to avoid re-posting data on refresh
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $errorMsg = "Error: " . $stmt->error;
            }
        }

        // Close statement
        $stmt->close();
    }

    // Update Pharmacist
    if (isset($_POST["editPharmacistBtn"])) {
        $id = $_POST['editPharmacistId'];
        $formUsername = $_POST['editUsername'];
        $formEmail = $_POST['editEmail'];
        $formFullName = $_POST['editFullName'];
        $formGender = $_POST['editGender'];

        // Prepare and bind the SQL statement
        $stmt = $conn->prepare("UPDATE pharmacists SET username = ?, email = ?, full_name = ?, gender = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $formUsername, $formEmail, $formFullName, $formGender, $id);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to avoid re-posting data on refresh
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $errorMsg = "Error: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    }

    // Delete Pharmacist
    if (isset($_POST["deletePharmacistBtn"])) {
        $id = $_POST['deletePharmacistId'];

        // Prepare and bind the SQL statement
        $stmt = $conn->prepare("DELETE FROM pharmacists WHERE id = ?");
        $stmt->bind_param("i", $id);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to avoid re-posting data on refresh
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $errorMsg = "Error: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    }
}

// Handle Search
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $searchQuery = $conn->real_escape_string($searchQuery);
    $sql = "SELECT * FROM pharmacists WHERE username LIKE '%$searchQuery%' OR email LIKE '%$searchQuery%' OR full_name LIKE '%$searchQuery%'";
} else {
    $sql = "SELECT * FROM pharmacists";
}

$result = $conn->query($sql);

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <style>
        .table-responsive {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="mt-5 mb-4"></h2>

        <!-- Display Error Message -->
        <?php if ($errorMsg): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $errorMsg; ?>
            </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <form class="d-flex mb-4" method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input class="form-control me-2" type="search" name="search" placeholder="Search by username, email, or name" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button class="btn btn-outline-success" type="submit">Search</button>
        </form>

        <!-- Add New Pharmacist Modal Button -->
        <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addPharmacistModal">
            + New Pharmacist
        </button>

        <!-- Add New Pharmacist Modal -->
        <div class="modal fade" id="addPharmacistModal" tabindex="-1" aria-labelledby="addPharmacistModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addPharmacistModalLabel">Add New Pharmacist</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="full_name">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="gender">Gender</label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="addPharmacistBtn">Save Pharmacist</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Pharmacists Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Gender</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['username']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['full_name']; ?></td>
                                <td><?php echo $row['gender']; ?></td>
                                <td>
                                    <!-- Edit Pharmacist Modal Button -->
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editPharmacistModal<?php echo $row['id']; ?>">
                                        Edit
                                    </button>
                                    <!-- Edit Pharmacist Modal -->
                                    <div class="modal fade" id="editPharmacistModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editPharmacistModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editPharmacistModalLabel">Edit Pharmacist</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="editPharmacistId" value="<?php echo $row['id']; ?>">
                                                        <div class="form-group mb-3">
                                                            <label for="editUsername">Username</label>
                                                            <input type="text" class="form-control" id="editUsername" name="editUsername" value="<?php echo $row['username']; ?>" required>
                                                        </div>
                                                        <div class="form-group mb-3">
                                                            <label for="editEmail">Email</label>
                                                            <input type="email" class="form-control" id="editEmail" name="editEmail" value="<?php echo $row['email']; ?>" required>
                                                        </div>
                                                        <div class="form-group mb-3">
                                                            <label for="editFullName">Full Name</label>
                                                            <input type="text" class="form-control" id="editFullName" name="editFullName" value="<?php echo $row['full_name']; ?>" required>
                                                        </div>
                                                        <div class="form-group mb-3">
                                                            <label for="editGender">Gender</label>
                                                            <select class="form-control" id="editGender" name="editGender" required>
                                                                <option value="Male" <?php if ($row['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                                                                <option value="Female" <?php if ($row['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                                                                <option value="Other" <?php if ($row['gender'] == 'Other') echo 'selected'; ?>>Other</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary" name="editPharmacistBtn">Update Pharmacist</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Delete Pharmacist Button -->
                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this pharmacist?');">
                                        <input type="hidden" name="deletePharmacistId" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" name="deletePharmacistBtn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="6" class="text-center">No pharmacists found.</td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-dAgZBOOepnlzLYB1zJoNc3pGQgYgE49DyPVpePr9HzL2HmogBpR7FceB3CcIfzN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-BZBmQ1hX6LLg2FVgkCkWOBWL7fdPsvVp97FtpTft4EBf2XJFY0Hr8+uPzP1K78yZ" crossorigin="anonymous"></script>
</body>

</html>
