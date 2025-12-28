<?php
/**
 * API Endpoint: Update Stock (IN/OUT)
 * Handles stock movements and logs history
 */

require_once '../config/database.php';

// Security: Check if user is logged in
$user = requireLogin();

// Set JSON header
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
$movementType = isset($input['type']) ? strtoupper(trim($input['type'])) : '';
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 0;
$notes = isset($input['notes']) ? trim($input['notes']) : '';

// Validation rules
if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit();
}

if (!in_array($movementType, ['IN', 'OUT'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid movement type. Use IN or OUT']);
    exit();
}

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0']);
    exit();
}

// Connect to database
$conn = getDBConnection();

// Start transaction
$conn->begin_transaction();

try {
    // Get current product quantity (with lock to prevent race conditions)
    $stmt = $conn->prepare("SELECT quantity, name FROM products WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Product not found');
    }
    
    $product = $result->fetch_assoc();
    $previousQuantity = (int)$product['quantity'];
    $productName = $product['name'];
    
    // Calculate new quantity
    if ($movementType === 'IN') {
        $newQuantity = $previousQuantity + $quantity;
    } else { // OUT
        $newQuantity = $previousQuantity - $quantity;
        
        // Prevent negative stock
        if ($newQuantity < 0) {
            throw new Exception('Insufficient stock. Available: ' . $previousQuantity);
        }
    }
    
    // Update product quantity
    $stmt = $conn->prepare("UPDATE products SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $newQuantity, $productId);
    $stmt->execute();
    
    // Log stock movement
    $stmt = $conn->prepare("
        INSERT INTO stock_movements 
        (product_id, movement_type, quantity, previous_quantity, new_quantity, user_id, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isiiiis", 
        $productId, 
        $movementType, 
        $quantity, 
        $previousQuantity, 
        $newQuantity, 
        $user['user_id'],
        $notes
    );
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Stock updated successfully',
        'data' => [
            'product_name' => $productName,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'movement' => $movementType . ' ' . $quantity
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>