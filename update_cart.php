<?php
session_start();
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_item_id = intval($_POST['cart_item_id']);
    $action = $_POST['action'] ?? '';

    if ($cart_item_id > 0) {
        if ($action === 'increase') {
            $sql = "UPDATE cart_items SET quantity = quantity + 1 WHERE cart_item_id = ?";
        } elseif ($action === 'decrease') {
            $sql = "UPDATE cart_items SET quantity = GREATEST(quantity - 1, 1) WHERE cart_item_id = ?";
        } elseif ($action === 'remove') {
            $sql = "DELETE FROM cart_items WHERE cart_item_id = ?";
        } else {
            header("Location: cart.php");
            exit();
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cart_item_id);
        $stmt->execute();
    }
}

header("Location: cart.php");
exit();
?>
