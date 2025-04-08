<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: SignIn.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "ranhuyasystemdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle reply submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_reply'])) {
    $feedback_id = $_POST['feedback_id'];
    $reply = trim($_POST['reply']);
    $replied_by = $_SESSION['name'];

    if (!empty($reply)) {
        $stmt = $conn->prepare("INSERT INTO feedback_replies (feedback_id, reply, replied_by) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $feedback_id, $reply, $replied_by);
        
        if ($stmt->execute()) {
            echo "<script>alert('Reply submitted successfully!'); window.location.href='feedback_staff.php';</script>";
        } else {
            echo "<script>alert('Error submitting reply.');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Reply cannot be empty.');</script>";
    }
}

// Handle reply deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_reply'])) {
    $reply_id = $_POST['reply_id'];

    $stmt = $conn->prepare("DELETE FROM feedback_replies WHERE id = ?");
    $stmt->bind_param("i", $reply_id);

    if ($stmt->execute()) {
        echo "<script>alert('Reply deleted successfully!'); window.location.href='feedback_staff.php';</script>";
    } else {
        echo "<script>alert('Error deleting reply.');</script>";
    }

    $stmt->close();
}

// Fetch all feedback
$feedback_query = "SELECT * FROM feedback ORDER BY created_at DESC";
$feedback_result = $conn->query($feedback_query);

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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Feedback</title>
    <link rel="stylesheet" href="st1.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">

        <div class="sidebar">
            <h2>Staff Dashboard</h2>
            <button class="sidebar-btn" onclick="document.location='Staff_dashboard.php'">Home</button>
            <button class="sidebar-btn" onclick="document.location='feedback_staff.php'">Feedbacks</button>
            <button class="sidebar-btn" onclick="document.location='Personal_Style_staff.php'">Personal Style Recommendations</button>
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

            <h2>Customer Feedback</h2>
            <?php if ($feedback_result->num_rows > 0): ?>
                <?php

                $feedback_result->data_seek(0);
                while ($feedback = $feedback_result->fetch_assoc()): ?>
                    <div class="feedback-item">
                        <p><strong>Feedback:</strong> <?= htmlspecialchars($feedback['feedback']) ?></p>
                        <p><small>Submitted by: <?= $feedback['email'] ?> on <?= $feedback['created_at'] ?></small></p>

                        <form method="POST" action="">
                            <input type="hidden" name="feedback_id" value="<?= $feedback['id'] ?>">
                            <textarea name="reply" placeholder="Enter your reply" required></textarea>
                            <button type="submit" name="submit_reply">Submit Reply</button>
                        </form>

                        <!-- Display Replies -->
                        <?php if (!empty($replies[$feedback['id']])): ?>
                            <div class="replies">
                                <h3>Replies:</h3>
                                <?php foreach ($replies[$feedback['id']] as $reply): ?>
                                    <div class="reply-item">
                                        <p><?= htmlspecialchars($reply['reply']) ?></p>
                                        <p><small>Replied by: <?= $reply['replied_by'] ?> on <?= $reply['replied_at'] ?></small></p>
                                        
                                        <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this reply?');">
                                            <input type="hidden" name="reply_id" value="<?= $reply['id'] ?>">
                                            <button type="submit" name="delete_reply" class="delete-reply-btn">Delete</button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No feedback submitted yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="JavaScript.js"></script>
</body>
</html>
