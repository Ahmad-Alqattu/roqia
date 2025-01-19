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
                            <td><a href="view_order.php?id='.$order['order_id'].'">View</a></td>
                        </tr>
                        ';
                    }
                    echo '</tbody></table>';
                } else {
                    echo "<p>You have no orders.</p>";
                }

          
        } else {
            echo "<p>Please <a href='login.php'>login</a> to view your account.</p>";
        }
        ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
