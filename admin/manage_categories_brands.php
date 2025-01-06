<?php
// admin/manage_categories_brands.php
require_once '../includes/db_connect.php';

// Check if user is logged in and is an admin
// Uncomment below lines if session and authentication are required
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//     header('Location: login.php');
//     exit();
// }

// Fetch categories and brands
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
$brands = $conn->query("SELECT * FROM brands ORDER BY brand_name ASC");

// Handle add/edit/delete operations for categories
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type'])) {
    if ($_POST['type'] === 'category') {
        if (isset($_POST['action']) && $_POST['action'] === 'add') {
            $category_name = $conn->real_escape_string($_POST['category_name']);
            $conn->query("INSERT INTO categories (category_name) VALUES ('$category_name')");
        } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
            $category_id = intval($_POST['category_id']);
            $category_name = $conn->real_escape_string($_POST['category_name']);
            $conn->query("UPDATE categories SET category_name = '$category_name' WHERE category_id = $category_id");
        } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $category_id = intval($_POST['category_id']);
            $conn->query("DELETE FROM categories WHERE category_id = $category_id");
        }
    }

    // Handle add/edit/delete operations for brands
    if ($_POST['type'] === 'brand') {
        if (isset($_POST['action']) && $_POST['action'] === 'add') {
            $brand_name = $conn->real_escape_string($_POST['brand_name']);
            $conn->query("INSERT INTO brands (brand_name) VALUES ('$brand_name')");
        } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
            $brand_id = intval($_POST['brand_id']);
            $brand_name = $conn->real_escape_string($_POST['brand_name']);
            $conn->query("UPDATE brands SET brand_name = '$brand_name' WHERE brand_id = $brand_id");
        } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $brand_id = intval($_POST['brand_id']);
            $conn->query("DELETE FROM brands WHERE brand_id = $brand_id");
        }
    }

    // Redirect to avoid form resubmission
    header('Location: manage_categories_brands.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories & Brands</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">Raqi Admin</div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="manage_categories_brands.php" class="active">Categories & Brands</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-card">
                <h1>Manage Categories</h1>
                <form method="POST" class="form-inline">
                    <input type="hidden" name="type" value="category">
                    <input type="hidden" name="action" value="add">
                    <input type="text" name="category_name" placeholder="Category Name" required>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </form>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $category['category_id']; ?></td>
                            <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="type" value="category">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                    <input type="text" name="category_name" value="<?php echo htmlspecialchars($category['category_name']); ?>">
                                    <button type="submit" class="btn btn-primary">Edit</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="type" value="category">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                    <button type="submit" class="btn btn-danger " onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="dashboard-card">
                <h1>Manage Brands</h1>
                <form method="POST" class="form-inline">
                    <input type="hidden" name="type" value="brand">
                    <input type="hidden" name="action" value="add">
                    <input type="text" name="brand_name" placeholder="Brand Name" required>
                    <button type="submit" class="btn btn-primary">Add Brand</button>
                </form>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($brand = $brands->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $brand['brand_id']; ?></td>
                            <td><?php echo htmlspecialchars($brand['brand_name']); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="type" value="brand">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="brand_id" value="<?php echo $brand['brand_id']; ?>">
                                    <input type="text" name="brand_name" value="<?php echo htmlspecialchars($brand['brand_name']); ?>">
                                    <button type="submit" class="btn btn-primary">Edit</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="type" value="brand">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="brand_id" value="<?php echo $brand['brand_id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
