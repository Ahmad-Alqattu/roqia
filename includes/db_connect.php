<?php
// includes/db_connect.php

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');        // Database Host
define('DB_USER', 'root');    // Database Username
define('DB_PASS', '');    // Database Password
define('DB_NAME', 'raqi_ecommerce');   // Database Name

// Establish a connection to the MySQL database using MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check for connection errors
if ($conn->connect_errno) {
    // Log the error message to a file or monitoring system
    error_log("Database Connection Failed: (" . $conn->connect_errno . ") " . $mysqli->connect_error);
    
    // Optionally, display a user-friendly message
    // Avoid displaying sensitive error details to the end-user
    die("Sorry, we're experiencing technical difficulties. Please try again later.");
}

// Set the character set to UTF-8 for proper encoding
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $conn->error);
    // Proceeding without setting the character set might cause encoding issues
}

// Function to close the database connection gracefully
function close_db_connection() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}

// Register the close function to execute at the end of the script
register_shutdown_function('close_db_connection');
?>
