<?php
// admin/add-product.php
require_once '../includes/db_connect.php';
require_once 'includes/product-functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$errors = [];
$success = false;

// Fetch brands and categories for dropdowns
$brands = $conn->query("SELECT brand_id, brand_name FROM brands ORDER BY brand_name");
$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $errors = validateProductData($_POST);
    
    if (empty($errors)) {
        // Handle multiple image uploads
        if (isset($_FILES['product_images'])) {
            $upload_result = uploadProductImages($_FILES['product_images']);
            
            if (!$upload_result['success']) {
                $errors = array_merge($errors, $upload_result['errors']);
            }
        } else {
            $errors[] = "At least one product image is required.";
        }
        
        if (empty($errors)) {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert product data
                $stmt = $conn->prepare("
                    INSERT INTO products (
                        product_name, description, brand_id, category_id,
                        price, stock_quantity, material, size, color
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->bind_param(
                    "ssiidisss",
                    $_POST['product_name'],
                    $_POST['description'],
                    $_POST['brand_id'],
                    $_POST['category_id'],
                    $_POST['price'],
                    $_POST['stock_quantity'],
                    $_POST['material'],
                    $_POST['size'],
                    $_POST['color']
                );
                
                $stmt->execute();
                $product_id = $conn->insert_id;
                
                // Save product images
                if (!empty($upload_result['images'])) {
                    saveProductImages($conn, $product_id, $upload_result['images']);
                }
                
                // Commit transaction
                $conn->commit();
                
                $_SESSION['message'] = "Product added successfully!";
                header('Location: products.php');
                exit();
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $errors[] = "Error adding product: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - Raqi E-commerce</title>
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
                <li><a href="users.php">Users</a></li>
                <li><a href="analytics.php">Analytics</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-card">
                <div class="card-header">
                    <h1 class="card-title">Add New Product</h1>
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

                <form method="POST" enctype="multipart/form-data" class="product-form">
                    <div class="form-group">
                        <label for="product_name">Product Name *</label>
                        <input type="text" id="product_name" name="product_name" class="form-control" 
                               value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="brand_id">Brand *</label>
                        <select id="brand_id" name="brand_id" class="form-control" required>
                            <option value="">Select Brand</option>
                            <?php while ($brand = $brands->fetch_assoc()): ?>
                                <option value="<?php echo $brand['brand_id']; ?>"
                                    <?php echo (isset($_POST['brand_id']) && $_POST['brand_id'] == $brand['brand_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $category['category_id']; ?>"
                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price">Price *</label>
                        <input type="number" id="price" name="price" class="form-control" 
                               step="0.01" min="0" 
                               value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity *</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" 
                               class="form-control" min="0" 
                               value="<?php echo isset($_POST['stock_quantity']) ? htmlspecialchars($_POST['stock_quantity']) : ''; ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="material">Material</label>
                        <input type="text" id="material" name="material" class="form-control" 
                               value="<?php echo isset($_POST['material']) ? htmlspecialchars($_POST['material']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="size">Size *</label>
                        <select id="size" name="size" class="form-control" required>
                            <option value="Small" <?php echo (isset($_POST['size']) && $_POST['size'] == 'Small') ? 'selected' : ''; ?>>Small</option>
                            <option value="Medium" <?php echo (isset($_POST['size']) && $_POST['size'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="Large" <?php echo (isset($_POST['size']) && $_POST['size'] == 'Large') ? 'selected' : ''; ?>>Large</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color" class="form-control" 
                               value="<?php echo isset($_POST['color']) ? htmlspecialchars($_POST['color']) : ''; ?>">
                    </div>

                    <div class="form-group">
    <label for="product_images">Product Images (You can select multiple images) *</label>
    <input type="file" id="product_images" name="product_images[]" class="form-control" 
           accept="image/*" multiple required>
    <small class="form-text text-muted">First image will be set as primary image. Maximum 5 images allowed.</small>
</div>

<div class="form-group">
    <div id="image_preview" class="image-preview-container"></div>
</div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Add Product</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>


<style>
.image-preview-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.image-preview {
    width: 150px;
    height: 150px;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
    position: relative;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-preview .remove-image {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(255, 0, 0, 0.7);
    color: white;
    border: none;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

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