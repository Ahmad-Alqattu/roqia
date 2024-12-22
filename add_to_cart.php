<?php

require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php"); // Redirect to login if not logged in
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    if ($product_id <= 0 || $quantity <= 0) {
        die("Invalid product or quantity.");
    }

    // 1. Retrieve or create a cart for the user
    $cart_check = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
    $cart_check->bind_param("i", $user_id);
    $cart_check->execute();
    $cart_result = $cart_check->get_result();

    if ($cart_result->num_rows > 0) {
        $cart = $cart_result->fetch_assoc();
        $cart_id = $cart['cart_id'];
    } else {
        // Create a new cart if none exists
        $create_cart = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
        $create_cart->bind_param("i", $user_id);
        $create_cart->execute();
        $cart_id = $create_cart->insert_id;
    }

    // 2. Check if the product already exists in the cart_items table
    $check_cart_item = $conn->prepare("SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $check_cart_item->bind_param("ii", $cart_id, $product_id);
    $check_cart_item->execute();
    $result = $check_cart_item->get_result();

    if ($result->num_rows > 0) {
        // Update the quantity if the product already exists
        $existing = $result->fetch_assoc();
        $new_quantity = $existing['quantity'] + $quantity;

        $update_cart_item = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?");
        $update_cart_item->bind_param("iii", $new_quantity, $cart_id, $product_id);
        $update_cart_item->execute();
    } else {
        // Insert a new item into the cart_items table
        $insert_cart_item = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert_cart_item->bind_param("iii", $cart_id, $product_id, $quantity);
        $insert_cart_item->execute();
    }

    header("Location: cart.php"); // Redirect to cart page after adding item
    exit();
}
?>
