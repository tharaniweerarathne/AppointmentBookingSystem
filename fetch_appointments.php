<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ranhuyasystemdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

$loggedInCustomer = $_SESSION['email'] ?? '';

// Fetch all appointments for the calendar
$sql = "SELECT a.id, a.customer_email, a.service_name, a.stylist_email, u.name AS stylist_name, a.date, a.start_time, a.end_time 
        FROM appointments a
        JOIN user u ON a.stylist_email = u.email";
$result = $conn->query($sql);

if (!$result) {
    die(json_encode(["error" => "Query failed: " . $conn->error]));
}

$appointments = []; 

while ($row = $result->fetch_assoc()) {
    $appointments[] = [
        'id' => $row['id'],
        'title' => 'Booked: ' . $row['service_name'], 
        'start' => $row['date'] . 'T' . $row['start_time'], 
        'end' => $row['date'] . 'T' . $row['end_time'], 
        'color' => ($row['customer_email'] === $loggedInCustomer) ? '#44ff44' : '#ff4444', 
        'extendedProps' => [
            'service_name' => $row['service_name'], 
            'stylist_name' => $row['stylist_name'], 
            'customer_email' => $row['customer_email']
        ]
    ];
}

$jsonOutput = json_encode($appointments);
if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(["error" => "JSON encoding error: " . json_last_error_msg()]));
}

echo $jsonOutput;

$conn->close();
?>