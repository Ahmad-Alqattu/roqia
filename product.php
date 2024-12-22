<?php include 'includes/db_connect.php'; ?>
<?php include 'includes/header.php'; ?>
<?php include 'product_card.php'; ?> <!-- Include the product card -->

<section class="product-list">
    <div class="container">
        <h2>Our Products</h2>
        <div class="filters">
            <form method="GET" action="product.php">
                <input type="text" name="search" placeholder="Search by name, color, size, or brand" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">

                <select name="color">
                    <option value="">All Colors</option>
                    <option value="Red" <?php echo ($_GET['color'] ?? '') === 'Red' ? 'selected' : ''; ?>>Red</option>
                    <option value="Black" <?php echo ($_GET['color'] ?? '') === 'Black' ? 'selected' : ''; ?>>Black</option>
                </select>

                <select name="size">
                    <option value="">All Sizes</option>
                    <option value="Small" <?php echo ($_GET['size'] ?? '') === 'Small' ? 'selected' : ''; ?>>Small</option>
                    <option value="Medium" <?php echo ($_GET['size'] ?? '') === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="Large" <?php echo ($_GET['size'] ?? '') === 'Large' ? 'selected' : ''; ?>>Large</option>
                </select>

                <select name="brand">
                    <option value="">All Brands</option>
                    <option value="1" <?php echo ($_GET['brand'] ?? '') === '1' ? 'selected' : ''; ?>>Louis Vuitton</option>
                    <option value="2" <?php echo ($_GET['brand'] ?? '') === '2' ? 'selected' : ''; ?>>Gucci</option>
                    <option value="3" <?php echo ($_GET['brand'] ?? '') === '3' ? 'selected' : ''; ?>>Prada</option>
                </select>

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
            // Fetch filters from GET parameters
            $search = $_GET['search'] ?? '';
            $color = $_GET['color'] ?? '';
            $size = $_GET['size'] ?? '';
            $brand = $_GET['brand'] ?? '';
            $price_order = $_GET['price_order'] ?? '';

            // Build SQL query
            $sql = "SELECT p.*, pi.image_path 
                    FROM products p
                    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                    WHERE 1=1";

            // Add search filter (matches name, color, size, or brand)
            if (!empty($search)) {
                $search = mysqli_real_escape_string($conn, $search);
                $sql .= " AND (
                    p.product_name LIKE '%$search%' OR
                    p.color LIKE '%$search%' OR
                    p.size LIKE '%$search%' OR
                    (SELECT brand_name FROM brands WHERE brands.brand_id = p.brand_id) LIKE '%$search%'
                )";
            }

            // Add additional filters
            if (!empty($color)) {
                $sql .= " AND p.color = '" . mysqli_real_escape_string($conn, $color) . "'";
            }
            if (!empty($size)) {
                $sql .= " AND p.size = '" . mysqli_real_escape_string($conn, $size) . "'";
            }
            if (!empty($brand)) {
                $sql .= " AND p.brand_id = " . intval($brand);
            }

            // Add price sorting
            if ($price_order === 'asc') {
                $sql .= " ORDER BY p.price ASC";
            } elseif ($price_order === 'desc') {
                $sql .= " ORDER BY p.price DESC";
            }

            // Execute query
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
