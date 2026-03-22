<?php
session_start();
// Connect to XAMPP MySQL
$conn = new mysqli("localhost", "root", "", "pet_care");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Secure check against database
    $result = $conn->query("SELECT * FROM users WHERE email='$email' AND password='$pass'");

    if ($result->num_rows > 0) {
        // SUCCESS: Fetch user data
        $user = $result->fetch_assoc();
        $_SESSION['user_name'] = $user['name'];
        $_SESSION[

