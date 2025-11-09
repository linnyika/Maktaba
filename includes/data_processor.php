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


public function getUserOrdersForChart($user_id) {
    $sql = "
        SELECT DATE(order_date) AS day, COUNT(*) AS orders
        FROM orders
        WHERE user_id = $user_id
        GROUP BY day
        ORDER BY day ASC
    ";
    $res = $this->conn->query($sql);
    $labels = [];
    $values = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $labels[] = $row['day'];
            $values[] = (int)$row['orders'];
        }
    }
    return ['labels'=>$labels,'values'=>$values];
}

public function getUserSpendingForChart($user_id) {
    $sql = "
        SELECT b.genre, COALESCE(SUM(oi.price * oi.quantity),0) AS total
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN books b ON oi.book_id = b.book_id
        WHERE o.user_id = $user_id
        GROUP BY b.genre
    ";
    $res = $this->conn->query($sql);
    $labels = [];
    $values = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $labels[] = $row['genre'];
            $values[] = (float)$row['total'];
        }
    }
    return ['labels'=>$labels,'values'=>$values];
}

public function getConnection() {
    return $this->conn;
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
 
// Only show test confirmation when accessing this file directly
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    echo "✅ File loaded successfully<br>";
    if ($conn) {
        echo "✅ Database connection exists<br>";
    } else {
        echo "❌ No database connection<br>";
    }
}

?>


