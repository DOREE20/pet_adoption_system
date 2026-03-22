<?php
// Start session to check if user is logged in
session_start();

// Check if user name exists in the "memory" of the browser
if (isset($_SESSION['user_name'])) {
    $user_display = "Welcome back, " . $_SESSION['user_name'] . "!";
} else {
    $user_display = "Welcome to our Pet Care and Adoption System Online Portal";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Home Portal</title>
</head>
<body>
    <div class="navbar">
        <a href="home.php">Home</a>
        <a href="register.html">Register</a>
        <a href="login.html">Login</a>
        <a href="catalogue.php">Catalogue</a>
        <a href="feedback.html">Feedback</a>
    </div>

    <div class="hero">
        <h1><?php echo $user_display; ?></h1>
        
        <div class="house-box">
            <p>
                Adopting a pet is a truly noble deed. Through our site, you can connect 
                with your future best friend and give them a forever home. 
                Every adoption saves a life.
            </p>
        </div>

        <div class="image-placeholder">
            [INSERT YOUR PET IMAGE HERE]
        </div>
    </div>
</body>
</html>

