<?php
// admin/dashboard.php
require_once '../includes/db_connect.php';

// // Check if user is logged in and is an admin
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//     header('Location: login.php');
//     exit();
// }

// Fetch key statistics
$stats = array();

// Total Orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders");
$stats['total_orders'] = $result->fetch_assoc()['total'];

// Total Revenue
$result = $conn->query("SELECT SUM(total_amount) as revenue FROM orders WHERE order_status != 'Cancelled'");
$stats['total_revenue'] = $result->fetch_assoc()['revenue'] ?? 0;

// Total Products
$result = $conn->query("SELECT COUNT(*) as total FROM products");
$stats['total_products'] = $result->fetch_assoc()['total'];

// Total Users
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_role = 'customer'");
$stats['total_users'] = $result->fetch_assoc()['total'];

// Recent Orders
$recent_orders = $conn->query("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Raqi E-commerce</title>
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

            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-card">
                <div class="card-header">
                    <h1 class="card-title">Dashboard Overview</h1>
                    <span><?php echo date('F d, Y'); ?></span>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Orders</h3>
                        <div class="value"><?php echo number_format($stats['total_orders']); ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Revenue</h3>
                        <div class="value">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Products</h3>
                        <div class="value"><?php echo number_format($stats['total_products']); ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Customers</h3>
                        <div class="value"><?php echo number_format($stats['total_users']); ?></div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h2 class="card-title">Recent Orders</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($order['order_status']); ?>">
                                        <?php echo $order['order_status']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>