<?php
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
