<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // Update if necessary
$password = ""; // Leave empty if root has no password, else provide the correct password
$dbname = "maintenance_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the user information from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = '$user_id' LIMIT 1";
$result = $conn->query($sql);

// Check if user data is found
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // Get user details
    $name = $user['name'];
    $roll_no = $user['roll_no'];
    $email = $user['email'];
    $role = $user['role'];
    $user_id = $user['user_id'];
} else {
    // If no user found, log them out
    session_destroy();
    header("Location: login.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Maintenance Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
    /* General styles */
    body {
        font-family: 'Roboto', sans-serif;
        background: url('') no-repeat center center fixed;
        background-size: cover;
        color: #333;
        margin: 0;
        padding: 0;
        display: flex;
    }

    header {
        width: 100%;
        background-color: #34495e;
        padding: 15px 0;
        text-align: center;
        color: white;
        font-size: 24px;
        font-weight: bold;
        position: fixed;
        top: 0;
        left: 0;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        z-index: 1000;
    }

    .sidebar {
        width: 250px;
        background-color: #34495e;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        padding-top: 80px;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .sidebar img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin-bottom: 20px;
    }

    .sidebar h3 {
        font-size: 20px;
        text-align: center;
        margin-bottom: 30px;
    }

    .sidebar a {
        text-decoration: none;
        color: white;
        padding: 10px 20px;
        margin: 5px 0;
        border-radius: 5px;
        text-align: center;
        width: 80%;
        display: block;
        transition: background-color 0.3s;
    }

    .sidebar a:hover {
        background-color: #2c3e50;
    }

    .container {
        margin-left: 270px;
        max-width: 800px;
        background-color: rgba(255, 255, 255, 0.9);
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        margin-top: 80px; /* Reduced the gap */
    }

    h1 {
        text-align: center;
        margin-bottom: 25px;
        font-size: 28px;
        color: #003366;
        font-weight: 700;
    }

    .profile-info {
        margin: 20px 0;
        padding: 20px;
        background-color: rgba(247, 249, 252, 0.9);
        border-radius: 10px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .profile-info h2 {
        font-size: 22px;
        color: #003366;
        margin-bottom: 10px;
        font-weight: bold;
    }

    .profile-info p {
        font-size: 16px;
        margin: 5px 0;
        color: #555;
    }

    .btn {
        display: block;
        width: 180px;
        margin: 20px auto;
        padding: 12px 0;
        text-align: center;
        background-color: #007bff;
        color: white;
        font-size: 16px;
        font-weight: 500;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .btn:hover {
        background-color: #0056b3;
    }

    /* Complaints Section */
.complaints {
    margin-top: 50px; /* Add space from the top */
    margin-bottom: 10px; /* Reduce space at the bottom */
    padding-top: 20px; /* Add internal padding to the top */
    padding-bottom: 15px; /* Add a little padding at the bottom */
    width: 90%; /* Slightly shrink the width for alignment */
    display: flex;
    flex-direction: column;
    align-items: flex-start; /* Align to the left side */
    margin-left: 50px; /* Shift box to the left */
    background-color: rgba(247, 249, 252, 0.9); /* Optional for contrast */
    border-radius: 10px; /* Optional for styling */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Optional for a clean look */
}

.complaints h2 {
    font-size: 22px;
    color: #003366;
    font-weight: bold;
    margin-bottom: 15px;
}

.complaints table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.complaints th,
.complaints td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.complaints th {
    background-color: #003366;
    color: white;
    font-weight: bold;
}

.complaints td {
    background-color: #f7f9fc;
    color: #333;
}

/* Status color coding */
.complaints td.status-working {
    background-color: #ffeb3b;
    font-weight: bold;
}

.complaints td.status-pending {
    background-color: #ff9800;
    font-weight: bold;
    color: #fff;
}

.complaints td.status-completed {
    background-color: #4caf50;
    color: white;
    font-weight: bold;
}

/* Lines between Complaint ID, Type, Description, and Status */
.complaints th, .complaints td {
    border-right: 1px solid #ddd; /* Add vertical lines between the columns */
}

.complaints th:last-child, .complaints td:last-child {
    border-right: none; /* Remove right border on the last column */
}

</style>

</head>

<body>

<header>
    Maintenance Portal
</header>

<!-- Sidebar Section -->
<div class="sidebar">
    <img src="images/profile_logo.png" alt="Profile Logo"> <!-- Replace 'profile_logo.png' with the actual profile image path -->
    <h3>Welcome,<br><?php echo  $name; ?></h3>
    <a href="add_complaint.php">Add Complaint</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</div>

<!-- Profile section -->
<div class="container">
    <h1>Welcome, <?php echo $name; ?></h1>

    <div class="profile-info">
        <h2>Profile Information</h2>
        <p><strong>Name:</strong> <?php echo $name; ?></p>
        <p><strong>Roll Number:</strong> <?php echo $roll_no; ?></p>
        <p><strong>Email:</strong> <?php echo $email; ?></p>
        <p><strong>User ID:</strong> <?php echo $user_id; ?></p>
        <p><strong>Role:</strong> <?php echo ucfirst($role); ?></p>
    </div>
</div>

<!-- Complaints Section -->
<div class="container complaints">
    <h2>Your Complaints</h2>
    <?php
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "SELECT * FROM complaints WHERE user_id = '$user_id' ORDER BY complaint_id DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<table>
                <tr>
                    <th>Complaint ID</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Status</th>
                </tr>';
        while ($complaint = $result->fetch_assoc()) {
            echo '<tr>
                    <td>' . $complaint['complaint_id'] . '</td>
                    <td>' . $complaint['type'] . '</td>
                    <td>' . $complaint['description'] . '</td>
                    <td class="status-' . strtolower($complaint['status']) . '">' . ucfirst($complaint['status']) . '</td>
                </tr>';
        }
        echo '</table>';
    } else {
        echo '<p>No complaints found.</p>';
    }

    $conn->close();
    ?>
</div>

</body>

</html>

