<?php
session_start();
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../database/config.php';

// --- Admin Access Guard ---
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: /index.php');
    exit;
}

// --- Handle Approve/Delete actions ---
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $conn->query("UPDATE reviews SET is_approved = 1 WHERE review_id = $id");
    header('Location: manage_reviews.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM reviews WHERE review_id = $id");
    header('Location: manage_reviews.php');
    exit;
}

// --- Fetch all reviews ---
$query = "
    SELECT 
        r.review_id,
        u.full_name,
        b.title AS book_title,
        r.rating,
        r.comment,
        r.review_date,
        r.is_approved
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    JOIN books b ON r.book_id = b.book_id
    ORDER BY r.review_date DESC
";
$res = $conn->query($query);

$reviews = [];
while ($row = $res->fetch_assoc()) {
    $reviews[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Maktaba | Manage Reviews</title>

  <!-- Bootswatch Minty -->
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include("../../includes/admin_nav.php"); ?>

<!-- Main Content -->
<main class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary">Manage Reviews</h3>
  </div>

  <?php if (empty($reviews)): ?>
    <div class="alert alert-info">No reviews found.</div>
  <?php else: ?>
    <div class="table-responsive shadow-sm rounded-4">
      <table class="table table-striped align-middle mb-0">
        <thead class="table-primary">
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Book</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reviews as $r): ?>
            <tr>
              <td><?php echo $r['review_id']; ?></td>
              <td><?php echo htmlspecialchars($r['full_name']); ?></td>
              <td><?php echo htmlspecialchars($r['book_title']); ?></td>
              <td>
                <?php for ($i = 0; $i < $r['rating']; $i++): ?>
                  <i class="bi bi-star-fill text-warning"></i>
                <?php endfor; ?>
              </td>
              <td><?php echo nl2br(htmlspecialchars($r['comment'])); ?></td>
              <td><?php echo date('d M Y, H:i', strtotime($r['review_date'])); ?></td>
              <td>
                <?php if ($r['is_approved']): ?>
                  <span class="badge bg-success">Approved</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Pending</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!$r['is_approved']): ?>
                  <a href="manage_reviews.php?approve=<?php echo $r['review_id']; ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-check-circle"></i> Approve
                  </a>
                <?php endif; ?>
                <a href="manage_reviews.php?delete=<?php echo $r['review_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this review?')">
                  <i class="bi bi-trash"></i> Delete
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>

<!-- Footer -->
 
<footer class="bg-primary text-white text-center py-3 mt-auto">
  <small>&copy; <?= date('Y') ?> Maktaba Bookstore | Admin Panel</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
