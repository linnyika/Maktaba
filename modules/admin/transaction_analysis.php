<?php
include_once "../../includes/session_check.php";
include_once "../../database/config.php";

// Default values for filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : "";
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : "";
$status = isset($_GET['status']) ? $_GET['status'] : "";

// Base query
$query = "SELECT * FROM mpesa_payments WHERE 1";

// Apply filters if provided
if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND date(timestamp) BETWEEN '$start_date' AND '$end_date'";
}

if (!empty($status)) {
    $query .= " AND status='$status'";
}

$query .= " ORDER BY timestamp DESC";

// Execute query
$result = mysqli_query($conn, $query);

// Calculate total revenue
$total_query = "SELECT SUM(amount) AS total_amount FROM mpesa_payments WHERE status='Success'";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_revenue = $total_row['total_amount'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaction Analysis</title>
    <link rel="stylesheet" href="../../assets/styles.css">
</head>
<body>

<?php include "../navbar.php"; ?>

<div class="container">
    <h2>📊 Transaction Analysis</h2>

    <form method="GET" style="margin-bottom: 20px;">
        <label>Start Date:</label>
        <input type="date" name="start_date" value="<?= $start_date ?>">
        
        <label>End Date:</label>
        <input type="date" name="end_date" value="<?= $end_date ?>">

        <label>Status:</label>
        <select name="status">
            <option value="">All</option>
            <option value="Success" <?= ($status == "Success") ? "selected" : "" ?>>Success</option>
            <option value="Failed" <?= ($status == "Failed") ? "selected" : "" ?>>Failed</option>
        </select>

        <button type="submit">Filter</button>
    </form>

    <h3>Total Revenue (Successful Payments): <strong>KES <?= number_format($total_revenue, 2) ?></strong></h3>

    <table border="1" cellpadding="8" width="100%">
        <tr>
            <th>Transaction ID</th>
            <th>Phone</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Timestamp</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $row['transaction_id'] ?></td>
            <td><?= $row['phone'] ?></td>
            <td>KES <?= number_format($row['amount'], 2) ?></td>
            <td><?= $row['status'] ?></td>
            <td><?= $row['timestamp'] ?></td>
        </tr>
        <?php endwhile; ?>
        
    </table>
</div>

</body>
</html>
