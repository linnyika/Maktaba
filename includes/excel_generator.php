<?php
   // Import necessary classes
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    
require_once 'config.php';  // Database configuration file


function generateExcel($data, $filename = 'maktaba_report.xlsx')
{
    // Load PhpSpreadsheet library (ensure Composer is installed)
    require_once __DIR__ . '/../vendor/autoload.php';

 

    // Create a new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    if (!empty($data)) {
        $headers = array_keys($data[0]);
        $column = 'A';

        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', strtoupper(str_replace('_', ' ', $header)));
            $column++;
        }

        $rowIndex = 2;
        foreach ($data as $row) {
            $column = 'A';
            foreach ($row as $value) {
                $sheet->setCellValue($column . $rowIndex, $value);
                $column++;
            }
            $rowIndex++;
        }
    } else {
        $sheet->setCellValue('A1', 'No data available for export.');
    }

   
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}


?>
