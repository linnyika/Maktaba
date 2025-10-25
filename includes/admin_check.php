<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
// check if the user is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}
?>
