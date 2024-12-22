<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Please <a href='login.php'>login</a> to proceed with checkout.");
}

$user_id = $_SESSION['user_id'];

// Fetch cart for the user
$cart_sql = "SELECT cart_id FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($cart_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();

if ($cart_result->num_rows === 0) {
    die("Your cart is empty. <a href='products.php'>Go shopping</a>");
}

$cart = $cart_result->fetch_assoc();
$cart_id = $cart['cart_id'];

// Fetch cart items
$items_sql = "SELECT ci.product_id, ci.quantity, p.price 
              FROM cart_items ci 
              JOIN products p ON ci.product_id = p.product_id 
              WHERE ci.cart_id = ?";
$stmt = $conn->prepare($items_sql);
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$items_result = $stmt->get_result();

if ($items_result->num_rows === 0) {
    die("Your cart is empty. <a href='products.php'>Go shopping</a>");
}

// Calculate total amount and prepare order items
$total_amount = 0;
$order_items = [];

while ($item = $items_result->fetch_assoc()) {
    $product_id = $item['product_id'];
    $quantity = $item['quantity'];
    $price = $item['price'];
    $total_amount += $price * $quantity;

    $order_items[] = [
        'product_id' => $product_id,
        'quantity' => $quantity,
        'price' => $price
    ];
}

// Insert order into "orders" table
$order_sql = "INSERT INTO orders (user_id, total_amount) VALUES (?, ?)";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("id", $user_id, $total_amount);
$stmt->execute();
$order_id = $stmt->insert_id; // Get the generated order_id

// Insert order items into "order_items" table
foreach ($order_items as $item) {
    $total = $item['price'] * $item['quantity'];
    // Corrected SQL: removed the extra comma
    $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($item_sql);


    // Corrected bind_param: 5 placeholders, 5 types (iiidd)
    $stmt->bind_param("iiidd", $order_id, $item['product_id'], $item['quantity'], $item['price'], $total);
    $stmt->execute();
}


// Clear the user's cart
$clear_cart_sql = "DELETE FROM cart_items WHERE cart_id = ?";
$stmt = $conn->prepare($clear_cart_sql);
$stmt->bind_param("i", $cart_id);
$stmt->execute();

// Redirect to order success page
header("Location: account.php");
exit();
?>
