<?php
require_once("../../includes/session_check.php");
require_once("../../includes/admin_check.php");
require_once("../../database/config.php");
require_once("../../includes/report_helper.php");
require_once("../../includes/summary_helper.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Analytics Dashboard | Maktaba Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../../assets/js/analytics_engine.js"></script>
</head>
<body>
<?php include("../../includes/admin_nav.php"); ?>

<main class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary"><i class="bi bi-bar-chart-line"></i> Analytics Dashboard</h3>
    <div>
      <a href="../../api/export_api.php?type=summary_pdf" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-file-earmark-pdf"></i> Export PDF
      </a>
      <a href="../../api/export_api.php?type=summary_excel" class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-excel"></i> Export Excel
      </a>
    </div>
  </div>

  <!-- Summary Cards -->
  <section class="row g-3 mb-5">
    <div class="col-md-3">
      <div class="card shadow-sm text-center p-3 bg-light">
        <h6>Total Sales</h6>
        <h4 id="salesCount">KES 0</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center p-3 bg-light">
        <h6>Total Orders</h6>
        <h4 id="orderCount">0</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center p-3 bg-light">
        <h6>Active Users</h6>
        <h4 id="userCount">0</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center p-3 bg-light">
        <h6>Books in Catalog</h6>
        <h4 id="bookCount">0</h4>
      </div>
    </div>
  </section>

  <!-- Sales Chart -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white fw-bold">
      <i class="bi bi-graph-up"></i> Sales Performance
    </div>
    <div class="card-body">
      <canvas id="salesChart" height="120"></canvas>
    </div>
  </div>

  <!-- Orders vs Revenue -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-success text-white fw-bold">
      <i class="bi bi-currency-exchange"></i> Orders vs Revenue
    </div>
    <div class="card-body">
      <canvas id="ordersRevenueChart" height="120"></canvas>
    </div>
  </div>

  <!-- Popular Books -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white fw-bold">
      <i class="bi bi-book-half"></i> Top Performing Books
    </div>
    <div class="card-body">
      <canvas id="popularBooksChart" height="120"></canvas>
    </div>
  </div>
</main>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    loadDashboardStats();
    loadSalesChart();
    loadOrdersRevenueChart();
    loadPopularBooksChart();
  });
</script>

</body>
</html>
