<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "pet_grooming_system";

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
