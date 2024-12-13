<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register - Maintenance Issue Portal</title>
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

.register-container {
    background: rgba(255, 255, 255, 0.8);
    padding: 50px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 420px;
    margin-top: 100px;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    text-align: center;
}

.register-container:hover {
    transform: translateY(0);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
}

h2 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 24px;
    color: #333;
}

.register-form input[type="text"],
.register-form input[type="password"],
.register-form input[type="email"],
.register-form select {
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

.register-form button {
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

.register-form button:hover {
    background: linear-gradient(135deg, #4a69bd, #1e3799);
    box-shadow: 0 6px 15px rgba(106, 137, 204, 0.4);
}

.error, .success {
    color: #e74c3c;
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 20px;
    animation: fadeIn 0.5s ease-in-out;
}

.error {
    color: red;
}

.success {
    color: green;
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
}
    </style>
</head>
<body>
    <!-- Header section -->
<header>
    Maintenance Complaint Portal
</header>


<div class="register-container">
    <h2>Register</h2>
    <?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'maintenance_portal');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $name = $_POST['name'];
    $roll_no = $_POST['roll_no'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];
    
    // Error tracking for each field
    $errorMessages = [];
    
    // Validate name
    if (empty($name)) {
        $errorMessages[] = "Name cannot be empty.";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
        $errorMessages[] = "Name can only contain alphabets and spaces.";
    }
    
    // Validate email domain
    if (!preg_match('/@sycet\.org$/', $email)) {
        $errorMessages[] = "Only @sycet.org emails are allowed!";
    }
    
    // Check if email exists
    $check_email_query = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check_email_query->bind_param("s", $email);
    $check_email_query->execute();
    if ($check_email_query->get_result()->num_rows > 0) {
        $errorMessages[] = "Email already exists!";
    }

    // Check if user ID exists
    $check_user_id_query = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $check_user_id_query->bind_param("s", $user_id);
    $check_user_id_query->execute();
    if ($check_user_id_query->get_result()->num_rows > 0) {
        $errorMessages[] = "User ID already exists!";
    }

    // Check if roll number exists
    $check_roll_no_query = $conn->prepare("SELECT * FROM users WHERE roll_no = ?");
    $check_roll_no_query->bind_param("s", $roll_no);
    $check_roll_no_query->execute();
    if ($check_roll_no_query->get_result()->num_rows > 0) {
        $errorMessages[] = "Roll Number already exists!";
    }

    // If no errors, insert user data
    if (empty($errorMessages)) {
        $stmt = $conn->prepare("INSERT INTO users (name, roll_no, email, password, user_id, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $roll_no, $email, $password, $user_id, $role);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Redirecting to login...'); window.location.href = 'login.php';</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        // Display all error messages in a single alert
        echo "<script>alert('" . implode("\\n", $errorMessages) . "');</script>";
    }

    // Close connections
    $check_email_query->close();
    $check_user_id_query->close();
    $check_roll_no_query->close();
    $conn->close();
}
?>



    <form class="register-form" action="register.php" method="post">
        <input type="text" name="name" placeholder="Name" required>
        <input type="text" name="roll_no" placeholder="Roll No" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="user_id" placeholder="User ID" required>
        <select name="role" required>
            <option value="" disabled selected>Select Role</option>
            <option value="admin">Admin</option>
            <option value="student">Student</option>
            <option value="staff">Staff</option>
            <option value="manager">Manager</option>
        </select>
        <button type="submit">Register</button>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('form');
        form.addEventListener('submit', (event) => {
            const errorMessages = [];

            // Get form field values
            const name = document.querySelector('[name="name"]').value.trim();
            const rollNo = document.querySelector('[name="roll_no"]').value.trim();
            const email = document.querySelector('[name="email"]').value.trim();
            const password = document.querySelector('[name="password"]').value.trim();
            const userId = document.querySelector('[name="user_id"]').value.trim();

            // Name validation
            if (name === "") {
                errorMessages.push("Name cannot be empty.");
            } else if (!/^[a-zA-Z\s]+$/.test(name)) {
                errorMessages.push("Name can only contain alphabets and spaces.");
            }

            // Roll number validation
            if (rollNo === "") {
                errorMessages.push("Roll number cannot be empty.");
            } else if (!/^\d+$/.test(rollNo)) {
                errorMessages.push("Roll number must contain only digits.");
            }

            // Email validation
            if (email === "") {
                errorMessages.push("Email cannot be empty.");
            } else if (/^\s/.test(email)) {
                errorMessages.push("Email cannot start with spaces.");
            } else if (/[\s]/.test(email)) {
                errorMessages.push("Email cannot contain spaces.");
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errorMessages.push("Invalid email format.");
            } else if (!email.endsWith('@sycet.org')) {
                errorMessages.push("Email must end with @sycet.org.");
            }

            // Password validation
            if (password === "") {
                errorMessages.push("Password cannot be empty.");
            } else if (password.length < 8) {
                errorMessages.push("Password must be at least 8 characters long.");
            }

            // User ID validation
            if (userId === "") {
                errorMessages.push("User ID cannot be empty.");
            } else if (/^\s/.test(userId)) {
                errorMessages.push("User ID cannot start with spaces.");
            } else if (/[\s]/.test(userId)) {
                errorMessages.push("User ID cannot contain spaces.");
            } else if (!/^[a-zA-Z0-9_]+$/.test(userId)) {
                errorMessages.push("User ID can only contain letters, numbers, and underscores.");
            }

            // Display error messages and prevent form submission
            if (errorMessages.length > 0) {
                event.preventDefault();
                alert(errorMessages.join("\n"));
            }
        });
    });
</script>




</body>
</html>