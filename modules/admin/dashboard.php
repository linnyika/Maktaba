<?php
session_start();
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../database/config.php';

// --- Admin access guard ---
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: /index.php');
    exit;
}

// --- Initialize counts ---
$counts = [
    'users' => 0,
    'books' => 0,
    'orders_total' => 0,
    'orders_pending' => 0,
    'shipping_pending' => 0,
];

// --- Fetch dashboard stats ---
$q = $conn->query("SELECT COUNT(*) AS c FROM users");
$counts['users'] = (int)$q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) AS c FROM books");
$counts['books'] = (int)$q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) AS c FROM orders");
$counts['orders_total'] = (int)$q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE order_status IN ('Pending', 'Processing')");
$counts['orders_pending'] = (int)$q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) AS c FROM shipping WHERE status IN ('Pending', 'Processing', 'Shipped', 'Out for Delivery')");
$counts['shipping_pending'] = (int)$q->fetch_assoc()['c'];

// --- Recent Orders ---
$recent_orders = [];
$res = $conn->query("
    SELECT o.order_id, o.order_date, o.total_amount, o.order_status, o.payment_status, u.full_name
    FROM orders o
    LEFT JOIN users u ON u.user_id = o.user_id
    ORDER BY o.order_date DESC
    LIMIT 8
");
while ($r = $res->fetch_assoc()) {
    $recent_orders[] = $r;
}

// --- Auto-create shipping reminder notification ---
if ($counts['orders_pending'] > 0) {
    $reminder_message = "Shipping Reminder: " . $counts['orders_pending'] . " pending orders need to be processed";
    
    // Check if this reminder already exists today
    $check_stmt = $conn->prepare("SELECT id FROM notifications WHERE user_id = ? AND message = ? AND DATE(created_at) = CURDATE()");
    $check_stmt->bind_param("is", $_SESSION['user_id'], $reminder_message);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        // Add new reminder
        $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, status, created_at) VALUES (?, ?, 'unread', NOW())");
        $notif_stmt->bind_param("is", $_SESSION['user_id'], $reminder_message);
        $notif_stmt->execute();
        $notif_stmt->close();
    }
    $check_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Maktaba | Admin Dashboard</title>

  <!-- Bootswatch Minty -->
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../assets/css/admin.css?v=4" />
  
  <style>
    /* Notification Styles */
    .notification-wrapper {
      position: relative;
      display: inline-block;
    }

    .notification-icon {
      position: relative;
      cursor: pointer;
      font-size: 1.5rem;
      padding: 0.5rem;
      color: #6c757d;
      transition: color 0.3s ease;
    }

    .notification-icon:hover {
      color: #8b5a2b;
    }

    .notification-badge {
      position: absolute;
      top: 0;
      right: 0;
      background: #dc3545;
      color: white;
      border-radius: 50%;
      padding: 0.25rem 0.5rem;
      font-size: 0.75rem;
      font-weight: bold;
      min-width: 1.5rem;
      text-align: center;
    }

    .notification-dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 100%;
      width: 400px;
      background: white;
      border: 1px solid #dee2e6;
      border-radius: 0.5rem;
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
      z-index: 1000;
    }

    .notification-header {
      padding: 1rem;
      border-bottom: 1px solid #dee2e6;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #f8f9fa;
      border-radius: 0.5rem 0.5rem 0 0;
    }

    .notification-list {
      max-height: 400px;
      overflow-y: auto;
    }

    .notification-item {
      padding: 1rem;
      border-bottom: 1px solid #f0f0f0;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    .notification-item.unread {
      background-color: #f0f8ff;
      font-weight: 600;
    }

    .notification-item:last-child {
      border-bottom: none;
    }

    .notification-item:hover {
      background-color: #f8f9fa;
    }

    .notification-message {
      font-size: 0.9rem;
      margin-bottom: 0.25rem;
      color: #495057;
    }

    .notification-time {
      font-size: 0.75rem;
      color: #6c757d;
    }

    .notification-empty {
      padding: 2rem 1rem;
      text-align: center;
      color: #6c757d;
    }

    .notification-actions {
      padding: 0.75rem 1rem;
      border-top: 1px solid #dee2e6;
      background: #f8f9fa;
      border-radius: 0 0 0.5rem 0.5rem;
    }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">
<?php include("../../includes/admin_nav.php"); ?>

<!-- Main Content -->
<main class="container my-5 flex-grow-1">

  <!-- Header -->
  <div class="dashboard-header mb-4">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <h1 class="display-5 fw-bold">Admin Dashboard</h1>
        <p class="lead text-muted">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?></strong></p>
        <div class="text-muted small mb-3">
          <i class="bi bi-calendar-check"></i> <?php echo date('l, F j, Y'); ?>
        </div>
      </div>
      
      <!-- Notification Bell -->
      <div class="notification-wrapper">
        <div class="notification-icon" onclick="toggleNotifications()">
          <i class="bi bi-bell"></i>
          <span class="notification-badge" id="notificationBadge" style="display: none">0</span>
        </div>
        
        <div class="notification-dropdown" id="notificationDropdown">
          <div class="notification-header">
            <h5 class="mb-0">Notifications</h5>
            <button class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()">Mark All Read</button>
          </div>
          <div class="notification-list" id="notificationList">
            <div class="notification-item text-center">
              <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <span class="ms-2">Loading notifications...</span>
            </div>
          </div>
          <div class="notification-actions">
            <a href="manage_notifications.php" class="btn btn-sm btn-outline-secondary w-100">View All Notifications</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Dashboard Stats Row -->
  <div class="dashboard-stats d-flex justify-content-between flex-wrap gap-3 mb-5">
    <div class="stat-box">
      <div class="icon-container"><i class="bi bi-people-fill"></i></div>
      <div class="stat-number"><?php echo $counts['users']; ?></div>
      <div class="stat-label">Users</div>
    </div>

    <div class="stat-box">
      <div class="icon-container"><i class="bi bi-book-half"></i></div>
      <div class="stat-number"><?php echo $counts['books']; ?></div>
      <div class="stat-label">Books</div>
    </div>

    <div class="stat-box">
      <div class="icon-container"><i class="bi bi-cart-check"></i></div>
      <div class="stat-number"><?php echo $counts['orders_total']; ?></div>
      <div class="stat-label">
        Orders
        <?php if ($counts['orders_pending'] > 0): ?>
          <div class="stat-subtext">(<?php echo $counts['orders_pending']; ?> pending)</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="stat-box">
      <div class="icon-container"><i class="bi bi-truck"></i></div>
      <div class="stat-number"><?php echo $counts['shipping_pending']; ?></div>
      <div class="stat-label">Shipments</div>
    </div>
  </div>

  <!-- Recent Orders Section -->
  <section>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h5 class="fw-bold text-primary mb-1">Recent Orders</h5>
        <p class="text-muted small mb-0">Latest customer orders requiring attention</p>
      </div>
      <!-- FIXED: Main "View All Orders" button -->
      <a href="manage_orders.php" class="btn btn-primary rounded-3 px-4">
        <i class="bi bi-list-ul me-2"></i>View All Orders
      </a>
    </div>

    <?php if (empty($recent_orders)): ?>
      <div class="text-center py-5">
        <i class="bi bi-cart-x display-1 text-muted"></i>
        <p class="text-muted mt-3 fs-5">No recent orders found.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive rounded-4 shadow-sm">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead class="table-primary">
            <tr>
              <th class="fw-semibold">#</th>
              <th class="fw-semibold">Customer</th>
              <th class="fw-semibold">Date</th>
              <th class="fw-semibold">Total (KSh)</th>
              <th class="fw-semibold">Order Status</th>
              <th class="fw-semibold">Payment</th>
              <th class="fw-semibold text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent_orders as $r): ?>
              <tr>
                <td class="fw-bold"><?php echo (int)$r['order_id']; ?></td>
                <td>
                  <div class="d-flex align-items-center">
                    <i class="bi bi-person-circle me-2 text-muted"></i>
                    <?php echo htmlspecialchars($r['full_name'] ?? '—'); ?>
                  </div>
                </td>
                <td>
                  <i class="bi bi-calendar3 me-2 text-muted"></i>
                  <?php echo htmlspecialchars(date('d M Y', strtotime($r['order_date']))); ?>
                </td>
                <td class="fw-bold text-success"><?php echo number_format((float)$r['total_amount'], 2); ?></td>
                <td>
                  <span class="badge bg-<?php echo ($r['order_status'] === 'Pending') ? 'warning' : (($r['order_status'] === 'Completed') ? 'success' : 'info'); ?> px-3 py-2">
                    <i class="bi bi-<?php echo ($r['order_status'] === 'Pending') ? 'clock' : (($r['order_status'] === 'Completed') ? 'check-circle' : 'arrow-repeat'); ?> me-1"></i>
                    <?php echo htmlspecialchars($r['order_status']); ?>
                  </span>
                </td>
                <td>
                  <span class="badge bg-<?php echo ($r['payment_status'] === 'Paid') ? 'success' : 'secondary'; ?> px-3 py-2">
                    <i class="bi bi-<?php echo ($r['payment_status'] === 'Paid') ? 'check-circle' : 'clock'; ?> me-1"></i>
                    <?php echo htmlspecialchars($r['payment_status']); ?>
                  </span>
                </td>
                <td class="text-center">
                  <!-- FIXED: "Open" buttons for individual orders -->
                  <a href="manage_orders.php?order_id=<?php echo (int)$r['order_id']; ?>" class="btn btn-sm btn-outline-primary rounded-3 px-3">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Open
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</main>

<!-- Footer -->
<footer class="bg-primary text-white text-center py-4 mt-auto">
  <div class="container">
    <small>&copy; <?php echo date('Y'); ?> Maktaba Bookstore | Admin Panel v1.0</small>
  </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Notification System - Fixed Version
class NotificationManager {
    constructor() {
        this.lastId = 0;
        this.init();
    }

    async init() {
        await this.loadNotifications();
        // Refresh every 30 seconds
        setInterval(() => this.loadNotifications(), 30000);
    }

    async loadNotifications() {
        try {
            console.log('Loading notifications...');
            const response = await fetch(`get_notifications.php?last_id=${this.lastId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Notifications response:', data);
            
            if (data.success) {
                this.displayNotifications(data.notifications);
                const unreadCount = data.notifications.filter(n => n.status === 'unread').length;
                this.updateBadge(unreadCount);
            } else {
                throw new Error(data.message || 'Failed to load notifications');
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            this.showError('Failed to load notifications: ' + error.message);
        }
    }

    displayNotifications(notifications) {
        const container = document.getElementById('notificationList');
        if (!container) {
            console.error('Notification list container not found');
            return;
        }

        if (!notifications || notifications.length === 0) {
            container.innerHTML = '<div class="notification-empty">No notifications</div>';
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            html += `
                <div class="notification-item ${notification.status}" onclick="notificationManager.markAsRead(${notification.id}, this)">
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-time">${this.formatTime(notification.created_at)}</div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    formatTime(dateString) {
        try {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins}m ago`;
            if (diffHours < 24) return `${diffHours}h ago`;
            return date.toLocaleDateString();
        } catch (e) {
            return 'Recently';
        }
    }

    updateBadge(unreadCount) {
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            badge.textContent = unreadCount;
            badge.style.display = unreadCount > 0 ? 'inline-block' : 'none';
        }
    }

    async markAsRead(notificationId, element) {
        try {
            const response = await fetch('mark_read.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({notification_id: notificationId})
            });
            
            if (response.ok) {
                element.classList.remove('unread');
                element.classList.add('read');
                // Update badge count
                const currentCount = parseInt(document.getElementById('notificationBadge').textContent || '0');
                if (currentCount > 0) {
                    this.updateBadge(currentCount - 1);
                }
            }
        } catch (error) {
            console.error('Error marking as read:', error);
        }
    }

    showError(message) {
        const container = document.getElementById('notificationList');
        if (container) {
            container.innerHTML = `<div class="notification-item text-danger">${message}</div>`;
        }
    }
}

// Make it globally available
const notificationManager = new NotificationManager();

// Global functions
function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    const isVisible = dropdown.style.display === 'block';
    dropdown.style.display = isVisible ? 'none' : 'block';
    
    // Load notifications when dropdown is opened
    if (!isVisible) {
        notificationManager.loadNotifications();
    }
}

async function markAllAsRead() {
    try {
        const response = await fetch('mark_all_read.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
        
        if (response.ok) {
            // Reload notifications to reflect changes
            notificationManager.loadNotifications();
        }
    } catch (error) {
        console.error('Error marking all as read:', error);
        alert('Failed to mark all as read');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('notificationDropdown');
    const icon = document.querySelector('.notification-icon');
    if (dropdown && icon && !icon.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Notification manager is already initialized
});
</script>

<!-- JavaScript -->
<script src="../../assets/js/admin_dashboard.js"></script>
</body>
</html>