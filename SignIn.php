<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Signup Modal</title>
    <link
        href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="SignInSignUp.css">
</head>
<body>

    <div class="login-container">
        <div class="login-form">
            <h1>Sign In</h1>

            <?php
            session_start();
            $conn = mysqli_connect("localhost", "root", "", "ranhuyasystemdb");

            if (!$conn) {
                die("ERROR: Could not connect. " . mysqli_connect_error());
            }

            $error_message = ""; 
            $email = ""; 
            $password = ""; 

            if (isset($_POST['SignIn'])) {
                $email = mysqli_real_escape_string($conn, $_POST['email']);
                $password = mysqli_real_escape_string($conn, $_POST['password']);

                // Email validation: Must contain "@", ".", and end with ".com"
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/@.*\..*com$/", $email)) {
                    $error_message = "Invalid email format! Email must contain @, ., and end with .com";
                } else {
                    
                    $sql = "SELECT * FROM user WHERE email='$email'";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();

                        if ($row['password'] === $password) {
                            
                            $_SESSION['email'] = $row['email'];
                            $_SESSION['role'] = $row['role'];
                            $_SESSION['name'] = $row['name'];
                            $_SESSION['gender'] = $row['gender'];

                            // Redirect based on role
                            if ($row['role'] === 'admin') {
                                header("Location: Admin_Dashboard.php");
                            } elseif ($row['role'] === 'staff') {
                                header("Location: Staff_Dashboard.php");
                            } elseif ($row['role'] === 'customer') {
                                header("Location: Customer_Dashbord.php");
                            } else {
                                $error_message = "Unknown User Role";
                            }
                            exit();
                        } else {
                            $error_message = "Incorrect Password";
                        }
                    } else {
                        $error_message = "Incorrect Email or Password";
                    }
                }
            }
            ?>

            <form id="sign_up_form" action="" method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" placeholder="Enter your Email" name="email" value="<?php echo $email; ?>" required>
                    <i class="ri-mail-line"></i>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" placeholder="Password" id="password" name="password" value="<?php echo $password; ?>" required>
                    <i class="ri-lock-line"></i>
                    <i class="ri-eye-line" data-target="password" onclick="togglePasswordVisibility(this)"></i>
                </div>


                <p id="error-message" style="color: red;"><?php echo $error_message; ?></p>

                <button type="submit" class="sign-in-button" name="SignIn">Sign In</button>
            </form>
        </div>
    </div>

    <div class="welcome-section">
        <h2>Welcome to Login</h2>
        <p>Don't have an account?</p>
        <a href="SignUp.php" class="button1">Sign Up</a>
    </div>

    <script src="JavaScript.js"></script>
</body>
</html>
