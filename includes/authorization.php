<?php
// authorization.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: unauthorized.php");
    exit();
}
?>