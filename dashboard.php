<?php
// Initialize session and database connection
session_start();
require_once 'config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle AJAX request for inventory data
if (isset($_GET['action']) && $_GET['action'] === 'get_inventory') {
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->query('SELECT * FROM InventoryTable ORDER BY ProductID ASC');
        $inventory = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $inventory]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Handle DELETE operation with prepared statement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    header('Content-Type: application/json');
    try {
        $productId = $_POST['product_id'];
        $supplierName = $_POST['supplier_name'];
        $oldPrice = $_POST['old_price'];
        $oldQuantity = $_POST['old_quantity'];
        
        // Delete using composite key to identify exact record
        $stmt = $pdo->prepare('DELETE FROM InventoryTable WHERE ProductID = ? AND SupplierName = ? AND Price = ? AND Quantity = ?');
        $stmt->execute([$productId, $supplierName, $oldPrice, $oldQuantity]);
        
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Handle UPDATE operation with prepared statement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    header('Content-Type: application/json');
    try {
        $productId = $_POST['product_id'];
        $supplierName = $_POST['supplier_name'];
        $oldPrice = $_POST['old_price'];
        $oldQuantity = $_POST['old_quantity'];
        $newQuantity = $_POST['quantity'];
        $newPrice = $_POST['price'];
        $newStatus = $_POST['status'];
        
        // Update using composite key to identify exact record
        $stmt = $pdo->prepare('UPDATE InventoryTable SET Quantity = ?, Price = ?, Status = ? WHERE ProductID = ? AND SupplierName = ? AND Price = ? AND Quantity = ?');
        $stmt->execute([$newQuantity, $newPrice, $newStatus, $productId, $supplierName, $oldPrice, $oldQuantity]);
        
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Handle ADD operation with prepared statement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    header('Content-Type: application/json');
    try {
        $productId = $_POST['product_id'];
        $productName = $_POST['product_name'];
        $quantity = $_POST['quantity'];
        $price = $_POST['price'];
        $status = $_POST['status'];
        $supplierName = $_POST['supplier_name'];
        
        // Insert new product record
        $stmt = $pdo->prepare('INSERT INTO InventoryTable (ProductID, ProductName, Quantity, Price, Status, SupplierName) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$productId, $productName, $quantity, $price, $status, $supplierName]);
        
        echo json_encode(['success' => true, 'message' => 'Product added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Load initial inventory data for page display
$inventory = [];
$error = '';
try {
    $stmt = $pdo->query('SELECT * FROM InventoryTable ORDER BY ProductID ASC');
    $inventory = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching inventory: ' . $e->getMessage();
}

// Get supplier list for form options
$suppliers = [];
try {
    $stmt = $pdo->query('SELECT DISTINCT SupplierName FROM InventoryTable ORDER BY SupplierName');
    $suppliers = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching suppliers: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <style>
        /* Basic styling for clean interface */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .actions button {
            padding: 4px 8px;
            margin-right: 5px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .edit-btn { background: #ffc107; }
        .delete-btn { background: #dc3545; color: white; }
        .save-btn { background: #28a745; color: white; }
        .cancel-btn { background: #6c757d; color: white; }
        
        .error { color: red; margin: 10px 0; }
        .success { color: green; margin: 10px 0; }
        
        input, select {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .add-form {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }
        
        .add-form.active {
            display: block;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group select {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Page header with user info and logout -->
        <div class="header">
            <div>
                <h1>Inventory Management</h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            </div>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
        
        <!-- Control buttons -->
        <div>
            <button class="btn btn-success" onclick="toggleAddForm()">Add Product</button>
            <button class="btn btn-primary" onclick="refreshInventory()">Refresh</button>
        </div>
        
        <!-- Add product form (hidden by default) -->
        <div id="add-form" class="add-form">
            <h3>Add New Product</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Product ID:</label>
                    <input type="number" id="new-product-id" required>
                </div>
                <div class="form-group">
                    <label>Product Name:</label>
                    <input type="text" id="new-product-name" required>
                </div>
                <div class="form-group">
                    <label>Quantity:</label>
                    <input type="number" id="new-quantity" min="0" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Price:</label>
                    <input type="number" id="new-price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label>Status:</label>
                    <select id="new-status" required>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Supplier Name:</label>
                    <input type="text" id="new-supplier" required>
                </div>
            </div>
            <button class="btn btn-success" onclick="addProduct()">Save Product</button>
            <button class="btn btn-secondary" onclick="toggleAddForm()">Cancel</button>
        </div>
        
        <!-- Message area for user feedback -->
        <div id="message-area"></div>
        
        <!-- Display any PHP errors -->
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Main inventory table -->
        <table id="inventory-table">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Supplier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="inventory-tbody">
                <!-- Display inventory data-->
                <?php if ($inventory): ?>
                    <?php foreach ($inventory as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ProductID']); ?></td>
                        <td><?php echo htmlspecialchars($row['ProductName']); ?></td>
                        <td class="quantity"><?php echo htmlspecialchars($row['Quantity']); ?></td>
                        <td class="price"><?php echo number_format($row['Price'], 2); ?></td>
                        <td class="status"><?php echo htmlspecialchars($row['Status']); ?></td>
                        <td class="supplier"><?php echo htmlspecialchars($row['SupplierName']); ?></td>
                        <td class="actions">
                            <button class="edit-btn" onclick="editRow(this)">Edit</button>
                            <button class="delete-btn" onclick="deleteRow(this)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No inventory records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Display messages to usr
        function showMessage(message, type = 'success') {
            const messageArea = document.getElementById('message-area');
            messageArea.innerHTML = `<div class="${type}">${message}</div>`;
            setTimeout(() => messageArea.innerHTML = '', 3000);
        }
        
        // Toggle add product form visibility
        function toggleAddForm() {
            const form = document.getElementById('add-form');
            form.classList.toggle('active');
            if (form.classList.contains('active')) {
                document.getElementById('new-product-id').focus();
            }
        }
        
        // Refresh inventory data via AJAX
        function refreshInventory() {
            fetch('?action=get_inventory')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateInventoryTable(data.data);
                        showMessage('Inventory refreshed');
                    } else {
                        showMessage('Error refreshing inventory: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    showMessage('Network error: ' + error.message, 'error');
                });
        }
        
        // Update table with new inventory data
        function updateInventoryTable(inventory) {
            const tbody = document.getElementById('inventory-tbody');
            
            if (inventory.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No inventory records found.</td></tr>';
                return;
            }
            
            tbody.innerHTML = inventory.map(row => `
                <tr>
                    <td>${row.ProductID}</td>
                    <td>${row.ProductName}</td>
                    <td class="quantity">${row.Quantity}</td>
                    <td class="price">${parseFloat(row.Price).toFixed(2)}</td>
                    <td class="status">${row.Status}</td>
                    <td class="supplier">${row.SupplierName}</td>
                    <td class="actions">
                        <button class="edit-btn" onclick="editRow(this)">Edit</button>
                        <button class="delete-btn" onclick="deleteRow(this)">Delete</button>
                    </td>
                </tr>
            `).join('');
        }
        
        // Make row editable
        function editRow(button) {
            const row = button.closest('tr');
            const cells = row.children;
            
            const quantity = cells[2].textContent;
            const price = parseFloat(cells[3].textContent);
            const status = cells[4].textContent;
            
            // Replace cells with input fields
            cells[2].innerHTML = `<input type="number" value="${quantity}" min="0">`;
            cells[3].innerHTML = `<input type="number" value="${price}" step="0.01" min="0">`;
            cells[4].innerHTML = `<select>
                <option value="A" ${status === 'A' ? 'selected' : ''}>A</option>
                <option value="B" ${status === 'B' ? 'selected' : ''}>B</option>
                <option value="C" ${status === 'C' ? 'selected' : ''}>C</option>
            </select>`;
            
            // Change action buttons
            cells[6].innerHTML = `
                <button class="save-btn" onclick="saveRow(this)">Save</button>
                <button class="cancel-btn" onclick="cancelEdit(this)">Cancel</button>
            `;
        }
        
        // Save edited row data
        function saveRow(button) {
            const row = button.closest('tr');
            const cells = row.children;
            
            const productId = cells[0].textContent;
            const supplierName = cells[5].textContent;
            const oldQuantity = cells[2].querySelector('input').defaultValue;
            const oldPrice = cells[3].querySelector('input').defaultValue;
            
            const newQuantity = cells[2].querySelector('input').value;
            const newPrice = cells[3].querySelector('input').value;
            const newStatus = cells[4].querySelector('select').value;
            
            // Send update request
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('product_id', productId);
            formData.append('supplier_name', supplierName);
            formData.append('old_quantity', oldQuantity);
            formData.append('old_price', oldPrice);
            formData.append('quantity', newQuantity);
            formData.append('price', newPrice);
            formData.append('status', newStatus);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message);
                    refreshInventory();
                } else {
                    showMessage('Error updating product: ' + data.error, 'error');
                }
            })
            .catch(error => {
                showMessage('Network error: ' + error.message, 'error');
            });
        }
        
        // Cancel edit mode
        function cancelEdit(button) {
            refreshInventory();
        }
        
        // Delete product row
        function deleteRow(button) {
            if (!confirm('Are you sure you want to delete this product?')) {
                return;
            }
            
            const row = button.closest('tr');
            const cells = row.children;
            
            const productId = cells[0].textContent;
            const supplierName = cells[5].textContent;
            const quantity = cells[2].textContent;
            const price = cells[3].textContent;
            
            // Send delete request using composite key
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('product_id', productId);
            formData.append('supplier_name', supplierName);
            formData.append('old_quantity', quantity);
            formData.append('old_price', price);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message);
                    refreshInventory();
                } else {
                    showMessage('Error deleting product: ' + data.error, 'error');
                }
            })
            .catch(error => {
                showMessage('Network error: ' + error.message, 'error');
            });
        }
        
        // add new product
        function addProduct() {
            const productId = document.getElementById('new-product-id').value;
            const productName = document.getElementById('new-product-name').value;
            const quantity = document.getElementById('new-quantity').value;
            const price = document.getElementById('new-price').value;
            const status = document.getElementById('new-status').value;
            const supplierName = document.getElementById('new-supplier').value;
            
            // Validate form data
            if (!productId || !productName || !quantity || !price || !status || !supplierName) {
                showMessage('Please fill in all fields', 'error');
                return;
            }
            
            //Send add request
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', productId);
            formData.append('product_name', productName);
            formData.append('quantity', quantity);
            formData.append('price', price);
            formData.append('status', status);
            formData.append('supplier_name', supplierName);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message);
                    toggleAddForm();
                    // Clear form fields
                    document.getElementById('new-product-id').value = '';
                    document.getElementById('new-product-name').value = '';
                    document.getElementById('new-quantity').value = '';
                    document.getElementById('new-price').value = '';
                    document.getElementById('new-status').value = 'A';
                    document.getElementById('new-supplier').value = '';
                    refreshInventory();
                } else {
                    showMessage('Error adding product: ' + data.error, 'error');
                }
            })
            .catch(error => {
                showMessage('Network error: ' + error.message, 'error');
            });
        }
    </script>
</body>
</html>