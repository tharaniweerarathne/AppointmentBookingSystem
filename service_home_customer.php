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
    <title>Our Services</title>
    <link rel="stylesheet" href="More_service1.css">
</head>
<body>

<header>
    <h1>Our Services</h1>
    <p>Explore our professional services designed to meet your needs.</p>
</header>

<!-- Services Section -->
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


</body>
</html>
