<?php
// /Maktaba/user/reservations.php

include('../../database/config.php');
include('../../includes/session_check.php');


$message = "";
$payment_link = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'];
    $pickup_date = $_POST['pickup_date'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO reservations (user_id, book_id, pickup_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $book_id, $pickup_date);

    if ($stmt->execute()) {
        $reservation_id = $stmt->insert_id;

        $message = "Reservation placed successfully!";
        $payment_link = "<a href='../includes/mpesa_api.php?reservation_id={$reservation_id}' 
                           class='btn btn-success mt-3'>Pay with M-Pesa</a>";
    } else {
        $message = "Failed to place reservation. Please try again.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reserve a Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/user.css">
</head>
<body>
    <?php include("../../includes/user_nav.php"); ?>

    <div class="container mt-5">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h2 class="text-center mb-4">Reserve a Book</h2>

                <!-- ✅ Message -->
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'danger'; ?> text-center">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- ✅ Payment Link -->
                <?php if (!empty($payment_link)): ?>
                    <div class="text-center mb-4">
                        <?php echo $payment_link; ?>
                    </div>
                <?php endif; ?>

                <!-- ✅ Reservation Form -->
                <form method="POST" class="p-3" style="max-width:500px; margin:auto;">
                    <div class="mb-3">
                        <label for="book_id" class="form-label">Select Book</label>
                        <select name="book_id" id="book_id" class="form-select" required>
                            <option value="">-- Choose Book --</option>
                            <?php
                            $books = $conn->query("SELECT book_id, title FROM books ORDER BY title ASC");
                            while ($row = $books->fetch_assoc()) {
                                echo "<option value='{$row['book_id']}'>{$row['title']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="pickup_date" class="form-label">Pickup Date</label>
                        <input type="date" name="pickup_date" id="pickup_date" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Reserve</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
