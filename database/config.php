<?php
// Database connection configuration
$host = 'localhost';   // or 'localhost'
$port = 3307;          // custom MySQL port
$user = 'root';
$pass = 'mariadb';     // adjust if your MySQL password differs
$dbname = 'maktaba_db';   // change if your actual database name differs

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
} else {
    echo "Successfully connected to the database!";
    die("Database connection failed: " . $conn->connect_error);
}

// Optional: uncomment this to confirm successful connection during testing
// echo "Successfully connected to the database!";
?>
