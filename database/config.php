<?php
// Database connection configuration
$host = 'localhost';  
$port = 3306;          // adjust MySQL port
$user = 'root';
$pass = 'MariaDB';     // adjust if your MySQL password differs
$dbname = 'maktaba';   // change if your actual database name differs

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>