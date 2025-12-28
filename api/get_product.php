<?php
/**
 * API Endpoint: Get Product by Barcode
 * Returns product details in JSON format
 */

require_once '../config/database.php';

// Security: Check if user is logged in
$user = requireLogin();

// Set JSON header
header('Content-Type: application/json');

// Get barcode from request
$barcode = isset($_GET['barcode']) ? trim($_GET['barcode']) : '';

// Validate barcode
if (empty($barcode)) {
    echo json_encode([
        'success' => false,
        'message' => 'Barcode is required'
    ]);
    exit();
}

// Connect to database
$conn = getDBConnection();

// Prepare query to get product by barcode
$stmt = $conn->prepare("
    SELECT 
        id, 
        name, 
        sku, 
        quantity, 
        barcode,
        min_stock,
        price,
        description
    FROM products 
    WHERE barcode = ?
");

$stmt->bind_param("s", $barcode);
$stmt->execute();
$result = $stmt->get_result();

// Check if product exists
if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    // Check if stock is low
    $isLowStock = $product['quantity'] <= $product['min_stock'];
    
    echo json_encode([
        'success' => true,
        'product' => [
            'id' => $product['id'],
            'name' => $product['name'],
            'sku' => $product['sku'],
            'quantity' => (int)$product['quantity'],
            'barcode' => $product['barcode'],
            'min_stock' => (int)$product['min_stock'],
            'price' => $product['price'],
            'description' => $product['description'],
            'is_low_stock' => $isLowStock
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found'
    ]);
}

$stmt->close();
$conn->close();
?>