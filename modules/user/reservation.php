<?php
// user/reservations.php
include('../includes/config.php');
include('../includes/session_check.php');

$message = "";
$payment_link = "";

// When form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'];
    $pickup_date = $_POST['pickup_date'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO reservations (user_id, book_id, pickup_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $book_id, $pickup_date);

    if ($stmt->execute()) {
        $reservation_id = $stmt->insert_id; // ✅ Get the ID of the new reservation

        $message = "✅ Reservation placed successfully!";
        // ✅ Create link to M-Pesa payment for this reservation
        $payment_link = "<a href='../includes/mpesa_api.php?reservation_id={$reservation_id}' 
                           style='background:#34b233; color:white; padding:10px 15px; border-radius:5px; text-decoration:none;'>
                           Pay with M-Pesa
                         </a>";
    } else {
        $message = "❌ Failed to place reservation.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reserve a Book</title>
    <link rel="stylesheet" href="../assets/css/user.css">
</head>
<body>
    <h2>Reserve a Book</h2>
    <p style="color:green;"><?php echo $message; ?></p>

    <!-- ✅ Show payment link only after a successful reservation -->
    <?php if (!empty($payment_link)) echo $payment_link; ?>

    <form method="POST">
        <label>Select Book:</label><br>
        <select name="book_id" required>
            <option value="">-- Choose Book --</option>
            <?php
            $books = $conn->query("SELECT book_id, title FROM books");
            while ($row = $books->fetch_assoc()) {
                echo "<option value='{$row['book_id']}'>{$row['title']}</option>";
            }
            ?>
        </select><br><br>

        <label>Pickup Date:</label><br>
        <input type="date" name="pickup_date" required><br><br>

        <button type="submit">Reserve</button>
    </form>

    <br><a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
