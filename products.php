<?php
/**
 * Products Management Page
 * Add, Edit, Delete Products
 */

require_once 'config/database.php';
$user = requireLogin();

$conn = getDBConnection();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // ADD NEW PRODUCT
    if ($action === 'add') {
        $name = trim($_POST['name']);
        $sku = trim($_POST['sku']);
        $barcode = trim($_POST['barcode']);
        $description = trim($_POST['description']);
        $category = trim($_POST['category']);
        $quantity = (int)$_POST['quantity'];
        $min_stock = (int)$_POST['min_stock'];
        $price = (float)$_POST['price'];
        $cost = (float)$_POST['cost'];
        
        $stmt = $conn->prepare("
            INSERT INTO products (name, sku, barcode, description, category, quantity, min_stock, price, cost)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssiidi", $name, $sku, $barcode, $description, $category, $quantity, $min_stock, $price, $cost);
        
        if ($stmt->execute()) {
            $message = "Product added successfully!";
            $messageType = "success";
        } else {
            $message = "Error: " . $stmt->error;
            $messageType = "error";
        }
        $stmt->close();
    }
    
    // EDIT PRODUCT
    if ($action === 'edit') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $sku = trim($_POST['sku']);
        $barcode = trim($_POST['barcode']);
        $description = trim($_POST['description']);
        $category = trim($_POST['category']);
        $quantity = (int)$_POST['quantity'];
        $min_stock = (int)$_POST['min_stock'];
        $price = (float)$_POST['price'];
        $cost = (float)$_POST['cost'];
        
        $stmt = $conn->prepare("
            UPDATE products 
            SET name=?, sku=?, barcode=?, description=?, category=?, quantity=?, min_stock=?, price=?, cost=?
            WHERE id=?
        ");
        $stmt->bind_param("sssssiidii", $name, $sku, $barcode, $description, $category, $quantity, $min_stock, $price, $cost, $id);
        
        if ($stmt->execute()) {
            $message = "Product updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error: " . $stmt->error;
            $messageType = "error";
        }
        $stmt->close();
    }
    
    // DELETE PRODUCT
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "Product deleted successfully!";
            $messageType = "success";
        } else {
            $message = "Error: " . $stmt->error;
            $messageType = "error";
        }
        $stmt->close();
    }
}

// Get all products
$result = $conn->query("
    SELECT * FROM products 
    ORDER BY created_at DESC
");
$products = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Inventory System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f5f5f5; 
            padding: 20px; 
        }
        .container { max-width: 1400px; margin: 0 auto; }
        
        .header { 
            background: #2c3e50; 
            color: white; 
            padding: 20px; 
            border-radius: 8px; 
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 28px; }
        .nav-links a { 
            color: white; 
            text-decoration: none; 
            margin-left: 20px;
            padding: 8px 16px;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
        }
        .nav-links a:hover { background: rgba(255,255,255,0.2); }
        
        .alert { 
            padding: 15px; 
            border-radius: 4px; 
            margin-bottom: 20px; 
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .action-bar { 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn { 
            padding: 10px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; }
        .btn-success { background: #27ae60; color: white; }
        .btn-success:hover { background: #229954; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-warning:hover { background: #e67e22; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-danger:hover { background: #c0392b; }
        .btn-small { padding: 6px 12px; font-size: 12px; }
        
        .products-table { 
            background: white; 
            border-radius: 8px; 
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th { 
            background: #2c3e50; 
            color: white; 
            padding: 15px; 
            text-align: left;
            font-weight: 600;
        }
        td { 
            padding: 12px 15px; 
            border-bottom: 1px solid #ecf0f1; 
        }
        tr:hover { background: #f8f9fa; }
        
        .badge { 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        .badge-low { background: #fee; color: #e74c3c; }
        .badge-ok { background: #e8f8f5; color: #27ae60; }
        
        .modal { 
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(0,0,0,0.5); 
            z-index: 1000;
            overflow-y: auto;
        }
        .modal.show { display: block; }
        
        .modal-content { 
            background: white; 
            max-width: 700px; 
            margin: 50px auto; 
            border-radius: 8px; 
            padding: 30px;
            position: relative;
        }
        
        .modal-header { 
            border-bottom: 2px solid #ecf0f1; 
            padding-bottom: 15px; 
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h2 { color: #2c3e50; }
        
        .close-btn { 
            background: none; 
            border: none; 
            font-size: 28px; 
            cursor: pointer; 
            color: #999;
            line-height: 1;
        }
        .close-btn:hover { color: #333; }
        
        .form-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 15px; 
        }
        .form-grid.full { grid-template-columns: 1fr; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: 600; 
            color: #555; 
        }
        .form-group input, 
        .form-group textarea, 
        .form-group select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            font-size: 14px;
        }
        .form-group textarea { resize: vertical; min-height: 80px; }
        
        .search-box { 
            padding: 10px 15px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            width: 300px;
            font-size: 14px;
        }
        
        .empty-state { 
            text-align: center; 
            padding: 60px 20px; 
            color: #999; 
        }
        .empty-state h3 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Product Management</h1>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="scan.php">Scanner</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="action-bar">
            <input type="text" id="searchBox" class="search-box" placeholder="🔍 Search products..." onkeyup="searchProducts()">
            <button class="btn btn-primary" onclick="openAddModal()">
                ➕ Add New Product
            </button>
        </div>

        <div class="products-table">
            <?php if (count($products) > 0): ?>
            <table id="productsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Barcode</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Min Stock</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><strong><?php echo escape($product['name']); ?></strong></td>
                        <td><?php echo escape($product['sku']); ?></td>
                        <td><?php echo escape($product['barcode']); ?></td>
                        <td><?php echo escape($product['category']); ?></td>
                        <td><strong><?php echo $product['quantity']; ?></strong></td>
                        <td><?php echo $product['min_stock']; ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td>
                            <?php if ($product['quantity'] <= $product['min_stock']): ?>
                                <span class="badge badge-low">LOW STOCK</span>
                            <?php else: ?>
                                <span class="badge badge-ok">OK</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-warning btn-small" onclick='editProduct(<?php echo json_encode($product); ?>)'>
                                ✏️ Edit
                            </button>
                            <button class="btn btn-danger btn-small" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')">
                                🗑️ Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <h3>No products found</h3>
                <p>Click "Add New Product" to get started</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Product</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="productId">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" id="productName" required>
                    </div>
                    
                    <div class="form-group">
                        <label>SKU *</label>
                        <input type="text" name="sku" id="productSku" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Barcode/QR Code *</label>
                        <input type="text" name="barcode" id="productBarcode" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" id="productCategory">
                            <option value="Electronics">Electronics</option>
                            <option value="Accessories">Accessories</option>
                            <option value="Storage">Storage</option>
                            <option value="Cables">Cables</option>
                            <option value="Audio">Audio</option>
                            <option value="Office">Office</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" id="productQuantity" value="0" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Minimum Stock Level *</label>
                        <input type="number" name="min_stock" id="productMinStock" value="5" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Price ($)</label>
                        <input type="number" name="price" id="productPrice" step="0.01" value="0.00" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label>Cost ($)</label>
                        <input type="number" name="cost" id="productCost" step="0.01" value="0.00" min="0">
                    </div>
                </div>
                
                <div class="form-grid full">
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="productDescription"></textarea>
                    </div>
                </div>
                
                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submitBtn">💾 Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Form -->
    <form id="deleteForm" method="POST" action="" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <script>
        // Open Add Product Modal
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Product';
            document.getElementById('formAction').value = 'add';
            document.getElementById('submitBtn').textContent = '💾 Save Product';
            
            // Reset form
            document.getElementById('productId').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productSku').value = '';
            document.getElementById('productBarcode').value = '';
            document.getElementById('productCategory').value = 'Electronics';
            document.getElementById('productQuantity').value = '0';
            document.getElementById('productMinStock').value = '5';
            document.getElementById('productPrice').value = '0.00';
            document.getElementById('productCost').value = '0.00';
            document.getElementById('productDescription').value = '';
            
            document.getElementById('productModal').classList.add('show');
        }

        // Edit Product
        function editProduct(product) {
            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('submitBtn').textContent = '💾 Update Product';
            
            // Fill form with product data
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productSku').value = product.sku;
            document.getElementById('productBarcode').value = product.barcode;
            document.getElementById('productCategory').value = product.category;
            document.getElementById('productQuantity').value = product.quantity;
            document.getElementById('productMinStock').value = product.min_stock;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productCost').value = product.cost;
            document.getElementById('productDescription').value = product.description;
            
            document.getElementById('productModal').classList.add('show');
        }

        // Close Modal
        function closeModal() {
            document.getElementById('productModal').classList.remove('show');
        }

        // Delete Product
        function deleteProduct(id, name) {
            if (confirm('Are you sure you want to delete "' + name + '"?\n\nThis action cannot be undone.')) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        // Search Products
        function searchProducts() {
            const search = document.getElementById('searchBox').value.toLowerCase();
            const table = document.getElementById('productsTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const text = row.textContent.toLowerCase();
                
                if (text.includes(search)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>