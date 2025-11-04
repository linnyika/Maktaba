<?php
require_once("../../database/config.php");
require_once("../../includes/session_check.php");
require_once("../../includes/logger.php");

// Fetch filter parameters
$userFilter = $_GET['user'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';

// Build query dynamically
$query = "
    SELECT logs.*, users.full_name 
    FROM logs 
    JOIN users ON logs.user_id = users.user_id 
    WHERE 1=1
";

$params = [];
$types = '';

if (!empty($userFilter)) {
    $query .= " AND logs.user_id = ?";
    $params[] = $userFilter;
    $types .= 'i';
}
if (!empty($dateFilter)) {
    $query .= " AND DATE(logs.timestamp) = ?";
    $params[] = $dateFilter;
    $types .= 's';
}
if (!empty($search)) {
    $query .= " AND logs.action LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

$query .= " ORDER BY logs.timestamp DESC";

$stmt = $conn->prepare($query);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch all users for filter dropdown
$users = $conn->query("SELECT user_id, full_name FROM users ORDER BY full_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Activity Logs - Maktaba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e6f4ea, #ffffff);
            font-family: 'Poppins', sans-serif;
            padding: 40px 0;
        }
        .container {
            max-width: 1100px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background: #2d8a34;
            color: #fff;
            border-radius: 15px 15px 0 0;
        }
        .btn-success {
            background-color: #2d8a34;
            border: none;
        }
        .btn-success:hover {
            background-color: #256c2a;
        }
        table {
            font-size: 0.95rem;
        }
        th {
            background-color: #2d8a34;
            color: #fff;
        }
        tr:hover {
            background-color: #f4fff4;
        }
        .filter-section select, 
        .filter-section input {
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        .filter-section button {
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header text-center py-3">
            <h4 class="mb-0"><i class="bi bi-activity"></i> Activity Logs</h4>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center filter-section mb-4">
                <div class="col-md-4">
                    <select name="user" class="form-select">
                        <option value="">All Users</option>
                        <?php while ($u = $users->fetch_assoc()): ?>
                            <option value="<?= $u['user_id'] ?>" <?= $userFilter == $u['user_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['full_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($dateFilter) ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search action..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-filter"></i> Filter</button>
                    <a href="view_logs.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Clear</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): $count = 1; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                                    <td><?= htmlspecialchars($row['action']) ?></td>
                                    <td><?= htmlspecialchars($row['timestamp']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No logs found for the selected filters.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
