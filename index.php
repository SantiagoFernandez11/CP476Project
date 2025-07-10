<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // User not logged in, redirect to login
    header('Location: login.php');
    exit;
}

// User is logged in, show the main content
require_once 'config.php';

echo "<h1>CP476 Project - Web Server Working!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Document Root: " . $_SERVER["DOCUMENT_ROOT"] . "</p>";
?>
