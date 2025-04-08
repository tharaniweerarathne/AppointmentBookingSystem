<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: SignIn.php"); 
    exit();
}
$name = $_SESSION['name']; 
$email = $_SESSION['email']; 

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ranhuyasystemdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Fetch waitlist details
// Fetch waitlist details with service and stylist information
$waitlist_query = "
    SELECT 
        w.id, 
        s.Name AS service, 
        w.date, 
        w.start_time, 
        w.end_time, 
        w.points_used, 
        w.added_at
    FROM 
        waitlist w
    JOIN 
        services s ON w.service_id = s.ServiceID

    WHERE 
        w.customer_email = '$email'
";
$waitlist_result = $conn->query($waitlist_query);

if (!$waitlist_result) {
    die("Error fetching waitlist data: " . $conn->error);
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_query = "DELETE FROM waitlist WHERE id = $id AND customer_email = '$email'";
    if ($conn->query($delete_query) === TRUE) {
        echo "<script>alert('Record deleted successfully');</script>";
        header("Location: Customer_Dashbord.php"); // Refresh the page
    } else {
        echo "<script>alert('Error deleting record: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="s11.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
</head>
<body>
    <div class="dashboard-container">
 
         
        <div class="dashboard-container">
        <div class="sidebar">
        <h2>Customer Dashboard</h2>
        <button class="sidebar-btn"  onclick="document.location='Customer_Dashbord.php'">Home</button>
            <button class="sidebar-btn"  onclick="document.location='services.php'">Services</button>
            <button class="sidebar-btn" onclick="document.location='Feedback.php'">FeedBack</button>
            <button class="sidebar-btn"  onclick="document.location='PersonalStyle.php'">Personal Style Recommendation</button>
            <button class="sidebar-btn" onclick="document.location='index_customer.php'"><i class="ri-home-4-line"></i> Back to Home</button> 
        </div>



        <div class="main-content">
            <div class="top-bar">

                <div class="profile-section">
                    <i class="ri-user-line profile-icon" onclick="toggleProfileOptions(event)"></i>
                    <div class="profile-options" id="profileOptions">
                        <a href="EditProfile.php">Edit Profile</a>
                    </div>
                </div>

                <div class="logout">
                    <a href="?logout=true" style="text-decoration: none; color: inherit;">
                        <i class="ri-logout-box-line"></i> <span>Log Out</span>
                    </a>
                </div>
            </div>

            <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>

            <!-- Appointment Booking Calendar -->
            <h2 class="calendar_h2">My Appointment</h2>
            <div id="calendar"></div>

            <h2 class="calendar_h2">My Appointment</h2>
            <div id="calendar"></div>

            <!-- Appointment Details Table -->
            <h2 class="table_h2">Appointment Details</h2>
            <table id="appointmentTable">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Stylist</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
    
                </tbody>
            </table>

<h2 class="table_h2">Waitlist Details</h2>
<table id="appointmentTable">
    <thead>
        <tr>
            <th>Service</th>
            <th>Date</th>
            <th>Start Time</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($waitlist_result->num_rows > 0) {
            while($row = $waitlist_result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['service']) . "</td>
                        <td>" . htmlspecialchars($row['date']) . "</td>
                        <td>" . htmlspecialchars($row['start_time']) . "</td>
                        <td>
                            <a href='?delete=" . $row['id'] . "' 
                               class='delete-btn' 
                               onclick='return confirm(\"Are you sure you want to delete this record?\");'>
                               Delete
                            </a>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No appointments found in the waitlist.</td></tr>";
        }
        ?>
    </tbody>
</table>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
    let calendarEl = document.getElementById('calendar');
    let appointmentTable = document.getElementById('appointmentTable').getElementsByTagName('tbody')[0];

    if (!calendarEl) {
        console.error("Calendar element not found!");
        return;
    }

    function fetchAndDisplayAppointments() {
        fetch('fetch_appointments.php')
            .then(response => response.json())
            .then(data => {
                appointmentTable.innerHTML = '';

                data.forEach(appointment => {
                    
                    if (appointment.extendedProps.customer_email === '<?php echo htmlspecialchars($_SESSION['email']); ?>') {
                        let row = appointmentTable.insertRow();
                        row.insertCell().innerText = appointment.extendedProps.service_name;
                        row.insertCell().innerText = appointment.extendedProps.stylist_name;
                        row.insertCell().innerText = appointment.start.split('T')[0]; 
                        row.insertCell().innerText = appointment.start.split('T')[1]; 

                        let cancelButton = document.createElement('button');
                        cancelButton.innerText = 'Cancel';
                        cancelButton.style.backgroundColor = '#ff4444';
                        cancelButton.style.color = '#fff';
                        cancelButton.style.border = 'none';
                        cancelButton.style.padding = '5px 10px';
                        cancelButton.style.borderRadius = '5px';
                        cancelButton.style.cursor = 'pointer';
                        cancelButton.addEventListener('click', function () {
                            if (confirm('Are you sure you want to cancel this appointment?')) {
                                cancelAppointment(appointment.id);
                            }
                        });
                        row.insertCell().appendChild(cancelButton);
                    }
                });
            })
            .catch(error => {
                console.error('Error fetching appointments:', error);
            });
    }

    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        selectable: false, 
        events: 'fetch_appointments.php', 
        eventContent: function (info) {

            let eventElement = document.createElement('div');
            eventElement.style.padding = '5px';

            if (info.event.extendedProps.customer_email === '<?php echo htmlspecialchars($_SESSION['email']); ?>') {
                let cancelButton = document.createElement('button');
                cancelButton.innerText = 'Cancel';
                cancelButton.style.marginLeft = '10px';
                cancelButton.style.backgroundColor = '#ff4444';
                cancelButton.style.color = '#fff';
                cancelButton.style.border = 'none';
                cancelButton.style.padding = '5px 10px';
                cancelButton.style.borderRadius = '5px';
                cancelButton.style.cursor = 'pointer';
                cancelButton.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (confirm('Are you sure you want to cancel this appointment?')) {
                        cancelAppointment(info.event.id);
                    }
                });
                eventElement.appendChild(cancelButton);
            } else {
                // Add a red dot for other customers' appointments
                let redDot = document.createElement('div');
                redDot.style.width = '10px';
                redDot.style.height = '10px';
                redDot.style.backgroundColor = '#ff4444';
                redDot.style.borderRadius = '50%';
                redDot.style.display = 'inline-block';
                redDot.style.marginLeft = '10px';
                eventElement.appendChild(redDot);
            }

            // Add service name
            let serviceName = document.createElement('div');
            serviceName.innerText = `Service: ${info.event.extendedProps.service_name}`;
            serviceName.style.fontWeight = 'bold';
            eventElement.appendChild(serviceName);

            // Add stylist name
            let stylistName = document.createElement('div');
            stylistName.innerText = `Stylist: ${info.event.extendedProps.stylist_name}`;
            eventElement.appendChild(stylistName);

            return { domNodes: [eventElement] };
        }
    });

    calendar.render();

    // Fetch and display appointments in the table
    fetchAndDisplayAppointments();

    // Function to cancel an appointment
    function cancelAppointment(appointmentId) {
        fetch('cancel_appointment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                appointment_id: appointmentId,
                customer_email: '<?php echo htmlspecialchars($_SESSION['email']); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Appointment canceled successfully!');
                calendar.refetchEvents(); 
                fetchAndDisplayAppointments(); 
            } else {
                alert('Failed to cancel appointment: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while canceling the appointment.');
        });
    }
});

        function toggleProfileOptions(event) {
            let profileOptions = document.getElementById('profileOptions');
            profileOptions.style.display = profileOptions.style.display === 'block' ? 'none' : 'block';
            event.stopPropagation();
        }

        window.onclick = function() {
            document.getElementById('profileOptions').style.display = 'none';
        }
    </script>

<script src="JavaScript.js"></script>
</body>
</html>