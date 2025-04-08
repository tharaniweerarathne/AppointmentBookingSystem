<?php
session_start();
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$connection = new mysqli("localhost", "root", "", "ranhuyasystemdb");

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Retrieve form data
$customer_email = $_POST['customer_email'];
$service_id = $_POST['service_id'];  
$date = $_POST['date'];
$start_time = $_POST['start_time'];
$points_used = $_POST['points_used'];
$staff_email = isset($_POST['stylist_email']) ? $_POST['stylist_email'] : NULL;

// Validate date
$today = date('Y-m-d'); 
if ($date < $today) {
    die("You cannot book appointments for past dates. Please select today's date or a future date.");
}

// Format start and end time
$start_time = date('H:i', strtotime($start_time));
$start_time_obj = new DateTime($start_time);
$end_time_obj = clone $start_time_obj;
$end_time_obj->modify('+1 hour'); 
$start_time = $start_time_obj->format('H:i');
$end_time = $end_time_obj->format('H:i');

// Validate points used
$points_used = max(0, (int)$points_used); 

// Fetch primary service details
$serviceQuery = "SELECT Name, Price FROM services WHERE ServiceID = ?";
$stmt = $connection->prepare($serviceQuery);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();

if ($service) {
    $primary_service_name = $service['Name'];
    $primary_service_price = $service['Price'];
} else {
    die("Service not found.");
}

// Fetch customer's loyalty points
$pointsQuery = "SELECT points FROM loyalty_points WHERE customer_email = ?";
$stmt = $connection->prepare($pointsQuery);
$stmt->bind_param("s", $customer_email);
$stmt->execute();
$pointsResult = $stmt->get_result()->fetch_assoc();
$customer_points = $pointsResult['points'] ?? 0; 

// Add 100 loyalty points if no points are found
if ($customer_points == 0) {
    $customer_points = 100;
    $updatePointsQuery = "INSERT INTO loyalty_points (customer_email, points) VALUES (?, ?)";
    $stmt = $connection->prepare($updatePointsQuery);
    $stmt->bind_param("si", $customer_email, $customer_points);
    $stmt->execute();
}

// Ensure points used do not exceed available points
$points_used = min($points_used, $customer_points);  

// Apply loyalty points
$discount = min($points_used, $primary_service_price);
$final_price = $primary_service_price - $discount;

// Fetch additional services
$additional_services = $_POST['additional_services'] ?? [];
$additional_price = 0;

foreach ($additional_services as $service_id) {
    $serviceQuery = "SELECT Price FROM services WHERE ServiceID = ?";
    $stmt = $connection->prepare($serviceQuery);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $additional_service = $stmt->get_result()->fetch_assoc();
    $additional_price += $additional_service['Price'];
}

$final_price += $additional_price;

// Validate working hours
$dayOfWeek = date('w', strtotime($date)); 
if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
    $workStart = "07:59:00";
    $workEnd = "20:00:00";
} else {
    $workStart = "07:59:00";
    $workEnd = "18:00:00";
}

if ($start_time < $workStart || $start_time > $workEnd || $end_time < $workStart || $end_time > $workEnd) {
    die("The selected time is outside of our working hours. Please choose a time between $workStart and $workEnd.");
}

// Assign stylist
$stylist_name = 'No available stylist';
$time_gap = 3600;
$buffer_start_time = date('H:i', strtotime($start_time) - $time_gap);
$buffer_end_time = date('H:i', strtotime($end_time) + $time_gap);

$stylist_assigned = false;

if (!empty($staff_email)) {
    $checkQuery = "SELECT * FROM appointments 
                   WHERE stylist_email = ? 
                   AND date = ? 
                   AND (
                       (start_time < ? AND end_time > ?) OR  
                       (start_time < ? AND end_time > ?) OR  
                       (start_time >= ? AND end_time <= ?)   
                   )";
    $stmt = $connection->prepare($checkQuery);
    $stmt->bind_param("ssssssss", $staff_email, $date, $buffer_end_time, $buffer_start_time, $buffer_start_time, $buffer_end_time, $buffer_start_time, $buffer_end_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $stylistQuery = "SELECT name FROM user WHERE email = ?";
        $stmt = $connection->prepare($stylistQuery);
        $stmt->bind_param("s", $staff_email);
        $stmt->execute();
        $stylistResult = $stmt->get_result()->fetch_assoc();
        $stylist_name = $stylistResult['name'];
        $stylist_assigned = true;
    } else {
        echo '<p style="color: #d9534f; background-color: #f2dede; border: 1px solid #d9534f; padding: 10px; border-radius: 5px; text-align: center; font-weight: bold; width: fit-content; margin: 10px auto;">
        The selected stylist is not available at this time. Please choose a different time or stylist.</p>';
        
    }
}

// Auto-assign staff if none selected or if the selected stylist is not available
if (!$stylist_assigned && empty($staff_email)) {
    $allStylistsQuery = "SELECT name, email FROM user WHERE role = 'staff'";
    $stmt = $connection->prepare($allStylistsQuery);
    $stmt->execute();
    $allStylistsResult = $stmt->get_result();

    while ($row = $allStylistsResult->fetch_assoc()) {
        $stylist_email = $row['email'];
        $checkQuery = "SELECT * FROM appointments 
                       WHERE stylist_email = ? 
                       AND date = ? 
                       AND (
                           (start_time < ? AND end_time > ?) OR  
                           (start_time < ? AND end_time > ?) OR  
                           (start_time >= ? AND end_time <= ?)   
                       )";
        $stmt = $connection->prepare($checkQuery);
        $stmt->bind_param("ssssssss", $stylist_email, $date, $buffer_end_time, $buffer_start_time, $buffer_start_time, $buffer_end_time, $buffer_start_time, $buffer_end_time);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $stylist_name = $row['name'];
            $staff_email = $stylist_email;
            $stylist_assigned = true;
            break;
        }
    }
}

if (!$stylist_assigned) {
    // No stylist is available, offer to join the waitlist
    echo '<p style="color: #d9534f; background-color: #f2dede; border: 1px solid #d9534f; padding: 10px; border-radius: 5px; text-align: center; font-weight: bold; width: fit-content; margin: 10px auto;">
    Would you like to join the waitlist?</p>';
    
    echo "<form action='join_waitlist.php' method='post'>";
    echo "<input type='hidden' name='customer_email' value='$customer_email'>";
    echo "<input type='hidden' name='service_id' value='$service_id'>";
    echo "<input type='hidden' name='date' value='$date'>";
    echo "<input type='hidden' name='start_time' value='$start_time'>";
    echo "<input type='hidden' name='end_time' value='$end_time'>";
    echo "<input type='hidden' name='points_used' value='$points_used'>";
    echo '<button type="submit" style="background-color: #007bff; color: white; border: none; padding: 10px 15px; border-radius: 5px; font-size: 16px; cursor: pointer; display: block; margin: 10px auto;">
    Join Waitlist
    </button>';
    
    echo "</form>";
    die();
}

// Insert booking into appointments table
$insertQuery = "INSERT INTO appointments (customer_email, service_id, service_name, stylist_email, date, start_time, end_time, points_used, total_amount) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $connection->prepare($insertQuery);
$stmt->bind_param("sissssddd", $customer_email, $service_id, $primary_service_name, $staff_email, $date, $start_time, $end_time, $points_used, $final_price);
$stmt->execute();

if ($stmt->affected_rows <= 0) {
    die("Failed to create appointment.");
}

$appointment_id = $stmt->insert_id;

// Insert additional services
if (!empty($additional_services)) {
    $insertAdditionalQuery = "INSERT INTO appointment_services (appointment_id, service_id) VALUES (?, ?)";
    $stmt = $connection->prepare($insertAdditionalQuery);
    foreach ($additional_services as $service_id) {
        $stmt->bind_param("ii", $appointment_id, $service_id);
        $stmt->execute();
    }
}

// Award points based on final price
$points_earned = floor($final_price / 100);  
$new_points = $customer_points - $points_used + $points_earned; 

$updatePointsQuery = "UPDATE loyalty_points SET points = ? WHERE customer_email = ?";
$stmt = $connection->prepare($updatePointsQuery);
$stmt->bind_param("is", $new_points, $customer_email);

if ($stmt->execute()) {
    echo "Points updated successfully. <br>";
} else {
    echo "Error updating points. <br>";
}

echo "Booking confirmed. Your appointment ID is: " . $appointment_id;

// Send email to customer using PHPMailer
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Gmail SMTP server
    $mail->SMTPAuth   = true;            // Enable SMTP authentication
    $mail->Username   = 'your_email'; // Your Gmail address
    $mail->Password   = 'your_password'; // Your Gmail password or App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port       = 587; // TCP port to connect to

    // Recipients
    $mail->setFrom('your_email', 'Ranhuyabridal');
    $mail->addAddress($customer_email); // Add customer email

    // Content
    $mail->isHTML(false); // Set email format to plain text
    $mail->Subject = 'Appointment Confirmation - Ranhuya Bridal House';
    $mail->Body    = "Dear Customer,\n\nYour appointment has been confirmed with the following details:\n\n"
        . "Appointment ID: $appointment_id\n"
        . "Service: $primary_service_name\n"
        . "Date: $date\n"
        . "Start Time: $start_time\n"
        . "Stylist: $stylist_name\n"
        . "Total Price: Rs. " . number_format($final_price, 2) . "\n\n"
        . "Thank you for choosing Ranhuya Bridal House!";

    $mail->send();
    echo 'Email sent to customer successfully.';
} catch (Exception $e) {
    echo "Email could not be sent. Error: {$mail->ErrorInfo}";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROCESS BOOKING</title>
    <link rel="stylesheet" href="process1.css">
</head>
<body>
    <div class="container">
        <h2>Your Final Price</h2>
        <div class="price-details">
            <p><strong>Service Name:</strong> <?php echo $primary_service_name; ?></p>
            <p><strong>Price for Service:</strong> Rs.<?php echo number_format($primary_service_price, 2); ?></p>
            <p><strong>Additional Services:</strong> Rs.<?php echo number_format($additional_price, 2); ?></p>
            <p><strong>Discount Applied:</strong> -Rs.<?php echo number_format($discount, 2); ?></p>
            <p><strong>Remaining Points:</strong> <?php echo $new_points; ?> points</p>
            <p><strong>Stylist Name:</strong> <?php echo $stylist_name; ?></p>
            <h3 class="total-price"><strong>Total Price:</strong> Rs.<?php echo number_format($final_price, 2); ?></h3>
            <button class="sidebar-btn" onclick="document.location='Customer_Dashbord.php'">Back to the Dashboard</button>
        </div>
    </div>
    <script type="text/javascript">
        var appointmentId = <?php echo $appointment_id; ?>;
        alert("Booking confirmed. Your appointment ID is: " + appointmentId);
    </script>
</body>
</html>
