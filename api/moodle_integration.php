<?php
// moodle_integration.php
require_once("../includes/session_check.php");
require_once("../database/config.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch orders for the user that are linked to Moodle courses
$stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, b.title AS book_title, b.moodle_course_id
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
        $courses[] = [
            'order_id' => $row['order_id'],
            'book_title' => $row['book_title'],
            'course_id' => $row['moodle_course_id'],
            'order_date' => $row['order_date'],
        ];
    }
}

header('Content-Type: application/json');
echo json_encode([
    'user_id' => $user_id,
    'moodle_courses' => $courses
]);
