<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pharmtech3_db";

// Create a PDO connection
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add Prescription
    if (isset($_POST["addPrescriptionBtn"])) {
        $productId = (int) $_POST['productId'];
        $prescription = htmlspecialchars($_POST['prescription']);

        // Prepare and execute the SQL statement
        $sql = "UPDATE products SET prescription = :prescription WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':prescription', $prescription);
        $stmt->bindParam(':id', $productId);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    }

    // Update Prescription
    if (isset($_POST["editPrescriptionBtn"])) {
        $productId = (int) $_POST['editProductId'];
        $prescription = htmlspecialchars($_POST['editPrescription']);

        // Prepare and execute the SQL statement
        $sql = "UPDATE products SET prescription = :prescription WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':prescription', $prescription);
        $stmt->bindParam(':id', $productId);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    }

    // Clear Prescription
    if (isset($_POST["clearPrescriptionBtn"])) {
        $productId = (int) $_POST['clearProductId'];

        // Prepare and execute the SQL statement
        $sql = "UPDATE products SET prescription = NULL WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $productId);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    }
}

// Handle the search functionality
$searchQuery = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = htmlspecialchars($_GET['search']);
    $searchQuery = " WHERE name LIKE :searchTerm";
}

// Fetch products from database with optional search filter
$sql = "SELECT * FROM products" . $searchQuery;
$stmt = $pdo->prepare($sql);

if ($searchQuery) {
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmTech</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <!-- Search Bar -->
        <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by product name" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-outline-dark" type="submit">Search</button>
            </div>
        </form>

        <!-- Button trigger modal -->
        <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addPrescriptionModal">
            + New Prescription
        </button>

        <!-- Modal for Adding Prescription -->
        <div class="modal fade" id="addPrescriptionModal" tabindex="-1" aria-labelledby="addPrescriptionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addPrescriptionModalLabel">Add Prescription</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="productId" class="form-label">Product ID</label>
                                <input type="number" class="form-control" id="productId" name="productId" required>
                            </div>
                            <div class="mb-3">
                                <label for="prescription" class="form-label">Prescription</label>
                                <textarea class="form-control" id="prescription" name="prescription" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="addPrescriptionBtn" class="btn btn-primary">Add Prescription</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Product List -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Prescription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['id']); ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['prescription']); ?></td>
                                <td>
                                    <!-- Edit Button Trigger -->
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editPrescriptionModal<?php echo htmlspecialchars($product['id']); ?>">Edit</button>
                                    
                                    <!-- Clear Prescription Button -->
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display:inline;">
                                        <input type="hidden" name="clearProductId" value="<?php echo htmlspecialchars($product['id']); ?>">
                                        <button type="submit" name="clearPrescriptionBtn" class="btn btn-danger btn-sm">Clear Prescription</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal for Editing Prescription -->
                            <div class="modal fade" id="editPrescriptionModal<?php echo htmlspecialchars($product['id']); ?>" tabindex="-1" aria-labelledby="editPrescriptionModalLabel<?php echo htmlspecialchars($product['id']); ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editPrescriptionModalLabel<?php echo htmlspecialchars($product['id']); ?>">Edit Prescription</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="editProductId" value="<?php echo htmlspecialchars($product['id']); ?>">
                                                <div class="mb-3">
                                                    <label for="editPrescription<?php echo htmlspecialchars($product['id']); ?>" class="form-label">Prescription</label>
                                                    <textarea class="form-control" id="editPrescription<?php echo htmlspecialchars($product['id']); ?>" name="editPrescription" rows="3"><?php echo htmlspecialchars($product['prescription']); ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" name="editPrescriptionBtn" class="btn btn-primary">Update Prescription</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
