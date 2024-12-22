<?php
session_start();
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php"); // Redirect to login if not logged in
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);

    if ($product_id <= 0) {
        die("Invalid product.");
    }

    // Check if the product exists
    $product_check = $conn->prepare("SELECT product_id FROM products WHERE product_id = ?");
    $product_check->bind_param("i", $product_id);
    $product_check->execute();
    $product_result = $product_check->get_result();

    if ($product_result->num_rows === 0) {
        die("Product not found.");
    }

// Check if item already exists in the wishlist
$check_wishlist = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
$check_wishlist->bind_param("ii", $user_id, $product_id);
$check_wishlist->execute();
$check_wishlist->store_result();

if ($check_wishlist->num_rows == 0) {
    // Item does not exist, add to wishlist
    $insert_wishlist = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $insert_wishlist->bind_param("ii", $user_id, $product_id);

    if ($insert_wishlist->execute()) {
        header("Location: wishlist.php"); // Redirect to wishlist page
        exit();
    } else {
        die("Error adding product to wishlist.");
    }

    $insert_wishlist->close();
} else {
    // Item already exists
    echo "Item already in wishlist.";
  
    header("Location: wishlist.php"); // Redirect to wishlist page
    exit();
}

// Close the check statement
$check_wishlist->close();

}
?>
