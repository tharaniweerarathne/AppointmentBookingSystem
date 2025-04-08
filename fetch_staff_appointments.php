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

$staff_email = $_SESSION['email'] ?? '';

// Fetch appointments with service and customer details
$sql = "SELECT a.id, a.customer_email, a.service_name, u.name AS customer_name, a.date, a.start_time, a.end_time 
        FROM appointments a
        JOIN user u ON a.customer_email = u.email
        WHERE a.stylist_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $staff_email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die(json_encode(["error" => "Query failed: " . $conn->error]));
}

$appointments = []; 

while ($row = $result->fetch_assoc()) {
    $appointments[] = [
        'id' => $row['id'],
        'title' => $row['service_name'], 
        'start' => $row['date'] . 'T' . $row['start_time'], 
        'end' => $row['date'] . 'T' . $row['end_time'], 
        'color' => '#44ff44', 
        'extendedProps' => [
            'service_name' => $row['service_name'], 
            'customer_name' => $row['customer_name'], 
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