<?php
session_start(); 

$conn = new mysqli("localhost", "root", "", "ranhuyasystemdb");

if (!isset($_SESSION['email']) || !isset($_SESSION['name']) || !isset($_SESSION['gender'])) {
    echo "Please log in first.";
    exit;
}

$email = $_SESSION['email'];
$client_name = $_SESSION['name']; 
$gender = $_SESSION['gender']; 

// Predefined styles based on gender and face shape
$style_suggestions = [
    "male" => [
        "round" => ["Short Buzz Cut", "Pompadour"],
        "oval" => ["Crew Cut", "Faux Hawk"],
        "square" => ["Undercut", "Slicked Back"],
        "heart" => ["Spiky Hair", "Textured Crop"],
        "diamond" => ["Buzz Cut", "Quiff"]
    ],
    "female" => [
        "round" => ["Layered Bob", "Side-Swept Bangs"],
        "oval" => ["Sleek Straight", "Loose Waves"],
        "square" => ["Soft Curls", "Shaggy Layers"],
        "heart" => ["Long Waves", "Chin-Length Bob"],
        "diamond" => ["Textured Pixie", "Curly Bob"]
    ]
];

// Predefined makeup suggestions based on gender and face shape
$makeup_suggestions = [
    "male" => [
        "round" => ["Matte Finish", "Natural Look"],
        "oval" => ["Bold Brows", "Subtle Contouring"],
        "square" => ["Defined Jawline", "Natural Skin Tone"],
        "heart" => ["Light Foundation", "Soft Eyes"],
        "diamond" => ["Full Brows", "Natural Lips"]
    ],
    "female" => [
        "round" => ["Dewy Glow", "Soft Pink Lips"],
        "oval" => ["Smoky Eyes", "Bold Red Lips"],
        "square" => ["Winged Liner", "Peachy Blush"],
        "heart" => ["Natural Look", "Light Pink Lips"],
        "diamond" => ["Cat Eyes", "Nude Lipstick"]
    ]
];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['face_shape']) && isset($_FILES['photo'])) {
    $face_shape = $_POST['face_shape'];
    $target_dir = "StyleRecommendation/";
    $customer_note = $_POST['customer_note'];
    $target_file = $target_dir . basename($_FILES["photo"]["name"]);
    
    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        // Pick suggestions based on selected gender and face shape
        if (isset($style_suggestions[$gender][$face_shape]) && isset($makeup_suggestions[$gender][$face_shape])) {
            $style_suggestion = $style_suggestions[$gender][$face_shape][array_rand($style_suggestions[$gender][$face_shape])];
            $makeup_suggestion = $makeup_suggestions[$gender][$face_shape][array_rand($makeup_suggestions[$gender][$face_shape])];

            $stmt = $conn->prepare("INSERT INTO stylerecommendation (client_name, email, style_suggestion, makeup_suggestion, image_path, customer_note) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $client_name, $email, $style_suggestion, $makeup_suggestion, $target_file, $customer_note);
            $stmt->execute();
            $stmt->close();

            echo "<script type='text/javascript'>
                alert('Your style recommendation has been submitted. The staff will reply shortly.');
            </script>";
        } else {
            echo "<script type='text/javascript'>
                alert('Invalid face shape selected.');
            </script>";
        }
    } else {
        echo "<script type='text/javascript'>
            alert('Error uploading file.');
        </script>";
    }
}

// Handle deletion of recommendations
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    $stmt = $conn->prepare("DELETE FROM stylerecommendation WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['customer_note']) && isset($_POST['style_id'])) {
    $customer_note = $_POST['customer_note']; 
    $style_id = $_POST['style_id']; 

    $stmt = $conn->prepare("UPDATE stylerecommendation SET customer_note = ? WHERE id = ? AND email = ?");
    $stmt->bind_param("sis", $customer_note, $style_id, $email);
    $stmt->execute();
    $stmt->close();

    echo "<script type='text/javascript'>
        alert('Your note has been updated.');
    </script>";
}

// Fetch recommendations
$result = $conn->query("SELECT * FROM stylerecommendation WHERE email='$email'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="s11.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body>  

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
                    <a href="index.php" style="text-decoration: none; color: inherit;">
                        <i class="ri-logout-box-line"></i> <span>Log Out</span>
                    </a>
                </div>
            </div>

            <div class="style2_container">
            <h2 class="style2">Upload Photo for Style Suggestion</h2>
            </div>
            <form action="" method="post" enctype="multipart/form-data" class="Style_Recommendation">
                <label>Name:</label>
                <input type="text" name="client_name" value="<?php echo htmlspecialchars($client_name ?? ''); ?>" readonly><br>
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" readonly><br>
                <label>Select Your Face Shape:</label>
                <select class="style" name="face_shape">
                    <option value="round">Round</option>
                    <option value="oval">Oval</option>
                    <option value="square">Square</option>
                    <option value="heart">Heart</option>
                    <option value="diamond">Diamond</option>
                </select><br>
                <input type="file" name="photo" required><br>
                <label>Add Note:</label>
                <textarea class="textarea1" name="customer_note"></textarea><br>
                <input type="submit" value="Get Recommendation">
            </form>

            <div class="style2_container">
            <h2 class="style2">Suggested Styles and Makeup</h2>
            </div>
            <?php while ($row = $result->fetch_assoc()): ?>
               <div class="card_style">
                    <p class="style1"><b><?php echo htmlspecialchars($row['client_name']); ?></b></p>
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>"><br>
                    <p class="style1"><b>Suggested Style:</b> <?php echo htmlspecialchars($row['style_suggestion']); ?></p>
                    <p class="style1"><b>Suggested Makeup:</b> <?php echo htmlspecialchars($row['makeup_suggestion']); ?></p>

                    <form action="" method="post">
    <textarea class="textarea1" name="customer_note" placeholder="Update your note"><?php echo htmlspecialchars($row['customer_note'] ?? ''); ?></textarea><br>
    <input type="hidden" name="style_id" value="<?php echo $row['id']; ?>">
    <input type="submit" value="Update Note" class="edit_service_btn">
</form>



                    <p class="style1"><b>Staff Reply:</b> <?php echo !empty($row['staff_reply']) ? htmlspecialchars($row['staff_reply']) : "Pending"; ?></p>
                    <form action="" method="get" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this recommendation?')">
                            <i class="ri-delete-bin-5-line">Delete</i>
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="JavaScript.js"></script>
</body>
</html>
