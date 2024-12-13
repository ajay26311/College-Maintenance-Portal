<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'maintenance_portal');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $sql = "SELECT * FROM users WHERE user_id='$username' AND role='$role'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } elseif ($user['role'] === 'student') {
                header("Location: profile.php");
            } elseif ($user['role'] === 'staff') {
                header("Location: profile.php");
            } elseif ($user['role'] === 'manager') {
                header("Location: manager.php");
            }
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - Maintenance Issue Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
       /* Reset default browser styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background-image: url('images/bakgroundimg.jpg');
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    color: #333;
    overflow: hidden;
}

header {
    width: 100%;
    background-color: #34495e;
    padding: 20px;
    text-align: center;
    color: #ffffff;
    font-size: 28px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    position: fixed;
    top: 0;
    left: 0;
}

.login-container {
    background: rgba(255, 255, 255, 0.8);
    padding: 50px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 420px;
    margin-top: 150px;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    text-align: center;
}

.login-container:hover {
    transform: translateY(0);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
}

h2 {
    font-size: 28px;
    color: #34495e;
    margin-bottom: 30px;
    font-weight: 600;
    letter-spacing: 0.6px;
}

.login-form input[type="text"],
.login-form input[type="password"],
.login-form select {
    width: 100%;
    padding: 14px;
    margin-bottom: 20px;
    border: 1px solid #dcdde1;
    border-radius: 8px;
    background-color: #f8f9fa;
    font-size: 16px;
    color: #2c3e50;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    font-weight: 500;
}

.login-form input:focus,
.login-form select:focus {
    border-color: #3498db;
    box-shadow: 0 0 8px rgba(52, 152, 219, 0.2);
    outline: none;
}

.login-form button {
    width: 100%;
    padding: 15px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(135deg, #6a89cc, #4a69bd);
    color: #ffffff;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease;
    box-shadow: 0 4px 12px rgba(106, 137, 204, 0.3);
    letter-spacing: 0.5px;
}

.login-form button:hover {
    background: linear-gradient(135deg, #4a69bd, #1e3799);
    box-shadow: 0 6px 15px rgba(106, 137, 204, 0.4);
}

.login-form p {
    color: #7f8c8d;
    font-size: 15px;
    margin-top: 20px;
}

.login-form p a {
    color: #3498db;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
}

.login-form p a:hover {
    color: #2980b9;
    text-decoration: underline;
}

.error {
    color: #e74c3c;
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 20px;
    animation: fadeIn 0.5s ease-in-out;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Responsive Design */
@media (max-width: 480px) {
    .login-container {
        padding: 30px;
    }

    h2 {
        font-size: 24px;
    }

    .login-form input,
    .login-form button {
        padding: 12px;
        font-size: 15px;
    }
}

    </style>
</head>
<body>

<!-- Header section -->
<header>
    Maintenance Complaint Portal
</header>

<div class="login-container">
    <h2>Login</h2>

    <!-- Display error message if any -->
    <?php if (!empty($error)) : ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form class="login-form" action="login.php" method="post">
        <input type="text" name="username" placeholder="User_id" required>
        <input type="password" name="password" placeholder="Password" required>

        <!-- Role selection dropdown -->
        <select name="role" required class="role-selection">
            <option value="" disabled selected>Select Role</option>
            <option value="student">student</option>
            <option value="staff">Staff</option>
            <option value="manager">Manager</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</div>

</body>
</html>
