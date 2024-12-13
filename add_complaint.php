<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'maintenance_portal');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $user_id = $_SESSION['user_id'];
    $type = $_POST['type'];
    $department = $_POST['department'];
    $description = $_POST['description'];
    $photo = '';

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo = 'uploads/' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }

    // Update the INSERT statement to exclude the status column
    $stmt = $conn->prepare("INSERT INTO complaints (user_id, type, department, description, photo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $user_id, $type, $department, $description, $photo);

    if ($stmt->execute()) {
        $success = "Complaint added successfully!";
    } else {
        $error = "Error adding complaint: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Complaint - Maintenance Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* Set the background image for the entire page */
        body {
            font-family: 'Roboto', sans-serif;
            background-image: url('complaint_img.webp'); /* Add your image path here */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        header {
            background-color: #003366;
            color: #fff;
            text-align: center;
            padding: 15px 0;
            font-size: 24px;
            font-weight: bold;
            width: 100%;
            position: fixed;
            top: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        
        .complaint-container {
            max-width: 600px;
            width: 100%;
            margin-top: 80px;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.9); /* Slight transparency to make text visible */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            animation: fadeIn 0.5s ease-in-out;
            position: relative;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .complaint-container h2 {
            font-size: 28px;
            color: #003366;
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }

        .complaint-container form {
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 2;
        }

        .complaint-container input[type="text"],
        .complaint-container textarea,
        .complaint-container select {
            margin: 10px 0;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
            position: relative;
            z-index: 2;
        }

        .complaint-container input[type="text"]:focus,
        .complaint-container textarea:focus,
        .complaint-container select:focus {
            border-color: #4A90E2;
            outline: none;
        }

        .complaint-container input[type="file"] {
            margin: 10px 0;
            font-size: 14px;
            color: #555;
            position: relative;
            z-index: 2;
        }

        .complaint-container button {
            background-color: #4A90E2;
            color: #fff;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            position: relative;
            z-index: 2;
        }

        .complaint-container button:hover {
            background-color: #357ABD;
            transform: scale(1.03);
        }

        .error, .success {
            font-size: 16px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .complaint-container {
                margin: 20px;
                padding: 20px;
            }
            
            header {
                font-size: 20px;
                padding: 15px 0;
            }
        }
    </style>
</head>
<body>
    <header>Add Complaint</header>
    <div class="complaint-container">
        <h2>Add a New Complaint</h2>
        <?php if ($error): ?><div class="error"><?= $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?= $success; ?></div><?php endif; ?>
        
        <form action="add_complaint.php" method="post" enctype="multipart/form-data">
            <input type="text" name="user_id" value="<?= $_SESSION['user_id']; ?>" readonly required>

            <!-- Dropdown for Complaint Type -->
            <select name="type" required>
                <option value="" disabled selected>Select Complaint Type</option>
                <option value="IT related">IT</option>
                <option value="Electrical">Electrical</option>
                <option value="Workshop">Workshop</option>
                <option value="Infrastructure related">Infrastructure</option>
            </select>

            <input type="text" name="department" placeholder="Department" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="file" name="photo" accept="image/*">
            <button type="submit">Submit Complaint</button>
        </form>
    </div>
</body>
</html>
