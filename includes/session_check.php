<?php
session_start();

// If user is not logged in, redirect to login
if (!isset($_SESSION['customer_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>
