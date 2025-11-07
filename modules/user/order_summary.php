<?php
require_once(__DIR__ . '/../../includes/data_processor.php');
require_once(__DIR__ . '/../../database/config.php');
session_start();

$user_id = $_SESSION['user_id'] ?? 1;

$processor = new DataProcessor($conn);
$summary  = $processor->getUserSummary($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Order Summary</title>
    <link rel="stylesheet" href="../../assets/css/analytics.css">
    <style>
        body { font-family: Poppins, sans-serif; background:#f9fafb; padding:2rem; }
        .summary-box { background:#fff; border-radius:10px; box-shadow:0 2px 5px rgba(0,0,0,.1); padding:1.5rem; width:350px;}
        h2 { color:#0077b6; }
    </style>
</head>
<body>
    <h2>My Order Summary</h2>
    <div class="summary-box">
        <p><strong>Total Orders:</strong> <?= $summary['total_orders'] ?? 0 ?></p>
        <p><strong>Total Spent:</strong> Ksh <?= number_format($summary['total_spent'] ?? 0,2) ?></p>
    </div>
</body>
</html>
