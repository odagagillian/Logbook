<?php
// Database connection settings
$servername = "sql100.infinityfree.com"; // Localhost server
$username   = "if0_40524084";      // Default username for XAMPP
$password   = "momoayase";          // Leave empty unless you set a password in phpMyAdmin
$dbname     = "if0_40524084_jamii"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("<p>❌ Connection failed: " . $conn->connect_error . "</p>");
}
?>
