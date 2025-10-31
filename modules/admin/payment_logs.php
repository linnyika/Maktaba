<?php
// admin/payment_logs.php
include('../includes/config.php');
include('../includes/admin_check.php'); // ensure only admins can view

// Optional search filter
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "
    SELECT 
        p.payment_id,
        p.payment_date,
        p.payment_method,
        p.payment_status,
        p.amount,
        p.mpesa_phone,
        p.mpesa_transaction_id,
        p.result_desc,
        CASE
            WHEN o.order_id IS NOT NULL THEN 'Order'
            WHEN r.reservation_id IS NOT NULL THEN 'Reservation'
            ELSE 'Unknown'
        END AS payment_type
    FROM payments p
    LEFT JOIN orders o ON p.order_id = o.order_id
    LEFT JOIN reservations r ON p.order_id = r.reservation_id
    WHERE p.mpesa_transaction_id LIKE ? 
       OR p.payment_status LIKE ?
       OR p.payment_method LIKE ?
    ORDER BY p.payment_date DESC
";

$stmt = $conn->prepare($query);
$param = "%$search%";
$stmt->bind_param("sss", $param, $param, $param);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Logs - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="admin-container">
    <header class="admin-header">
        <h2>💰 Payment Logs</h2>
        <nav>
            <a href="dashboard.php" class="btn-secondary">← Back to Dashboard</a>
        </nav>
    </header>

    <section class="admin-content">
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search transaction ID, status..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn-primary">Search</button>
        </form>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Amount (Ksh)</th>
                        <th>Phone</th>
                        <th>Transaction ID</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['payment_id']; ?></td>
                            <td><?php echo $row['payment_date']; ?></td>
                            <td><?php echo $row['payment_type']; ?></td>
                            <td><?php echo $row['payment_method']; ?></td>
                            <td class="<?php echo strtolower($row['payment_status']); ?>">
                                <?php echo $row['payment_status']; ?>
                            </td>
                            <td><?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['mpesa_phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['mpesa_transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['result_desc']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" style="text-align:center;">No payments found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

</body>
</html>
