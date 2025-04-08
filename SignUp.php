<?php
$conn = mysqli_connect("localhost", "root", "", "ranhuyasystemdb");

if ($conn === false) {
    die("ERROR: Could not connect." . mysqli_connect_error());
}


$email = $password = $name = $phoneNo = $gender = '';
$errorMessage = '';

if (isset($_POST['SignUp'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $name = trim($_POST['name']);
    $phoneNo = trim($_POST['phoneNo']);
    $gender = trim($_POST['gender']);
    $role = 'customer';


    if (empty($email) || empty($password) || empty($name) || empty($phoneNo) || empty($gender)) {
        $errorMessage = 'All fields are required!';
    } 

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/@.*\..*com$/", $email)) {
        $errorMessage = 'Invalid email format! Email must contain @, ., and end with .com';
    } 
    // Validate phone number format (Only digits, exactly 10 characters)
    elseif (!preg_match("/^[0-9]{10}$/", $phoneNo)) {
        $errorMessage = 'Invalid phone number! It must be 10 digits long.';
    } 
    // Check if email already exists
    else {
        $checkEmail = "SELECT * FROM user WHERE email='$email'";
        $result = $conn->query($checkEmail);

        if ($result->num_rows > 0) {
            $errorMessage = 'Email Already Exists!';
        } else {
            $insertQuery = "INSERT INTO user(email, password, name, phoneNo, gender, role)
                            VALUES ('$email', '$password', '$name', '$phoneNo', '$gender', '$role')";

            if ($conn->query($insertQuery) === TRUE) {
                echo "<script>
                        alert('Registration successful! ');
                        window.location.href='SignIn.php';
                      </script>";
            } else {
                $errorMessage = 'Error: ' . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Signup Modal</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="SignInSignUp.css">
</head>
<body>
    <div class="welcome-section">
        <h2>Welcome Back!</h2>
        <p>Already have an account?</p>
        <a href="SignIn.php" class="button1">Sign In</a>
    </div>
    <div class="login-container">
        <div class="signup-form">
            <h1>Sign Up</h1>
            <form id="sign_up_form" action="" method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" placeholder="Enter your name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    <i class="ri-user-3-line"></i>
                </div>

                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender">
                        <option value="male" <?php if($gender == 'male') echo 'selected'; ?>>Male</option>
                        <option value="female" <?php if($gender == 'female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" placeholder="Enter your email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    <i class="ri-mail-line"></i>
                </div>

                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" placeholder="Enter your Contact number" name="phoneNo" value="<?php echo htmlspecialchars($phoneNo); ?>" required>
                    <i class="ri-phone-fill"></i>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" placeholder="Password" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" required>
                    <i class="ri-lock-line"></i>
                    <i class="ri-eye-line" data-target="password" onclick="togglePasswordVisibility(this)"></i>
                </div>

                <button type="submit" class="signup-submit-button" name="SignUp">Sign Up</button>
            </form>

            <?php
            if ($errorMessage != '') {
                echo "<script>alert('$errorMessage');</script>";
            }
            ?>
        </div>
    </div>

    <script src="JavaScript.js"></script>
</body>
</html>
