<?php include 'includes/db_connect.php';?>

<?php include 'includes/header.php'; ?>

<section class="account-section">
    <div class="container">
        <h2>Your Account</h2>
        <?php
        if(isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];

            // Fetch user details
            $user_sql = "SELECT * FROM users WHERE user_id = $user_id";
            $user_result = mysqli_query($conn, $user_sql);
            $user = mysqli_fetch_assoc($user_result);

            echo '
            <div class="account-details">
                <h3>Profile Information</h3>

                <p><strong>Username:</strong> '.$user['username'].'</p>
                <p><strong>Email:</strong> '.$user['email'].'</p>
                <p><strong>First Name:</strong> '.$user['first_name'].'</p>
                <p><strong>Last Name:</strong> '.$user['last_name'].'</p>
                <p><strong>Phone:</strong> '.$user['phone'].'</p>
                <p><strong>Address:</strong> '.$user['address'].'</p>
                <a href="edit_profile.php" class="edit-button">Edit Profile</a>
                <a href="logout.php" class="logout-button">Logout</a>

            </div>

            <div class="account-orders">
                <h3>Order History</h3>
                ';
                
                $orders_sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
                $orders_result = mysqli_query($conn, $orders_sql);

                if(mysqli_num_rows($orders_result) > 0) {
                    echo '<table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>';
                    while($order = mysqli_fetch_assoc($orders_result)) {
                        echo '
                        <tr>
                            <td>'.$order['order_id'].'</td>
                            <td>'.$order['order_status'].'</td>
                            <td>$'.$order['total_amount'].'</td>
                            <td>'.date('F j, Y', strtotime($order['created_at'])).'</td>
                            <td><a href="order_detail.php?id='.$order['order_id'].'">View</a></td>
                        </tr>
                        ';
                    }
                    echo '</tbody></table>';
                } else {
                    echo "<p>You have no orders.</p>";
                }

            echo '</div>

            <div class="account-favorites">
                <h3>Your Favorites</h3>
                ';
                $wishlist_sql = "SELECT w.*, p.product_name, p.price, pi.image_path FROM wishlist w
                                JOIN products p ON w.product_id = p.product_id
                                JOIN product_images pi ON p.product_id = pi.product_id
                                WHERE w.user_id = $user_id AND pi.is_primary = 1";
                $wishlist_result = mysqli_query($conn, $wishlist_sql);
                if(mysqli_num_rows($wishlist_result) > 0) {
                    echo '<div class="favorites-grid">';
                    while($fav = mysqli_fetch_assoc($wishlist_result)) {
                        echo '
                        <div class="favorite-card">
                            <a href="product_detail.php?id='.$fav['product_id'].'">
                                <img src="assets/images/products/' .$fav['image_path'].'" alt="'.$fav['product_name'].'">
                                <h4>'.$fav['product_name'].'</h4>
                                <p>$'.$fav['price'].'</p>
                            </a>
                            <button onclick="removeFromWishlist('.$fav['wishlist_id'].')">Remove</button>
                        </div>
                        ';
                    }
                    echo '</div>';
                } else {
                    echo "<p>You have no favorite products.</p>";
                }

            echo '</div>';
        } else {
            echo "<p>Please <a href='login.php'>login</a> to view your account.</p>";
        }
        ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
