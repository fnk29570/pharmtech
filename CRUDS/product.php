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
    // Add New Product
    if (isset($_POST["addProductBtn"])) {
        $productName = $_POST['productName'];
        $productDescription = $_POST['productDescription'];
        $productQuantity = (int) $_POST['productQuantity'];
        $productExpiry = $_POST['productExpiry'];
        $productPrice = $_POST['productPrice'];

        // File upload handling
        $imageData = null;
        if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == UPLOAD_ERR_OK) {
            $fileTmpName = $_FILES['productImage']['tmp_name'];
            $imageData = file_get_contents($fileTmpName);
        }

        // Prepare and execute the SQL statement
        $sql = "INSERT INTO products (name, description, quantity, expiry_date, price, image) VALUES (:name, :description, :quantity, :expiry_date, :price, :image)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $productName);
        $stmt->bindParam(':description', $productDescription);
        $stmt->bindParam(':quantity', $productQuantity);
        $stmt->bindParam(':expiry_date', $productExpiry);
        $stmt->bindParam(':price', $productPrice);
        $stmt->bindParam(':image', $imageData, PDO::PARAM_LOB);

        // Execute the statement
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    }

    // Update Product
    if (isset($_POST["editProductBtn"])) {
        $productId = (int) $_POST['editProductId'];
        $productName = $_POST['editProductName'];
        $productDescription = $_POST['editProductDescription'];
        $productQuantity = (int) $_POST['editProductQuantity'];
        $productExpiry = $_POST['editProductExpiry'];
        $productPrice = $_POST['editProductPrice'];

        // Prepare base update query
        $sql = "UPDATE products SET name = :name, description = :description, quantity = :quantity, expiry_date = :expiry_date, price = :price";
        $params = [
            ':name' => $productName,
            ':description' => $productDescription,
            ':quantity' => $productQuantity,
            ':expiry_date' => $productExpiry,
            ':price' => $productPrice
        ];

        // File upload handling
        if (isset($_FILES['editProductImage']) && $_FILES['editProductImage']['error'] == UPLOAD_ERR_OK) {
            $fileTmpName = $_FILES['editProductImage']['tmp_name'];
            $imageData = file_get_contents($fileTmpName);
            $sql .= ", image = :image";
            $params[':image'] = $imageData;
        }

        $sql .= " WHERE id = :id";
        $params[':id'] = $productId;

        // Prepare and execute the SQL statement
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute($params)) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    }

    // Delete Product
    if (isset($_POST["deleteProductBtn"])) {
        $productId = (int) $_POST['deleteProductId'];

        // Prepare and execute the SQL statement
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
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
    <style>
        .product-img {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Search Bar -->
        <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by product name" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-outline-dark" type="submit" >Search</button>
            </div>
        </form>

        <!-- Button trigger modal -->
        <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">
            + New Product
        </button>

        <!-- Modal for Adding Product -->
        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="productName" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="productName" name="productName" required>
                            </div>
                            <div class="mb-3">
                                <label for="productDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="productDescription" name="productDescription"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="productQuantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="productQuantity" name="productQuantity" required>
                            </div>
                            <div class="mb-3">
                                <label for="productExpiry" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" id="productExpiry" name="productExpiry">
                            </div>
                            <div class="mb-3">
                                <label for="productPrice" class="form-label">Price</label>
                                <input type="number" class="form-control" id="productPrice" name="productPrice" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label for="productImage" class="form-label">Image</label>
                                <input type="file" class="form-control" id="productImage" name="productImage" accept="image/*" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="addProductBtn" class="btn btn-primary">Add Product</button>
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
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Expiry Date</th>
                        <th>Price</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['id']); ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                                <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($product['expiry_date']); ?></td>
                                <td><?php echo htmlspecialchars($product['price']); ?></td>
                                <td>
                                    <?php if ($product['image']): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($product['image']); ?>" class="product-img" />
                                    <?php else: ?>
                                        No Image
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Edit Button Trigger -->
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editProductModal<?php echo htmlspecialchars($product['id']); ?>">Edit</button>
                                    
                                    <!-- Delete Button -->
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display:inline;">
                                        <input type="hidden" name="deleteProductId" value="<?php echo htmlspecialchars($product['id']); ?>">
                                        <button type="submit" name="deleteProductBtn" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal for Editing Product -->
                            <div class="modal fade" id="editProductModal<?php echo htmlspecialchars($product['id']); ?>" tabindex="-1" aria-labelledby="editProductModalLabel<?php echo htmlspecialchars($product['id']); ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editProductModalLabel<?php echo htmlspecialchars($product['id']); ?>">Edit Product</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="editProductId" value="<?php echo htmlspecialchars($product['id']); ?>">
                                                <div class="mb-3">
                                                    <label for="editProductName<?php echo htmlspecialchars($product['id']); ?>" class="form-label">Product Name</label>
                                                    <input type="text" class="form-control" id="editProductName<?php echo htmlspecialchars($product['id']); ?>" name="editProductName" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="editProductDescription<?php echo htmlspecialchars($product['id']); ?>" class="form-label">Description</label>
                                                    <textarea class="form-control" id="editProductDescription<?php echo htmlspecialchars($product['id']); ?>" name="editProductDescription"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="editProductQuantity<?php echo htmlspecialchars($product['id']); ?>" class="form-label">Quantity</label>
                                                    <input type="number" class="form-control" id="editProductQuantity<?php echo htmlspecialchars($product['id']); ?>" name="editProductQuantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="editProductExpiry<?php echo htmlspecialchars($product['id']); ?>" class="form-label">Expiry Date</label>
                                                    <input type="date" class="form-control" id="editProductExpiry<?php echo htmlspecialchars($product['id']); ?>" name="editProductExpiry" value="<?php echo htmlspecialchars($product['expiry_date']); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="editProductPrice<?php echo htmlspecialchars($product['id']); ?>" class="form-label">Price</label>
                                                    <input type="number" class="form-control" id="editProductPrice<?php echo htmlspecialchars($product['id']); ?>" name="editProductPrice" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="editProductImage<?php echo htmlspecialchars($product['id']); ?>" class="form-label">New Image (optional)</label>
                                                    <input type="file" class="form-control" id="editProductImage<?php echo htmlspecialchars($product['id']); ?>" name="editProductImage" accept="image/*">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" name="editProductBtn" class="btn btn-primary">Update Product</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
