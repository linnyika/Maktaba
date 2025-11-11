<?php
require_once("../../database/config.php");
require_once("../../includes/session_check.php");
require_once("../../includes/admin_check.php");
require_once("../../includes/admin_nav.php");
require_once("../../includes/logger.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics Dashboard | Maktaba Admin</title>
  <link href="../../assets/css/admin.css" rel="stylesheet">
  <link href="../../assets/css/analytics.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://www.gstatic.com/charts/loader.js"></script>
</head>

<body>
  <?php include("../../includes/admin_nav.php"); ?>

  <div class="container-fluid mt-4">
    <div class="row mb-3">
      <div class="col-md-12 text-center">
        <h2 class="fw-bold text-primary">📊 Analytics Dashboard</h2>
        <p class="text-muted">Real-time insights across sales, books, and users</p>
      </div>
    </div>

    <!-- Row 1: Sales and Orders -->
    <div class="row g-3">
      <div class="col-md-6">
        <div class="analytics-card shadow-sm">
          <h5 class="card-title">Sales Overview (Last 30 Days)</h5>
          <canvas id="salesTrendChart" height="120"></canvas>
        </div>
      </div>

      <div class="col-md-6">
        <div class="analytics-card shadow-sm">
          <h5 class="card-title">Orders Breakdown</h5>
          <div id="orderStatusPieChart" style="height: 280px;"></div>
        </div>
      </div>
    </div>

    <!-- Row 2: Book & User Analytics -->
    <div class="row g-3 mt-3">
      <div class="col-md-6">
        <div class="analytics-card shadow-sm">
          <h5 class="card-title">Top Performing Books</h5>
          <canvas id="bookPerformanceChart" height="120"></canvas>
        </div>
      </div>

      <div class="col-md-6">
        <div class="analytics-card shadow-sm">
          <h5 class="card-title">Active Users (By Month)</h5>
          <div id="userActivityChart" style="height: 280px;"></div>
        </div>
      </div>
    </div>

    <!-- Row 3: Revenue Summary -->
    <div class="row g-3 mt-3">
      <div class="col-md-12">
        <div class="analytics-card shadow-sm">
          <h5 class="card-title">Revenue Performance</h5>
          <canvas id="revenueBarChart" height="120"></canvas>
        </div>
      </div>
    </div>
  </div>

  <script src="../../assets/js/chart_config.js"></script>
</body>
</html>
