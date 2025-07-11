<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Inventory Management System</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        
        <nav>
            <a href="inventory.php">View Inventory</a>
            <a href="search.php">Search</a>
            <a href="update.php">Update</a>
            <a href="delete.php">Delete</a>
            <a href="logout.php">Logout</a>
        </nav>
        
         
    </div>
</body>
</html>