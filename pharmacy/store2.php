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

// Function to check if logged-in user is a patient
function checkPatient() {
    checkLoggedIn();

    global $conn;

    // Fetch user details
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT id FROM patients WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $num_rows = $stmt->num_rows;
    $stmt->close();

    // Return true if the user is a patient, false otherwise
    return $num_rows > 0;
}

// Fetch products from database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmtech</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
         .product-card {
            width: 100%; /* Full width of the column */
            max-width: 300px; /* Fixed maximum width for the card */
            height: 450px; /* Fixed height for all cards */
            margin: 10px; /* Space between cards */
            border-radius: 10px; /* Rounded corners */
            overflow: hidden; /* Hide overflow content */
            display: flex;
            flex-direction: column; /* Stack children vertically */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Optional: Add shadow for card effect */
            text-align: center; /* Center text inside card */
            
        }

        .product-card img {
            width: 100%;
            height: 200px; /* Fixed height for the image */
            object-fit: contain; /* Cover the area without distortion */
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
           
        }

        .card-body {
            flex: 1; /* Allow the card body to grow and take up remaining space */
            padding: 15px;
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Space out the title, text, and buttons */
        }

        .card-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .card-text {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }

        .card-price {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
        }

        .btn-group {
            display: flex;
            flex-direction: column; /* Stack buttons vertically */
            gap: 50px; /* Space between buttons */
        }

        .btn-group .btn {
            width: 100%; /* Make buttons full width */
        }

        @media (min-width: 576px) {
            .product-card {
                max-width: 280px; /* Adjust for smaller screens */
            }
        }

        @media (min-width: 768px) {
            .product-card {
                max-width: 260px; /* Adjust for medium screens */
            }
        }

        @media (min-width: 992px) {
            .product-card {
                max-width: 240px; /* Adjust for larger screens */
            }
        }

        @media (min-width: 1200px) {
            .product-card {
                max-width: 300px; /* Keep larger size on extra-large screens */
            }
        }

        .offcanvas-content {
            max-height: calc(100vh - 60px);
            overflow-y: auto;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
        }

        .offcanvas-toggle {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1031;
        }
        
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4">Our Products</h2>
        <!-- Search Bar -->
        <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by product name" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-outline-dark" type="submit" >Search</button>
            </div>
        </form>
        <div class="row">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Check if image data exists
                    if (!empty($row['image'])) {
                        // Encode image data to Base64
                        $imageData = base64_encode($row['image']);
                        $imageSrc = 'data:image/jpeg;base64,' . htmlspecialchars($imageData);
                    } else {
                        // Use a placeholder image if no image is available
                        $imageSrc = 'path/to/placeholder.jpg'; // Replace with actual path or URL
                    }
            ?>
                    <div class="col-md-4">
                        <div class="card product-card" style="width: 18rem;">
                            <!-- Display the image -->
                            <img src="<?php echo $imageSrc; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                                <p class="card-text"><strong>Price: $<?php echo number_format($row['price'], 2); ?></strong></p>
                                <?php if (checkPatient()): ?>
                                <button class="btn btn-primary add-to-cart" 
                                        data-id="<?php echo $row['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                        data-price="<?php echo $row['price']; ?>">
                                    Add to Cart
                                </button>
                                <?php endif; ?>
                                <!-- Button trigger modal -->
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal<?php echo $row['id']; ?>">
                                    Prescription
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="exampleModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel<?php echo $row['id']; ?>">Prescription for <?php echo htmlspecialchars($row['name']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                // Fetch prescription for the product
                                                $productId = $row['id'];
                                                $prescriptionSql = "SELECT prescription FROM products WHERE id = $productId";
                                                $prescriptionResult = $conn->query($prescriptionSql);

                                                if ($prescriptionResult->num_rows > 0) {
                                                    $prescriptionRow = $prescriptionResult->fetch_assoc();
                                                    echo htmlspecialchars($prescriptionRow['prescription']);
                                                } else {
                                                    echo "No prescription available.";
                                                }
                                                ?>
                                            </div>
                                            <div class="modal-footer">
                <?php if (checkPatient()): ?>
                    <button type="button" class="btn btn-secondary" id="saveBtn<?php echo $row['id']; ?>" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>" data-description="<?php echo htmlspecialchars($row['description']); ?>" data-image="<?php echo $imageSrc; ?>">Save</button>
                <?php endif; ?>
            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "<p>No products found.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Offcanvas Menu -->
    <?php if (checkPatient()): ?>
    <button class="btn btn-primary offcanvas-toggle" type="button" data-bs-toggle="offcanvas"
        data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart-plus"
            viewBox="0 0 16 16">
            <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9z" />
            <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zm3.915 10L3.102 4h10.796l-1.313 7zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0m7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0" />
        </svg>
        Cart
        <span class="badge bg-secondary cart-count">0</span>
    </button>
    <?php endif; ?>

    <!-- Offcanvas Menu -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">Cart</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="offcanvas-content">
                <div id="cart-items">
                    <!-- Cart items will be dynamically added here -->
                </div>
                <div class="total mt-3">
                    <h5>Total: <span id="cart-total">$0.00</span></h5>
                </div>
                <button id="checkoutBtn" class="btn btn-primary mt-3">Checkout</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
  $(document).ready(function () {
    var cart = []; // Array to store cart items

    // Add to Cart button click handler
    $('.add-to-cart').click(function () {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var price = parseFloat($(this).data('price'));

        // Check if item already exists in cart
        var existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            // Item already exists, increment quantity
            existingItem.quantity++;
        } else {
            // Item doesn't exist, add as new entry
            var newItem = {
                id: id,
                name: name,
                price: price,
                quantity: 1 // Initial quantity
            };
            cart.push(newItem);
        }

        updateCart();
    });

    // Function to update the cart display
    function updateCart() {
        $('#cart-items').empty();
        var total = 0;

        cart.forEach(function (item) {
            var itemHtml = `
                <div class="cart-item">
                    <div class="cart-item-details">
                        <span>${item.name}</span> - $${item.price.toFixed(2)} 
                        <span class="text-muted"> x ${item.quantity}</span>
                    </div>
                    <div class="cart-item-actions">
                        <button class="btn btn-danger btn-sm remove-from-cart" data-id="${item.id}">Remove</button>
                    </div>
                </div>
            `;
            $('#cart-items').append(itemHtml);
            total += item.price * item.quantity; // Calculate total price
        });

        $('#cart-total').text('$' + total.toFixed(2)); // Update total price
        $('#amount').val(total.toFixed(2)); // Update amount field in payment form

        $('.cart-count').text(cart.length); // Update cart count
    }

    // Remove from Cart button click handler
    $(document).on('click', '.remove-from-cart', function () {
        var id = $(this).data('id');
        var itemIndex = cart.findIndex(item => item.id === id);

        if (itemIndex !== -1) {
            // Decrease quantity by 1
            if (cart[itemIndex].quantity > 1) {
                cart[itemIndex].quantity--;
            } else {
                // If quantity is 1, remove the item from cart
                cart.splice(itemIndex, 1);
            }
            updateCart();
        }
    });

    // Checkout button click handler
    $('#checkoutBtn').click(function () {
    if (cart.length > 0) {
        var totalAmount = $('#cart-total').text();
        // Redirect to the new HTML page with the total amount
        window.location.href = 'payment_information.html?amount=' + encodeURIComponent(totalAmount);
    } else {
        alert('Your cart is empty. Add some items before checking out.');
    }
});


    // Payment form submission handler
    $('#paymentForm').submit(function (event) {
        event.preventDefault();

        var phoneNumber = $('#phone').val();
        var totalAmount = $('#amount').val();
        var address = $('#address').val();

        // Perform AJAX call to backend to initiate payment
        $.ajax({
            url: 'process_payment.php',
            type: 'POST',
            dataType: 'json',
            data: {
                phone: phoneNumber,
                amount: totalAmount,
                address: address
            },
            success: function (response) {
                // Handle successful response
                alert('Payment successful! Transaction ID: ' + response.transactionId);
                // Redirect to thank you page
                window.location.href = 'thank_you.html';
            },
            error: function (xhr, status, error) {
                // Handle errors
                alert('Payment failed: ' + xhr.responseText);
            }
        });
    });
    $(document).ready(function () {
    $('.btn-secondary').click(function () {
        var button = $(this);
        var productId = button.data('id');
        var name = button.data('name');
        var description = button.data('description');
        var image = button.data('image');

        $.ajax({
            type: 'POST',
            url: 'save_product.php',
            data: {
                product_id: productId,
                name: name,
                description: description,
                image: image
            },
            success: function (response) {
                var result = JSON.parse(response);
                if (result.status === 'success') {
                    alert(result.message);
                } else {
                    alert(result.message);
                }
            },
            error: function () {
                alert('An error occurred while saving the product.');
            }
        });
    });
});

    // Toggle offcanvas cart
    $('.offcanvas-toggle').click(function () {
        $('.offcanvas').toggleClass('show');
    });
});


</script>
</body>
</html>

