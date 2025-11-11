
<?php


require_once '../../includes/config.php';
require_once '../../includes/excel_generator.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../index.php');
    exit();
}

$reportType = isset($_GET['type']) ? $_GET['type'] : 'sales';
$filename   = "maktaba_{$reportType}_report.xlsx";

try {
    $conn = new mysqli ($host, $user, $pass, $dbname, $port0);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

       switch ($reportType) {
        case 'sales':
            $query = "SELECT transaction_id, customer_name, total_amount, payment_method, created_at 
                      FROM transactions 
                      ORDER BY created_at DESC";
            break;

        case 'books':
            $query = "SELECT book_id, title, author, category, total_loans, rating 
                      FROM books 
                      ORDER BY total_loans DESC";
            break;

        case 'users':
            $query = "SELECT user_id, full_name, email, role, created_at 
                      FROM users 
                      ORDER BY created_at DESC";
            break;

        default:
            throw new Exception("Invalid report type specified.");
    }

    $result = $conn->query($query);

    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

        if (!empty($data)) {
        generateExcel($data, $filename);
    } else {
        echo "<h3 style='color: red; text-align: center;'>No data available for the selected report.</h3>";
    }

} catch (Exception $e) {
    echo "<h3 style='color: red; text-align: center;'>Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

?>

