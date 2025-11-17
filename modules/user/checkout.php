<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../database/config.php';
require_once __DIR__ . '/../../includes/audit_helper.php';
require_once __DIR__ . '/../../includes/notification_helper.php';

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'];
$total_price = 0;

// Calculate total cart amount
foreach ($_SESSION['cart'] as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

$shipping_fee = 200;
$tax = $total_price * 0.16;
$grand_total = $total_price + $shipping_fee + $tax;

// Get user details including phone number from database
$user_query = $conn->prepare("SELECT full_name, phone FROM users WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user_data = $user_result->fetch_assoc();
$user_query->close();

$user_full_name = $user_data['full_name'] ?? '';
$user_phone = $user_data['phone'] ?? '';

// Function to record payment
function recordPayment($order_id, $user_id, $amount, $payment_method, $status = 'Completed', $mpesa_phone = null, $mpesa_transaction_id = null, $result_desc = null) {
    global $conn;
    
    $stmt = $conn->prepare("
        INSERT INTO payments (order_id, user_id, amount, payment_method, payment_status, mpesa_phone, mpesa_transaction_id, result_desc) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iidsssss", $order_id, $user_id, $amount, $payment_method, $status, $mpesa_phone, $mpesa_transaction_id, $result_desc);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = $_POST['shipping_address'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'M-Pesa';
    $phone_number = $_POST['phone_number'] ?? $user_phone; // Get phone from form or user data
    
    if (empty($shipping_address)) {
        $_SESSION['error'] = "Please enter your shipping address";
        header("Location: checkout.php");
        exit;
    }
    
    if (empty($phone_number)) {
        $_SESSION['error'] = "Please enter your phone number";
        header("Location: checkout.php");
        exit;
    }

    // Generate transaction ID based on payment method
    $transaction_id = '';
    
    switch($payment_method) {
        case 'mpesa':
            $payment_method_db = 'M-Pesa';
            $transaction_id = 'MPE' . date('YmdHis') . rand(100, 999);
            break;
        case 'cash':
            $payment_method_db = 'Cash';
            $transaction_id = 'CASH' . date('YmdHis') . rand(100, 999);
            break;
        case 'card':
            $payment_method_db = 'Card';
            $transaction_id = 'CARD' . date('YmdHis') . rand(100, 999);
            break;
        default:
            $payment_method_db = 'M-Pesa';
            $transaction_id = 'MPE' . date('YmdHis') . rand(100, 999);
    }

    // 1️⃣ Insert order into the database WITH SHIPPING ADDRESS
    $insertOrder = $conn->prepare("
        INSERT INTO orders (user_id, shipping_address, total_amount, order_status, payment_status)
        VALUES (?, ?, ?, 'Pending', 'Paid')
    ");
    $insertOrder->bind_param('isd', $user_id, $shipping_address, $grand_total);
    
    if (!$insertOrder->execute()) {
        $_SESSION['error'] = "Failed to create order. Please try again.";
        header("Location: checkout.php");
        exit;
    }
    
    $order_id = $conn->insert_id;
    $insertOrder->close();

    // 2️⃣ Insert all items from the cart
    $insertItem = $conn->prepare("
        INSERT INTO order_items (order_id, book_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($_SESSION['cart'] as $book_id => $item) {
        $insertItem->bind_param('iiid', $order_id, $book_id, $item['quantity'], $item['price']);
        if (!$insertItem->execute()) {
            $_SESSION['error'] = "Failed to add order items. Please try again.";
            header("Location: checkout.php");
            exit;
        }

        // Reduce stock in books table
        $conn->query("
            UPDATE books 
            SET stock_quantity = GREATEST(stock_quantity - {$item['quantity']}, 0)
            WHERE book_id = $book_id
        ");
    }
    $insertItem->close();

    // 3️⃣ RECORD PAYMENT IN PAYMENTS TABLE
    $payment_recorded = recordPayment(
        $order_id, 
        $user_id, 
        $grand_total, 
        $payment_method_db, 
        'Completed', 
        $payment_method_db === 'M-Pesa' ? $phone_number : null, 
        $transaction_id, 
        'Book purchase payment successful - Order #' . $order_id
    );

    // 4️⃣ Log activity & notify
    $action = 'Checkout by User: Placed Order #' . $order_id . ' (KSh ' . number_format($grand_total, 2) . ') via ' . $payment_method_db;
    logActivity($conn, $user_id, $action);
    addNotification($user_id, 'Your order #' . $order_id . ' has been placed successfully!');

    // Also notify admin about new order
    addNotification(1, 'New Order #' . $order_id . ' received from ' . $user_full_name . ' - KSh ' . number_format($grand_total, 2) . ' via ' . $payment_method_db);

    // 5️⃣ Clear cart
    $_SESSION['cart'] = [];

    // 6️⃣ Redirect to confirmation
    header("Location: confirmation.php?order_id=$order_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Maktaba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/user.css">
    <style>
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-method:hover {
            border-color: #8b5a2b;
        }
        .payment-method.selected {
            border-color: #8b5a2b;
            background-color: #f8f9fa;
        }
        .payment-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        .sticky-summary {
            position: sticky;
            top: 20px;
        }
        .book-cover {
            width: 60px;
            height: 80px;
            object-fit: cover;
        }
        .form-control:read-only {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
<?php include("../../includes/user_nav.php"); ?>

<div class="container my-5">
    <h3 class="text-primary fw-bold mb-4"><i class="bi bi-credit-card"></i> Checkout</h3>

    <!-- Error Message -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-cart-check"></i> Order Items (<?php echo count($_SESSION['cart']); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($_SESSION['cart'] as $book_id => $item): ?>
                        <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                            <div class="d-flex align-items-center">
                                <img src="../../assets/book_covers/<?php echo $item['cover_image'] ?? 'default.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                     class="rounded me-3 book-cover">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['title']); ?></h6>
                                    <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                                    <br>
                                    <small class="text-muted">Price: KSh <?php echo number_format($item['price'], 2); ?> each</small>
                                </div>
                            </div>
                            <span class="fw-bold">KSh <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-truck"></i> Shipping Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="checkout-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user_full_name); ?>" required>
                                <div class="form-text">Your full name for delivery</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="phone_number" value="<?php echo htmlspecialchars($user_phone); ?>" placeholder="2547XXXXXXXX" required>
                                <div class="form-text">Your phone number for delivery updates</div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Shipping Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" rows="3" placeholder="Enter your complete shipping address (Street, City, Postal Code)" name="shipping_address" required><?php echo $_POST['shipping_address'] ?? ''; ?></textarea>
                                <div class="form-text">Please provide your complete address for delivery</div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-credit-card"></i> Payment Method</h5>
                </div>
                <div class="card-body">
                    <div class="payment-method selected" onclick="selectPayment('mpesa')">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="mpesa" value="mpesa" checked>
                            <label class="form-check-label fw-bold" for="mpesa">
                                <i class="bi bi-phone payment-icon text-success"></i>
                                M-Pesa
                            </label>
                        </div>
                        <p class="text-muted mb-0 mt-2">Pay securely via M-Pesa. You will receive a prompt on your phone.</p>
                    </div>

                    <div class="payment-method" onclick="selectPayment('cash')">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash">
                            <label class="form-check-label fw-bold" for="cash">
                                <i class="bi bi-cash payment-icon text-warning"></i>
                                Cash on Delivery
                            </label>
                        </div>
                        <p class="text-muted mb-0 mt-2">Pay when your order is delivered.</p>
                    </div>

                    <div class="payment-method" onclick="selectPayment('card')">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="card" value="card">
                            <label class="form-check-label fw-bold" for="card">
                                <i class="bi bi-credit-card payment-icon text-primary"></i>
                                Credit/Debit Card
                            </label>
                        </div>
                        <p class="text-muted mb-0 mt-2">Pay securely with your credit or debit card.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm sticky-summary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal (<?php echo count($_SESSION['cart']); ?> items):</span>
                        <strong>KSh <?php echo number_format($total_price, 2); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping Fee:</span>
                        <strong>KSh <?php echo number_format($shipping_fee, 2); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (16%):</span>
                        <strong>KSh <?php echo number_format($tax, 2); ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">Total Amount:</span>
                        <strong class="text-success fs-5">KSh <?php echo number_format($grand_total, 2); ?></strong>
                    </div>

                    <div class="mt-3">
                        <input type="hidden" name="payment_method" id="payment_method_input" value="mpesa">
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Method Selected:</label>
                            <div class="alert alert-info py-2">
                                <i class="bi bi-phone text-success"></i>
                                <strong id="selected-method">M-Pesa</strong>
                            </div>
                        </div>
                        
                        <button type="submit" form="checkout-form" class="btn btn-success w-100 btn-lg py-3">
                            <i class="bi bi-lock-fill"></i> 
                            <span id="pay-button-text">Pay KSh <?php echo number_format($grand_total, 2); ?> via M-Pesa</span>
                        </button>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="bi bi-shield-check"></i> Your payment is secure and encrypted
                            </small>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <small>
                            <i class="bi bi-info-circle"></i> 
                            By completing this purchase, you agree to our 
                            <a href="#" class="alert-link">Terms of Service</a> and 
                            <a href="#" class="alert-link">Privacy Policy</a>.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="bg-primary text-white text-center py-3 mt-auto">
    <small>&copy; <?php echo date('Y'); ?> Maktaba Bookstore</small>
</footer>

<script>
function selectPayment(method) {
    // Remove selected class from all payment methods
    document.querySelectorAll('.payment-method').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Add selected class to clicked method
    event.currentTarget.classList.add('selected');
    
    // Update radio button and hidden input
    document.getElementById(method).checked = true;
    document.getElementById('payment_method_input').value = method;
    
    // Update display
    const methodNames = {
        'mpesa': 'M-Pesa',
        'cash': 'Cash on Delivery',
        'card': 'Credit/Debit Card'
    };
    
    const methodIcons = {
        'mpesa': 'bi-phone text-success',
        'cash': 'bi-cash text-warning',
        'card': 'bi-credit-card text-primary'
    };
    
    const selectedMethodElement = document.getElementById('selected-method');
    selectedMethodElement.innerHTML = `<i class="bi ${methodIcons[method]}"></i> ${methodNames[method]}`;
    
    document.getElementById('pay-button-text').textContent = 
        method === 'cash' 
            ? `Place Order - KSh <?php echo number_format($grand_total, 2); ?> (Pay on Delivery)` 
            : `Pay KSh <?php echo number_format($grand_total, 2); ?> via ${methodNames[method]}`;
}

// Initialize payment method selection
document.addEventListener('DOMContentLoaded', function() {
    selectPayment('mpesa');
    
    // Add form validation
    const form = document.getElementById('checkout-form');
    form.addEventListener('submit', function(e) {
        const shippingAddress = document.querySelector('textarea[name="shipping_address"]').value.trim();
        const phoneNumber = document.querySelector('input[name="phone_number"]').value.trim();
        const fullName = document.querySelector('input[name="full_name"]').value.trim();
        
        if (!shippingAddress) {
            e.preventDefault();
            alert('Please enter your shipping address');
            return false;
        }
        
        if (!phoneNumber) {
            e.preventDefault();
            alert('Please enter your phone number');
            return false;
        }
        
        if (!fullName) {
            e.preventDefault();
            alert('Please enter your full name');
            return false;
        }
        
        // Add payment method to form data
        const paymentMethodInput = document.createElement('input');
        paymentMethodInput.type = 'hidden';
        paymentMethodInput.name = 'payment_method';
        paymentMethodInput.value = document.getElementById('payment_method_input').value;
        form.appendChild(paymentMethodInput);
    });
});
</script>
</body>
</html>