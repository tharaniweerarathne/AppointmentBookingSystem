<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: SignIn.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "ranhuyasystemdb");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the staff details to edit
if (isset($_GET['email'])) {
    $email = mysqli_real_escape_string($conn, $_GET['email']);
    $query = "SELECT * FROM user WHERE email = ? AND role = 'staff'";
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $staff = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$staff) {
        die("Staff not found.");
    }
}

if (isset($_POST['update_staff'])) {
    $staff_name = mysqli_real_escape_string($conn, $_POST['staff_name']);
    $new_email = mysqli_real_escape_string($conn, $_POST['new_staff_email']);
    $staff_password = mysqli_real_escape_string($conn, $_POST['staff_password']);
    $staff_phone = mysqli_real_escape_string($conn, $_POST['staff_phone']);

    // Check if the new email is already in use
    $check_query = "SELECT email FROM user WHERE email = ? AND email != ?";
    $stmt = mysqli_prepare($conn, $check_query);
    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, "ss", $new_email, $staff['email']);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('Email is already taken. Please choose a different one.'); window.location='EditStaff.php?email=" . $staff['email'] . "';</script>";
        exit();
    }
    
    mysqli_stmt_close($stmt);

    if (!empty($staff_password)) {
        // Update password along with other details
        $update_sql = "UPDATE user SET name = ?, email = ?, password = ?, phoneNo = ? WHERE email = ? AND role = 'staff'";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "sssss", $staff_name, $new_email, $staff_password, $staff_phone, $staff['email']);
    } else {
        // Update only name, email, and phone number
        $update_sql = "UPDATE user SET name = ?, email = ?, phoneNo = ? WHERE email = ? AND role = 'staff'";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "ssss", $staff_name, $new_email, $staff_phone, $staff['email']);
    }

    if (mysqli_stmt_execute($stmt)) {

        if ($_SESSION['email'] == $staff['email']) {
            $_SESSION['email'] = $new_email;
        }
        echo "<script>alert('Staff updated successfully!'); window.location='Admin_Dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error updating staff. Please try again.'); window.location='EditStaff.php?email=$staff[email]';</script>";
        exit();
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff</title>
    <link rel="stylesheet" href="editCss.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<div class="main-content">
    <form method="POST">
        <h1>Edit Staff Details</h1>

        <label>Name:</label>
        <input type="text" name="staff_name" value="<?php echo htmlspecialchars($staff['name']); ?>" required>

        <label>Current Email:</label>
        <input type="text" value="<?php echo htmlspecialchars($staff['email']); ?>" readonly>

        <label>New Email:</label>
        <input type="text" name="new_staff_email" value="<?php echo htmlspecialchars($staff['email']); ?>" required>

        <label>New Password (Leave blank to keep current password):</label>
        <input type="text" name="staff_password" placeholder="Enter New Password">

        <label>Phone number:</label>
        <input type="text" name="staff_phone" value="<?php echo htmlspecialchars($staff['phoneNo']); ?>" required>

        <button type="submit" name="update_staff" class="edit_staff">Update Staff</button>
    </form>
</div>

</body>
</html>
