<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not, redirect to login
    header('Location: login.php');
    exit;
}

// User is logged in, show the main content
require_once 'config.php';

echo "<h1>CP476 Project - Web Server Working!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Document Root: " . $_SERVER["DOCUMENT_ROOT"] . "</p>";
?>
