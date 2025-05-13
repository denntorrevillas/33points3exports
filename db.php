<?php
// Database connection variables
$host = "localhost";       // Database host (default for local server)
$user = "root";            // Database username (default for XAMPP/WAMP/LAMP)
$password = "";            // Database password (default for XAMPP/WAMP/LAMP is empty)
$database = "33points3exports"; // Name of your database

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Uncomment the below line to confirm connection during testing
// echo "Database connected successfully!";
?>
