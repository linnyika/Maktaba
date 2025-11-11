<?php
// includes/excel_generator.php
// Wrapper around PhpSpreadsheet for ExportHelper

require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoload

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!function_exists('generateExcelFromArray')) {
    /**
     * Generate an Excel file from an array of data and output/download it.
     *
     * @param array $data Array of associative arrays representing rows
     * @param string $filename Name of the Excel file
     * @return string|void Returns temp file path if saved to disk, else streams directly
     */
    function generateExcelFromArray(array $data, string $filename)
    {
        if (empty($data)) {
            throw new Exception("No data provided for Excel export.");
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers (from first row keys)
        $headers = array_keys(reset($data));
        $sheet->fromArray($headers, null, 'A1');

        // Set data starting from row 2
        $rowIndex = 2;
        foreach ($data as $row) {
            $sheet->fromArray(array_values($row), null, 'A' . $rowIndex);
            $rowIndex++;
        }

        // Optional: auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output to browser for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . basename($filename) . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
