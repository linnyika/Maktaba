<?php
<<<<<<< HEAD
// includes/pdf_generator.php
// Wrapper around TCPDF for ExportHelper

require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoload

use TCPDF;

if (!function_exists('generatePDF')) {
    /**
     * Generate and stream a PDF from HTML
     * @param string $html The HTML content
     * @param string $filename The filename to send to browser
     * @return void|string If returns string, path to temp file
     */
    function generatePDF(string $html, string $filename)
    {
        try {
            $pdf = new TCPDF();
            $pdf->SetCreator('Maktaba Admin');
            $pdf->SetAuthor('Maktaba');
            $pdf->SetTitle($filename);
            $pdf->SetMargins(10, 10, 10);
            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Option 1: Stream directly
            $pdf->Output($filename, 'D'); // 'D' = force download
            return true;

            // Option 2: Save to temp file instead of streaming
            // $tmpPath = sys_get_temp_dir() . '/' . $filename;
            // $pdf->Output($tmpPath, 'F');
            // return $tmpPath;

        } catch (Exception $e) {
            error_log("generatePDF error: " . $e->getMessage());
            throw new Exception("PDF generation failed: " . $e->getMessage());
        }
    }
}
=======
require_once("../../database/config.php");
require_once("../../includes/session_check.php");
require_once("../../includes/tcpdf_min/tcpdf.php"); // Make sure TCPDF is in this path

// --- Admin guard ---
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: /index.php');
    exit;
}

// --- Fetch activity logs ---
$activity_logs = $conn->query("
    SELECT a.user_id, a.action, u.role, a.timestamp
    FROM activity_logs a
    INNER JOIN users u ON a.user_id = u.user_id
    ORDER BY a.timestamp DESC
");

// --- Fetch audit logs ---
$audit_logs = $conn->query("
    SELECT at.id, at.user_id, at.activity, u.role, at.log_time
    FROM audit_trail at
    INNER JOIN users u ON at.user_id = u.user_id
    ORDER BY at.log_time DESC
");

// --- Create PDF ---
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Maktaba Admin');
$pdf->SetTitle('User Activity & Audit Logs');
$pdf->AddPage();

// Title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'User Activity & Audit Logs', 0, 1, 'C');
$pdf->Ln(5);

// User Activity Table
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'User Activity', 0, 1);
$pdf->SetFont('helvetica', '', 10);

$html = '<table border="1" cellpadding="4">
<thead>
<tr>
<th>User ID</th>
<th>Action</th>
<th>Role</th>
<th>Timestamp</th>
</tr>
</thead>
<tbody>';
if ($activity_logs && $activity_logs->num_rows > 0) {
    while($log = $activity_logs->fetch_assoc()) {
        $html .= '<tr>
        <td>'.$log['user_id'].'</td>
        <td>'.$log['action'].'</td>
        <td>'.$log['role'].'</td>
        <td>'.$log['timestamp'].'</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="4" align="center">No user activity logs found</td></tr>';
}
$html .= '</tbody></table>';
$pdf->writeHTML($html, true, false, true, false, '');

// Audit Trail Table
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Audit Trail', 0, 1);
$pdf->SetFont('helvetica', '', 10);

$html2 = '<table border="1" cellpadding="4">
<thead>
<tr>
<th>ID</th>
<th>User ID</th>
<th>Activity</th>
<th>Role</th>
<th>Log Time</th>
</tr>
</thead>
<tbody>';
if ($audit_logs && $audit_logs->num_rows > 0) {
    while($audit = $audit_logs->fetch_assoc()) {
        $html2 .= '<tr>
        <td>'.$audit['id'].'</td>
        <td>'.$audit['user_id'].'</td>
        <td>'.$audit['activity'].'</td>
        <td>'.$audit['role'].'</td>
        <td>'.$audit['log_time'].'</td>
        </tr>';
    }
} else {
    $html2 .= '<tr><td colspan="5" align="center">No audit records found</td></tr>';
}
$html2 .= '</tbody></table>';
$pdf->writeHTML($html2, true, false, true, false, '');

// Output PDF to browser
$pdf_file = 'User_Activity_'.date('Ymd_His').'.pdf';
$pdf->Output($pdf_file, 'I'); // 'I' = inline view; use 'D' for direct download
>>>>>>> d976d09 (user_activity_report.php + audit integration + usage logs)
