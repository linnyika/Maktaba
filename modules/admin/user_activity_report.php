<?php
ob_start();

require_once("../../database/config.php");
require_once("../../includes/session_check.php");
require_once("../../includes/audit_helper.php");
require_once("../../includes/vendor/tecnickcom/tcpdf/tcpdf.php");

// Only allow admin access
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: /index.php');
    exit;
}

// Ensure users table has role column
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user','admin') NOT NULL DEFAULT 'user'");

// Log admin activity
logActivity($conn, $_SESSION['user_id'], "Viewed User Activity & Audit Logs");

// Fetch user activity logs
$activity_logs = $conn->query("
    SELECT a.user_id, a.action, u.role, a.timestamp
    FROM activity_logs a
    INNER JOIN users u ON a.user_id = u.user_id
    ORDER BY a.timestamp DESC
");

// For audit logs, use the same table (or if you have a separate audit_trail table, adjust the table name)
$audit_logs = $conn->query("
    SELECT at.log_id, at.user_id, at.action, u.role, at.timestamp
    FROM activity_logs at
    INNER JOIN users u ON at.user_id = u.user_id
    ORDER BY at.timestamp DESC
");

// PDF generation
if (isset($_POST['generate_pdf'])) {
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Maktaba Admin');
    $pdf->SetTitle('User Activity & Audit Logs');
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'User Activity & Audit Logs', 0, 1, 'C');
    $pdf->Ln(5);

    // User Activity Table
    $pdf->SetFont('helvetica', '', 10);
    $html = '<table border="1" cellpadding="4">
    <thead>
        <tr><th>User ID</th><th>Action</th><th>Role</th><th>Timestamp</th></tr>
    </thead><tbody>';
    if ($activity_logs && $activity_logs->num_rows > 0) {
        $activity_logs->data_seek(0);
        while($log = $activity_logs->fetch_assoc()) {
            $html .= '<tr>
            <td>'.$log['user_id'].'</td>
            <td>'.$log['action'].'</td>
            <td>'.$log['role'].'</td>
            <td>'.$log['timestamp'].'</td>
            </tr>';
        }
    } else { 
        $html .= '<tr><td colspan="4">No user activity logs</td></tr>'; 
    }
    $html .= '</tbody></table>';
    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Ln(5);

    // Audit Logs Table
    $html2 = '<table border="1" cellpadding="4">
    <thead>
        <tr><th>ID</th><th>User ID</th><th>Action</th><th>Role</th><th>Log Time</th></tr>
    </thead><tbody>';
    if ($audit_logs && $audit_logs->num_rows > 0) {
        $audit_logs->data_seek(0);
        while($audit = $audit_logs->fetch_assoc()) {
            $html2 .= '<tr>
            <td>'.$audit['log_id'].'</td>
            <td>'.$audit['user_id'].'</td>
            <td>'.$audit['action'].'</td>
            <td>'.$audit['role'].'</td>
            <td>'.$audit['timestamp'].'</td>
            </tr>';
        }
    } else { 
        $html2 .= '<tr><td colspan="5">No audit logs</td></tr>'; 
    }
    $html2 .= '</tbody></table>';
    $pdf->writeHTML($html2, true, false, true, false, '');

    $pdf->Output('User_Activity_'.date('Ymd_His').'.pdf', 'D');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Activity & Audit Logs | Maktaba Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background: #e6f2eb; }
.card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
thead { background-color: #3ac47d; color: #fff; }
.search-bar { max-width: 300px; }
.tab-content { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
.btn-report { background-color:#3ac47d; color:#fff; border-radius:8px; padding:0.35rem 0.75rem; font-size:0.9rem; display:inline-flex; align-items:center; }
.btn-report:hover { background-color:#34b471; color:#fff; }
</style>
</head>
<body>
<?php include("../../includes/admin_nav.php"); ?>

<main class="container py-5">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold">User Activity & Audit Logs</h3>
    <form method="post" class="m-0">
        <button type="submit" name="generate_pdf" class="btn btn-report">
            <i class="bi bi-download me-1"></i> Download PDF
        </button>
    </form>
</div>

<div class="card shadow-lg">
  <div class="card-header d-flex justify-content-between align-items-center">
      <input type="text" id="searchInput" class="form-control search-bar" placeholder="Search by user or role...">
  </div>
  <div class="card-body">
    <ul class="nav nav-tabs" id="logTabs" role="tablist">
      <li class="nav-item">
        <button class="nav-link active fw-semibold" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">User Activity</button>
      </li>
      <li class="nav-item">
        <button class="nav-link fw-semibold" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit" type="button" role="tab">Audit Trail</button>
      </li>
    </ul>

    <div class="tab-content mt-4">
      <div class="tab-pane fade show active" id="activity" role="tabpanel">
        <div class="table-responsive">
          <table class="table table-hover table-striped align-middle" id="activityTable">
            <thead>
              <tr>
                <th>User ID</th>
                <th>Action</th>
                <th>Role</th>
                <th>Timestamp</th>
              </tr>
            </thead>
            <tbody>
            <?php if ($activity_logs && $activity_logs->num_rows > 0): ?>
              <?php $activity_logs->data_seek(0); while($log = $activity_logs->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($log['user_id']) ?></td>
                  <td><?= htmlspecialchars($log['action']) ?></td>
                  <td><?= htmlspecialchars($log['role']) ?></td>
                  <td><?= htmlspecialchars($log['timestamp']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center text-muted">No user activity logs found.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="tab-pane fade" id="audit" role="tabpanel">
        <div class="table-responsive">
          <table class="table table-hover table-striped align-middle" id="auditTable">
            <thead>
              <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Action</th>
                <th>Role</th>
                <th>Log Time</th>
              </tr>
            </thead>
            <tbody>
            <?php if ($audit_logs && $audit_logs->num_rows > 0): ?>
              <?php $audit_logs->data_seek(0); while($audit = $audit_logs->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($audit['log_id']) ?></td>
                  <td><?= htmlspecialchars($audit['user_id']) ?></td>
                  <td><?= htmlspecialchars($audit['action']) ?></td>
                  <td><?= htmlspecialchars($audit['role']) ?></td>
                  <td><?= htmlspecialchars($audit['timestamp']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="5" class="text-center text-muted">No audit records available.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</main>

<footer class="bg-success text-white text-center py-3 mt-auto">
    <small>&copy; <?= date('Y') ?> Maktaba Bookstore | Admin Reports</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('keyup', function() {
  const filter = this.value.toLowerCase();
  document.querySelectorAll('tbody tr').forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(filter) ? '' : 'none';
  });
});
</script>
</body>
</html>
