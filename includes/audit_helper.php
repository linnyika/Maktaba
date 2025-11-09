<?php
// --------------------
// audit_helper.php
// --------------------

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Log an activity performed by a user
 *
 * @param mysqli $conn The MySQLi connection object
 * @param int $user_id ID of the user performing the activity
 * @param string $activity Description of the activity
 * @return bool True on success, false on failure
 */
function logActivity($conn, $user_id, $activity) {
    if (!$conn || !$user_id || !$activity) {
        return false; // invalid input
    }

    // Ensure activity_logs table exists
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(255) NOT NULL,
            timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX(user_id),
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $conn->query($createTableSQL);

    // Insert activity log
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, timestamp) VALUES (?, ?, NOW())");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("is", $user_id, $activity);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Optional: fetch recent activity logs
 *
 * @param mysqli $conn
 * @param int $limit
 * @return array
 */
function getActivityLogs($conn, $limit = 50) {
    $logs = [];
    $result = $conn->query("
        SELECT a.user_id, a.action, u.role, a.timestamp
        FROM activity_logs a
        LEFT JOIN users u ON a.user_id = u.user_id
        ORDER BY a.timestamp DESC
        LIMIT $limit
    ");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    }
    return $logs;
}
