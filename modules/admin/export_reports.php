<?php


require_once '../../includes/session_check.php';
require_once '../../includes/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maktaba | Export Reports</title>
    <link rel="stylesheet" href="../../assets/styles.css">
    <script src="../../assets/js/print_report.js" defer></script>
    <style>
        /* Inline specific styling for clarity */
        .export-container {
            width: 90%;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
        }
        .export-options {
            display: flex;
            justify-content: space-around;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        .export-box {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            width: 280px;
            text-align: center;
            transition: 0.3s;
            background: #f9f9f9;
        }
        .export-box:hover {
            box-shadow: 0 3px 8px rgba(0,0,0,0.15);
            transform: scale(1.03);
        }
        .export-box h3 {
            color: #34495e;
        }
        .export-box button {
            background: #00695c;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }
        .export-box button:hover {
            background: #004d40;
        }
    </style>
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="export-container">
    <h2>📦 Export Reports Dashboard</h2>
    <p style="text-align:center;">Select a report type to generate and download in Excel format.</p>

    <div class="export-options">
        <!-- Sales Report -->
        <div class="export-box">
            <h3>Sales Report</h3>
            <p>Includes M-Pesa payments, total revenue, and transaction trends.</p>
            <button onclick="window.location.href='download_excel.php?type=sales'">Download Excel</button>
            <button onclick="printReportPreview('sales')">Quick Preview</button>
        </div>

        <!-- Book Performance Report -->
        <div class="export-box">
            <h3>Book Performance</h3>
            <p>Shows loan frequency, popular categories, and ratings.</p>
            <button onclick="window.location.href='download_excel.php?type=books'">Download Excel</button>
            <button onclick="printReportPreview('books')">Quick Preview</button>
        </div>

        <!-- User Report -->
        <div class="export-box">
            <h3>User Report</h3>
            <p>Summarizes registered users, roles, and activity levels.</p>
            <button onclick="window.location.href='download_excel.php?type=users'">Download Excel</button>
            <button onclick="printReportPreview('users')">Quick Preview</button>
        </div>
    </div>
</div>

</body>
</html>

<?php

?>
