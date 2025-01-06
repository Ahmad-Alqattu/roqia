<?php include 'includes/db_connect.php';?>
<?php include 'includes/header.php'; ?>

<section class="product-detail">
    <div class="container">
        <?php

        $product_id = $_GET['id'] ?? 0;
        $product_id = intval($product_id);

        $sql = "SELECT p.*, b.brand_name FROM products p JOIN brands b ON p.brand_id = b.brand_id WHERE p.product_id = $product_id";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0) {
            $product = mysqli_fetch_assoc($result);

            // Fetch product images
            $img_sql = "SELECT * FROM product_images WHERE product_id = $product_id";
            $img_result = mysqli_query($conn, $img_sql);
            $images = mysqli_fetch_all($img_result, MYSQLI_ASSOC);
            echo '
            <div class="product-detail-container">
                <div class="product-images">
                    <div class="main-image">
                         <img src="'. 'assets/images/products/'.$images[0]['image_path'].'" ,alt="'.$product['product_name'].'" id="currentImage">
                    </div>
                    <div class="thumbnail-images">
            ';
            foreach($images as $img) {
                $image_path = 'assets/images/products/'.$img['image_path'];
                echo '<img src="'.$image_path.'" alt="'.$product['product_name'].'" onclick="changeImage(\''. $image_path.'\')">';
            }
            echo '
            </div>
            </div>
            <div class="product-info">
                <h2>' . $product['product_name'] . '</h2>
                <p>Brand: ' . $product['brand_name'] . '</p>
                <p>Price: ₪' . $product['price'] . '</p>
                <p>Color: ' . $product['color'] . '</p>
                <p>Size: ' . $product['size'] . '</p>
                <p>Description: ' . nl2br(($product['description'])) . '</p>
                
                <!-- Add to Cart Form -->
                <form method="POST" action="add_to_cart.php">
                    <input type="hidden" name="product_id" value="' . $product['product_id'] . '">
                    <input type="number" name="quantity" value="1" min="1" required>
                    <button type="submit">Add to Cart</button>
                </form>
                       <form method="POST" action="add_to_wishlist.php">
             
                    <button type="submit">Add to wishlist</button>
                </form>
            </div>
        </div>';
        

            // Fetch and display reviews
            $review_sql = "SELECT r.*, u.username FROM reviews r 
                           JOIN users u ON r.user_id = u.user_id 
                           WHERE r.product_id = $product_id
                           ORDER BY r.created_at DESC";
            $review_result = mysqli_query($conn, $review_sql);
            $reviews = mysqli_fetch_all($review_result, MYSQLI_ASSOC);

            echo '<div class="product-reviews">
                    <h3>Reviews</h3>';
            if (!empty($reviews)) {
                foreach ($reviews as $review) {
                    echo '<div class="review">
                            <p><strong>' . ($review['username']) . '</strong> - Rated ' . $review['rating'] . '/5</p>
                            <p>' . nl2br(($review['review_text'])) . '</p>
                            <small>Reviewed on ' . date('F j, Y, g:i a', strtotime($review['created_at'])) . '</small>
                          </div>
                          <hr>';
                }
            } else {
                echo '<p>No reviews yet. Be the first to review this product!</p>';
            }
            echo '</div>';

            // Add review form
            echo '<div class="add-review">
                    <h3>Write a Review</h3>
                    <form method="POST" action="submit_review.php">
                        <input type="hidden" name="product_id" value="'.$product_id.'">
                        <label for="rating">Rating:</label>
                        <select name="rating" id="rating" required>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Good</option>
                            <option value="3">3 - Average</option>
                            <option value="2">2 - Poor</option>
                            <option value="1">1 - Terrible</option>
                        </select>
                        <label for="review_text">Your Review:</label>
                        <textarea name="review_text" id="review_text" rows="4" required></textarea>
                        <button type="submit">Submit Review</button>
                    </form>
                  </div>';
        } else {
            echo "<p>Product not found.</p>";
        }
        ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
