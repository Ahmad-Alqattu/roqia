<?php
require_once '../includes/db_connect.php';
require_once 'header.php';

// // Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle order status updates
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $conn->real_escape_string($_POST['new_status']);
    
    $conn->query("UPDATE orders SET order_status = '$new_status' WHERE order_id = $order_id");
    $_SESSION['message'] = "Order status updated successfully.";
    header('Location: orders.php');
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Get total orders count
$total_result = $conn->query("SELECT COUNT(*) as count FROM orders");
$total_orders = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_orders / $items_per_page);

// Fetch orders with user information
$orders = $conn->query("
    SELECT o.*, u.username, u.email,
           COUNT(oi.order_item_id) as item_count
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    GROUP BY o.order_id
    ORDER BY o.created_at DESC
    LIMIT $offset, $items_per_page
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Raqi E-commerce</title>
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
                    <h1 class="card-title">Orders Management</h1>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                        ?>
                    </div>
                <?php endif; ?>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td><a href="view_order.php?id=<?php echo $order['order_id']; ?>">#<?php echo $order['order_id']; ?></a></td>
                            <td>
                                <?php echo $order['username']; ?><br>
                                <small><?php echo $order['email']; ?></small>
                            </td>
                            <td><?php echo $order['item_count']; ?> items</td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                    <?php echo $order['order_status']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                    <?php echo $order['payment_status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" 
                                           value="<?php echo $order['order_id']; ?>">
                                    <select name="new_status" class="form-control" 
                                            onchange="this.form.submit()">
                                        <option value="">Update Status</option>
                                        <option value="Processing">Processing</option>
                                        <option value="Shipped">Shipped</option>
                                        <option value="Delivered">Delivered</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                               
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <?php if ($total_pages > 1): ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="<?php echo $page === $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>