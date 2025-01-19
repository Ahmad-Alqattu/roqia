<?php include 'includes/header.php'; 
require_once 'includes/authorization.php';
?>

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
    </div>
</section>

<?php include 'includes/footer.php'; ?>
