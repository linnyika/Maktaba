<?php
include '../../includes/admin_check.php';
include '../../database/config.php';

$query = "
    SELECT 
        o.order_id, 
        o.user_id, 
        u.full_name, 
        o.total_amount, 
        o.order_status, 
        o.order_date 
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="container">
        <h2>Manage Orders</h2>

        <table border="1" cellpadding="10" cellspacing="0">
            <tr>
                <th>Order ID</th>
                <th>User</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Order Date</th>
                <th>Action</th>
            </tr>

            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['order_id']) ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td>KES <?= htmlspecialchars($row['total_amount']) ?></td>
                        <td><?= htmlspecialchars($row['order_status']) ?></td>
                        <td><?= htmlspecialchars($row['order_date']) ?></td>
                        <td>
                            <form action="update_order_status.php" method="POST">
                                <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                <select name="order_status">
                                    <option value="Pending" <?= $row['order_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Processing" <?= $row['order_status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="Completed" <?= $row['order_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="Cancelled" <?= $row['order_status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button type="submit">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No orders found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
