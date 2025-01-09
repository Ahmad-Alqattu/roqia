<?php


if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$adminName = ($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/admin.css">
    <title>Admin Panel</title>
</head>
<body>
    <header class="admin-header">
        <div class="header-left">
            <h1>Admin Panel</h1>
        </div>
        <div class="header-right">
        <a href="./signup.php" class="btn">add admin</a>

            <span>Welcome, <?php echo $adminName; ?></span>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </header>
    <style>.admin-header {
    display: flex;
    position: sticky;
    top: 0;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    background-color:rgb(23, 37, 55);
    color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.admin-header h1 {
    margin: 0;
    font-size: 24px;
}

.admin-header .header-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.admin-header .header-right span {
    font-size: 16px;
}

.admin-header .logout-btn {
    text-decoration: none;
    color: white;
    background-color: #dc3545;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 14px;
    transition: background-color 0.3s;
}

.admin-header .logout-btn:hover {
    background-color: #c82333;
}</style>
</body>
</html>
