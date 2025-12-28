<?php
/**
 * Barcode/QR Code Scanner Page - Version 2
 * With Manual Barcode Entry Option
 */

require_once 'config/database.php';
$user = requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Scanner v2 - Inventory System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f5f5f5; 
            padding: 20px; 
        }
        .container { max-width: 1200px; margin: 0 auto; }
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
        .header h1 { font-size: 24px; }
        .nav-links a { color: white; text-decoration: none; margin-left: 20px; }
        .nav-links a:hover { text-decoration: underline; }
        
        .card { 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card h2 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .manual-scan { 
            display: flex; 
            gap: 10px; 
            margin-bottom: 20px; 
        }
        
        .manual-scan input { 
            flex: 1; 
            padding: 12px 15px; 
            border: 2px solid #ddd; 
            border-radius: 5px;
            font-size: 16px;
        }
        
        .manual-scan input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .test-barcodes {
            background: #f8f9fa;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .test-barcodes code {
            background: white;
            padding: 2px 6px;
            border-radius: 3px;
            color: #e74c3c;
            font-weight: 600;
            font-size: 13px;
        }
        
        .result-box { 
            padding: 15px; 
            border-radius: 5px; 
            margin-top: 15px;
            display: none;
            border-left: 4px solid;
        }
        
        .result-box.show { display: block; }
        .result-box.success { 
            background: #d4edda; 
            border-color: #28a745;
            color: #155724;
        }
        .result-box.error { 
            background: #f8d7da; 
            border-color: #dc3545;
            color: #721c24;
        }
        .result-box.info { 
            background: #d1ecf1; 
            border-color: #17a2b8;
            color: #0c5460;
        }
        
        #reader { 
            width: 100%; 
            max-width: 600px; 
            margin: 0 auto 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .product-info { 
            background: white; 
            padding: 30px; 
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: none;
        }
        
        .product-info.show { display: block; }
        
        .product-header { 
            border-bottom: 2px solid #ecf0f1; 
            padding-bottom: 15px; 
            margin-bottom: 20px; 
        }
        
        .product-header h2 { color: #2c3e50; margin-bottom: 5px; }
        .product-detail { 
            display: flex; 
            justify-content: space-between; 
            padding: 12px 0; 
            border-bottom: 1px solid #ecf0f1; 
        }
        .product-detail label { 
            font-weight: 600; 
            color: #555; 
        }
        .product-detail span { 
            color: #2c3e50; 
        }
        
        .stock-quantity { 
            font-size: 32px; 
            font-weight: bold; 
            color: #27ae60; 
        }
        
        .stock-quantity.low { color: #e74c3c; }
        
        .warning-badge { 
            background: #e74c3c; 
            color: white; 
            padding: 6px 12px; 
            border-radius: 4px; 
            font-size: 12px;
            display: inline-block;
            margin-left: 10px;
        }
        
        .stock-actions { 
            display: flex; 
            gap: 15px; 
            margin-top: 30px; 
        }
        
        .stock-actions > div { flex: 1; }
        
        .input-group { margin-bottom: 15px; }
        .input-group label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: 600; 
            color: #555; 
        }
        .input-group input, .input-group textarea { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            font-size: 14px;
        }
        
        .btn { 
            padding: 12px 24px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-in { 
            background: #27ae60; 
            color: white; 
        }
        .btn-in:hover { background: #229954; }
        
        .btn-out { 
            background: #e74c3c; 
            color: white; 
        }
        .btn-out:hover { background: #c0392b; }
        
        .btn-success { 
            background: #27ae60; 
            color: white; 
        }
        .btn-success:hover { background: #229954; }
        
        .btn-primary {
            background: #3498db;
            color: white;
            margin-bottom: 10px;
        }
        .btn-primary:hover { background: #2980b9; }
        
        .alert { 
            padding: 15px; 
            border-radius: 4px; 
            margin-bottom: 20px;
            display: none;
        }
        .alert.show { display: block; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        
        .scan-status { 
            text-align: center; 
            padding: 15px; 
            font-size: 16px; 
            color: #555;
        }
        
        .scan-tips {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            text-align: left;
        }
        
        .scan-tips h4 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .scan-tips ul {
            margin-left: 20px;
            color: #856404;
            font-size: 13px;
        }
        
        .scan-tips li {
            margin: 5px 0;
        }
        
        .loading { 
            display: inline-block; 
            width: 20px; 
            height: 20px; 
            border: 3px solid #f3f3f3; 
            border-top: 3px solid #2c3e50; 
            border-radius: 50%; 
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }
        
        @keyframes spin { 
            0% { transform: rotate(0deg); } 
            100% { transform: rotate(360deg); } 
        }

        @media (max-width: 768px) {
            .stock-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Barcode Scanner v2</h1>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="products.php">Products</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div id="alertBox" class="alert"></div>

        <!-- Manual Barcode Entry -->
        <div class="card">
            <h2>1️⃣ Manual Barcode Entry (Always Works)</h2>
            <p style="color: #666; margin-bottom: 15px;">
                If camera scanner doesn't work, type or paste barcode here:
            </p>
            <div class="manual-scan">
                <input type="text" id="manualBarcode" placeholder="Enter barcode (e.g., 1234567890123)" autofocus>
                <button class="btn btn-success" onclick="searchManual()">🔍 Search</button>
            </div>
            
            <div class="test-barcodes">
                <strong>Test with these barcodes from your database:</strong><br>
                <code>1234567890123</code> (Dell Laptop),
                <code>2345678901234</code> (Wireless Mouse),
                <code>3456789012345</code> (USB Hub),
                <code>4567890123456</code> (Monitor)
            </div>

            <div id="manualResult" class="result-box"></div>
        </div>

        <!-- Camera Scanner -->
        <div class="card">
            <h2>2️⃣ Camera Scanner</h2>
            <button class="btn btn-primary" id="startScanBtn" onclick="toggleScanner()">
                📷 Start Camera Scanner
            </button>
            <div id="scannerContainer" style="display: none;">
                <div id="reader"></div>
                <p class="scan-status" id="scanStatus">Initializing scanner...</p>
                
                <div class="scan-tips">
                    <h4>📱 Scanning Tips:</h4>
                    <ul>
                        <li>Hold barcode <strong>5-10 cm</strong> from camera</li>
                        <li>Ensure good <strong>lighting</strong></li>
                        <li>Keep barcode <strong>centered</strong> in box</li>
                        <li>Hold device <strong>steady</strong> for 1-2 seconds</li>
                        <li><strong>1D Barcodes:</strong> Hold horizontally</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Product Information -->
        <div class="product-info" id="productInfo">
            <div class="product-header">
                <h2 id="productName">Product Name</h2>
                <small style="color: #777;" id="productSku">SKU: </small>
            </div>

            <div class="product-detail">
                <label>Current Stock:</label>
                <span class="stock-quantity" id="productQuantity">0</span>
            </div>

            <div class="product-detail">
                <label>Minimum Stock Level:</label>
                <span id="productMinStock">5</span>
            </div>

            <div class="product-detail">
                <label>Barcode:</label>
                <span id="productBarcode">-</span>
            </div>

            <div class="stock-actions">
                <div>
                    <div class="input-group">
                        <label>Stock IN Quantity:</label>
                        <input type="number" id="quantityIn" min="1" value="1">
                    </div>
                    <div class="input-group">
                        <label>Notes (optional):</label>
                        <textarea id="notesIn" rows="2" placeholder="e.g., New delivery"></textarea>
                    </div>
                    <button class="btn btn-in" onclick="updateStock('IN')">
                        ➕ Add Stock IN
                    </button>
                </div>

                <div>
                    <div class="input-group">
                        <label>Stock OUT Quantity:</label>
                        <input type="number" id="quantityOut" min="1" value="1">
                    </div>
                    <div class="input-group">
                        <label>Notes (optional):</label>
                        <textarea id="notesOut" rows="2" placeholder="e.g., Customer order"></textarea>
                    </div>
                    <button class="btn btn-out" onclick="updateStock('OUT')">
                        ➖ Remove Stock OUT
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- HTML5 QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>

    <script>
        let currentProduct = null;
        let html5QrCode = null;
        let isScannerActive = false;

        // Allow Enter key for manual search
        document.getElementById('manualBarcode').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchManual();
            }
        });

        /**
         * Manual barcode search
         */
        function searchManual() {
            const barcode = document.getElementById('manualBarcode').value.trim();
            
            if (!barcode) {
                showManualResult('Please enter a barcode', 'error');
                return;
            }

            showManualResult('Searching... <span class="loading"></span>', 'info');

            fetchProduct(barcode, 'manual');
        }

        /**
         * Toggle camera scanner on/off
         */
        function toggleScanner() {
            if (isScannerActive) {
                stopScanner();
            } else {
                startScanner();
            }
        }

        /**
         * Start camera scanner
         */
        function startScanner() {
            document.getElementById('scannerContainer').style.display = 'block';
            document.getElementById('startScanBtn').textContent = '⏹ Stop Camera Scanner';
            document.getElementById('startScanBtn').style.background = '#e74c3c';
            
            initScanner();
            isScannerActive = true;
        }

        /**
         * Stop camera scanner
         */
        function stopScanner() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    document.getElementById('scannerContainer').style.display = 'none';
                    document.getElementById('startScanBtn').textContent = '📷 Start Camera Scanner';
                    document.getElementById('startScanBtn').style.background = '#3498db';
                    isScannerActive = false;
                });
            }
        }

        /**
         * Initialize barcode/QR code scanner
         */
        function initScanner() {
            html5QrCode = new Html5Qrcode("reader");
            
            // Configuration for better barcode detection
            const config = {
                fps: 10,
                qrbox: { width: 300, height: 150 },
                aspectRatio: 2.0,
                formatsToSupport: [
                    Html5QrcodeSupportedFormats.QR_CODE,
                    Html5QrcodeSupportedFormats.EAN_13,
                    Html5QrcodeSupportedFormats.EAN_8,
                    Html5QrcodeSupportedFormats.CODE_128,
                    Html5QrcodeSupportedFormats.CODE_39,
                    Html5QrcodeSupportedFormats.UPC_A,
                    Html5QrcodeSupportedFormats.UPC_E
                ]
            };
            
            // Start scanning
            html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanError
            ).then(() => {
                document.getElementById('scanStatus').innerHTML = 
                    '✓ Scanner ready. <strong>Hold barcode steady and centered.</strong>';
            }).catch(err => {
                console.error('Scanner error:', err);
                document.getElementById('scanStatus').innerHTML = 
                    '❌ Camera error. Please use Manual Entry above or check permissions.';
            });
        }

        /**
         * Handle successful camera scan
         */
        function onScanSuccess(decodedText, decodedResult) {
            html5QrCode.pause();
            
            document.getElementById('scanStatus').innerHTML = 
                'Scanning...<span class="loading"></span>';
            
            fetchProduct(decodedText, 'camera');
        }

        /**
         * Handle scan errors (ignore - happens frequently)
         */
        function onScanError(error) {
            // Ignore - fires on every frame without barcode
        }

        /**
         * Fetch product by barcode from API
         */
        function fetchProduct(barcode, source) {
            fetch(`api/get_product.php?barcode=${encodeURIComponent(barcode)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayProduct(data.product);
                        showAlert('Product found: ' + data.product.name, 'success');
                        
                        if (source === 'manual') {
                            showManualResult('✓ Product found! See details below.', 'success');
                        }
                    } else {
                        showAlert('Product not found: ' + barcode, 'error');
                        document.getElementById('productInfo').classList.remove('show');
                        
                        if (source === 'manual') {
                            showManualResult('❌ Product not found in database', 'error');
                        } else if (source === 'camera') {
                            setTimeout(() => {
                                if (html5QrCode && isScannerActive) {
                                    html5QrCode.resume();
                                    document.getElementById('scanStatus').textContent = 
                                        '✓ Scanner ready. Try again.';
                                }
                            }, 2000);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error fetching product', 'error');
                    
                    if (source === 'manual') {
                        showManualResult('❌ Error: ' + error.message, 'error');
                    }
                });
        }

        /**
         * Display product information
         */
        function displayProduct(product) {
            currentProduct = product;
            
            document.getElementById('productName').textContent = product.name;
            document.getElementById('productSku').textContent = 'SKU: ' + product.sku;
            document.getElementById('productQuantity').textContent = product.quantity;
            document.getElementById('productMinStock').textContent = product.min_stock;
            document.getElementById('productBarcode').textContent = product.barcode;
            
            // Highlight if low stock
            const quantityEl = document.getElementById('productQuantity');
            if (product.is_low_stock) {
                quantityEl.classList.add('low');
                document.getElementById('productName').innerHTML = 
                    product.name + '<span class="warning-badge">⚠ LOW STOCK</span>';
            } else {
                quantityEl.classList.remove('low');
            }
            
            // Reset input fields
            document.getElementById('quantityIn').value = 1;
            document.getElementById('quantityOut').value = 1;
            document.getElementById('notesIn').value = '';
            document.getElementById('notesOut').value = '';
            
            // Show product info
            document.getElementById('productInfo').classList.add('show');
            
            if (isScannerActive) {
                document.getElementById('scanStatus').innerHTML = 
                    '✓ Product loaded. Scan another or update stock below.';
                
                // Resume scanner
                setTimeout(() => {
                    if (html5QrCode) {
                        html5QrCode.resume();
                    }
                }, 1000);
            }
        }

        /**
         * Update stock (IN or OUT)
         */
        function updateStock(type) {
            if (!currentProduct) {
                showAlert('No product selected', 'error');
                return;
            }

            const quantity = parseInt(document.getElementById('quantity' + type).value);
            const notes = document.getElementById('notes' + type).value;

            if (quantity <= 0) {
                showAlert('Quantity must be greater than 0', 'error');
                return;
            }

            const requestData = {
                product_id: currentProduct.id,
                type: type,
                quantity: quantity,
                notes: notes
            };

            fetch('api/stock_update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message + ' - New quantity: ' + data.data.new_quantity, 'success');
                    
                    currentProduct.quantity = data.data.new_quantity;
                    document.getElementById('productQuantity').textContent = data.data.new_quantity;
                    
                    const quantityEl = document.getElementById('productQuantity');
                    if (data.data.new_quantity <= currentProduct.min_stock) {
                        quantityEl.classList.add('low');
                    } else {
                        quantityEl.classList.remove('low');
                    }
                    
                    document.getElementById('quantity' + type).value = 1;
                    document.getElementById('notes' + type).value = '';
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error updating stock', 'error');
            });
        }

        /**
         * Show alert message
         */
        function showAlert(message, type) {
            const alertBox = document.getElementById('alertBox');
            alertBox.textContent = message;
            alertBox.className = 'alert alert-' + type + ' show';
            
            setTimeout(() => {
                alertBox.classList.remove('show');
            }, 5000);
        }

        /**
         * Show manual search result
         */
        function showManualResult(message, type) {
            const resultBox = document.getElementById('manualResult');
            resultBox.innerHTML = message;
            resultBox.className = 'result-box ' + type + ' show';
        }
    </script>
</body>
</html>