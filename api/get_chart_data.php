<?php
/**
 * API Endpoint: Get Chart Data
 * Returns data for dashboard charts
 */

require_once '../config/database.php';

// Security: Check if user is logged in
$user = requireLogin();

// Set JSON header
header('Content-Type: application/json');

// Get chart type
$chartType = isset($_GET['type']) ? $_GET['type'] : 'all';

$conn = getDBConnection();
$data = [];

// 1. Bar Chart: Quantity per product
if ($chartType === 'bar' || $chartType === 'all') {
    $result = $conn->query("
        SELECT name, quantity, min_stock 
        FROM products 
        ORDER BY quantity DESC 
        LIMIT 15
    ");
    
    $barData = [
        'labels' => [],
        'quantities' => [],
        'minStocks' => []
    ];
    
    while ($row = $result->fetch_assoc()) {
        $barData['labels'][] = $row['name'];
        $barData['quantities'][] = (int)$row['quantity'];
        $barData['minStocks'][] = (int)$row['min_stock'];
    }
    
    $data['barChart'] = $barData;
}

// 2. Line Chart: Stock movements over time (last 30 days)
if ($chartType === 'line' || $chartType === 'all') {
    $result = $conn->query("
        SELECT 
            DATE(created_at) as date,
            SUM(CASE WHEN movement_type = 'IN' THEN quantity ELSE 0 END) as stock_in,
            SUM(CASE WHEN movement_type = 'OUT' THEN quantity ELSE 0 END) as stock_out
        FROM stock_movements
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    
    $lineData = [
        'labels' => [],
        'stockIn' => [],
        'stockOut' => []
    ];
    
    while ($row = $result->fetch_assoc()) {
        $lineData['labels'][] = date('M d', strtotime($row['date']));
        $lineData['stockIn'][] = (int)$row['stock_in'];
        $lineData['stockOut'][] = (int)$row['stock_out'];
    }
    
    $data['lineChart'] = $lineData;
}

// 3. Low Stock Count
if ($chartType === 'alerts' || $chartType === 'all') {
    $result = $conn->query("
        SELECT COUNT(*) as count 
        FROM products 
        WHERE quantity <= min_stock
    ");
    
    $row = $result->fetch_assoc();
    $data['lowStockCount'] = (int)$row['count'];
    
    // Get low stock products
    $result = $conn->query("
        SELECT id, name, sku, quantity, min_stock 
        FROM products 
        WHERE quantity <= min_stock
        ORDER BY quantity ASC
    ");
    
    $lowStockProducts = [];
    while ($row = $result->fetch_assoc()) {
        $lowStockProducts[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'sku' => $row['sku'],
            'quantity' => (int)$row['quantity'],
            'min_stock' => (int)$row['min_stock']
        ];
    }
    
    $data['lowStockProducts'] = $lowStockProducts;
}

echo json_encode(['success' => true, 'data' => $data]);

$conn->close();
?>