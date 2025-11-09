<?php
// api/data_api.php
header('Content-Type: application/json');
require_once(__DIR__ . '/../includes/data_processor.php');
require_once(__DIR__ . '/../database/config.php');

$processor = new DataProcessor($conn);
$type = $_GET['type'] ?? '';
$user_id = (int)($_GET['id'] ?? $_GET['user_id'] ?? 0); // <- move this here

switch ($type) {
    case 'summary':
        echo json_encode([
            'total_orders'  => $processor->getTotalOrders(),
            'total_revenue' => $processor->getTotalRevenue(),
            'avg_rating'    => $processor->getAverageRating()
        ]);
        break;

    case 'top_books':
        echo json_encode($processor->getTopBooks());
        break;

    case 'monthly_revenue':
        echo json_encode($processor->getMonthlyRevenue());
        break;

    case 'user_summary':
        echo json_encode($processor->getUserSummary($user_id));
        break;

    case 'user_orders':
        echo json_encode($processor->getUserOrdersForChart($user_id));
        break;

    case 'user_spending':
        echo json_encode($processor->getUserSpendingForChart($user_id));
        break;

    default:
        echo json_encode(['error' => 'Invalid or missing type parameter']);
}
?>
