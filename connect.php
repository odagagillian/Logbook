<?php
// Database connection settings
$servername = "localhost"; // Localhost server
$username   = "root";      // Default username for XAMPP
$password   = "";          // Leave empty unless you set a password in phpMyAdmin
$dbname     = "jamii_system"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("<p>❌ Connection failed: " . $conn->connect_error . "</p>");
}
?>
