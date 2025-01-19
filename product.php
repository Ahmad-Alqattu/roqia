<?php include 'includes/db_connect.php'; ?>
<?php include 'includes/header.php'; ?>
<?php include 'product_card.php'; ?> 

<section class="product-list">
    <div class="container">
        <h2>Our Products</h2>
        <div class="filters">
            <form method="GET" action="product.php">
                <input type="text" name="search" placeholder="Search by name, color, size, or brand" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">

               

                <select name="price_order">
                    <option value="">Sort by Price</option>
                    <option value="asc" <?php echo ($_GET['price_order'] ?? '') === 'asc' ? 'selected' : ''; ?>>Low to High</option>
                    <option value="desc" <?php echo ($_GET['price_order'] ?? '') === 'desc' ? 'selected' : ''; ?>>High to Low</option>
                </select>

                <button type="submit">Filter</button>
            </form>
        </div>

        <div class="products-grid">
            <?php
            $search = $_GET['search'] ?? '';
            $price_order = $_GET['price_order'] ?? '';

            // Build SQL query
            $sql = "SELECT p.*, pi.image_path 
                    FROM products p
                    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                    WHERE 1=1";

            // search filter
            if (!empty($search)) {
                $search = mysqli_real_escape_string($conn, $search);
                $sql .= " AND (
                    p.product_name LIKE '%$search%' OR
                    p.color LIKE '%$search%' OR
                    p.size LIKE '%$search%' OR
                    (SELECT brand_name FROM brands WHERE brands.brand_id = p.brand_id) LIKE '%$search%'
                )";
            }


            // Add price sorting
            if ($price_order === 'asc') {
                $sql .= " ORDER BY p.price ASC";
            } elseif ($price_order === 'desc') {
                $sql .= " ORDER BY p.price DESC";
            }

            $result = mysqli_query($conn, $sql);

            // Display products
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    renderProductCard($row); // Use the reusable product card
                }
            } else {
                echo "<p>No products found.</p>";
            }
            ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
