<?php
// modules/admin/system_reports.php
require_once("../../database/config.php");
require_once("../../includes/session_check.php");

// helper: run count query safely (returns 0 on error)
function runCountQuery($conn, $query) {
    $res = $conn->query($query);
    if (!$res) return 0;
    $row = $res->fetch_row();
    return (int)($row[0] ?? 0);
}

// Summary counts
$totalUsers  = runCountQuery($conn, "SELECT COUNT(*) FROM users");
$totalBooks  = runCountQuery($conn, "SELECT COUNT(*) FROM books");
$totalLogs   = runCountQuery($conn, "SELECT COUNT(*) FROM activity_logs");
$totalAudits = runCountQuery($conn, "SELECT COUNT(*) FROM audit_trail");

// Discover audit_trail columns to adapt dynamically
$columns = [];
$colQ = $conn->query("
    SELECT COLUMN_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'audit_trail'
");
if ($colQ) {
    while ($c = $colQ->fetch_assoc()) {
        $columns[] = $c['COLUMN_NAME'];
    }
}

// Determine which columns exist
$col_id = in_array('id', $columns) ? 'id' : (in_array('audit_id', $columns) ? 'audit_id' : null);
$col_user = in_array('user_id', $columns) ? 'user_id' : (in_array('admin_id', $columns) ? 'admin_id' : null);
$col_action = in_array('action', $columns) ? 'action' : (in_array('activity', $columns) ? 'activity' : null);
$col_description = in_array('description', $columns) ? 'description' : (in_array('details', $columns) ? 'details' : null);
$col_ip = in_array('ip_address', $columns) ? 'ip_address' : (in_array('ip', $columns) ? 'ip' : null);
$col_timestamp = in_array('timestamp', $columns) ? 'timestamp' : (in_array('created_at', $columns) ? 'created_at' : (in_array('date', $columns) ? 'date' : null));

// Build select list dynamically
$selectCols = [];
if ($col_id) $selectCols[] = $col_id . " AS id";
if ($col_user) $selectCols[] = $col_user . " AS user_id";
if ($col_action) $selectCols[] = $col_action . " AS action";
if ($col_description) $selectCols[] = $col_description . " AS description";
if ($col_ip) $selectCols[] = $col_ip . " AS ip_address";
if ($col_timestamp) $selectCols[] = $col_timestamp . " AS timestamp";

// Fetch audit logs if possible
$audits = false;
if (!empty($selectCols)) {
    $selectSql = implode(", ", $selectCols);
    $sql = "SELECT $selectSql FROM audit_trail ORDER BY " . ($col_timestamp ?? ($col_id ?? '1')) . " DESC LIMIT 100";
    $audits = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>System Reports | Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8fafc; }
    .card { border-radius: 12px; border: none; box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
    .stat-card { padding: 20px; text-align:center; }
    .stat-card .num { font-size: 1.75rem; font-weight:700; }
    .table thead th { background: #eef7ff; }
  </style>
</head>
<body>
  <?php include("../../includes/admin_nav.php"); ?>

  <main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold text-primary"><i class="bi bi-bar-chart-line me-2"></i>System Reports</h2>
        <p class="text-muted mb-0">Overview of system usage and recent audit entries.</p>
      </div>
    </div>

    <!-- Summary -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card stat-card">
          <div class="text-muted">Registered Users</div>
          <div class="num text-primary"><?= htmlspecialchars($totalUsers) ?></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card">
          <div class="text-muted">Books Listed</div>
          <div class="num text-success"><?= htmlspecialchars($totalBooks) ?></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card">
          <div class="text-muted">Activity Logs</div>
          <div class="num text-warning"><?= htmlspecialchars($totalLogs) ?></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card">
          <div class="text-muted">Audit Entries</div>
          <div class="num text-danger"><?= htmlspecialchars($totalAudits) ?></div>
        </div>
      </div>
    </div>

    <!-- Export UI (buttons) -->
    <div class="mb-4">
      <div class="d-flex gap-2">
        <form method="post" action="download_excel.php">
          <button type="submit" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
          </button>
        </form>
        <form method="post" action="download_pdf.php">
          <button type="submit" class="btn btn-danger btn-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
          </button>
        </form>
      </div>
    </div>

    <!-- Audit trail table -->
    <div class="card mb-4">
      <div class="card-header bg-danger text-white">
        <i class="bi bi-shield-lock-fill me-2"></i>Recent Audit Trail Entries
      </div>
      <div class="card-body">
        <?php if ($audits === false): ?>
          <div class="alert alert-warning">Audit trail table missing or has an unexpected schema.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table id="auditTable" class="table table-striped table-hover">
              <thead>
                <tr>
                  <?php if ($col_id): ?><th>#</th><?php endif; ?>
                  <?php if ($col_user): ?><th>User ID</th><?php endif; ?>
                  <?php if ($col_action): ?><th>Action</th><?php endif; ?>
                  <?php if ($col_description): ?><th>Description</th><?php endif; ?>
                  <?php if ($col_ip): ?><th>IP Address</th><?php endif; ?>
                  <?php if ($col_timestamp): ?><th>Timestamp</th><?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php if ($audits && $audits->num_rows > 0): ?>
                  <?php while ($row = $audits->fetch_assoc()): ?>
                    <tr>
                      <?php if ($col_id): ?><td><?= htmlspecialchars($row['id'] ?? '') ?></td><?php endif; ?>
                      <?php if ($col_user): ?><td><?= htmlspecialchars($row['user_id'] ?? '') ?></td><?php endif; ?>
                      <?php if ($col_action): ?><td><?= htmlspecialchars($row['action'] ?? '') ?></td><?php endif; ?>
                      <?php if ($col_description): ?><td><?= htmlspecialchars($row['description'] ?? '') ?></td><?php endif; ?>
                      <?php if ($col_ip): ?><td><?= htmlspecialchars($row['ip_address'] ?? '') ?></td><?php endif; ?>
                      <?php if ($col_timestamp): ?><td><?= htmlspecialchars($row['timestamp'] ?? '') ?></td><?php endif; ?>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="<?= max(1, count($selectCols)) ?>" class="text-center text-muted">No audit records found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <footer class="bg-primary text-white text-center py-3 mt-auto">
    <small>&copy; <?= date('Y') ?> Maktaba Bookstore | System Reports</small>
  </footer>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#auditTable').DataTable({
        "order": [],
        "pageLength": 25
      });
    });
  </script>
</body>
</html>
