<?php
$conn = new mysqli("localhost", "root", "", "ranhuyasystemdb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_reply'])) {
        // Delete the reply
        $id = $_POST['recommendation_id'];
        $stmt = $conn->prepare("UPDATE StyleRecommendation SET staff_reply = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['submit_reply'])) {
        // Update the reply
        $id = $_POST['recommendation_id'];
        $reply = $_POST['staff_reply'];
        $stmt = $conn->prepare("UPDATE StyleRecommendation SET staff_reply = ? WHERE id = ?");
        $stmt->bind_param("si", $reply, $id);
        $stmt->execute();
        $stmt->close();
    }
}

$result = $conn->query("SELECT * FROM StyleRecommendation ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal style Reply Message</title>
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

            <h2 class="Style">Client Personal Style Requests</h2>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="style_container">
                    <p class="style1"><b><?php echo htmlspecialchars($row['client_name']); ?></b></p>
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" width="200" alt="Client Image"><br>
                    <p class="style1"><b>Suggested Style:</b> <?php echo htmlspecialchars($row['style_suggestion']); ?></p>
                    <p class="style1"><b>Suggested Makeup:</b> <?php echo htmlspecialchars($row['makeup_suggestion']); ?></p>
                    <p class="style1"><b>Customer Note:</b> <?php echo htmlspecialchars($row['customer_note'] ?? ''); ?></p>
                    <p class="style1"><b>Staff Reply:</b> 
                        <?php echo $row['staff_reply'] !== null ? htmlspecialchars($row['staff_reply']) : 'Pending'; ?>
                    </p>

                    <form action="" method="post" class="form_style">
                        <input class="input_style" type="hidden" name="recommendation_id" value="<?php echo $row['id']; ?>">
                        <textarea name="staff_reply"><?php echo htmlspecialchars($row['staff_reply'] ?? ''); ?></textarea></br>
                        <input class="input_style" type="submit" name="submit_reply" value="<?php echo $row['staff_reply'] !== null ? 'Edit Reply' : 'Reply'; ?>">
                    </form>
                    
                    <?php if (!empty($row['staff_reply'])): ?>
                        <form action="" method="post">
                            <input type="hidden" name="recommendation_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_reply" class="delete-btn" onclick="return confirm('Are you sure you want to delete this reply?');">
                                <i class="ri-delete-bin-5-line"></i> Delete Reply
                            </button>
                        </form>
                    <?php endif; ?>
                    <hr>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="JavaScript.js"></script>
</body>
</html>
