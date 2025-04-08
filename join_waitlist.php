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

$customer_email = $_POST['customer_email'];
$service_id = $_POST['service_id'];
$date = $_POST['date'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$points_used = $_POST['points_used'];

$insertQuery = "INSERT INTO waitlist (customer_email, service_id, date, start_time, end_time, points_used) 
                VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $connection->prepare($insertQuery);
$stmt->bind_param("sisssi", $customer_email, $service_id, $date, $start_time, $end_time, $points_used);
$stmt->execute();

echo "<div style='text-align: center; margin-top: 20px;'>";

if ($stmt->affected_rows > 0) {
    echo "<p style='color: green; font-weight: bold;'>You have been added to the waitlist. We will notify you if an appointment becomes available.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>Failed to join the waitlist.</p>";
}

// Button to go back to the Customer Dashboard
echo "<a href='customer_Dashbord.php' style='display: inline-block; background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Go to Dashboard</a>";

echo "</div>";

$stmt->close();
$connection->close();
?>
