<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: SignIn.php");
    exit();
}

$name = $_SESSION['name'];

$conn = mysqli_connect("localhost", "root", "", "ranhuyasystemdb");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle adding staff
if (isset($_POST['add_staff'])) {
    $staff_name = mysqli_real_escape_string($conn, $_POST['staff_name']);
    $staff_email = mysqli_real_escape_string($conn, $_POST['staff_email']);
    $staff_password = mysqli_real_escape_string($conn, $_POST['staff_password']);
    $staff_phone = mysqli_real_escape_string($conn, $_POST['staff_phone']);
    $staff_gender = mysqli_real_escape_string($conn, $_POST['staff_gender']);

    // Insert staff details into the database
    $insert_sql = "INSERT INTO user (email, password, name, phoneNo, gender, role) 
                   VALUES ('$staff_email', '$staff_password', '$staff_name', '$staff_phone', '$staff_gender', 'staff')";
    
    if (mysqli_query($conn, $insert_sql)) {
        echo "<script>alert('Staff added successfully!'); window.location='Admin_Dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error adding staff. Please try again.'); window.location='Admin_Dashboard.php';</script>";
        exit();
    }
}

// Handle deleting staff
if (isset($_GET['delete_staff'])) {
    $staff_email = mysqli_real_escape_string($conn, $_GET['delete_staff']);
    $delete_sql = "DELETE FROM user WHERE email = '$staff_email' AND role = 'staff'";
    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('Staff deleted successfully!'); window.location='Admin_Dashboard.php';</script>";
        exit();
    }
}

// Handle deleting customers
if (isset($_GET['delete_customer'])) {
    $customer_email = mysqli_real_escape_string($conn, $_GET['delete_customer']);
    $delete_sql = "DELETE FROM user WHERE email = '$customer_email' AND role = 'customer'";
    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('Customer deleted successfully!'); window.location='Admin_Dashboard.php';</script>";
        exit();
    }
}

// Fetch only staff users
$staff_result = mysqli_query($conn, "SELECT * FROM user WHERE role = 'staff'");

// Fetch customers (excluding passwords)
$customer_result = mysqli_query($conn, "SELECT name, email, phoneNo, gender FROM user WHERE role = 'customer'");

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

//Waitlist

// Fetch customer appointments for staff
$appointments_query = "
    SELECT 
        w.customer_email, 
        s.Name AS service, 
        w.date, 
        w.start_time, 
        w.end_time
    FROM 
        waitlist w
    JOIN 
        services s ON w.service_id = s.ServiceID
    ORDER BY 
        w.date, w.start_time;
";
$appointments_result = $conn->query($appointments_query);

if (!$appointments_result) {
    die("Error fetching appointments data: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="a5.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

    <div class="dashboard-container">
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <button class="sidebar-btn" onclick="document.location='Admin_Dashboard.php'">Home</button>
            <button class="sidebar-btn" onclick="document.location='insertServices.php'">Insert New Services</button>
            <button class="sidebar-btn" onclick="document.location='admin_feedback.php'">Feedbacks</button>
            <button class="sidebar-btn" onclick="document.location='Personal_Style_Admin.php'">Personal Style Recommendations</button>
        </div>

        <div class="main-content">
            <div class="top-bar">
                <div class="profile-section">
                    <i class="ri-user-line profile-icon" onclick="toggleProfileOptions(event)"></i>
                    <div class="profile-options" id="profileOptions">
                        <a href="EditProfile.php">Edit Profile</a>
                    </div>
                </div>
                <div class="logout">
                    <a href="?logout=true" style="text-decoration: none; color: inherit;">
                        <i class="ri-logout-box-line"></i> <span>Log Out</span>
                    </a>
                </div>
            </div>

            <h1 class="welcome">Welcome to the Admin Dashboard, <?php echo htmlspecialchars($name); ?>!</h1>

            <!-- Add Staff Form -->
            <h2>Add Staff</h2>
            <form method="POST" class="Add_staff">
                <input type="text" name="staff_name" placeholder="Enter Name" required>
                <input type="email" name="staff_email" placeholder="Enter Email" required>
                <input type="password" name="staff_password" placeholder="Enter Password" required>
                <input type="text" name="staff_phone" placeholder="Enter Phone No" required>
                <select name="staff_gender">
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
                <button type="submit" name="add_staff">Add Staff</button>
            </form>

            <!-- Staff Table -->
            <h2>Staff Members</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone No</th>
                        <th>Gender</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($staff = mysqli_fetch_assoc($staff_result)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($staff['name']); ?></td>
                            <td><?php echo htmlspecialchars($staff['email']); ?></td>
                            <td><?php echo htmlspecialchars($staff['phoneNo']); ?></td>
                            <td><?php echo htmlspecialchars($staff['gender']); ?></td>
                            <td>
                                <a href="EditStaff.php?email=<?php echo urlencode($staff['email']); ?>" class="edit">
                                    <i class="ri-edit-box-line"></i><span class="edit-text">Edit</span>
                                </a>
                                <a href="?delete_staff=<?php echo urlencode($staff['email']); ?>" class="delete-btn">
                                    <i class="ri-delete-bin-line"></i><span class="delete-text">Delete</span>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Customer Table -->
            <h2>Customers</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone No</th>
                        <th>Gender</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($customer = mysqli_fetch_assoc($customer_result)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phoneNo']); ?></td>
                            <td><?php echo htmlspecialchars($customer['gender']); ?></td>
                            <td>
                                <a href="?delete_customer=<?php echo urlencode($customer['email']); ?>" class="delete-btn">
                                    <i class="ri-delete-bin-line"></i><span class="delete-text">Delete</span>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Messages Section -->
            <h2>Customer Messages</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Reply</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $messages_result = mysqli_query($conn, "SELECT * FROM messages WHERE reply_message IS NULL");
                    while ($message = mysqli_fetch_assoc($messages_result)) { ?>
                        <tr>
                            <td><?= htmlspecialchars($message['fullname']) ?></td>
                            <td><?= htmlspecialchars($message['email']) ?></td>
                            <td><?= htmlspecialchars($message['phone']) ?></td>
                            <td><?= htmlspecialchars($message['message']) ?></td>
                            <td>
                                <form method="POST" action="reply_message.php">
                                    <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                                    <textarea name="reply" required></textarea>
                                    <button type="submit" class="delete-btn">Send Reply</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <h2 >Customer Waitlist</h2>
            <table>
                <thead>
                    <tr>
                        <th>Customer Email</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($appointments_result->num_rows > 0) {
                        while($row = $appointments_result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['customer_email']) . "</td>
                                    <td>" . htmlspecialchars($row['service'] ?? 'N/A') . "</td>
                                    <td>" . htmlspecialchars($row['date'] ?? 'N/A') . "</td>
                                    <td>" . htmlspecialchars($row['start_time'] ?? 'N/A') . "</td>
                                    <td>" . htmlspecialchars($row['end_time'] ?? 'N/A') . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No appointments found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="JavaScript.js"></script>
</body>
</html>