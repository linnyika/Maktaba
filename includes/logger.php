<?php
require_once(__DIR__ . '/../database/config.php');

function logActivity($user_id, $action, $module, $description) {
    global $conn; // Access DB connection

    if (!$conn) {
        error_log("Database connection not found in logger.php");
        return;
    }

    // If the user_id is 0 or invalid, set it to NULL to avoid FK constraint errors
    if (empty($user_id) || $user_id == 0) {
        $user_id = null;
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare("
        INSERT INTO logs (user_id, action, module, description, timestamp)
        VALUES (?, ?, ?, ?, NOW())
    ");

    if ($stmt) {
        //  Use 'i' if integer, but NULL must be bound using mysqli_stmt::bind_param carefully
        // To safely handle NULL, we must pass the variable by reference and specify the type
        $stmt->bind_param("isss", $user_id, $action, $module, $description);
        
        // Special handling: if user_id is NULL, update the parameter to NULL explicitly
        if (is_null($user_id)) {
            $stmt->bind_param("isss", $user_id, $action, $module, $description);
        }

        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Failed to prepare log statement: " . $conn->error);
    }
}
?>
