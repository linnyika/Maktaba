<?php
require_once("../../database/config.php");
require_once("../../includes/session_check.php");
$result = $conn->query("SELECT a.*, u.full_name FROM audit_trail a 
                        LEFT JOIN users u ON a.user_id = u.user_id
                        ORDER BY a.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Audit Trail</title>
<link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
<h2>Audit Trail</h2>
<table>
<tr><th>User</th><th>Action</th><th>Description</th><th>IP</th><th>Date</th></tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['full_name']) ?></td>
<td><?= htmlspecialchars($row['action']) ?></td>
<td><?= htmlspecialchars($row['description']) ?></td>
<td><?= htmlspecialchars($row['ip_address']) ?></td>
<td><?= htmlspecialchars($row['created_at']) ?></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
