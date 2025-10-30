<?php
// user/reservations.php
include('../includes/config.php');
include('../includes/session_check.php');

$message = "";

// When form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'];
    $pickup_date = $_POST['pickup_date'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO reservations (user_id, book_id, pickup_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $book_id, $pickup_date);

    if ($stmt->execute()) {
        $message = "✅ Reservation placed successfully!";
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
