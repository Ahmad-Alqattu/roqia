<?php
session_start();
require_once 'includes/db_connect.php';

require_once 'includes/authorization.php';


$user_id = $_SESSION['user_id'];

$cart_sql = "SELECT cart_id FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($cart_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();

if ($cart_result->num_rows > 0) {
    $cart = $cart_result->fetch_assoc();
    $cart_id = $cart['cart_id'];

    $items_sql = "SELECT ci.cart_item_id, ci.quantity, p.product_id, p.product_name, p.price, pi.image_path 
                  FROM cart_items ci
                  JOIN products p ON ci.product_id = p.product_id
                  LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                  WHERE ci.cart_id = ?";
    $stmt = $conn->prepare($items_sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $items_result = $stmt->get_result();
} else {
    $items_result = false;
}

include 'includes/header.php';
?>

<section class="cart-section">
    <div class="container">
        <h2>Your Shopping Cart</h2>
        <?php if ($items_result && $items_result->num_rows > 0): ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grand_total = 0;
                    while ($item = $items_result->fetch_assoc()): 
                        $total = $item['price'] * $item['quantity'];
                        $grand_total += $total;
                    ?>
                        <tr>
                            <td class="cart-product">
                                <img src="<?php echo 'assets/images/products/'.$item['image_path'] ?? 'assets/images/products/default.jpg'; ?>" 
                                     alt="<?php echo $item['product_name']; ?>" class="cart-product-image">
                                <span>
                                    <a href="product_detail.php?id=<?php echo $item['product_id']; ?>">
                                        <?php echo $item['product_name']; ?>
                                    </a>
                                </span>
                            </td>
                            <td>₪<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <form method="POST" action="update_cart.php" class="cart-quantity-form">
                                    <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                    <button type="submit" name="action" value="decrease">-</button>
                                    <input type="text" value="<?php echo $item['quantity']; ?>" readonly>
                                    <button type="submit" name="action" value="increase">+</button>
                                </form>
                            </td>
                            <td>₪<?php echo number_format($total, 2); ?></td>
                            <td>
                                <form method="POST" action="update_cart.php" style="display:inline;">
                                    <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                    <button type="submit" name="action" value="remove" class="delete-button">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="cart-summary">
                <h3>Grand Total: ₪<?php echo number_format($grand_total, 2); ?></h3>
                <a href="checkout.php" class="checkout-button">Proceed to Checkout</a>
            </div>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
