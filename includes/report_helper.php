<?php
require_once(__DIR__ . '/../database/config.php');

/**
 * SALES SUMMARY (by date)
 */
function getSalesSummary($conn, $startDate, $endDate) {
    $stmt = $conn->prepare("
        SELECT DATE(payment_date) AS sale_date,
               SUM(amount) AS total_sales,
               COUNT(payment_id) AS total_transactions
        FROM payments
        WHERE payment_status = 'Paid'
          AND payment_date BETWEEN ? AND ?
        GROUP BY DATE(payment_date)
        ORDER BY sale_date ASC
    ");
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * TOP SELLING BOOKS
 */
function getTopBooks($conn, $limit = 5) {
    $query = "
        SELECT b.title,
               SUM(oi.quantity) AS total_sold,
               SUM(oi.price * oi.quantity) AS total_earned
        FROM order_items oi
        JOIN books b ON oi.book_id = b.book_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE o.payment_status = 'Paid'
        GROUP BY b.book_id
        ORDER BY total_sold DESC
        LIMIT $limit
    ";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

/**
 * USER ROLE SUMMARY
 */
function getUserRoleDistribution($conn) {
    $query = "
        SELECT user_role, COUNT(*) AS total
        FROM users
        GROUP BY user_role
    ";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

/**
 * REVIEW SUMMARY
 */
function getReviewInsights($conn) {
    $query = "
        SELECT b.title,
               ROUND(AVG(r.rating),1) AS avg_rating,
               COUNT(r.review_id) AS total_reviews
        FROM reviews r
        JOIN books b ON r.book_id = b.book_id
        WHERE r.is_approved = 1
        GROUP BY r.book_id
        ORDER BY avg_rating DESC
        LIMIT 5
    ";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

/**
 * ACTIVITY LOGS (Recent)
 */
function getRecentLogs($conn, $limit = 10) {
    $stmt = $conn->prepare("
        SELECT l.log_id, u.full_name, l.action, l.module, l.description, l.timestamp
        FROM logs l
        LEFT JOIN users u ON l.user_id = u.user_id
        ORDER BY l.timestamp DESC
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
