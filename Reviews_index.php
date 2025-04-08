<?php
session_start();
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "ranhuyasystemdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT email, feedback FROM feedback";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews</title>
    <link rel="stylesheet" href="reviewspage.css">
</head>
<body>

<header>
    <button class="nav-btn" onclick="window.location.href='index.php'">Home</button>
    <button class="nav-btn" onclick="document.location='signIn.php'">Join Now</button>
</header>

<section class="reviews-section">
    <h1>What Our Customers Say</h1>
    <div class="carousel">
        <button class="prev" onclick="moveSlide(-1)">&#10094;</button>
        <div class="reviews-container">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='review-card'>";
                    echo "<div class='review-email'>" . htmlspecialchars($row["email"]) . "</div>";
                    echo "<p class='review-text'>" . htmlspecialchars($row["feedback"]) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p class='no-feedback'>No feedback available.</p>";
            }
            ?>
        </div>
        <button class="next" onclick="moveSlide(1)">&#10095;</button>
    </div>
</section>

<script>
    let currentIndex = 0;

    function moveSlide(direction) {
        const container = document.querySelector('.reviews-container');
        const totalCards = document.querySelectorAll('.review-card').length;
        const cardWidth = document.querySelector('.review-card').offsetWidth + 20; 

        currentIndex += direction;

        if (currentIndex < 0) {
            currentIndex = totalCards - 1;
        } else if (currentIndex >= totalCards) {
            currentIndex = 0;  
        }

        container.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
    }
</script>

</body>
</html>
