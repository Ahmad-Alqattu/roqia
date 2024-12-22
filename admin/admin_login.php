<?php
require_once '../includes/db_connect.php';

// If already logged in, redirect to admin dashboard
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);

    // Validate input
    if (empty($username) || empty($password)) {
        $errors[] = "Username and password are required.";
    } else {
        // Check admin credentials
        $stmt = $conn->prepare("SELECT user_id, username, password_hash, user_role FROM users WHERE username = ? AND user_role = 'admin' LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $admin['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $admin['user_id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['user_role'] = $admin['user_role'];
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $errors[] = "Invalid username or password.";
            }
        } else {
            $errors[] = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="admin_login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
