<?php
session_start();
if (!isset($_SESSION['email'])) {
    die("Access denied.");
}

// Autoload PHPMailer (if installed via Composer)
require 'vendor/autoload.php';

// If downloaded manually, use these lines instead:
// require 'phpmailer/src/PHPMailer.php';
// require 'phpmailer/src/SMTP.php';
// require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$conn = mysqli_connect("localhost", "root", "", "ranhuyasystemdb");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message_id = mysqli_real_escape_string($conn, $_POST['message_id']);
    $reply = mysqli_real_escape_string($conn, $_POST['reply']);
    $replied_by = $_SESSION['name'];

    // Update the message with the reply
    $sql = "UPDATE messages SET 
            reply_message = '$reply',
            replied_by = '$replied_by',
            replied_at = NOW()
            WHERE id = $message_id";

    if (mysqli_query($conn, $sql)) {
        // Get user email
        $email_result = mysqli_query($conn, "SELECT email FROM messages WHERE id = $message_id");
        $user_email = mysqli_fetch_assoc($email_result)['email'];

        // Send email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'ranhuyabridal@gmail.com'; // Your Gmail address
            $mail->Password = 'spbx wyjx mlbm fdtb'; // Your Gmail password or app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
            $mail->Port = 587; // TCP port to connect to

            // Recipients
            $mail->setFrom('ranhuyabridal@gmail.com', 'Ranhuya Bridal House');
            $mail->addAddress($user_email); // Add a recipient

            // Content
            $mail->isHTML(false); // Set email format to plain text
            $mail->Subject = 'Reply from Ranhuya Bridal House';
            $mail->Body = "Dear Customer,\n\nWe received your message and here's our reply:\n\n$reply\n\nBest regards,\nRanhuya Team";

            $mail->send();
            echo "<script>alert('Reply sent successfully!'); window.history.back();</script>";
        } catch (Exception $e) {
            echo "<script>alert('Error sending email: {$mail->ErrorInfo}'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Error: Unable to send reply.'); window.history.back();</script>";
    }
}

mysqli_close($conn);
?>