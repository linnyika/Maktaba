<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// includes/data_processor.php
require_once(__DIR__ . '/../database/config.php');

class DataProcessor {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    //  Total number of orders
    public function getTotalOrders() {
        $sql = "SELECT COUNT(*) AS total_orders FROM orders";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_assoc()['total_orders'] : 0;
    }

    //  Total revenue (sum of total_amount where payment_status = 'Paid')
    public function getTotalRevenue() {
        $sql = "SELECT COALESCE(SUM(total_amount), 0) AS total_revenue 
                FROM orders 
                WHERE payment_status = 'Paid'";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_assoc()['total_revenue'] : 0;
    }

    //  Top 5 selling books by quantity
    public function getTopBooks() {
        $sql = "
            SELECT b.title, SUM(oi.quantity) AS total_sold
            FROM order_items oi
            JOIN books b ON oi.book_id = b.book_id
            JOIN orders o ON oi.order_id = o.order_id
            WHERE o.order_status IN ('Delivered','Shipped')
            GROUP BY b.book_id, b.title
            ORDER BY total_sold DESC
            LIMIT 5
        ";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    //  Revenue grouped by month
    public function getMonthlyRevenue() {
        $sql = "
            SELECT DATE_FORMAT(order_date, '%Y-%m') AS month,
                   COALESCE(SUM(total_amount), 0) AS total
            FROM orders
            WHERE payment_status = 'Paid'
            GROUP BY month
            ORDER BY month ASC
        ";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    //  User-specific order summary
    public function getUserSummary($user_id) {
        $sql = "
            SELECT 
                COUNT(*) AS total_orders,
                COALESCE(SUM(total_amount), 0) AS total_spent
            FROM orders
            WHERE user_id = $user_id
        ";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_assoc() : ['total_orders'=>0,'total_spent'=>0];
    }

    //  Overall average rating (from approved reviews)
    public function getAverageRating() {
        $sql = "SELECT ROUND(AVG(rating),1) AS avg_rating 
                FROM reviews 
                WHERE is_approved = 1";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_assoc()['avg_rating'] : 0;
    }
}
 
//to test thhe connection
echo "✅ File loaded successfully<br>";

if (isset($conn)) {
    echo "✅ Database connection exists<br>";
} else {
    echo "❌ No database connection<br>";
}

?>


