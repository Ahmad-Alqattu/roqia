<?php

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');        
define('DB_PORT', 3306);        
define('DB_USER', 'root');    
define('DB_PASS', '');    
define('DB_NAME', 'raqi_ecommerce');  

// Establish a connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check errors
if ($conn->connect_errno) {
    error_log("Database Connection Failed: (" . $conn->connect_errno . ") " . $mysqli->connect_error);
   
}

// Function to close the database connection 
function close_db_connection() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}

// Register the close function to execute at the end of the script
register_shutdown_function('close_db_connection');
?>
