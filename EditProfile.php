<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ranhuyasystemdb");

if (!$conn) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Fetch user details
$email = $_SESSION['email']; 
$sql = "SELECT name, phoneNo, email FROM user WHERE email='$email'";
$result = mysqli_query($conn, $sql);

if ($result->num_rows > 0) {
    $row = mysqli_fetch_assoc($result);
    $name = $row['name'];
    $phoneNo = $row['phoneNo'];
    $currentEmail = $row['email']; 
} else {
    echo "User not found.";
    exit();
}

// Handle form submission
if (isset($_POST['updateProfile'])) {
    $newName = mysqli_real_escape_string($conn, $_POST['name']);
    $newPhoneNo = mysqli_real_escape_string($conn, $_POST['phoneNo']);
    $newPassword = mysqli_real_escape_string($conn, $_POST['password']);
    $newEmail = mysqli_real_escape_string($conn, $_POST['email']);

    // Update query
    if (!empty($newPassword)) {
        $updateSql = "UPDATE user SET name='$newName', phoneNo='$newPhoneNo', password='$newPassword', email='$newEmail' WHERE email='$email'";
    } else {
        $updateSql = "UPDATE user SET name='$newName', phoneNo='$newPhoneNo', email='$newEmail' WHERE email='$email'";
    }

    if (mysqli_query($conn, $updateSql)) {
        $_SESSION['email'] = $newEmail;
        echo "<script>alert('Profile updated successfully!');</script>";
        header("Refresh:0");
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="EDitPro1.css">
</head>
<body>

        <div class="main-content">
            <h1 class="edit_h1">EDIT PROFILE</h1>

            <form method="POST">
                <label>Name:</label>
                <input type="text" name="name" value="<?php echo $name; ?>" required><br>

                <label>Phone Number:</label>
                <input type="text" name="phoneNo" value="<?php echo $phoneNo; ?>" required><br>

                <label>Email:</label>
                <input type="email" name="email" value="<?php echo $currentEmail; ?>" required><br>

                <label>New Password (Leave blank to keep current password):</label>
                <input type="password" name="password"><br>

                <button type="submit" name="updateProfile">Update Profile</button>
            </form>
        </div>
    </div>

</body>
</html>
