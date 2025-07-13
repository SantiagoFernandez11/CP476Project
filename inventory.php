
<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$inventory = [];
$error = '';
try {
    $stmt = $pdo->query('SELECT * FROM InventoryTable');
    $inventory = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching inventory: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Inventory Table</h1>
        <a href="dashboard.php">&larr; Back to Dashboard</a>
        <?php if ($error): ?>
            <p style="color:red;"> <?php echo htmlspecialchars($error); ?> </p>
        <?php endif; ?>
        <?php if ($inventory): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Supplier Name</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ProductID']); ?></td>
                    <td><?php echo htmlspecialchars($row['ProductName']); ?></td>
                    <td><?php echo htmlspecialchars($row['Quantity']); ?></td>
                    <td><?php echo htmlspecialchars($row['Price']); ?></td>
                    <td><?php echo htmlspecialchars($row['Status']); ?></td>
                    <td><?php echo htmlspecialchars($row['SupplierName']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No inventory records found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
