<?php
session_start();
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/admin_check.php';
require_once __DIR__ . '/../../database/config.php';

function runCountQuery($sql, $params = []) {
    global $conn, $pdo;
    if (isset($conn) && $conn instanceof mysqli) {
        $result = $conn->query($sql);
        if ($result) {
            $row = $result->fetch_row();
            return (int)$row[0];
        }
    } elseif (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
    return 0;
}

$counts = [
    'users' => runCountQuery("SELECT COUNT(*) FROM users"),
    'books' => runCountQuery("SELECT COUNT(*) FROM books"),
    'orders' => runCountQuery("SELECT COUNT(*) FROM orders"),
    'payments' => runCountQuery("SELECT COUNT(*) FROM payments"),
    'reservations' => runCountQuery("SELECT COUNT(*) FROM reservations"),
    'reviews' => runCountQuery("SELECT COUNT(*) FROM reviews"),
    'system_logs' => runCountQuery("SELECT COUNT(*) FROM system_logs")
];

$low_stock_books = [];
$recent_orders = [];
$top_books = [];

try {
    if (isset($conn)) {
        $res = $conn->query("SELECT book_id, title, stock_quantity FROM books ORDER BY stock_quantity ASC LIMIT 10");
        while ($r = $res->fetch_assoc()) $low_stock_books[] = $r;

        $res = $conn->query("SELECT o.order_id, u.full_name, o.order_date, o.total_amount, o.order_status 
               FROM orders o LEFT JOIN users u ON u.user_id = o.user_id
               ORDER BY o.order_date DESC LIMIT 10");
        while ($r = $res->fetch_assoc()) $recent_orders[] = $r;

        $res = $conn->query("SELECT b.book_id, b.title, SUM(oi.quantity) AS sold_qty
               FROM order_items oi JOIN books b ON b.book_id = oi.book_id
               GROUP BY oi.book_id ORDER BY sold_qty DESC LIMIT 10");
        while ($r = $res->fetch_assoc()) $top_books[] = $r;
    }
} catch (Exception $e) {}

$payment_status_summary = [
    'Paid' => runCountQuery("SELECT COUNT(*) FROM payments WHERE payment_status = 'Paid'"),
    'Pending' => runCountQuery("SELECT COUNT(*) FROM payments WHERE payment_status = 'Pending'"),
    'Unpaid' => runCountQuery("SELECT COUNT(*) FROM payments WHERE payment_status = 'Unpaid'"),
    'Refunded' => runCountQuery("SELECT COUNT(*) FROM payments WHERE payment_status = 'Refunded'")
];

$order_status_summary = [
    'Pending' => runCountQuery("SELECT COUNT(*) FROM orders WHERE order_status = 'Pending'"),
    'Shipped' => runCountQuery("SELECT COUNT(*) FROM orders WHERE order_status = 'Shipped'"),
    'Delivered' => runCountQuery("SELECT COUNT(*) FROM orders WHERE order_status = 'Delivered'"),
    'Cancelled' => runCountQuery("SELECT COUNT(*) FROM orders WHERE order_status = 'Cancelled'")
];

$reportData = [
    'counts' => $counts,
    'low_stock_books' => $low_stock_books,
    'recent_orders' => $recent_orders,
    'top_books' => $top_books,
    'payment_status_summary' => $payment_status_summary,
    'order_status_summary' => $order_status_summary,
    'generated_at' => date('c')
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Reports - Maktaba Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
</head>
<body class="d-flex flex-column min-vh-100">
<?php include("../../includes/admin_nav.php"); ?>

<main class="container my-5 flex-grow-1">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary">System Reports Overview</h3>
    </div>

    <!-- ✅ Export Controls Section -->
    <div class="mb-5">
        <?php include __DIR__ . '/export_ui.php'; ?>
    </div>

    <!-- Charts -->
    <section class="row g-3 mb-5">
        <div class="col-md-6">
            <div class="card chart-card shadow-sm">
                <div class="card-header fw-semibold bg-primary text-white">Order Status Breakdown</div>
                <div class="card-body"><canvas id="ordersStatusChart" height="180"></canvas></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card chart-card shadow-sm">
                <div class="card-header fw-semibold bg-primary text-white">Payments Status Breakdown</div>
                <div class="card-body"><canvas id="paymentsStatusChart" height="180"></canvas></div>
            </div>
        </div>
    </section>

    <!-- Lists -->
    <section class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold bg-success text-white">Top-Selling Books</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if ($top_books): ?>
                            <?php foreach ($top_books as $tb): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($tb['title']) ?>
                                    <span class="badge bg-success rounded-pill"><?= (int)$tb['sold_qty'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted">No data available</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold bg-warning text-dark">Low Stock Books</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if ($low_stock_books): ?>
                            <?php foreach ($low_stock_books as $lb): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($lb['title']) ?>
                                    <span class="badge <?= ((int)$lb['stock_quantity'] <= 5) ? 'bg-danger' : 'bg-secondary' ?>">
                                        <?= (int)$lb['stock_quantity'] ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted">No books found</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Orders -->
    <div class="table-wrapper shadow-sm rounded bg-white p-3 mb-4">
        <h5 class="fw-bold text-primary mb-3">Recent Orders</h5>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-primary">
                <tr>
                    <th>#</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($recent_orders): ?>
                    <?php foreach ($recent_orders as $ord): ?>
                        <tr>
                            <td><?= htmlspecialchars($ord['order_id']) ?></td>
                            <td><?= htmlspecialchars($ord['full_name'] ?? 'Guest') ?></td>
                            <td><?= htmlspecialchars(date('d M Y, h:i A', strtotime($ord['order_date']))) ?></td>
                            <td><strong><?= number_format($ord['total_amount'], 2) ?></strong></td>
                            <td>
                                <span class="badge <?= match(strtolower($ord['order_status'])) {
                                    'pending' => 'bg-warning text-dark',
                                    'shipped' => 'bg-info text-dark',
                                    'delivered' => 'bg-success',
                                    'cancelled' => 'bg-danger',
                                    default => 'bg-secondary'
                                } ?>">
                                <?= htmlspecialchars($ord['order_status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No recent orders</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="text-center text-muted small mt-4">
        Report generated at <?= htmlspecialchars($reportData['generated_at']) ?>
    </footer>
</main>

<footer class="bg-primary text-white text-center py-3 mt-auto">
    <small>&copy; <?= date('Y') ?> Maktaba Bookstore | Admin Panel</small>
</footer>

<script>
window.reportData = <?= json_encode($reportData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<script src="/assets/js/admin_charts.js"></script>
<script src="/assets/js/export.js"></script>
</body>
</html>
