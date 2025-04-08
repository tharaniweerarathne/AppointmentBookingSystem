<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: SignIn.php");
    exit();
}

$email = $_SESSION['email'];

$conn = new mysqli("localhost", "root", "", "ranhuyasystemdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle feedback submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['feedback'])) {
    $feedback = trim($_POST['feedback']);

    if (!empty($feedback)) {
        $stmt = $conn->prepare("INSERT INTO feedback (email, feedback) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $feedback);
        
        if ($stmt->execute()) {
            echo "<script>alert('Thank you for your feedback!');</script>";
        } else {
            echo "<script>alert('Error submitting feedback.');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Feedback cannot be empty.');</script>";
    }
}

// Handle feedback deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_feedback'])) {
    $feedback_id = $_POST['feedback_id'];
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ? AND email = ?");
    $stmt->bind_param("is", $feedback_id, $email);
    
    if ($stmt->execute()) {
        echo "<script>alert('Feedback deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting feedback.');</script>";
    }

    $stmt->close();
}

// Fetch all feedback submitted by the customer
$feedback_query = "SELECT * FROM feedback WHERE email = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($feedback_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$feedback_result = $stmt->get_result();

// Fetch replies for each feedback
$replies = [];
if ($feedback_result->num_rows > 0) {
    while ($feedback = $feedback_result->fetch_assoc()) {
        $feedback_id = $feedback['id'];
        $reply_query = "SELECT * FROM feedback_replies WHERE feedback_id = ?";
        $reply_stmt = $conn->prepare($reply_query);
        $reply_stmt->bind_param("i", $feedback_id);
        $reply_stmt->execute();
        $reply_result = $reply_stmt->get_result();
        $replies[$feedback_id] = $reply_result->fetch_all(MYSQLI_ASSOC);
        $reply_stmt->close();
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback</title>
    <link rel="stylesheet" href="s11.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <h2>Customer Dashboard</h2>
            <button class="sidebar-btn" onclick="document.location='Customer_Dashbord.php'">Home</button>
            <button class="sidebar-btn" onclick="document.location='services.php'">Services</button>
            <button class="sidebar-btn" onclick="document.location='Feedback.php'">FeedBack</button>
            <button class="sidebar-btn" onclick="document.location='PersonalStyle.php'">Personal Style Recommendation</button>
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
                    <a href="index.php" style="text-decoration: none; color: inherit;">
                        <i class="ri-logout-box-line"></i> <span>Log Out</span>
                    </a>
                </div>
            </div>

            <div class="style2_container">
                <h1 class="style2">Submit Your Feedback</h1>
            </div>
            <div class="feedback-container">
                <form method="POST" action="">
                    <label for="feedback" class="feedback_label">Your Feedback:</label>
                    <textarea class="feedback_textarea" name="feedback" id="feedback" required></textarea>
                    <button class="feedbackbtn" type="submit">Submit</button>
                </form>
            </div>

            <div class="feedback-list">
                <h2>Your Feedback History</h2>
                <?php if ($feedback_result->num_rows > 0): ?>
                    <table class="table_feedback">
                        <thead>
                            <tr class="feedbac_tr">
                                <th>Feedback</th>
                                <th>Submitted On</th>
                                <th>Replies</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $feedback_result->data_seek(0);
                            while ($feedback = $feedback_result->fetch_assoc()): ?>
                                <tr class="feedbac_tr">
                                    <td class="feedback_td"><?= htmlspecialchars($feedback['feedback']) ?></td>
                                    <td class="feedback_td"><?= $feedback['created_at'] ?></td>
                                    <td class="feedback_td">
                                        <?php if (!empty($replies[$feedback['id']])): ?>
                                            <ul>
                                                <?php foreach ($replies[$feedback['id']] as $reply): ?>
                                                    <li>
                                                        <strong><?= htmlspecialchars($reply['replied_by']) ?>:</strong>
                                                        <?= htmlspecialchars($reply['reply']) ?>
                                                        <br>
                                                        <small><?= $reply['replied_at'] ?></small>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            No replies yet.
                                        <?php endif; ?>
                                    </td>
                                    <td class="feedback_td">
                                        <form method="POST" action="">
                                            <input type="hidden" name="feedback_id" value="<?= $feedback['id'] ?>">
                                            <button type="submit" name="delete_feedback" class="delete-btn">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No feedback submitted yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="JavaScript.js"></script>
</body>
</html>