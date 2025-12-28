<?php
/**
 * Scanner Testing & Debugging Page
 * Test if scanner works and manually enter barcodes
 */

require_once 'config/database.php';
$user = requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner Test - Inventory System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f5f5f5; 
            padding: 20px; 
        }
        .container { max-width: 900px; margin: 0 auto; }
        .header { 
            background: #2c3e50; 
            color: white; 
            padding: 20px; 
            border-radius: 8px; 
            margin-bottom: 20px;
        }
        .card { 
            background: white; 
            padding: 25px; 
            border-radius: 8px; 
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card h2 { color: #2c3e50; margin-bottom: 15px; }
        .manual-scan { 
            display: flex; 
            gap: 10px; 
            margin-bottom: 20px; 
        }
        .manual-scan input { 
            flex: 1; 
            padding: 12px; 
            border: 2px solid #ddd; 
            border-radius: 5px;
            font-size: 16px;
        }
        .btn { 
            padding: 12px 24px; 
            background: #3498db; 
            color: white; 
            border: none; 
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn:hover { background: #2980b9; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #229954; }
        #reader { 
            width: 100%; 
            max-width: 500px; 
            margin: 20px auto;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        .result-box { 
            padding: 15px; 
            border-radius: 5px; 
            margin-top: 15px;
            display: none;
        }
        .result-box.show { display: block; }
        .result-success { background: #d4edda; border-left: 4px solid #28a745; }
        .result-error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .result-info { background: #d1ecf1; border-left: 4px solid #17a2b8; }
        .test-barcodes { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 5px;
            margin-top: 15px;
        }
        .test-barcodes code { 
            background: white; 
            padding: 3px 8px; 
            border-radius: 3px;
            color: #e74c3c;
            font-weight: 600;
        }
        .debug-log { 
            background: #2c3e50; 
            color: #2ecc71; 
            padding: 15px; 
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 15px;
        }
        .debug-log div { margin: 3px 0; }
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-green { background: #28a745; }
        .status-red { background: #dc3545; }
        .status-yellow { background: #ffc107; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Scanner Test & Debug Tool</h1>
            <p>Test barcode scanning and manually search products</p>
        </div>

        <!-- Manual Barcode Entry -->
        <div class="card">
            <h2>1️⃣ Manual Barcode Entry (Always Works)</h2>
            <p style="color: #666; margin-bottom: 15px;">
                If scanner doesn't work, type or paste barcode here:
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

        <!-- Camera Scanner Test -->
        <div class="card">
            <h2>2️⃣ Camera Scanner Test</h2>
            <div style="margin-bottom: 15px;">
                <span class="status-indicator" id="cameraStatus"></span>
                <strong>Camera Status:</strong> <span id="cameraStatusText">Checking...</span>
            </div>
            
            <button class="btn" onclick="startScanner()">📷 Start Camera Scanner</button>
            <button class="btn" onclick="stopScanner()" style="background: #e74c3c;">⏹ Stop Scanner</button>
            
            <div id="reader"></div>
            
            <div id="scanResult" class="result-box"></div>
        </div>

        <!-- Debug Console -->
        <div class="card">
            <h2>3️⃣ Debug Console</h2>
            <p style="color: #666; margin-bottom: 10px;">Real-time scanner events:</p>
            <div id="debugLog" class="debug-log">
                <div>[READY] Debug console initialized...</div>
            </div>
            <button class="btn" onclick="clearDebug()" style="margin-top: 10px;">Clear Log</button>
        </div>

        <!-- System Info -->
        <div class="card">
            <h2>4️⃣ System Information</h2>
            <p><strong>Browser:</strong> <span id="browserInfo"></span></p>
            <p><strong>HTTPS:</strong> <span id="httpsInfo"></span></p>
            <p><strong>Camera API:</strong> <span id="cameraApiInfo"></span></p>
            <p style="margin-top: 10px; color: #666; font-size: 14px;">
                ⚠️ <strong>Important:</strong> Camera scanning requires HTTPS or localhost. 
                If you're on HTTP (not localhost), the camera may not work.
            </p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>

    <script>
        let html5QrCode = null;
        let isScanning = false;

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkSystemInfo();
            logDebug('Page loaded, system ready');
        });

        // Manual barcode search
        function searchManual() {
            const barcode = document.getElementById('manualBarcode').value.trim();
            
            if (!barcode) {
                showResult('manualResult', 'Please enter a barcode', 'error');
                return;
            }

            logDebug(`Manual search: ${barcode}`);
            showResult('manualResult', 'Searching...', 'info');

            fetch(`api/get_product.php?barcode=${encodeURIComponent(barcode)}`)
                .then(response => response.json())
                .then(data => {
                    logDebug(`API Response: ${JSON.stringify(data)}`);
                    
                    if (data.success) {
                        const p = data.product;
                        const html = `
                            <strong>✓ Product Found!</strong><br>
                            <strong>Name:</strong> ${p.name}<br>
                            <strong>SKU:</strong> ${p.sku}<br>
                            <strong>Stock:</strong> ${p.quantity} units<br>
                            <strong>Min Stock:</strong> ${p.min_stock}<br>
                            ${p.is_low_stock ? '<span style="color: #e74c3c;">⚠️ LOW STOCK</span>' : ''}
                        `;
                        showResult('manualResult', html, 'success');
                    } else {
                        showResult('manualResult', '❌ Product not found: ' + barcode, 'error');
                    }
                })
                .catch(error => {
                    logDebug(`ERROR: ${error}`);
                    showResult('manualResult', '❌ Error: ' + error.message, 'error');
                });
        }

        // Start camera scanner
        function startScanner() {
            if (isScanning) {
                logDebug('Scanner already running');
                return;
            }

            logDebug('Starting camera scanner...');
            document.getElementById('cameraStatusText').textContent = 'Starting...';
            document.getElementById('cameraStatus').className = 'status-indicator status-yellow';

            html5QrCode = new Html5Qrcode("reader");

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
                    Html5QrcodeSupportedFormats.CODE_93,
                    Html5QrcodeSupportedFormats.UPC_A,
                    Html5QrcodeSupportedFormats.UPC_E,
                    Html5QrcodeSupportedFormats.ITF,
                    Html5QrcodeSupportedFormats.CODABAR
                ]
            };

            html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanError
            ).then(() => {
                isScanning = true;
                logDebug('✓ Scanner started successfully');
                document.getElementById('cameraStatusText').textContent = 'Active';
                document.getElementById('cameraStatus').className = 'status-indicator status-green';
                showResult('scanResult', 'Scanner ready! Point at a barcode.', 'info');
            }).catch(err => {
                logDebug(`✗ Scanner failed: ${err}`);
                document.getElementById('cameraStatusText').textContent = 'Failed';
                document.getElementById('cameraStatus').className = 'status-indicator status-red';
                showResult('scanResult', '❌ Camera error: ' + err, 'error');
            });
        }

        // Stop camera scanner
        function stopScanner() {
            if (html5QrCode && isScanning) {
                html5QrCode.stop().then(() => {
                    isScanning = false;
                    logDebug('Scanner stopped');
                    document.getElementById('cameraStatusText').textContent = 'Stopped';
                    document.getElementById('cameraStatus').className = 'status-indicator status-red';
                    showResult('scanResult', 'Scanner stopped.', 'info');
                });
            }
        }

        // Scan success callback
        function onScanSuccess(decodedText, decodedResult) {
            logDebug(`✓ SCAN SUCCESS: ${decodedText}`);
            
            // Temporarily pause scanner
            if (html5QrCode) {
                html5QrCode.pause(true);
            }

            showResult('scanResult', `Scanned: ${decodedText}. Searching...`, 'info');

            // Search product
            fetch(`api/get_product.php?barcode=${encodeURIComponent(decodedText)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const p = data.product;
                        const html = `
                            <strong>✓ Product Found!</strong><br>
                            <strong>Name:</strong> ${p.name}<br>
                            <strong>SKU:</strong> ${p.sku}<br>
                            <strong>Stock:</strong> ${p.quantity} units
                        `;
                        showResult('scanResult', html, 'success');
                        logDebug(`Product found: ${p.name}`);
                    } else {
                        showResult('scanResult', '❌ Product not found: ' + decodedText, 'error');
                        logDebug(`Product not found in database`);
                    }

                    // Resume scanning after 3 seconds
                    setTimeout(() => {
                        if (html5QrCode && isScanning) {
                            html5QrCode.resume();
                            showResult('scanResult', 'Scanner ready again.', 'info');
                        }
                    }, 3000);
                });
        }

        // Scan error callback (fires constantly, so we ignore most)
        function onScanError(error) {
            // Only log real errors, not "no barcode detected"
            if (!error.includes('NotFoundException')) {
                logDebug(`Scan error: ${error}`);
            }
        }

        // Show result message
        function showResult(elementId, message, type) {
            const el = document.getElementById(elementId);
            el.innerHTML = message;
            el.className = 'result-box result-' + type + ' show';
        }

        // Debug logging
        function logDebug(message) {
            const log = document.getElementById('debugLog');
            const time = new Date().toLocaleTimeString();
            const div = document.createElement('div');
            div.textContent = `[${time}] ${message}`;
            log.appendChild(div);
            log.scrollTop = log.scrollHeight;
        }

        // Clear debug log
        function clearDebug() {
            document.getElementById('debugLog').innerHTML = '<div>[READY] Debug console cleared...</div>';
        }

        // Check system information
        function checkSystemInfo() {
            document.getElementById('browserInfo').textContent = navigator.userAgent.split(' ').slice(-2).join(' ');
            document.getElementById('httpsInfo').innerHTML = location.protocol === 'https:' || location.hostname === 'localhost' 
                ? '<span style="color: green;">✓ Secure (HTTPS or localhost)</span>' 
                : '<span style="color: red;">✗ Not secure (Camera may not work)</span>';
            
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                document.getElementById('cameraApiInfo').innerHTML = '<span style="color: green;">✓ Supported</span>';
                logDebug('Camera API is available');
            } else {
                document.getElementById('cameraApiInfo').innerHTML = '<span style="color: red;">✗ Not supported</span>';
                logDebug('⚠️ Camera API not available');
            }
        }

        // Allow Enter key for manual search
        document.getElementById('manualBarcode').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchManual();
            }
        });
    </script>
</body>
</html>