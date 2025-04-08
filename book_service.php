<?php
session_start();
$connection = new mysqli("localhost", "root", "", "ranhuyasystemdb");

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if (!isset($_SESSION['email'])) {
    die("Please log in first.");
}

$customer_email = $_SESSION['email'];
$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;

// Fetch customer details
$customerQuery = "SELECT name FROM user WHERE email = ?";
$stmt = $connection->prepare($customerQuery);
$stmt->bind_param("s", $customer_email);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$customer_name = $customer['name'] ?? 'Unknown';

// Fetch service details
$serviceQuery = "SELECT Name, Price FROM services WHERE ServiceID = ?";
$stmt = $connection->prepare($serviceQuery);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();
$service_name = $service['Name'] ?? 'N/A';
$service_price = $service['Price'] ?? 0;

// Fetch customer's loyalty points
$pointsQuery = "SELECT points FROM loyalty_points WHERE customer_email = ?";
$stmt = $connection->prepare($pointsQuery);
$stmt->bind_param("s", $customer_email);
$stmt->execute();
$pointsData = $stmt->get_result()->fetch_assoc();
$availablePoints = $pointsData['points'] ?? 0;

// Fetch all additional services excluding the chosen one
$additionalServicesQuery = "SELECT ServiceID, Name, Price FROM services WHERE ServiceID != ?";
$stmt = $connection->prepare($additionalServicesQuery);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$additionalServices = $stmt->get_result();

// Fetch stylists
$stylistQuery = "SELECT email, name FROM user WHERE role = 'staff'";
$stylists = $connection->query($stylistQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Service</title>
    <link rel="stylesheet" href="book1.css">
</head>
<body>

    <h2>Book Service</h2>
    <form action="process_booking.php" method="post">
        <input type="hidden" name="customer_email" value="<?php echo htmlspecialchars($customer_email); ?>">
        <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">

        <label>Name:</label>
        <input type="text" value="<?php echo htmlspecialchars($customer_name); ?>" readonly>

        <label>Email:</label>
        <input type="text" value="<?php echo htmlspecialchars($customer_email); ?>" readonly>

        <label>Service:</label>
        <input type="text" value="<?php echo htmlspecialchars($service_name); ?>" readonly>

        <label>Price: Rs.<?php echo number_format($service_price, 2); ?></label>

        <label>Additional Services:</label>
        <div class="checkbox-container">
        <div class="checkbox-item">
            <?php while ($row = $additionalServices->fetch_assoc()): ?>
                <input type="checkbox" name="additional_services[]" value="<?php echo htmlspecialchars($row['ServiceID']); ?>">
                <label><?php echo htmlspecialchars($row['Name']); ?>- Rs.<?php echo number_format($row['Price'], 2); ?></label><br>
            <?php endwhile; ?>
        </div>

        <br>

        <label>Available Loyalty Points: <?php echo $availablePoints; ?></label>
        <label>Use Points (Optional):</label>
        <input type="number" name="points_used" min="0" max="<?php echo $availablePoints; ?>" >

        <label>Date:</label>
        <input type="date" name="date" required>

        <label>Time:</label>
        <input type="time" name="start_time" required>


        <label>Choose Stylist:</label>
        <select name="stylist_email">
            <option value="">Any Available Stylist</option>
            <?php while ($row = $stylists->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($row['email']); ?>"><?php echo htmlspecialchars($row['name']); ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Confirm Booking</button>
    </form>

</body>
</html>
