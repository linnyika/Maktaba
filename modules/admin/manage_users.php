<?php
require_once("../../database/config.php");
require_once("../../includes/admin_check.php");

$message = "";

// Delete user
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE user_id = $id");
    $message = "User deleted successfully.";
}

// --- Handle search and filter ---
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';

$query = "SELECT * FROM users WHERE 1";
$params = [];
$types = '';

if ($search) {
    $query .= " AND (full_name LIKE ? OR email LIKE ?)";
    $searchParam = "%$search%";
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $types .= 'ss';
}

if ($role_filter) {
    $query .= " AND user_role = ?";
    $params[] = &$role_filter;
    $types .= 's';
}

$query .= " ORDER BY date_created DESC";

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users | Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow p-4 border-success">
    <h3 class="text-success mb-3">Manage Users</h3>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <!-- Search & Filter -->
    <form method="get" class="row g-2 mb-4">
      <div class="col-md-5">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search name or email..." class="form-control">
      </div>
      <div class="col-md-4">
        <select name="role" class="form-select">
          <option value="">All Roles</option>
          <option value="user" <?= $role_filter === 'Customer' ? 'selected' : '' ?>>Customer</option>
          <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
      </div>
      <div class="col-md-3 d-grid">
        <button type="submit" class="btn btn-success">Apply Filter</button>
      </div>
    </form>

    <!-- Users Table -->
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-success">
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Last Login</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($users->num_rows > 0): ?>
            <?php while ($u = $users->fetch_assoc()): ?>
              <tr>
                <td><?= $u['user_id'] ?></td>
                <td><?= htmlspecialchars($u['full_name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td>
                  <span class="badge bg-<?= $u['user_role'] === 'admin' ? 'danger' : 'success' ?>">
                    <?= ucfirst($u['user_role']) ?>
                  </span>
                </td>
                <td><?= $u['last_login'] ?? '—' ?></td>
                <td>
                  <a href="edit_user.php?id=<?= $u['user_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                  <a href="?delete=<?= $u['user_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-3">No users found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
