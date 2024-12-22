<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Raqi</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <?php
    include 'includes/db_connect.php';

    $error = "";
    $success = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = "Username, email, and password are required.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            $check_sql = "SELECT * FROM users WHERE email = ? OR username = ?";
            $stmt = $conn->prepare($check_sql);

            if (!$stmt) {
                die("SQL Error: " . $conn->error);
            }

            $stmt->bind_param("ss", $email, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email or username already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $insert_sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, phone, address, user_role) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($insert_sql);

                if (!$stmt) {
                    die("SQL Error: " . $conn->error);
                }

                $user_role = 'customer';
                $stmt->bind_param("ssssssss", $username, $email, $hashed_password, $first_name, $last_name, $phone, $address, $user_role);

                if ($stmt->execute()) {
                    $success = "Account created successfully. You can now <a href='login.php'>login</a>.";
                } else {
                    $error = "Error creating account. Please try again.";
                }
            }
        }
    }
    ?>
    <div class="auth-container">
        <h1>Sign Up</h1>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name">

            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name">

            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone">

            <label for="address">Address</label>
            <textarea id="address" name="address"></textarea>

            <button type="submit" class="submit-btn">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
