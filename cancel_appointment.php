<?php
session_start();
header('Content-Type: application/json');

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ranhuyasystemdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);

error_log("Input data: " . print_r($data, true));

if (!isset($data['appointment_id']) || !isset($data['customer_email'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

$appointment_id = $data['appointment_id'];
$customer_email = $data['customer_email'];

// Check if the appointment belongs to the logged-in customer
$checkQuery = "SELECT * FROM appointments WHERE id = ? AND customer_email = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("is", $appointment_id, $customer_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found or does not belong to you']);
    exit();
}

// Fetch appointment details
$appointmentQuery = "SELECT customer_email, service_id, service_name, date, start_time, end_time, stylist_email, total_amount, points_used FROM appointments WHERE id = ?";
$stmt = $conn->prepare($appointmentQuery);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found']);
    exit();
}

$service_id = $appointment['service_id'];
$service_name = $appointment['service_name'];
$date = $appointment['date'];
$start_time = $appointment['start_time'];
$end_time = $appointment['end_time'];
$stylist_email = $appointment['stylist_email'];
$total_amount = $appointment['total_amount'];
$points_used = $appointment['points_used'];

// Fetch stylist name
$stylistQuery = "SELECT name FROM user WHERE email = ?";
$stmt = $conn->prepare($stylistQuery);
$stmt->bind_param("s", $stylist_email);
$stmt->execute();
$stylistResult = $stmt->get_result()->fetch_assoc();
$stylist_name = $stylistResult['name'] ?? 'No stylist assigned';

// Cancel the appointment
$cancelQuery = "DELETE FROM appointments WHERE id = ?";
$stmt = $conn->prepare($cancelQuery);
$stmt->bind_param("i", $appointment_id);

if ($stmt->execute()) {
    // Send cancellation email to the customer
    $cancellationBody = "Dear Customer,\n\nWe regret to inform you that your appointment has been cancelled with the following details:\n\n"
        . "Service: $service_name\n"
        . "Date: $date\n"
        . "Start Time: $start_time\n"
        . "Stylist: $stylist_name\n\n"
        . "If you have any questions or would like to reschedule, please contact us.\n\n"
        . "Thank you for choosing Ranhuya Bridal House!";
    sendEmail($customer_email, 'Appointment Cancellation - Ranhuya Bridal House', $cancellationBody);

    // Check if there is a waitlist for this service and date
    $waitlistQuery = "SELECT * FROM waitlist WHERE service_id = ? AND date = ? ORDER BY id ASC LIMIT 1";
    $stmt = $conn->prepare($waitlistQuery);
    $stmt->bind_param("is", $service_id, $date);
    $stmt->execute();
    $waitlistResult = $stmt->get_result();

    if ($waitlistResult->num_rows > 0) {
        $waitlistCustomer = $waitlistResult->fetch_assoc();
        $waitlist_customer_email = $waitlistCustomer['customer_email'];
        $waitlist_id = $waitlistCustomer['id'];
        $waitlist_points_used = $waitlistCustomer['points_used'];

        // Assign the appointment to the waitlist customer
        $insertQuery = "INSERT INTO appointments (customer_email, service_id, service_name, stylist_email, date, start_time, end_time, total_amount, points_used) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("sisssssdi", $waitlist_customer_email, $service_id, $service_name, $stylist_email, $date, $start_time, $end_time, $total_amount, $waitlist_points_used);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $new_appointment_id = $stmt->insert_id;

            // Delete the waitlist entry
            $deleteWaitlistQuery = "DELETE FROM waitlist WHERE id = ?";
            $stmt = $conn->prepare($deleteWaitlistQuery);
            $stmt->bind_param("i", $waitlist_id);
            $stmt->execute();

            // Send confirmation email to the waitlist customer
            $confirmationBody = "Dear Customer,\n\nYou have been assigned a new appointment from the waitlist with the following details:\n\n"
                . "Appointment ID: $new_appointment_id\n"
                . "Service: $service_name\n"
                . "Date: $date\n"
                . "Start Time: $start_time\n"
                . "Stylist: $stylist_name\n"
                . "Total Price: Rs. " . number_format($total_amount, 2) . "\n\n"
                . "Thank you for choosing Ranhuya Bridal House!";
            sendEmail($waitlist_customer_email, 'Appointment Confirmation - Ranhuya Bridal House', $confirmationBody);

            echo json_encode(['success' => true, 'message' => 'Appointment cancelled and assigned to waitlist customer']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to assign appointment to waitlist customer']);
        }
    } else {
        echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cancel appointment']);
}

$conn->close();

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ranhuyabridal@gmail.com'; 
        $mail->Password   = 'spbx wyjx mlbm fdtb'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('ranhuyabridal@gmail.com', 'Ranhuya Bridal House');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>