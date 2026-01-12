<?php
/**
 * Database Connection File for Campus Food Reviewer
 * Uses mysqli for MySQL database connectivity
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Change to your database username
define('DB_PASS', '');               // Change to your database password
define('DB_NAME', 'campus_food_reviewer');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection and handle errors
if ($conn->connect_error) {
    // Log error (in production, log to file instead of displaying)
    error_log("Database Connection Failed: " . $conn->connect_error);
    
    // Display user-friendly error message
    die("Connection failed. Please try again later.");
}

// Set charset to utf8mb4 for proper character encoding
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $conn->error);
    die("Character set error. Please try again later.");
}

// Optional: Set timezone (adjust as needed)
$conn->query("SET time_zone = '+08:00'");

// Connection successful
// You can uncomment the line below for testing purposes
// echo "Database connection successful!";

?>
