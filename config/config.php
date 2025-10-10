<?php
// Database connection configuration
$host = 'localhost';
$user = 'root';
$pass = ''; // change if you have a password
$dbname = 'maktaba_db';

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
