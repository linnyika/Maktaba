<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/admin_check.php';
require_once __DIR__ . '/../../database/config.php';

// filters
$genre = $_GET['genre'] ?? '';
$rating = $_GET['rating'] ?? '';

// get genres
$genres_result = $conn->query("SELECT DISTINCT genre FROM books WHERE genre IS NOT NULL ORDER BY genre ASC");

// base query
$query = "SELECT * FROM book_performance_view WHERE 1=1";
$params = [];
$types = "";

// genre filter
if (!empty($genre)) {
    $query .= " AND genre = ?";
    $params[] = $genre;
    $types .= "s";
}

// rating filter
if (!empty($rating)) {
    $query .= " AND avg_rating >= ?";
    $params[] = $rating;
    $types .= "d";
}

$query .= " ORDER BY total_sold DESC LIMIT 50";

$stmt = $conn->prepare($query);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Performance Analytics | Admin - Maktaba</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/analytics.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <?php include("../../includes/admin_nav.php"); ?>

  <div class="container my-5">
    <h3 class="text-primary fw-bold mb-4"><i class="bi bi-graph-up"></i> Book Performance Analytics</h3>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
      <div class="col-md-4">
        <select name="genre" class="form-select">
          <option value="">All Genres</option>
          <?php while($g = $genres_result->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($g['genre']) ?>" <?= $genre==$g['genre']?'selected':'' ?>>
              <?= htmlspecialchars($g['genre']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-4">
        <select name="rating" class="form-select">
          <option value="">All Ratings</option>
          <option value="4">4 stars & above</option>
          <option value="3">3 stars & above</option>
          <option value="2">2 stars & above</option>
        </select>
      </div>
      <div class="col-md-4">
        <button class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Apply Filters</button>
      </div>
    </form>

    <!-- Table -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light"><h5 class="mb-0">Performance Summary</h5></div>
      <div class="card-body table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-primary">
            <tr>
              <th>Book Title</th>
              <th>Genre</th>
              <th>Avg Rating</th>
              <th>Total Sold</th>
              <th>Revenue (KSh)</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $chart_titles = [];
            $chart_sales = [];
            $chart_revenue = [];

            if ($result->num_rows > 0): 
              while($row = $result->fetch_assoc()):
                $chart_titles[] = $row['title'];
                $chart_sales[] = $row['total_sold'];
                $chart_revenue[] = $row['total_revenue'];
            ?>
              <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['genre'] ?? '-') ?></td>
                <td><?= number_format($row['avg_rating'], 1) ?> ⭐</td>
                <td><?= $row['total_sold'] ?></td>
                <td><?= number_format($row['total_revenue'], 2) ?></td>
              </tr>
            <?php endwhile; else: ?>
              <tr><td colspan="5" class="text-center text-muted">No records found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Chart -->
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white"><h5 class="mb-0">Top 10 Books by Sales</h5></div>
      <div class="card-body">
        <canvas id="salesChart" height="120"></canvas>
      </div>
    </div>
  </div>

  <script>
  const ctx = document.getElementById('salesChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_slice($chart_titles, 0, 10)) ?>,
      datasets: [{
        label: 'Total Sold',
        data: <?= json_encode(array_slice($chart_sales, 0, 10)) ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.6)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
  </script>
</body>
</html>
