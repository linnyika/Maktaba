<?php
// includes/summary_helper.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/data_processor.php');
require_once(__DIR__ . '/../database/config.php');

class SummaryHelper {
    private $processor;

    public function __construct($conn) {
        $this->processor = new DataProcessor($conn);
    }

    // -------------------------
    // SYSTEM-WIDE SUMMARIES
    // -------------------------

    // Total orders and revenue for the system
    public function getSystemSummary() {
        return [
            'total_orders'  => $this->processor->getTotalOrders(),
            'total_revenue' => $this->processor->getTotalRevenue(),
            'avg_rating'    => $this->processor->getAverageRating()
        ];
    }

    // Top 5 selling books
    public function getTopBooksSummary() {
        return $this->processor->getTopBooks();
    }

    // Monthly revenue for charts or reports
    public function getMonthlyRevenueSummary() {
        return $this->processor->getMonthlyRevenue();
    }

    // -------------------------
    // USER-SPECIFIC SUMMARIES
    // -------------------------

    // Summary per user
    public function getUserSummary($user_id) {
        return $this->processor->getUserSummary($user_id);
    }

    // User orders over time for charts
    public function getUserOrdersChart($user_id) {
        return $this->processor->getUserOrdersForChart($user_id);
    }

    // User spending breakdown by genre
    public function getUserSpendingChart($user_id) {
        return $this->processor->getUserSpendingForChart($user_id);
    }

public function getUserRoleDistribution() {
    $sql = "SELECT user_role, COUNT(*) AS total FROM users GROUP BY user_role";
    $res = $this->processor->getConnection()->query($sql); 
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

public function getRecentLogs() {
    $sql = "SELECT u.full_name, l.action, l.timestamp
            FROM activity_logs l
            LEFT JOIN users u ON l.user_id = u.user_id
            ORDER BY l.timestamp DESC
            LIMIT 10";
    $res = $this->processor->getConnection()->query($sql);
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}


}

// -------------------------
// TESTING CODE
// -------------------------
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $summary = new SummaryHelper($conn);
    echo "<pre>";
    print_r($summary->getSystemSummary());
    print_r($summary->getTopBooksSummary());
    print_r($summary->getMonthlyRevenueSummary());
    echo "</pre>";
}
?>
