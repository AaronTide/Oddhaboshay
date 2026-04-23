<?php
// ============================================================
//  Database Configuration
//  Edit DB_USER and DB_PASS to match your MySQL setup
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change this
define('DB_PASS', '');           // Change this
define('DB_NAME', 'oddhaboshay');

// Create connection using MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die('<h2 style="color:red;font-family:sans-serif;padding:20px;">
         Database connection failed: ' . $conn->connect_error . '<br>
         Please check config/db.php settings.</h2>');
}

// Set charset for proper Unicode support
$conn->set_charset('utf8mb4');
?>
