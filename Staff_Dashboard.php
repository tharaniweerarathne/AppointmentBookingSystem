<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: SignIn.php"); 
    exit();
}
$name = $_SESSION['name']; 


if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="st1.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
</head>
<body>

    <div class="dashboard-container">
        
        <div class="sidebar">
            <h2>Staff Dashboard</h2>
            <button class="sidebar-btn"  onclick="document.location='Staff_dashboard.php'" >Home</button>
            <button class="sidebar-btn" onclick="document.location='feedback_staff.php'">Feedbacks</button>
            <button class="sidebar-btn"  onclick="document.location='Personal_Style_staff.php'" >Personal Style Recommendations</button>
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
            <h2 class=calendar_h2>Your Appointments</h2>
            <div id="calendar"></div>

          
            <h2 class="calendar_h2">Your Appointments</h2>
            <div id="calendar"></div>

            <h2 class="table_h2">Appointment Details</h2>
            <table id="appointmentTable">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Start Time</th>
                    </tr>
                </thead>
                <tbody>
                    
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
        fetch('fetch_staff_appointments.php')
            .then(response => response.json())
            .then(data => {
                
                appointmentTable.innerHTML = '';

                
                data.forEach(appointment => {
                    let row = appointmentTable.insertRow();
                    row.insertCell().innerText = appointment.extendedProps.service_name; 
                    row.insertCell().innerText = appointment.extendedProps.customer_name; 
                    row.insertCell().innerText = appointment.start.split('T')[0]; 
                    row.insertCell().innerText = appointment.start.split('T')[1]; 
                    
                });
            })
            .catch(error => {
                console.error('Error fetching appointments:', error);
            });
    }

 
    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: 'fetch_staff_appointments.php', // Fetch staff-specific appointments
        eventContent: function (info) {
           
            let eventElement = document.createElement('div');
            eventElement.style.padding = '5px';

        
            let serviceName = document.createElement('div');
            serviceName.innerText = `Service: ${info.event.extendedProps.service_name}`;
            serviceName.style.fontWeight = 'bold';
            eventElement.appendChild(serviceName);

            let customerName = document.createElement('div');
            customerName.innerText = `Customer: ${info.event.extendedProps.customer_name}`;
            eventElement.appendChild(customerName);

            return { domNodes: [eventElement] };
        }
    });

    calendar.render();

    fetchAndDisplayAppointments();
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