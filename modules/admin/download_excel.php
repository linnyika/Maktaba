<?php
require_once("../../database/config.php"); // FIXED PATH
require_once("../../includes/export_helper.php"); // FIXED PATH

class ExcelDownloader {
    
    public static function download($type, $conn) {
        try {
            if (!ExportHelper::isValidType($type)) {
                throw new Exception("Invalid export type");
            }
            
            $data = self::getData($type, $conn);
            $headers = ExportHelper::getHeaders($type);
            $filename = $type . '_report_' . date('Y-m-d') . '.csv';
            
            self::sendExcelHeaders($filename);
            self::generateCSV($headers, $data);
            
        } catch (Exception $e) {
            self::handleError($e->getMessage());
        }
    }
    
    private static function getData($type, $conn) {
        $queries = [
            'reservations' => "
                SELECT r.reservation_id, u.full_name, b.title, r.pickup_date, 
                       r.status, r.payment_status, r.reservation_date
                FROM reservations r
                JOIN users u ON r.user_id = u.user_id
                JOIN books b ON r.book_id = b.book_id
                ORDER BY r.reservation_date DESC
            ",
            'audit_trail' => "
                SELECT log_id, user_id, action, description, ip_address, timestamp
                FROM audit_trail 
                ORDER BY timestamp DESC
            ",
            'users' => "
                SELECT user_id, username, full_name, email, role, status, created_at
                FROM users 
                ORDER BY created_at DESC
            ",
            'books' => "
                SELECT book_id, title, author, isbn, category, status, created_at
                FROM books 
                ORDER BY created_at DESC
            "
        ];
        
        $result = $conn->query($queries[$type]);
        if (!$result) {
            throw new Exception("Database query failed: " . $conn->error);
        }
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = self::formatRow($type, $row);
        }
        
        return $data;
    }
    
    private static function formatRow($type, $row) {
        // Your formatting logic here
        switch ($type) {
            case 'reservations':
                return [
                    $row['reservation_id'],
                    $row['full_name'],
                    $row['title'],
                    $row['pickup_date'] ? date('Y-m-d', strtotime($row['pickup_date'])) : 'Not Set',
                    $row['status'],
                    $row['payment_status'] ?: 'Pending',
                    date('Y-m-d H:i:s', strtotime($row['reservation_date']))
                ];
            // Add other cases...
            default:
                return array_values($row);
        }
    }
    
    private static function sendExcelHeaders($filename) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
    }
    
    private static function generateCSV($headers, $data) {
        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
        fputcsv($output, $headers);
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
    }
    
    private static function handleError($message) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="export_error.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ["Error"]);
        fputcsv($output, [$message]);
        fclose($output);
        exit;
    }
}

// Main execution
$type = $_GET['type'] ?? '';
if (empty($type)) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

ExcelDownloader::download($type, $conn);
?>