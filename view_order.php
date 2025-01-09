<?php
require_once 'includes/db_connect.php';
require_once 'includes/authorization.php';
require_once'includes/header.php';

// Validate the order ID
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    die("Invalid order ID.");
}

// Fetch the order items
$items_query = "
    SELECT oi.*, p.product_name, p.price, pi.image_path AS product_image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id
    WHERE oi.order_id = $order_id
";

$items_result = $conn->query($items_query);

if (!$items_result) {
    die("Query failed: " . $conn->error . " | Query: " . $items_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <style>
        .order-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .order-container h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .order-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
    
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
        }
    
        .item-details p {
            margin: 5px 0;
        }
        .item-details p strong {
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="order-container">
        <h1>Order #<?php echo $order_id; ?></h1>

        <?php if ($items_result->num_rows === 0): ?>
            <p>No items found for this order.</p>
        <?php else: ?>
            <?php while ($item = $items_result->fetch_assoc()): ?>
                <div class="order-item">
                    <img src="assets/images/products/<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_name']; ?>" class="product-image">
                    <div class="item-details">
                        <p><strong><?php echo $item['product_name']; ?></strong></p>
                        <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                        <p>Subtotal: $<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</body>
</html>