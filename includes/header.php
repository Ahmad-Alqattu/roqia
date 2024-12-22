<?php
require_once 'includes/db_connect.php';

$cart_count = 0;

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // SQL query to count total cart items for the logged-in user
    $cart_query = $conn->prepare("
        SELECT SUM(ci.quantity) AS total_items
        FROM cart_items ci
        JOIN cart c ON ci.cart_id = c.cart_id
        WHERE c.user_id = ?
    ");
    $cart_query->bind_param("i", $user_id);
    $cart_query->execute();

    $result = $cart_query->get_result();
    if ($row = $result->fetch_assoc()) {
        $cart_count = $row['total_items'] ?? 0; // Fallback to 0 if null
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raqi - Premium Bags</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <!-- Logo -->
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/logo.png" alt="Raqi Logo">
                </a>
            </div>

            <!-- Search Bar -->
            <div class="search-bar">
                <form method="GET" action="product.php">
                    <input type="text" name="search" placeholder="Search for bags..." required>
                    <button type="submit">Search</button>
                </form>
            </div>

            <!-- Navigation Menu -->
            <nav class="nav-menu">
                <ul>
                    <li><a href="product.php">Products</a></li>
                    <li>
                        <?php
                        if (isset($_SESSION['username'])) {
                            echo '<a href="account.php"> <img src="assets/images/accuont.png" alt="accuont" class="icon"> ' . htmlspecialchars($_SESSION['username']) . '
</a>';
                        } else {
                            echo '<a href="login.php">
                             <img src="assets/images/accuont.png" alt="accuont" class="icon">Sign In
                                                       
</a>';
                        }
                        ?>
                    </li>
                    <li>
                        <a href="wishlist.php">
                            <img src="assets/images/wish.png" alt="Wishlist" class="icon">
                            Wishlist
                        </a>
                    </li>
                    <li>
                        <a href="cart.php">
                            <img src="assets/images/cart.png" alt="Cart" class="icon">
                            Cart
                            <span id="cartCount"><?php echo $cart_count; ?></span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
</body>
</html>
