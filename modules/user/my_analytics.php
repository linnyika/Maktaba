<?php
require_once("../../includes/session_check.php");
require_once("../../database/config.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Analytics | Maktaba</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../../assets/js/analytics_engine.js"></script>
</head>
<body>
<?php include("../../includes/user_nav.php"); ?>

<main class="container my-5">
  <h3 class="fw-bold text-primary mb-4"><i class="bi bi-graph-up-arrow"></i> My Analytics</h3>

  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">My Orders Over Time</div>
        <div class="card-body">
          <canvas id="myOrdersChart"></canvas>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-success text-white">My Spending Breakdown</div>
        <div class="card-body">
          <canvas id="mySpendingChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="text-end">
    <a href="../../api/export_api.php?type=user_analytics" class="btn btn-outline-secondary">
      <i class="bi bi-download"></i> Download My Report
    </a>
  </div>
</main>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    loadUserOrdersChart(<?php echo $_SESSION['user_id']; ?>);
    loadUserSpendingChart(<?php echo $_SESSION['user_id']; ?>);
  });
</script>

</body>
</html>
