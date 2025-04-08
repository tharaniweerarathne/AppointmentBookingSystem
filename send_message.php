<?php

$conn = mysqli_connect("localhost", "root", "", "ranhuyasystemdb");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phonenumber']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Insert into database
    $sql = "INSERT INTO messages (fullname, email, phone, message) 
            VALUES ('$fullname', '$email', '$phone', '$message')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Message sent successfully!'); window.location.href = 'index.php';</script>";
    } else {
        echo "<script>alert('Error: Unable to send message.'); window.location.href = 'index.php';</script>";
    }
}

mysqli_close($conn);
?>