<?php include 'includes/db_connect.php';?>

<?php include 'includes/header.php'; ?>

<?php include 'product_card.php'; ?> 

<!-- banner Section -->
<section class="hero-banner">
    <div class="container">
        <div class="hero-content">
            <h1>Welcome to Raqi Store</h1>
            <p>Carry elegance wherever you go.</p>
            <a href="product.php" class="btn">Shop Now</a>
        </div>
    </div>
</section>


<!-- Featured Products -->
<section class="featured-products">
    <div class="container">
        <h2>Featured Products</h2>
        <div class="product-grid">
            <?php

            // Fetch featured products from the database)
            $sql = "SELECT p.*, pi.image_path
                    FROM products p
                    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                    LIMIT 12";
            $result = mysqli_query($conn, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    renderProductCard($row); // Use product card component
                }
            } else {
                echo "<p>No featured products available.</p>";
            }
            ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
