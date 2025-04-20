<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link
    href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css"
    rel="stylesheet"
/>
    <link rel="stylesheet" href="index2.css">
</head>
<body>
    <nav>
        <div class="navbar_logo">
            <a href="#"><img src="images/logo.png" alt="logo"></a>
        </div>
        <ul class="navbar">
            <li class="links"><a href="#home">Home</a></li>
            <li class="links"><a href="#aboutus">About Us</a></li>
            <li class="links"><a href="#services">Our Services</a></li>
            <li class="links"><a href="Reviews.php">Reviews</a></li>
            <li class="links"><a href="#team">Our Team</a></li>
            <li class="links"><a href="#Contact">Contact Us</a></li>
        </ul>
        <button class="joinbtn"  onclick="document.location='Customer_Dashbord.php'">Back To Dashboard</button>
    </nav>

    <header id="home" class="section_container header_container">

        <div class="bg_blur"></div> 
        <div class="header_content">
            <h4>Where Beauty Meets Perfection!</h4>
            <h1><span>TRANSFORM  </span>YOUR STYLE</h1>
            <p>
                Transform your look with expert salon hair services tailored to perfection for every style and occasion.
            </p>
            <a href="#Contact">
                <button class="contactbtn" onclick="document.location='PersonalStyle.php'">Personal Style Recommendations</button>
              </a>
              

        </div>
        <div class="header_image">
            <img src="images/header.png" alt="header image" />
        </div>
    </header>

    <section id="aboutus" class="about">
        <div class="about_us">
            <img src="images/about4.webp" alt="Aboutus image" />
            <div class="about_text">
                <h1>About Us</h1>
                <h5>SALON <span class="about1">HAIR </span></h5>
                <p>Salon hair in Seeduwa offers premium beauty and wellness services, 
                    including exceptional bridal makeup and other treatments. 
                    The salon focuses on delivering high-quality services in a relaxing, luxurious environment, 
                    ensuring customer satisfaction through personalized attention and detail.</p>
                    
            </div>
        </div>
    </section>

    <section id="target-section" class="target">
        <h2 class="gallery_h2">Image Gallery</h2><br>
        <hr class="gallery_hr"><br>
    
        <div class="gallery1">
            <div id="gallery" class="gallery">
                <img class="gallerImg" src="images/gallery1.avif" alt="Image 1">
                <img class="gallerImg" src="images/gallery5.jpg" alt="Image 2">
                <img class="gallerImg" src="images/gallery6.jpg" alt="Image 3">
                <img class="gallerImg" src="images/gallery7.jpg" alt="Image 4">
            </div>
    
            <button onclick="scrollGallery(-1)" class="gallery_btn">&#10094;</button>
            <button onclick="scrollGallery(1)" class="gallery_btn1">&#10095;</button>
        </div>
    </section>
    


    <section id="team" class="team">
        <div class="center1">
            <h1>OUR TEAMS</h1>
        </div>

        <div class="team_content">

            <div class="box1">
                <img src="images/salon1.avif" alt="Team">
                <h3>Shugi Perera</h3>
                <h5>Founder</h5>
                <div class="iconTeam">
                    <a href="#"><i class="ri-facebook-circle-fill"></i></a>
                    <a href="#"></a><i class="ri-instagram-fill"></i></a>
                </div>
            </div>
    
            <div class="box1">
                <img src="images/Staff2.jpg" alt="Team">
                <h3>Marian Silva</h3>
                <h5>Staff</h5>
                <div class="iconTeam">
                    <i class="ri-facebook-circle-fill"></i>
                    <i class="ri-instagram-fill"></i>
                </div>
            </div>
    
            <div class="box1">
                <img src="images/staff3.jpg" alt="Team">
                <h3>Samantha Perera</h3>
                <h5>Staff</h5>
                <div class="iconTeam">
                    <i class="ri-facebook-circle-fill"></i>
                    <i class="ri-instagram-fill"></i>
                </div>
            </div>
    
            <div class="box1">
                <img src="images/staff4.jpg" alt="Team">
                <h3>Iresha Janani</h3>
                <h5>Staff</h5>
                <div class="iconTeam">
                    <i class="ri-facebook-circle-fill"></i>
                    <i class="ri-instagram-fill"></i>
                </div>
            </div>

        </div>
    </section>

    <section class="services" id="services">
        <h2 class="heading">Our <span>Services</span></h2>

        <div class="services_content">
            <div class="row">
                <img src="images/bridaldrressing.webp">
                <h4>Bridal Dressing Services</h4>

            </div>

            <div class="row">
                <img src="images/img1.jpg">
                <h4>Facial & Skincare Services</h4>

            </div>

            <div class="row">
                <img src="images/img2.jpg">
                <h4>Body Treatments</h4>

            </div>

            <div class="row">
                <img src="images/istockphoto-1498202842-612x612.jpg">
                <h4>Hair Services</h4>

            </div>

            <div class="row">
                <img src="images/giorgio-trovato-gb6gtiTZKB8-unsplash.jpg">
                <h4>Nail Services</h4>

            </div>

            <button class="contactbtn"  onclick="document.location='service_home_customer.php'">More Services</button>

        </div>
    </section>

<section class="section_message">
    <div class="SendMSg_container">
        <form id="sendUsmsg_form" action="" method="POST" onsubmit="sendEmail(event)">
            <h2 class="h2_message">GET IN TOUCH</h2>
            <label>Full name </label> <br>
            <input type="text" name="fullname" placeholder="Enter the full name" > <br>
    
            <label>E-mail </label> <br>
            <input type="text" name="email" placeholder="Enter the E-mail"> <br>
    
            <label>Phone number</label> <br>
            <input type="tel" name="phonenumber" placeholder="Enter the Phone Number"> <br>
    
            <label>Message</label> <br>
            <textarea cols="100" rows="8" name="message" placeholder="Enter the message" value="message" required></textarea><br>
    
            <button type="submit" class="submitbtn1">Submit</button><br>
    
            </form>
    </div>

</section>

    <footer id="Contact" class="section_container footer_container">
        <div class="footer_col">
            <div class="footer_logo"><img src="images/logo.png" alt="logo"></div>
            <p>
                Salon hair in Seeduwa offers premium beauty and wellness services, 
                including exceptional bridal makeup and other treatments, 
                focusing on customer satisfaction and personalized attention.
            </p>

            <div class="footer-socials">
                <a href="#"><i class="ri-facebook-circle-fill"></i></a>
                <a href="#"><i class="ri-instagram-fill"></i></a>
                <a href="#"><i class="ri-phone-fill">071122769</i></a>
            </div>
        </div>
    
        <div class="footer_col">
            <h4>Company</h4>
            <a href="#aboutus">About Us</a>
            <a href="#services">Services</a>
            <a href="#">Feedback</a>
        </div>
    
        <div class="footer_col">
            <h4>About Us</h4>
            <a href="#aboutus">Our Details</a>
            <a href="#team">Our Team</a>
        </div>
    
        <div class="footer_col">
            <h4>Work Hours</h4>
            <p>Weekdays: 8:00 AM - 8:00 PM</p>
            <p>Weekend: 8:00 AM - 6:00 PM</p>
        </div>

        <div class="footer_bar">
            © 2024 Salon Hair. All Rights Reserved.
        </div>
    </footer>

    <script src="JavaScript.js"></script>
</body>
</html>
