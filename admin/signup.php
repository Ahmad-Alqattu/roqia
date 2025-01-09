<?php
require_once '../includes/db_connect.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors['fields'] = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email address';
    } elseif ($password !== $confirm_password) {
        $errors['password'] = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $errors['password_length'] = 'Password must be at least 8 characters long';
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            $errors['exists'] = 'Username or email already exists';
        } else {
            // Hash the password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Insert new admin user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, user_role) VALUES (?, ?, ?, 'admin')");
            if ($stmt) {
                $stmt->bind_param("sss", $username, $email, $password_hash);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $success = true;
                } else {
                    $errors['database'] = 'Error creating user. Please try again.';
                }
            } else {
                $errors['database'] = 'SQL Error: ' . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <title>Admin Sign Up - Raqi E-commerce</title>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <img src="../assets/images/logo.png" width="100px" alt="Logo">
            <h1>Admin Sign Up</h1>
            <p>Create a new admin account</p>
        </div>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <?php if ($success): ?>
                <div class="success-message">Account created successfully. <a href="login.php">Log in</a></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                >
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required
                >
            </div>

            <button type="submit" class="submit-btn">Sign Up</button>

            <div class="back-link">
                Already have an account? <a href="login.php">Log in</a>
            </div>
        </form>
    </div>

    <script>
        // Validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            const confirmPassword = document.getElementById('confirm_password').value.trim();

            if (!username || !email || !password || !confirmPassword) {
                e.preventDefault();
                alert('Please fill in all fields');
            } else if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
            }
        });
    </script>
</body>
</html>
