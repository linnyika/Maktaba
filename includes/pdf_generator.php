<?php
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
