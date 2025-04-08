<?php
$connection = new mysqli("localhost", "root", "", "ranhuyasystemdb");

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Fetch services from the database
$query = "SELECT * FROM services";
$result = $connection->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services</title>
    <link rel="stylesheet" href="s11.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

    <div class="dashboard-container">
        <div class="sidebar">
        <h2>Customer Dashboard</h2>
        <button class="sidebar-btn"  onclick="document.location='Customer_Dashbord.php'">Home</button>
            <button class="sidebar-btn"  onclick="document.location='services.php'">Services</button>
            <button class="sidebar-btn" onclick="document.location='Feedback.php'">FeedBack</button>
            <button class="sidebar-btn"  onclick="document.location='PersonalStyle.php'">Personal Style Recommendation</button>
            <button class="sidebar-btn" onclick="document.location='index_customer.php'"><i class="ri-home-4-line"></i> Back to Home</button> 
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

            <h1 class="service_h1">Book Services</h1>

            <div class="services-container">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="service-card">
            <?php if(isset($row['Image'])): ?>
                <img src="services/<?php echo $row['Image']; ?>" alt="<?php echo isset($row['Name']) ? $row['Name'] : 'Service Image'; ?>">
            <?php else: ?>
                <img src="services/default-image.jpg" alt="Default Service Image">
            <?php endif; ?>
            <div class="service-info">
                <h3><?php echo isset($row['Name']) ? $row['Name'] : 'Service Name'; ?></h3>
                <p class="description"><?php echo isset($row['Description']) ? $row['Description'] : 'No description available'; ?></p>
                <p class="Price">Price: Rs.<?php echo isset($row['Price']) ? number_format($row['Price'], 2) : '0.00'; ?></p>

                <a href="book_service.php?service_id=<?php echo isset($row['ServiceID']) ? $row['ServiceID'] : '#'; ?>" class="book-btn">Book Now</a>

            </div>
        </div>
    <?php endwhile; ?>
</div>

    <script src="JavaScript.js"></script>

</body>
</html>

