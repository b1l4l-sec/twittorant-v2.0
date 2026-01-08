<?php
// Database connection settings (localhost)
$host = "localhost";         // XAMPP default
$user = "root";              // Default XAMPP user
$pass = "";                  // Empty password by default
$db   = "valotwitt_db";      // Use your DB name

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Optional: Set charset
$conn->set_charset("utf8mb4");
?>
