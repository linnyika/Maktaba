<?php
session_start();
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../database/config.php';

// --- Admin access guard ---
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: /index.php');
    exit;
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_all_read'])) {
        $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete_read'])) {
        $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ? AND status = 'read'");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete_id'])) {
        $notification_id = (int)$_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notification_id, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
    }
    
    header('Location: manage_notifications.php');
    exit;
}

// Get all notifications
$notifications = [];
$stmt = $conn->prepare("SELECT id, message, status, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

$unread_count = 0;
foreach ($notifications as $notification) {
    if ($notification['status'] === 'unread') {
        $unread_count++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Maktaba | Manage Notifications</title>

  <!-- Bootswatch Minty -->
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../assets/css/admin.css?v=4" />
  
  <style>
    .notification-item {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }
    .notification-item.unread {
        border-left-color: #8b5a2b;
        background-color: #f8f9fa;
    }
    .notification-item:hover {
        background-color: #e9ecef;
    }
    .badge-unread {
        background-color: #8b5a2b;
    }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">
<?php include("../../includes/admin_nav.php"); ?>

<!-- Main Content -->
<main class="container my-5 flex-grow-1">
  <!-- Header -->
  <div class="dashboard-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 class="display-5 fw-bold">Manage Notifications</h1>
        <p class="lead text-muted">View and manage your notifications</p>
      </div>
      <div class="d-flex gap-2">
        <form method="POST" class="d-inline">
          <button type="submit" name="mark_all_read" class="btn btn-success">
            <i class="bi bi-check-all me-2"></i>Mark All as Read
          </button>
        </form>
        <form method="POST" class="d-inline">
          <button type="submit" name="delete_read" class="btn btn-outline-danger" 
                  onclick="return confirm('Delete all read notifications?')">
            <i class="bi bi-trash me-2"></i>Delete Read
          </button>
        </form>
        <a href="dashboard.php" class="btn btn-primary">
          <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
      </div>
    </div>
    
    <!-- Stats -->
    <div class="row mt-4">
      <div class="col-md-3">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="card-title"><?php echo count($notifications); ?></h4>
                <p class="card-text">Total Notifications</p>
              </div>
              <i class="bi bi-bell display-6 opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-warning text-dark">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="card-title"><?php echo $unread_count; ?></h4>
                <p class="card-text">Unread Notifications</p>
              </div>
              <i class="bi bi-bell-fill display-6 opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Notifications List -->
  <section>
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">
          <i class="bi bi-bell me-2"></i>All Notifications
        </h5>
      </div>
      <div class="card-body p-0">
        <?php if (empty($notifications)): ?>
          <div class="text-center py-5">
            <i class="bi bi-bell-slash display-1 text-muted"></i>
            <p class="text-muted mt-3 fs-5">No notifications found.</p>
            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
          </div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($notifications as $notification): ?>
              <div class="list-group-item notification-item <?php echo $notification['status'] === 'unread' ? 'unread' : ''; ?>">
                <div class="d-flex justify-content-between align-items-start">
                  <div class="flex-grow-1 me-3">
                    <div class="d-flex align-items-center mb-2">
                      <?php if ($notification['status'] === 'unread'): ?>
                        <span class="badge badge-unread me-2">Unread</span>
                      <?php else: ?>
                        <span class="badge bg-secondary me-2">Read</span>
                      <?php endif; ?>
                      <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>
                        <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                      </small>
                    </div>
                    <p class="mb-1 fs-6"><?php echo htmlspecialchars($notification['message']); ?></p>
                  </div>
                  <div class="d-flex gap-1">
                    <?php if ($notification['status'] === 'unread'): ?>
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="mark_read_id" value="<?php echo $notification['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as Read">
                          <i class="bi bi-check"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="delete_id" value="<?php echo $notification['id']; ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger" 
                              onclick="return confirm('Delete this notification?')" title="Delete">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
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
</body>
</html>