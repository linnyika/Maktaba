<?php
// includes/export_helper.php
// Updated Export Helper for Maktaba with current database

require_once __DIR__ . '/../database/config.php'; // $conn (mysqli) or $pdo
require_once __DIR__ . '/pdf_generator.php';


if (!function_exists('logActivity')) {
    function logActivity($user_id, $action, $module = null, $description = null)
    {
        global $conn;

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $stmt = $conn->prepare("
            INSERT INTO system_logs (user_id, action, module, description, ip_address, log_date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        if ($stmt === false) {
            error_log('logActivity prepare failed: ' . $conn->error);
            return false;
        }
        $stmt->bind_param("issss", $user_id, $action, $module, $description, $ip);
        $exec = $stmt->execute();
        if ($stmt->error) {
            error_log('logActivity execute failed: ' . $stmt->error);
        }
        $stmt->close();
        return $exec;
    }
}

class ExportHelper
{
    protected $conn;
    protected $user_id;

    public function __construct($conn, $user_id = 0)
    {
        $this->conn = $conn;
        $this->user_id = $user_id;
    }

    /* ---------------------------
       Queries
       --------------------------- */

    public function getSalesSummary($from = null, $to = null)
    {
        $sql = "
            SELECT o.order_id, o.order_date, o.total_amount, o.order_status, p.payment_status, u.full_name, u.email
            FROM orders o
            LEFT JOIN payments p ON p.order_id = o.order_id
            LEFT JOIN users u ON u.user_id = o.user_id
            WHERE 1=1
        ";
        if ($from) $sql .= " AND o.order_date >= '" . $this->conn->real_escape_string($from . ' 00:00:00') . "'";
        if ($to) $sql .= " AND o.order_date <= '" . $this->conn->real_escape_string($to . ' 23:59:59') . "'";
        $sql .= " ORDER BY o.order_date DESC";

        $result = $this->conn->query($sql);
        $rows = [];
        if ($result) {
            while ($r = $result->fetch_assoc()) $rows[] = $r;
        }
        return $rows;
    }

    public function getInventoryReport()
    {
        $sql = "
            SELECT b.book_id, b.title, b.author, p.name AS publisher, b.price, b.stock_quantity, b.reserved_stock, b.is_available
            FROM books b
            LEFT JOIN publishers p ON p.publisher_id = b.publisher_id
            ORDER BY b.title
        ";
        $result = $this->conn->query($sql);
        $rows = [];
        if ($result) {
            while ($r = $result->fetch_assoc()) $rows[] = $r;
        }
        return $rows;
    }

    /* ---------------------------
       CSV Export
       --------------------------- */

    public function arrayToCsvString(array $rows, array $headers = []): string
    {
        if (empty($rows) && empty($headers)) return '';
        $fh = fopen('php://temp', 'r+');
        if ($headers) fputcsv($fh, $headers);
        else fputcsv($fh, array_keys(reset($rows)));
        foreach ($rows as $row) {
            if ($headers) {
                $line = [];
                foreach ($headers as $h) $line[] = $row[$h] ?? '';
                fputcsv($fh, $line);
            } else fputcsv($fh, $row);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);
        return $csv;
    }

    public function streamCsvDownload(string $filename, array $rows, array $headers = []): void
    {
        $csv = $this->arrayToCsvString($rows, $headers);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        echo $csv;
        logActivity($this->user_id, 'EXPORT_CSV', 'Reports', "Exported CSV: $filename");
        exit;
    }

    /* ---------------------------
       Excel / PDF (requires generator scripts)
       --------------------------- */

    public function streamExcelDownload(string $filename, array $data): void
    {
        if (!function_exists('generateExcelFromArray')) {
            throw new \Exception("Excel generator not available.");
        }
        $result = generateExcelFromArray($data, $filename);
        if (is_string($result) && file_exists($result)) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            readfile($result);
            unlink($result);
            logActivity($this->user_id, 'EXPORT_EXCEL', 'Reports', "Exported Excel: $filename");
            exit;
        } else {
            logActivity($this->user_id, 'EXPORT_EXCEL', 'Reports', "Excel exported (generator handled output): $filename");
            exit;
        }
    }

    public function streamPdfDownloadFromHtml(string $filename, string $html): void
    {
        if (!function_exists('generatePDF')) throw new \Exception("PDF generator not available.");
        $result = generatePDF($html, $filename);
        if (is_string($result) && file_exists($result)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            readfile($result);
            unlink($result);
            logActivity($this->user_id, 'EXPORT_PDF', 'Reports', "Exported PDF: $filename");
            exit;
        } else {
            logActivity($this->user_id, 'EXPORT_PDF', 'Reports', "PDF exported (generator handled output): $filename");
            exit;
        }
    }

    /* ---------------------------
       HTML Table Builder (for PDF / Preview)
       --------------------------- */

    public function buildSimpleHtmlTable(string $title, array $rows): string
    {
        $html = '<!doctype html><html><head><meta charset="utf-8"><style>
                 table{width:100%;border-collapse:collapse}td,th{border:1px solid #ddd;padding:6px;font-size:12px}th{background:#f4f4f4}
                 </style></head><body>';
        $html .= "<h2>$title</h2>";
        if (empty($rows)) $html .= "<p>No data available.</p>";
        else {
            $html .= "<table><thead><tr>";
            foreach (array_keys(reset($rows)) as $col) $html .= "<th>" . htmlspecialchars($col) . "</th>";
            $html .= "</tr></thead><tbody>";
            foreach ($rows as $r) {
                $html .= "<tr>";
                foreach ($r as $v) $html .= "<td>" . htmlspecialchars((string)$v) . "</td>";
                $html .= "</tr>";
            }
            $html .= "</tbody></table>";
        }
        $html .= "</body></html>";
        return $html;
    }

    /* ---------------------------
       High-level export flows
       --------------------------- */

    public function exportSales(string $format = 'csv', array $options = [])
    {
        $from = $options['from'] ?? null;
        $to = $options['to'] ?? null;
        $rows = $this->getSalesSummary($from, $to);

        $filenameBase = 'sales_summary_' . date('Ymd_His');
        $filename = str_replace(' ', '_', $filenameBase);
        $filename .= ($format === 'pdf') ? '.pdf' : (($format === 'xlsx') ? '.xlsx' : '.csv');

        if ($format === 'csv') $this->streamCsvDownload($filename, $rows);
        elseif ($format === 'xlsx') $this->streamExcelDownload($filename, $rows);
        elseif ($format === 'pdf') {
            $html = $this->buildSimpleHtmlTable('Sales Summary', $rows);
            $this->streamPdfDownloadFromHtml($filename, $html);
        } else throw new \Exception("Unsupported format: $format");
    }

    public function exportInventory(string $format = 'csv')
    {
        $rows = $this->getInventoryReport();
        $filenameBase = 'inventory_' . date('Ymd_His');
        $filename = str_replace(' ', '_', $filenameBase);
        $filename .= ($format === 'pdf') ? '.pdf' : (($format === 'xlsx') ? '.xlsx' : '.csv');

        if ($format === 'csv') $this->streamCsvDownload($filename, $rows);
        elseif ($format === 'xlsx') $this->streamExcelDownload($filename, $rows);
        elseif ($format === 'pdf') {
            $html = $this->buildSimpleHtmlTable('Inventory Report', $rows);
            $this->streamPdfDownloadFromHtml($filename, $html);
        } else throw new \Exception("Unsupported format: $format");
    }
}