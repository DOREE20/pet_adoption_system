<?php
session_start();
$conn = new mysqli("localhost", "root", "", "pet_care");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pin = $_POST['pincode'];
    $addr = $_POST['flat'] . ", " . $_POST['building'] . ", " . $_POST['street'];

    $sql = "INSERT INTO users (name, email, password, address, pincode) 
            VALUES ('$name', '$email', '$pass', '$addr', '$pin')";

    if ($conn->query($sql) === TRUE) {
        header("Location: login.html");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
