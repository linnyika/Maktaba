<?php
// manage_publishers.php - Quick version
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/admin_check.php';
require_once __DIR__ . '/../../database/config.php';

// Handle add publisher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_publisher'])) {
    $name = trim($_POST['name']);
    $conn->query("INSERT INTO publishers (name) VALUES ('$name')");
    header("Location: manage_publishers.php?success=1");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Publishers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include("../../includes/admin_nav.php"); ?>
<div class="container mt-4">
    <h3>Manage Publishers</h3>
    <form method="POST" class="mb-4">
        <input type="hidden" name="add_publisher" value="1">
        <div class="row">
            <div class="col-md-8">
                <input type="text" name="name" class="form-control" placeholder="Publisher Name" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success">Add Publisher</button>
            </div>
        </div>
    </form>
    
    <h5>Existing Publishers:</h5>
    <ul class="list-group">
        <?php
        $result = $conn->query("SELECT * FROM publishers ORDER BY name");
        while ($row = $result->fetch_assoc()) {
            echo '<li class="list-group-item">' . htmlspecialchars($row['name']) . '</li>';
        }
        ?>
    </ul>
    <a href="manage_books.php" class="btn btn-primary mt-3">Back to Books</a>
</div>
</body>
</html>