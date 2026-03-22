<?php
session_start();
$conn = new mysqli("localhost", "root", "", "pet_care");

echo '<link rel="stylesheet" href="style.css">';
echo '<div class="navbar"><a href="home.php">Home</a><a href="register.html">Register</a><a href="login.html">Login</a><a href="catalogue.html">Catalogue</a><a href="feedback.html">Feedback</a></div>';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['user_email'];
    
    // Check if email is registered
    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($check->num_rows == 0) {
        echo "<script>alert('Email not found! Please register first.'); window.location='feedback.html';</script>";
        exit();
    }

    // Calculate Average of 7 traits
    $total = 0;
    $count = 0;
    for($i=0; $i<7; $i++) {
        if(isset($_POST["rate$i"])) {
            $total += $_POST["rate$i"];
            $count++;
        }
    }

    $avg = ($count > 0) ? $total / $count : 0;
    $avg = round($avg, 2); // Float with 2 decimal points

