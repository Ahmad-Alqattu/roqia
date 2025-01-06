<?php
require_once 'includes/db_connect.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $errors['auth'] = 'All fields are required';
    } else {
        // Using prepared statement for security
        $stmt = $conn->prepare("SELECT user_id, user_role,username, password_hash FROM users WHERE username = ? OR email = ? LIMIT 1");

        // Check if prepare() succeeded
        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Verify password hash
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['user_role'];
                $_SESSION['logged_in'] = true;
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                header("Location: index.php");
                exit();
            } else {
                $errors['auth'] = 'Invalid username or password';
            }
        } else {
            $errors['auth'] = 'Invalid username or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/auth.css">
    <title>Login - Your Site Name</title>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <img src="assets\images\logo.png" width="100PX" alt="Logo">
            <h1>Welcome Back</h1>
            <p>Please login to your account</p>
        </div>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <?php if (!empty($errors['auth'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($errors['auth']); ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="username">Username or Email</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="submit-btn">Login</button>

            <div class="signup-link">
                Don't have an account? <a href="signup.php">Sign up</a>
            </div>
        </form>
    </div>

    <script>
        //validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
            }
        });
    </script>
</body>
</html>