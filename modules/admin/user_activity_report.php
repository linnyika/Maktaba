<?php
require_once("../../database/config.php");
require_once("../../includes/session_check.php");

$activities = $conn->query("SELECT * FROM user_activity_report ORDER BY last_activity DESC");
$notifications = $conn->query("SELECT * FROM notifications WHERE status='unread' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>User Activity Report</title>
<link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
<h2>User Activity Report</h2>
<table>
<tr><th>User</th><th>Total Actions</th><th>Last Activity</th></tr>
<?php while($row = $activities->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['full_name']) ?></td>
<td><?= htmlspecialchars($row['total_actions']) ?></td>
<td><?= htmlspecialchars($row['last_activity']) ?></td>
</tr>
<?php endwhile; ?>
</table>

<h3>Notifications</h3>
<ul>
<?php while($note = $notifications->fetch_assoc()): ?>
<li><?= htmlspecialchars($note['message']) ?> (<?= $note['created_at'] ?>)</li>
<?php endwhile; ?>
</ul>
</body>
</html>
