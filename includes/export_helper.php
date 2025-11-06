<?php
// includes/export_helper.php
// Basic CSV export helper (Phase 4 - M4 task)
// Phase 6 Member 5 will extend this for PDF/Excel

function exportToCSV($filename, $header, $data)
{
    // Output headers so browser treats response as download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Write header row
    fputcsv($output, $header);

    // Write data rows
    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}
