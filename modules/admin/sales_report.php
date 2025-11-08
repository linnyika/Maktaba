<?php
// modules/admin/sales_report.php

include_once "../../includes/session_check.php";
include_once "../../database/config.php";

// Restrict Access to Admin Only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Fetch Total Revenue
$totalQuery = $conn->query("
    SELECT SUM(amount) AS total_revenue 
    FROM payments 
    WHERE payment_status='Paid'
");
$total = $totalQuery->fetch_assoc()['total_revenue'] ?? 0;

// Fetch Daily Revenue (for Line Chart)
$dailyResult = $conn->query("
    SELECT DATE(created_at) AS day, SUM(amount) AS total
    FROM payments
    WHERE payment_status='Paid'
    GROUP BY DATE(created_at)
    ORDER BY day ASC
");

$days = [];
$dailyTotals = [];

while ($row = $dailyResult->fetch_assoc()) {
    $days[] = $row['day'];
    $dailyTotals[] = $row['total'];
}

// Fetch Payment Method Distribution (for Pie Chart)
$methodResult = $conn->query("
    SELECT payment_method, COUNT(*) AS count
    FROM payments
    GROUP BY payment_method
");

$methods = [];
$methodCounts = [];

while ($row = $methodResult->fetch_assoc()) {
    $methods[] = $row['payment_method'];
    $methodCounts[] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales & Financial Analytics</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/analytics.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
<?php include "../../includes/admin_nav.php"; ?>

<div class="admin-container">
    <h2>📊 Sales & Financial Analytics</h2>

    <h3>Total Revenue Collected:</h3>
    <div class="chart-box">
        <h1>KES <?php echo number_format($total, 2); ?></h1>
    </div>

    <br>

    <div class="chart-box">
        <h3>Revenue Trend (Daily)</h3>
        <canvas id="dailyRevenueChart"></canvas>
    </div>

    <div class="chart-box">
        <h3>Payment Method Distribution</h3>
        <canvas id="methodChart"></canvas>
    </div>

</div>

<script>
    // Daily Revenue Chart
    const dailyCtx = document.getElementById('dailyRevenueChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($days); ?>,
            datasets: [{
                label: 'Revenue (KES)',
                data: <?php echo json_encode($dailyTotals); ?>,
                borderWidth: 2
            }]
        }
    });

    // Payment Method Chart
    const methodCtx = document.getElementById('methodChart').getContext('2d');
    new Chart(methodCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($methods); ?>,
            datasets: [{
                data: <?php echo json_encode($methodCounts); ?>,
                borderWidth: 1
            }]
        }
    });
</script>

</body>
</html>
