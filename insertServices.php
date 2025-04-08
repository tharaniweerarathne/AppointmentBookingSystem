<?php
$connection = new mysqli("localhost", "root", "", "ranhuyasystemdb");

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Handle Form Submission for Adding a Service
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_service'])) {
    $name = $_POST['Name'];
    $price = $_POST['Price'];
    $description = $_POST['Description'];

    $target_dir = "services/";  
    $image = basename($_FILES["Image"]["name"]);
    $target_file = $target_dir . $image;

    if (move_uploaded_file($_FILES["Image"]["tmp_name"], $target_file)) {

        $query = "INSERT INTO services (Name, Price, Description,Image) VALUES ('$name', '$price', '$description', '$image')";
        if ($connection->query($query) === TRUE) {
            echo "<script>alert('Service added successfully!'); window.location='insertServices.php';</script>";
        } else {
            echo "Error: " . $connection->error;
        }
    } else {
        echo "Error uploading image.";
    }
}

// Handle Deletion of Service
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_service'])) {
    $id = $_POST['ServiceID'];
    $image = $_POST['Image'];

    $query = "DELETE FROM services WHERE ServiceID='$id'";
    if ($connection->query($query) === TRUE) {

        if (!empty($image)) {
            $image_path = "services/" . $image;
            if (file_exists($image_path)) {
                unlink($image_path);  
            }
        }
        echo "<script>alert('Service deleted successfully!'); window.location='insertServices.php';</script>";
    } else {
        echo "Error: " . $connection->error;
    }
}

// Fetch services from database
$query = "SELECT * FROM services";
$result = $connection->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Services</title>
    <link rel="stylesheet" href="a5.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

    <div class="dashboard-container">

        <div class="sidebar">
        <h2>Admin Dashboard</h2>
            <button class="sidebar-btn" onclick="document.location='Admin_Dashboard.php'">Home</button>
            <button class="sidebar-btn"  onclick="document.location='insertServices.php'">Insert New Services</button>
            <button class="sidebar-btn"  onclick="document.location='admin_feedback.php'">Feedbacks</button>
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
                    <a href="index.php" style="text-decoration: none; color: inherit;">
                        <i class="ri-logout-box-line"></i> <span>Log Out</span>
                    </a>
                </div>
            </div>

            <h2 class="services">Add a New Service</h2>
            <form action="insertServices.php" method="POST" enctype="multipart/form-data">
                <label>Service Name:</label>
                <input type="text" name="Name" required><br><br>
            
                <label>Price:</label>
                <input type="number"  step="0.01" name="Price" required><br><br>
            
                <label>Upload Image:</label>
                <input type="file" name="Image" required><br><br>

                <label>Description:</label>
                <textarea name="Description" rows="4" required></textarea><br><br>
            
                <input type="submit" name="add_service" value="Add Service">

            </form>


            <!-- Services Table -->
<table>
    <thead>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Price</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td>
                
                <img src="services/<?php echo isset($row['Image']) ? $row['Image'] : 'default.jpg'; ?>" width="60">
            </td>
            <td>
                
                <?php echo isset($row['Name']) ? $row['Name'] : 'N/A'; ?>
            </td>
            <td>
                
                Rs.<?php echo isset($row['Price']) ? number_format($row['Price'], 2) : '0.00'; ?>
            </td>

            <td>
                <?php echo isset($row['Description']) ? $row['Description'] : 'No description available'; ?>
            </td>

            <td>
                            <a href="editService.php?id=<?php echo $row['ServiceID']; ?>" class="edit">
                                <i class="ri-edit-box-line"></i> Edit
                            </a>
                            <form action="insertServices.php" method="POST" style="display:inline;">
                                <input type="hidden" name="ServiceID" value="<?php echo $row['ServiceID']; ?>">
                                <input type="hidden" name="Image" value="<?php echo $row['Image']; ?>">
                                <button type="submit" name="delete_service" class="delete">
                                    <i class="ri-delete-bin-line"></i> Delete
                                </button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>

</table>


        </div>
    </div>

    <script src="JavaScript.js"></script>
</body>
</html>

