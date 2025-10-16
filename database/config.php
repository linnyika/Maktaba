<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Database configuration
$host = "127.0.0.1";
$port = "3307";
$servername = "localhost";
$username = "root";       
$password = "mariadb";        
$dbname = "maktaba_db";   

// Create connection
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connected successfully!";
?>
s