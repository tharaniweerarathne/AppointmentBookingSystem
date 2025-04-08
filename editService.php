<?php
$connection = new mysqli("localhost", "root", "", "ranhuyasystemdb");

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Fetch service details based on ServiceID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM services WHERE ServiceID='$id'";
    $result = $connection->query($query);
    $service = $result->fetch_assoc();
}

// Handle Update Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_service'])) {
    $name = $_POST['Name'];
    $price = $_POST['Price'];
    $description = $_POST['Description'];
    $image = $_FILES['Image']['name'] ? basename($_FILES['Image']['name']) : $service['Image'];

    $target_dir = "services/";
    $target_file = $target_dir . $image;

    if ($_FILES['Image']['name']) {
        move_uploaded_file($_FILES["Image"]["tmp_name"], $target_file);
    }

    // Update query
    $query = "UPDATE services SET Name='$name', Price='$price', Description='$description',Image='$image' WHERE ServiceID='$id'";

    if ($connection->query($query) === TRUE) {
        echo "<script>alert('Service updated successfully!'); window.location='insertServices.php';</script>";
    } else {
        echo "Error: " . $connection->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service</title>
    <link rel="stylesheet" href="editCss.css">
</head>
<body>
    <div class="main-content">
        
        <form action="editService.php?id=<?php echo $service['ServiceID']; ?>" method="POST" enctype="multipart/form-data">
        <h2>Edit Service</h2>
            <label>Service Name:</label>
            <input type="text" name="Name" value="<?php echo $service['Name']; ?>" required><br><br>
        
            <label>Price:</label>
            <input type="number" step="0.01" name="Price" value="<?php echo $service['Price']; ?>" required><br><br>

            <label>Description:</label>
            <textarea name="Description" required><?php echo $service['Description']; ?></textarea><br><br> <!-- New Description Field -->
        
            <label>Upload New Image (Optional):</label>
            <input type="file" name="Image"><br><br>
        
            <input type="submit" name="update_service" value="Update Service">
        </form>
    </div>
</body>
</html>
