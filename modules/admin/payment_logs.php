<?php
// admin/payment_logs.php
include('../includes/config.php');
include('../includes/session_check.php');
include('../includes/admin_check.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Logs - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container">
    <h2>Payment Logs</h2>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Amount</th>
            <th>Phone</th>
            <th>Transaction ID</th>
            <th>Status</th>
            <th>Date</th>
        </tr>

        <?php
        $result = $conn->query("
            SELECT pl.*, c.full_name 
            FROM payment_logs pl
            JOIN customers c ON pl.user_id = c.customer_id
            ORDER BY pl.created_at DESC
        ");

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['full_name']}</td>
                        <td>{$row['amount']}</td>
                        <td>{$row['phone_number']}</td>
                        <td>{$row['transaction_id']}</td>
                        <td>{$row['status']}</td>
                        <td>{$row['created_at']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No payments recorded yet.</td></tr>";
        }
        ?>
    </table>
</div>
</body>
</html>
