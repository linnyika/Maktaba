<?php
include("../includes/session_check.php");
include("../database/config.php");

// Allow only admin
if ($_SESSION['role'] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Filters
$start_date = $_GET['start_date'] ?? "";
$end_date = $_GET['end_date'] ?? "";
$status = $_GET['status'] ?? "";

// Base query
$query = "
    SELECT p.*, c.full_name
    FROM payments p
    LEFT JOIN customers c ON p.user_id = c.customer_id
    WHERE 1
";

if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND DATE(p.created_at) BETWEEN '$start_date' AND '$end_date'";
}

if (!empty($status)) {
    $query .= " AND p.payment_status = '$status'";
}

$query .= " ORDER BY p.created_at DESC";
$result = $conn->query($query);

// Total revenue from successful payments
$total_query = "SELECT SUM(amount) AS total_amount FROM payments WHERE payment_status='Paid'";
$total_result = $conn->query($total_query);
$total_revenue = $total_result->fetch_assoc()['total_amount'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaction Analysis</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include("../includes/admin_nav.php"); ?>

<div class="admin-container">
    <h2>📊 Transaction & Payment Analysis</h2>

    <form method="GET" class="filter-form">
        <label>Start Date:</label>
        <input type="date" name="start_date" value="<?= $start_date ?>">

        <label>End Date:</label>
        <input type="date" name="end_date" value="<?= $end_date ?>">

        <label>Status:</label>
        <select name="status">
            <option value="">All</option>
            <option value="Paid" <?= ($status == "Paid") ? "selected" : "" ?>>Paid</option>
            <option value="Pending" <?= ($status == "Pending") ? "selected" : "" ?>>Pending</option>
            <option value="Failed" <?= ($status == "Failed") ? "selected" : "" ?>>Failed</option>
        </select>

        <button type="submit" class="btn btn-confirm">Apply Filters</button>
        <a href="../api/export_api.php?type=payments" class="btn btn-alt">📄 Export Payments</a>
    </form>

    <h3>Total Revenue: <strong>KES <?= number_format($total_revenue, 2) ?></strong></h3>

    <table>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>User</th>
                <th>Method</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Transaction ID</th>
                <th>Receipt</th>
                <th>Phone</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['payment_id'] ?></td>
                    <td><?= $row['full_name'] ?></td>
                    <td><?= $row['payment_method'] ?></td>
                    <td><?= $row['payment_status'] ?></td>
                    <td>KES <?= number_format($row['amount'], 2) ?></td>
                    <td><?= $row['mpesa_transaction_id'] ?></td>
                    <td><?= $row['mpesa_receipt_number'] ?></td>
                    <td><?= $row['mpesa_phone'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
