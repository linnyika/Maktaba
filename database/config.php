<?php
// Database connection configuration
$host = 'localhost';  
$port = 3306;          
$user = 'root';
$pass = 'maria';     
$dbname = 'maktaba';  

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
