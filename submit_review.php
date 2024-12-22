<?php
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to submit a review.");
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id']);
$rating = intval($_POST['rating']);
$review_text = mysqli_real_escape_string($conn, $_POST['review_text']);

// Insert review into the database
$sql = "INSERT INTO reviews (product_id, user_id, rating, review_text) 
        VALUES ($product_id, $user_id, $rating, '$review_text')";

if (mysqli_query($conn, $sql)) {
    header("Location: product_detail.php?id=$product_id");
    exit;
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
