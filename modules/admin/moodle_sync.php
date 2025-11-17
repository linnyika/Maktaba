<?php
// Include the database configuration
require_once '../../database/config.php'; // Adjust path to your config.php

// --- Moodle API Configuration ---
$moodle_url = "http://localhost/Maktaba/Moodle-5.0.2/webservice/rest/server.php"; // Updated URL
$token = "YOUR_REAL_TOKEN"; // <-- Replace with your actual Moodle token
$function = "core_course_get_courses"; // Function to get all courses

// Build Moodle API request URL
$request_url = $moodle_url . "?wstoken=$token&wsfunction=$function&moodlewsrestformat=json";

// --- Fetch Courses from Moodle using cURL ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $request_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute request
$response = curl_exec($ch);

// Handle cURL errors
if(curl_errno($ch)){
    die("cURL Error: " . curl_error($ch));
}

// Get HTTP status code
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Check if server returned 404 or other error
if ($http_code != 200) {
    die("<h3>Error: Moodle URL not reachable.</h3>
         <p>HTTP status code: $http_code</p>
         <p>Check your Moodle REST URL and make sure the file <code>webservice/rest/server.php</code> exists inside Moodle-5.0.2 folder.</p>");
}

// Decode JSON response
$courses = json_decode($response, true);

// Check if response is valid JSON
if ($courses === null) {
    echo "<h3>Unable to decode Moodle response. Raw response:</h3>";
    echo "<pre>";
    print_r($response);
    echo "</pre>";
    die("Check your Moodle URL and token. The URL must point to your REST server.php and token must be valid.");
}

// --- Sync Courses to Bookstore ---
foreach ($courses as $course) {
    $course_id = $course['id'];
    $course_name = $conn->real_escape_string($course['fullname']); // Course title

    // Check if this course already exists in books table
    $check = $conn->query("SELECT * FROM books WHERE moodle_course = '$course_id'");
    
    if ($check->num_rows == 0) {
        // Insert new book with default values
        $sql = "INSERT INTO books 
                (title, author, publisher_id, price, stock_quantity, reserved_stock, genre, yop, description, `book cover`, moodle_course, is_available, date_added)
                VALUES 
                ('$course_name', 'TBD', 0, 0.00, 0, 0, 'Unknown', 0, 'TBD', '', '$course_id', 1, NOW())";
        
        if ($conn->query($sql) === TRUE) {
            echo "Added book for course: $course_name<br>";
        } else {
            echo "Error adding book: " . $conn->error . "<br>";
        }
    } else {
        echo "Book already exists for course: $course_name<br>";
    }
}

// --- Close Database Connection ---
$conn->close();
?>
