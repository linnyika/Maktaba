<?php
// moodle_integration.php
require_once(__DIR__ . '/../includes/session_check.php');
require_once(__DIR__ . '/../database/config.php');

$user_id = $_SESSION['user_id'] ?? 0;

// Fetch orders for the user that are linked to Moodle courses
$stmt = $conn->prepare("
    SELECT o.order_id, b.title AS book_title, b.moodle_course_id
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN books b ON oi.book_id = b.book_id
    WHERE o.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    if ($row['moodle_course_id']) {
        $courses[$row['order_id']][] = [
            'book_title' => $row['book_title'],
            'course_id' => $row['moodle_course_id']
        ];
    }
}

return $courses; 