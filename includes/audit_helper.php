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
 * @param string $action Description of the activity
 * @return bool True on success, false on failure
 */
function logActivity(mysqli $conn, int $user_id, string $action): bool {
    if (!$conn || $user_id <= 0 || empty($action)) {
        return false; // invalid input
    }

    // Ensure the activity_logs table exists
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(255) NOT NULL,
            timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX(user_id),
            CONSTRAINT fk_user_activity FOREIGN KEY (user_id)
                REFERENCES users(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $conn->query($createTableSQL);

    // Insert the activity log safely
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, timestamp) VALUES (?, ?, NOW())");
    if (!$stmt) {
        error_log("Failed to prepare activity log statement: " . $conn->error);
        return false;
    }

    $stmt->bind_param("is", $user_id, $action);
    $success = $stmt->execute();

    if (!$success) {
        error_log("Failed to execute activity log statement: " . $stmt->error);
    }

    $stmt->close();
    return $success;
}

/**
 * Fetch recent activity logs
 *
 * @param mysqli $conn
 * @param int $limit Number of logs to fetch
 * @return array
 */
function getActivityLogs(mysqli $conn, int $limit = 50): array {
    $logs = [];
    $limit = max(1, $limit); // ensure positive integer

    $sql = "
        SELECT a.id, a.user_id, a.action, a.timestamp, u.full_name, u.user_role
        FROM activity_logs a
        LEFT JOIN users u ON a.user_id = u.user_id
        ORDER BY a.timestamp DESC
        LIMIT ?
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare fetch logs statement: " . $conn->error);
        return $logs;
    }

    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    $stmt->close();
    return $logs;
}
