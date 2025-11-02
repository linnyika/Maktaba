<?php
// user/place_order.php
include('../../database/config.php');
include('../../includes/session_check.php');

$message = "";

// Handle order form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = intval($_POST['book_id']);
    $quantity = intval($_POST['quantity']);
    $user_id = $_SESSION['user_id'];

    // Get book details
    $stmt = $conn->prepare("SELECT title, price FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();

    if (!$book) {
        $message = " Invalid book selected.";
    } else {
        $total = $book['price'] * $quantity;

        // Insert into orders table
        $insert = $conn->prepare("INSERT INTO orders (user_id, book_id, quantity, total_amount, order_status, payment_status) VALUES (?, ?, ?, ?, 'Pending', 'Unpaid')");
        $insert->bind_param("iiid", $user_id, $book_id, $quantity, $total);

        if ($insert->execute()) {
            $order_id = $insert->insert_id;
            header("Location: ../api/mpesa_payment.php?order_id=" . $order_id);
            exit();
        } else {
            $message = "❌ Failed to create order.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Place Order - Maktaba</title>
    <link rel="stylesheet" href="../assets/css/user.css">
</head>
<body>
<?php include("../../includes/user_nav.php"); ?>
    <div class="container">
        <h2>📚 Place a Book Order</h2>
        <?php if ($message): ?>
            <p style="color:red;"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="book_id">Select Book:</label><br>
            <select name="book_id" required>
                <option value="">-- Choose a Book --</option>
                <?php
                $books = $conn->query("SELECT book_id, title, price FROM books");
                while ($row = $books->fetch_assoc()) {
                    echo "<option value='{$row['book_id']}'>{$row['title']} (Ksh {$row['price']})</option>";
                }
                ?>
            </select><br><br>

            <label for="quantity">Quantity:</label><br>
            <input type="number" name="quantity" min="1" value="1" required><br><br>

            <button type="submit">Proceed to Payment</button>
        </form>

        <br>
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</body>
</html>
