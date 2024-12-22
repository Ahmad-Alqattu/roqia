<?php
// admin/includes/product-functions.php

function uploadProductImages($files) {
    $uploaded_images = [];
    $errors = [];
    
    // Create directory if it doesn't exist
    $target_dir = "../assets/images/products/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Reformat files array if multiple images
    $files_array = [];
    if(isset($files['name']) && is_array($files['name'])) {
        for($i = 0; $i < count($files['name']); $i++) {
            if($files['error'][$i] === 0) {
                $files_array[] = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
            }
        }
    }

    foreach($files_array as $file) {
        $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        // Validate file type
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (!in_array($file_extension, $allowed_types)) {
            $errors[] = "File {$file['name']}: Only JPG, JPEG, PNG, WEBP & GIF files are allowed.";
            continue;
        }

        // Validate file size (5MB max)
        if ($file["size"] > 5000000) {
            $errors[] = "File {$file['name']}: File is too large. Maximum size is 5MB.";
            continue;
        }

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $uploaded_images[] = $new_filename;
        } else {
            $errors[] = "File {$file['name']}: Failed to upload image.";
        }
    }

    return [
        'success' => empty($errors),
        'images' => $uploaded_images,
        'errors' => $errors
    ];
}

function saveProductImages($conn, $product_id, $images, $primary_image_index = 0) {
    foreach($images as $index => $image_path) {
        $is_primary = ($index === $primary_image_index) ? 1 : 0;
        $stmt = $conn->prepare("
            INSERT INTO product_images (product_id, image_path, is_primary)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("isi", $product_id, $image_path, $is_primary);
        $stmt->execute();
    }
}

function deleteProductImage($conn, $image_id) {
    // First get the image path
    $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE image_id = ?");
    $stmt->bind_param("i", $image_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();

    if ($image) {
        // Delete the physical file
        $file_path = "../assets/images/products/" . $image['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete from database
        $stmt = $conn->prepare("DELETE FROM product_images WHERE image_id = ?");
        $stmt->bind_param("i", $image_id);
        return $stmt->execute();
    }
    return false;
}

function validateProductData($data) {
    $errors = [];
    
    if (empty($data['product_name'])) {
        $errors[] = "Product name is required.";
    }
    
    if (!is_numeric($data['price']) || $data['price'] <= 0) {
        $errors[] = "Valid price is required.";
    }
    
    if (!is_numeric($data['stock_quantity']) || $data['stock_quantity'] < 0) {
        $errors[] = "Valid stock quantity is required.";
    }
    
    if (empty($data['brand_id'])) {
        $errors[] = "Brand selection is required.";
    }
    
    if (empty($data['category_id'])) {
        $errors[] = "Category selection is required.";
    }
    
    return $errors;
}
?>