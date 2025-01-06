<?php
function renderProductCard($product) {
    $image_path = 'assets/images/products/'.$product['image_path'] ?? 'assets/images/products/default_image.jpg';

    echo '
    <div class="product-card">
        <a href="product_detail.php?id=' . $product['product_id'] . '">
            <img src="' . ($image_path) . '" alt="' . ($product['product_name']) . '">
            <h3>' . ($product['product_name']) . '</h3>
            <p>₪' . ($product['price']) . '</p>
        </a>

        <form method="POST" action="add_to_cart.php" style="display:inline;">
            <input type="hidden" name="product_id" value="' . $product['product_id'] . '">
            <input type="hidden" name="quantity" value="1">
            <button type="submit">Add to Cart</button>
        </form>

        <form method="POST" action="add_to_wishlist.php" style="display:inline;">
            <input type="hidden" name="product_id" value="' . $product['product_id'] . '">
            <button type="submit">Add to Wishlist</button>
        </form>
    </div>';
}
?>
