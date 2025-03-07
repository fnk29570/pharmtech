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

// Function to check if user is logged in
function checkLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.html'); // Redirect to login page if not logged in
        exit;
    }
}

// Ensure the user is logged in
checkLoggedIn();

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $user_id = $_SESSION['user_id'];

    // Prepare and execute the deletion query
    $sql = "DELETE FROM saved_products WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);

    if ($stmt->execute()) {
        echo "Product deleted successfully";
    } else {
        echo "Error deleting product: " . $stmt->error;
    }

    $stmt->close();
}

// Retrieve the search term if available
$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';

// Fetch saved products from database
$user_id = $_SESSION['user_id'];

// Modify query to include search functionality
$sql = "SELECT * FROM saved_products WHERE user_id = ? AND (name LIKE ? OR description LIKE ?) ORDER BY saved_at DESC";
$stmt = $conn->prepare($sql);

// Use wildcard for partial match in search
$searchTermLike = '%' . $searchTerm . '%';
$stmt->bind_param("iss", $user_id, $searchTermLike, $searchTermLike);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmtech</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        .product-image {
            width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    
    <div class="container mt-5">
        <h2 class="mb-4"></h2>

        <!-- Search Form -->
        <form method="POST" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Saved At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td>
                                <?php if (!empty($row['image'])): ?>
                                    <?php
                                    // Create a base64-encoded image src URL
                                    $imageData = base64_encode($row['image']);
                                    $imageSrc = 'data:image/jpeg;base64,' . htmlspecialchars($imageData);
                                    ?>
                                    <img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-image">
                                <?php else: ?>
                                    <img src="path/to/placeholder.jpg" alt="No Image" class="product-image">
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['saved_at']); ?></td>
                            <td>
                                <!-- Delete Button -->
                                <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo htmlspecialchars($row['id']); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No saved products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Click handler for the delete button
        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            if (confirm('Are you sure you want to delete this product?')) {
                $.ajax({
                    type: 'POST',
                    url: 'save_product.php', // Current file URL
                    data: {
                        delete_id: id
                    },
                    success: function(response) {
                        alert('Product deleted successfully!');
                        location.reload(); // Reload the page to reflect changes
                    },
                    error: function(xhr, status, error) {
                        console.error('Error deleting product:', error);
                    }
                });
            }
        });
    });
    </script>
</body>
</html>

<?php
// Close the statement and connection
$stmt->close();
$conn->close();
?>
