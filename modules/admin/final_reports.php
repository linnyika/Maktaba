<?php
require_once("../../includes/session_check.php");
require_once("../../includes/admin_check.php");
require_once("../../includes/summary_helper.php");
require_once("../../database/config.php");

$start = date('Y-m-01');
$end   = date('Y-m-t');


$summaryHelper = new SummaryHelper($conn);
$sales  = $summaryHelper->getMonthlyRevenueSummary();
$books  = $summaryHelper->getTopBooksSummary();
$users  = $summaryHelper->getUserRoleDistribution();
$logs   = $summaryHelper->getRecentLogs();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Maktaba | Final Report Summary</title>
<link rel="stylesheet" href="../../assets/css/reports.css">
</head>
<body>
<?php include("../../includes/admin_nav.php"); ?>

<div class="final-report">
    <h1>📄 Final Reports Summary</h1>
    <p>Reporting period: <?= date('F Y') ?></p>

    <h2>Sales Summary</h2>
    <table>
        <thead><tr><th>Date</th><th>Total Sales (KSh)</th><th>Transactions</th></tr></thead>
        <tbody>

        <?php foreach($sales as $s): ?>
       <tr>
  <td><?= $s['month'] ?></td>
  <td><?= number_format($s['total'], 2) ?></td>
  <td>-</td> 
</tr>
    <?php endforeach; ?>

        </tbody>
    </table>

    <h2>Top Books</h2>
    <ul>
    <?php foreach($books as $b): ?>
<li><?= htmlspecialchars($b['title']) ?> — Sold: <?= $b['total_sold'] ?></li>
    <?php endforeach; ?>
    </ul>

    <h2>User Role Distribution</h2>
    <ul>
    <?php foreach($users as $u): ?>
        <li><?= ucfirst($u['user_role']) ?> — <?= $u['total'] ?> users</li>
    <?php endforeach; ?>
    </ul>

    <h2>Recent Activity Logs</h2>
    <table>
        <thead><tr><th>User</th><th>Action</th><th>Module</th><th>Time</th></tr></thead>
        <tbody>
        <?php foreach($logs as $l): ?>
            <tr><td><?= htmlspecialchars($l['full_name'] ?? 'System') ?></td><td><?= htmlspecialchars($l['action']) ?></td><td><?= htmlspecialchars($l['module']) ?></td><td><?= $l['timestamp'] ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="export-controls">
        <a href="download_pdf.php" class="btn">📄 Export PDF</a>
        <a href="download_excel.php" class="btn">📊 Export Excel</a>
    </div>
</div>
</body>
</html>