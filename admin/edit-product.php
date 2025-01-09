<?php
session_start();
require_once '../includes/db_connect.php';
require_once 'includes/product-functions.php';
require_once 'header.php';

    // // Check if user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: login.php');
        exit();
    }

// Ensure product ID is provided
if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = (int)$_GET['id'];
$errors = [];
$success = false;

// Fetch product data
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit();
}

// Fetch brands and categories
$brands = $conn->query("SELECT brand_id, brand_name FROM brands ORDER BY brand_name");
$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name");

// Fetch all product images
$product_images = $conn->query("
    SELECT * FROM product_images 
    WHERE product_id = $product_id 
    ORDER BY is_primary DESC
")->fetch_all(MYSQLI_ASSOC);

// Handle image deletion
if (isset($_POST['delete_image'])) {
    $image_id = (int)$_POST['image_id'];
    if (deleteProductImage($conn, $image_id)) {
        $_SESSION['message'] = "Image deleted successfully.";
        header('Location: edit-product.php?id=' . $product_id);
        exit();
    } else {
        $errors[] = "Error deleting image.";
    }
}

// Handle setting primary image
if (isset($_POST['set_primary'])) {
    $image_id = (int)$_POST['image_id'];
    
    $conn->begin_transaction();
    try {
        // Reset all images to non-primary
        $conn->query("UPDATE product_images SET is_primary = 0 WHERE product_id = $product_id");
        
        // Set selected image as primary
        $stmt = $conn->prepare("UPDATE product_images SET is_primary = 1 WHERE image_id = ?");
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        
        $conn->commit();
        $_SESSION['message'] = "Primary image updated successfully.";
        header('Location: edit-product.php?id=' . $product_id);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $errors[] = "Error updating primary image: " . $e->getMessage();
    }
}

// Handle form submission for product updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_image']) && !isset($_POST['set_primary'])) {
    // Validate form data
    $errors = validateProductData($_POST);
    
    // Handle multiple image uploads if any new images are provided
    $upload_result = ['success' => true, 'images' => []];
    if (isset($_FILES['product_images']) && $_FILES['product_images']['error'][0] !== UPLOAD_ERR_NO_FILE) {
        $upload_result = uploadProductImages($_FILES['product_images']);
        if (!$upload_result['success']) {
            $errors = array_merge($errors, $upload_result['errors']);
        }
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            // Update product information
            $stmt = $conn->prepare("
                UPDATE products SET 
                    product_name = ?, 
                    description = ?, 
                    brand_id = ?, 
                    category_id = ?,
                    price = ?, 
                    stock_quantity = ?, 
                    material = ?, 
                    size = ?, 
                    color = ?
                WHERE product_id = ?
            ");
            
            $stmt->bind_param(
                "ssiidisssi",
                $_POST['product_name'],
                $_POST['description'],
                $_POST['brand_id'],
                $_POST['category_id'],
                $_POST['price'],
                $_POST['stock_quantity'],
                $_POST['material'],
                $_POST['size'],
                $_POST['color'],
                $product_id
            );
            
            $stmt->execute();
            
            // If new images were uploaded, save them
            if ($upload_result['success'] && !empty($upload_result['images'])) {
                saveProductImages($conn, $product_id, $upload_result['images']);
            }

            $conn->commit();
            $_SESSION['message'] = "Product updated successfully!";
            header('Location: products.php');
            exit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = "Error updating product: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Raqi E-commerce</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">Raqi Admin</div>
            <ul class="sidebar-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="manage_categories_brands.php">Categories & Brands</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-card">
                <div class="card-header">
                    <h1 class="card-title">Edit Product</h1>
                    <a href="products.php" class="btn btn-primary">Back to Products</a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($_SESSION['message']); ?>
                        <?php unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Main form for updating product info and adding new images -->
                <form method="POST" enctype="multipart/form-data" class="product-form">
                    <div class="form-group">
                        <label for="product_name">Product Name *</label>
                        <input type="text" id="product_name" name="product_name" class="form-control" 
                               value="<?php echo htmlspecialchars($product['product_name']); ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="brand_id">Brand *</label>
                        <select id="brand_id" name="brand_id" class="form-control" required>
                            <option value="">Select Brand</option>
                            <?php
                            // Reset pointer for brands result set
                            $brands->data_seek(0);
                            while ($brand = $brands->fetch_assoc()): ?>
                                <option value="<?php echo $brand['brand_id']; ?>"
                                    <?php echo ($product['brand_id'] == $brand['brand_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php
                            // Reset pointer for categories result set
                            $categories->data_seek(0);
                            while ($category = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $category['category_id']; ?>"
                                    <?php echo ($product['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price">Price *</label>
                        <input type="number" id="price" name="price" class="form-control" 
                               step="0.01" min="0" 
                               value="<?php echo htmlspecialchars($product['price']); ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity *</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" 
                               class="form-control" min="0" 
                               value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="material">Material</label>
                        <input type="text" id="material" name="material" class="form-control" 
                               value="<?php echo htmlspecialchars($product['material']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="size">Size *</label>
                        <select id="size" name="size" class="form-control" required>
                            <option value="Small" <?php echo ($product['size'] == 'Small') ? 'selected' : ''; ?>>Small</option>
                            <option value="Medium" <?php echo ($product['size'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="Large" <?php echo ($product['size'] == 'Large') ? 'selected' : ''; ?>>Large</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color" class="form-control" 
                               value="<?php echo htmlspecialchars($product['color']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="product_images">Add More Images (You can select multiple images)</label>
                        <input type="file" id="product_images" name="product_images[]" class="form-control" 
                               accept="image/*" multiple>
                        <small class="form-text text-muted">You can upload up to 5 images at once. If no primary image exists, the first new image will become primary.</small>
                    </div>

                    <div class="form-group">
                        <div id="image_preview" class="image-preview-container"></div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Update Product</button>
                    </div>
                </form>

                <!-- Display existing product images after main form -->
                <h2>Existing Product Images</h2>
                <div class="image-preview-container">
                    <?php foreach ($product_images as $img): ?>
                        <div class="image-preview">
                            <img src="<?php echo htmlspecialchars('../assets/images/products/'.$img['image_path']); ?>" alt="Product Image">
                            <?php if ($img['is_primary'] == 1): ?>
                                <span class="primary-label">Primary</span>+
                            <?php endif; ?>

                            <div class="image-actions-container">
                                <!-- Form for setting primary image -->
                                <?php if ($img['is_primary'] == 0): ?>
                                    <form action="edit-product.php?id=<?php echo $product_id; ?>" method="POST">
                                        <input type="hidden" name="image_id" value="<?php echo $img['image_id']; ?>">
                                        <button type="submit" name="set_primary" class="btn btn-primary btn-sm">Set Primary</button>
                                    </form>
                                <?php endif; ?>

                                <!-- Form for deleting image -->
                                <form action="edit-product.php?id=<?php echo $product_id; ?>" method="POST">
                                    <input type="hidden" name="image_id" value="<?php echo $img['image_id']; ?>">
                                    <button type="submit" name="delete_image" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </main>
    </div>
</body>
</html>


<script>
document.getElementById('product_images').addEventListener('change', function(e) {
    const preview = document.getElementById('image_preview');
    preview.innerHTML = '';
    
    if (this.files.length > 5) {
        alert('You can only upload a maximum of 5 images');
        this.value = '';
        return;
    }
    
    Array.from(this.files).forEach((file, index) => {
        if (file) {
            const reader = new FileReader();
            const div = document.createElement('div');
            div.className = 'image-preview';
            
            reader.onload = function(e) {
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-image" onclick="removeImage(${index})">&times;</button>
                `;
            }
            
            reader.readAsDataURL(file);
            preview.appendChild(div);
        }
    });
});

function removeImage(index) {
    const input = document.getElementById('product_images');
    const dt = new DataTransfer();
    const { files } = input;
    
    for (let i = 0; i < files.length; i++) {
        if (index !== i)
            dt.items.add(files[i]);
    }
    
    input.files = dt.files;
    const event = new Event('change');
    input.dispatchEvent(event);
}
</script>
