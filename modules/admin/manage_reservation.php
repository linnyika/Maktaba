<?php
// admin/manage_reservations.php
include('../includes/config.php');
include('../includes/session_check.php');

// Restrict to admin users only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

// --- Handle admin actions ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'confirm') {
        $conn->query("UPDATE reservations SET status='Confirmed' WHERE reservation_id=$id");
        $message = "✅ Reservation confirmed successfully!";
    } elseif ($action === 'cancel') {
        $conn->query("UPDATE reservations SET status='Cancelled' WHERE reservation_id=$id");
        $message = "❌ Reservation cancelled successfully!";
    } elseif ($action === 'delete') {
        $conn->query("DELETE FROM reservations WHERE reservation_id=$id");
        $message = "🗑️ Reservation deleted successfully!";
    }
}

// --- Fetch reservations ---
$query = "
    SELECT r.*, b.title, c.full_name 
    FROM reservations r
    JOIN books b ON r.book_id = b.book_id
    JOIN customers c ON r.user_id = c.customer_id
    ORDER BY r.reservation_date DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Reservations</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #333;
            color: #fff;
        }
        .btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
        }
        .btn-confirm { background: #28a745; }
        .btn-cancel { background: #dc3545; }
        .btn-delete { background: #6c757d; }
        .message {
            margin: 15px 0;
            color: green;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h2>📚 Manage Reservations</h2>
        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Book</th>
                    <th>Pickup Date</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Reservation Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['reservation_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo $row['pickup_date']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td><?php echo $row['payment_status'] ?? 'Pending'; ?></td>
                        <td><?php echo $row['reservation_date']; ?></td>
                        <td>
                            <?php if ($row['status'] === 'Pending'): ?>
                                <a href="?action=confirm&id=<?php echo $row['reservation_id']; ?>" class="btn btn-confirm">Confirm</a>
                                <a href="?action=cancel&id=<?php echo $row['reservation_id']; ?>" class="btn btn-cancel">Cancel</a>
                            <?php endif; ?>
                            <a href="?action=delete&id=<?php echo $row['reservation_id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this reservation?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <br>
        <a href="dashboard.php" class="btn btn-confirm">⬅ Back to Dashboard</a>
    </div>
</body>
</html>
