<?php
session_start();

if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = intval($_GET['order_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success</title>
</head>
<body>
    <div class="container">
        <h1>Order Successfully Placed!</h1>
        <p>Your order ID is: <strong><?php echo $order_id; ?></strong></p>
        <p>Thank you for your purchase. You can <a href="orders.php">view your orders</a> or <a href="products.php">continue shopping</a>.</p>
    </div>
</body>
</html>
    