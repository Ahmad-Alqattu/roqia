<!-- checkout.php -->
<?php include 'includes/db_connect.php';?>

<?php include 'includes/header.php'; ?>

<section class="checkout-section">
    <div class="container">
        <h2>Checkout</h2>
        <?php
        session_start();
        if(isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];

            // Fetch cart items
            $cart_sql = "SELECT ci.*, p.product_name, p.price FROM cart_items ci
                        JOIN products p ON ci.product_id = p.product_id
                        WHERE ci.cart_id = (SELECT cart_id FROM cart WHERE user_id = $user_id)";
            $cart_result = mysqli_query($conn, $cart_sql);

            if(mysqli_num_rows($cart_result) > 0) {
                echo '
                <form action="process_checkout.php" method="POST">
                    <div class="checkout-details">
                        <h3>Shipping Information</h3>
                        <label for="shipping_address">Address:</label>
                        <textarea id="shipping_address" name="shipping_address" required></textarea>

                        <h3>Payment Method</h3>
                        <select name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Cash on Delivery">Cash on Delivery</option>
                        </select>

                        <h3>Order Summary</h3>
                        <table class="order-summary-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                ';

                $grand_total = 0;
                while($item = mysqli_fetch_assoc($cart_result)) {
                    $total = $item['price'] * $item['quantity'];
                    $grand_total += $total;
                    echo '
                    <tr>
                        <td>'.$item['product_name'].'</td>
                        <td>$'.$item['price'].'</td>
                        <td>'.$item['quantity'].'</td>
                        <td>$'.$total.'</td>
                    </tr>
                    ';
                }

                echo '
                            </tbody>
                        </table>
                        <h3>Grand Total: $'.$grand_total.'</h3>
                        <button type="submit">Place Order</button>
                    </div>
                </form>
                ';
            } else {
                echo "<p>Your cart is empty.</p>";
            }
        } else {
            echo "<p>Please <a href='login.php'>login</a> to proceed to checkout.</p>";
        }
        ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
