<?php
<<<<<<< HEAD


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
=======
// modules/admin/export_reports.php

session_start();
require_once __DIR__ . '/../../includes/admin_check.php'; // ensure admin
require_once __DIR__ . '/../../includes/export_helper.php';
require_once __DIR__ . '/../../database/config.php';

// Ensure ExportHelper class is loaded
if (!class_exists('\Maktaba\ExportHelper\ExportHelper')) {
    require_once __DIR__ . '/../../includes/ExportHelper.php';
}

// If your config.php defines $pdo as PDO, pass it to the helper
$user_id = $_SESSION['user_id'] ?? 0;

try {
    // Read request body
    $body = file_get_contents('php://input');
    $isJson = !empty($body) && (json_decode($body, true) !== null);

    if ($isJson) {
        $req = json_decode($body, true);
        $action = $req['action'] ?? 'sales';
        $format = strtolower($req['format'] ?? 'csv');
        $options = $req['options'] ?? [];
    } else {
        // fallback to form POST (e.g., direct form submit)
        $action = $_POST['action'] ?? $_GET['action'] ?? 'sales';
        $format = strtolower($_POST['format'] ?? $_GET['format'] ?? 'csv');
        $options = $_POST['options'] ?? [];
    }

    // Instantiate helper
    $helper = new \Maktaba\ExportHelper\ExportHelper($pdo, $user_id);

    /* ---------------------------
       Preview Mode (return small HTML table)
       --------------------------- */
    if ($format === 'preview') {
        if ($action === 'sales') {
            $rows = $helper->getSalesSummary($options['from'] ?? null, $options['to'] ?? null);
            echo $helper->buildSimpleHtmlTable('Sales Preview', $rows);
            exit;
        } elseif ($action === 'inventory') {
            $rows = $helper->getInventoryReport();
            echo $helper->buildSimpleHtmlTable('Inventory Preview', $rows);
            exit;
        } else {
            echo '<p>No preview available for this action</p>';
            exit;
        }
    }

    /* ---------------------------
       Route actions to export functions
       --------------------------- */
    if ($action === 'sales') {
        $helper->exportSales($format, $options);
    } elseif ($action === 'inventory') {
        $rows = $helper->getInventoryReport();

        $filenameBase = 'inventory_' . date('Ymd_His');
        $filenameCsv = $filenameBase . '.csv';
        $filenameXlsx = $filenameBase . '.xlsx';
        $filenamePdf = $filenameBase . '.pdf';

        if ($format === 'csv') {
            $helper->streamCsvDownload($filenameCsv, $rows);
        } elseif ($format === 'xlsx' || $format === 'excel') {
            $helper->streamExcelDownload($filenameXlsx, $rows);
        } elseif ($format === 'pdf') {
            $html = $helper->buildSimpleHtmlTable('Inventory Report', $rows);
            $helper->streamPdfDownloadFromHtml($filenamePdf, $html);
        } else {
            http_response_code(400);
            echo 'Unsupported format';
            exit;
        }
    } else {
        http_response_code(400);
        echo 'Unsupported action';
        exit;
    }

} catch (\Exception $e) {
    http_response_code(500);
    error_log("Export error: " . $e->getMessage());
    echo 'Server error: ' . $e->getMessage();
    exit;
}
>>>>>>> 01d33dc84fd81d92c468b125c2b84e5e7d1486d0
