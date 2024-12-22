<?php include 'includes/header.php'; ?>
<?php

if (!isset($_SESSION['user_id'])) {
    die("Please <a href='login.php'>login</a> to view your wishlist.");
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT DISTINCT  p.product_id, p.product_name, p.price, pi.image_path 
        FROM wishlist w
        JOIN products p ON w.product_id = p.product_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        WHERE w.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>


<section class="wishlist-section">
    <div class="container">
        <h2>Your Wishlist</h2>
        <?php if ($result && $result->num_rows > 0): ?>
            <table class="wishlist-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="wishlist-product<?php $user_id ?>">
                                <img src="<?php echo $row['image_path'] ?? 'assets/images/products/default.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($row['product_name']); ?>" 
                                     class="wishlist-product-image">
                                <span><?php echo htmlspecialchars($row['product_name']); ?></span>
                            </td>
                            <td>₪<?php echo number_format($row['price'], 2); ?></td>
                            <td>
                                <!-- Add to Cart Form -->
                                <form method="POST" action="add_to_cart.php" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="action-button">Add to Cart</button>
                                </form>

                                <!-- Remove from Wishlist Form -->
                                <form method="POST" action="remove_from_wishlist.php" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                    <button type="submit" class="action-button delete">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Your wishlist is empty.</p>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
