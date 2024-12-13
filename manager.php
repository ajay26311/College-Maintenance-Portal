<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // Update as necessary
$password = ""; // Update as necessary
$dbname = "maintenance_portal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = '$user_id' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $name = $user['name'];
    $roll_no = $user['roll_no'];
    $email = $user['email'];
    $role = $user['role'];
} else {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Fetch manager details
$manager_sql = "SELECT * FROM users WHERE user_id = '$user_id' AND role = 'manager' LIMIT 1";
$manager_result = $conn->query($manager_sql);
$manager_details = null;
if ($manager_result->num_rows > 0) {
    $manager_details = $manager_result->fetch_assoc();
} else {
    // Redirect if not a manager
    header("Location: login.php");
    exit();
}

// Check if user is manager
$is_manager = ($role === 'manager');

// Notification Functions
function sendNotification($sender_id, $message, $conn) {
    // Fetch sender's role
    $sender_query = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $sender_query->bind_param("s", $sender_id);
    $sender_query->execute();
    $sender_result = $sender_query->get_result();
    $sender_data = $sender_result->fetch_assoc();

    if ($sender_data) {
        $sender_role = $sender_data['role'];

        // Set receiver based on sender's role
        $receiver_role = ($sender_role === 'manager') ? 'admin' : 'manager';

        // Fetch receiver ID
        $receiver_query = $conn->prepare("SELECT user_id FROM users WHERE role = ?");
        $receiver_query->bind_param("s", $receiver_role);
        $receiver_query->execute();
        $receiver_result = $receiver_query->get_result();

        while ($receiver = $receiver_result->fetch_assoc()) {
            $receiver_id = $receiver['user_id'];

            // Insert notification
            $stmt = $conn->prepare("INSERT INTO notifications (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $sender_id, $receiver_id, $message);
            $stmt->execute();
        }
    }
}

function getNotifications($user_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE receiver_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function clearNotifications($user_id, $conn) {
    $stmt = $conn->prepare("DELETE FROM notifications WHERE receiver_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
}

// Process POST Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Send notification
    if (isset($_POST['send_notification']) && !empty($_POST['notification_message'])) {
        $message = $conn->real_escape_string($_POST['notification_message']);
        sendNotification($user_id, $message, $conn);
    }

    // Clear notifications
    elseif (isset($_POST['clear_notifications'])) {
        clearNotifications($user_id, $conn);
    }
}

// Fetch notifications for the current user
$notifications = getNotifications($user_id, $conn);


// Fetch complaints for manager or specific user
$complaints_sql = $is_manager 
    ? "SELECT c.complaint_id, u.name, c.type, c.description, c.status FROM complaints c JOIN users u ON c.user_id = u.user_id" 
    : "SELECT * FROM complaints WHERE user_id = '$user_id'";
$complaints_result = $conn->query($complaints_sql);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $complaint_id = $conn->real_escape_string($_POST['complaint_id']);
    $new_status = $conn->real_escape_string($_POST['new_status']);

    // Ensure the new status is valid
    $valid_statuses = ['Pending', 'Completed', 'In Progress'];
    if (in_array($new_status, $valid_statuses)) {
        $update_sql = "UPDATE complaints SET status = '$new_status' WHERE complaint_id = '$complaint_id'";
        if ($conn->query($update_sql)) {
            echo "<p>Status updated successfully.</p>";
        } else {
            echo "<p>Error updating status: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Invalid status selected.</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_manager ? 'Manager Dashboard' : 'Profile - Maintenance Portal'; ?></title>
    <style>
    /* General Styles */
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f7f6;
        margin: 0;
        padding: 0;
        color: #333;
        background-image: url('images/student_staff.webp'); /* Add a background image for the body */
        background-size: cover;
        background-repeat: no-repeat;
        background-attachment: fixed;
    }

    header {
        background-color:  #34495e;
        color: #fff;
        padding: 0.5px;
        text-align: center;
        font-size: 1.5rem;
        font-weight: bold;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        background-image: url('header-background.jpg'); /* Background image for header */
        background-size: cover;
        background-position: center;
    }

    .container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background-color: rgba(255, 255, 255, 0.8); /* Adjusted opacity */
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}


    h2, h3 {
        color: #007bff;
        margin-bottom: 20px;
    }

    /* User Info Section */
    .user-info {
        margin: 20px 0;
        padding: 20px;
        background-color: #f9f9f9;
        border-left: 5px solid #007bff;
        border-radius: 10px;
        background-image: url('user-info-bg.jpg'); /* Background image for user info */
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
    }

    .user-info p {
        font-size: 1.2rem;
        margin: 10px 0;
    }

    /* Notifications Section */
    .notifications {
        margin: 20px 0;
        background-image: url('notifications-bg.jpg'); /* Background image for notifications */
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        padding: 20px;
        border-radius: 10px;
    }

    .notifications ul {
        list-style: none;
        padding: 0;
    }

    .notifications ul li {
        background-color: #f0f8ff;
        padding: 10px 15px;
        margin: 5px 0;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .notifications form button {
        background-color: #dc3545;
        color: #fff;
        border: none;
        padding: 10px 15px;
        font-size: 1rem;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .notifications form button:hover {
        background-color: #c82333;
    }

    /* Form Styling */
    form textarea, form select, form input[type="text"] {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    form button {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 10px 20px;
        font-size: 1rem;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    form button:hover {
        background-color: #0056b3;
    }

    /* Complaint Management Section */
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        background-image: url('table-bg.jpg'); /* Background image for tables */
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
    }

    table th, table td {
        padding: 15px;
        text-align: left;
        border: 1px solid #ddd;
    }

    table th {
        background-color: #007bff;
        color: white;
    }

    table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    table td select {
        padding: 5px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    table td button {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
    }

    table td button:hover {
        background-color: #218838;
    }

    /* Buttons and Links */
    a {
        text-decoration: none;
        color: #fff;
    }

    a.btn {
        display: inline-block;
        margin: 10px 0;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border-radius: 5px;
        text-align: center;
        transition: background-color 0.3s ease;
    }

    a.btn:hover {
        background-color: #0056b3;
    }
</style>

</head>
<body>

<header>
    <h1><?php echo $is_manager ? 'Manager Dashboard' : 'User Profile'; ?></h1>
</header>

<!-- Notifications Section -->
<div class="notifications">
    <h3>Notifications</h3>
    <ul>
        <?php if (isset($notifications) && count($notifications) > 0): ?>
            <?php foreach ($notifications as $notification): ?>
                <li><?php echo $notification['message']; ?></li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No notifications available.</li>
        <?php endif; ?>
    </ul>
    <form method="POST">
        <button type="submit" name="clear_notifications">Clear All Notifications</button>
    </form>
</div>

<div class="container">
    <!-- Profile Section -->
    <?php if (!$is_manager): ?>
        <div>
            <h2>Welcome, <?php echo $name; ?></h2>
            <p><strong>User ID:</strong> <?php echo $user_id; ?></p>
            <p><strong>Email:</strong> <?php echo $email; ?></p>
            <p><strong>Roll Number:</strong> <?php echo $roll_no; ?></p>
            <p><strong>Role:</strong> <?php echo ucfirst($role); ?></p>
            <a href="add_complaint.php">Add Complaint</a>
            <a href="logout.php">Logout</a>
        </div>
    <?php endif; ?>

    <!-- Manager Details Section (Only for Managers) -->
    <?php if ($is_manager): ?>
        <div>
            <h3>Manager Details</h3>
            <p><strong>Manager Name:</strong> <?php echo $manager_details['name']; ?></p>
            <p><strong>User ID:</strong> <?php echo $manager_details['user_id']; ?></p>
            <p><strong>Email:</strong> <?php echo $manager_details['email']; ?></p>
            <p><strong>Role:</strong> <?php echo $manager_details['role']; ?></p>
        </div>
    <?php endif; ?>

    <!-- Send Notification Section (for Managers) -->
    <?php if ($is_manager): ?>
        <div>
            <h3>Send Notification</h3>
            <form method="POST">
                <textarea name="notification_message" placeholder="Enter notification message" required></textarea>
                <button type="submit" name="send_notification">Send Notification</button>
            </form>
        </div>
    <?php endif; ?>

   <!-- Complaint Management Section for Manager -->
<?php if ($is_manager): ?>
    <div>
        <h2>Complaint Management</h2>
        <table>
            <thead>
                <tr>
                    <th>Complaint ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($complaint = $complaints_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $complaint['complaint_id']; ?></td>
                        <td><?php echo $complaint['name']; ?></td>
                        <td><?php echo $complaint['type']; ?></td>
                        <td><?php echo $complaint['description']; ?></td>
                        <td><?php echo $complaint['status']; ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="complaint_id" value="<?php echo $complaint['complaint_id']; ?>">
                                <select name="new_status" required>
                                    <option value="Pending" <?php echo $complaint['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Completed" <?php echo $complaint['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="In Progress" <?php echo $complaint['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                </select>
                                <button type="submit" name="update_status">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</div>

</body>
</html>
