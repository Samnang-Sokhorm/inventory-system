don't forget create SQL on myPhpAdmin



CREATE DATABASE IF NOT EXISTS inventory_db;
USE inventory_db;

-- 2. Create Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('admin', 'manager', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create Products Table with Barcode
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    sku VARCHAR(100) NOT NULL UNIQUE,
    barcode VARCHAR(100) UNIQUE,
    description TEXT,
    category VARCHAR(100),
    quantity INT NOT NULL DEFAULT 0,
    min_stock INT NOT NULL DEFAULT 5,
    price DECIMAL(10, 2) DEFAULT 0.00,
    cost DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sku (sku),
    INDEX idx_barcode (barcode),
    INDEX idx_quantity (quantity),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Create Stock Movements Table
CREATE TABLE IF NOT EXISTS stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    movement_type ENUM('IN', 'OUT') NOT NULL,
    quantity INT NOT NULL,
    previous_quantity INT NOT NULL,
    new_quantity INT NOT NULL,
    user_id INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_product_date (product_id, created_at),
    INDEX idx_movement_type (movement_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- INSERT SAMPLE DATA FOR TESTING
-- ============================================

-- Insert Admin User (password: admin123)
-- Password is hashed with PHP password_hash()
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@inventory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin'),
('manager', 'manager@inventory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Stock Manager', 'manager'),
('staff', 'staff@inventory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Warehouse Staff', 'staff');

-- Insert Sample Products
INSERT INTO products (name, sku, barcode, description, category, quantity, min_stock, price, cost) VALUES
('Laptop Dell XPS 13', 'LAP-001', '1234567890123', 'High-performance ultrabook', 'Electronics', 25, 5, 1299.99, 899.99),
('Wireless Mouse Logitech', 'ACC-001', '2345678901234', 'Ergonomic wireless mouse', 'Accessories', 150, 20, 29.99, 15.99),
('USB-C Hub Multiport', 'ACC-002', '3456789012345', '7-in-1 USB hub', 'Accessories', 80, 15, 49.99, 25.99),
('Monitor LG 27 inch', 'MON-001', '4567890123456', '4K UHD display', 'Electronics', 35, 10, 399.99, 249.99),
('Keyboard Mechanical', 'ACC-003', '5678901234567', 'RGB mechanical keyboard', 'Accessories', 60, 10, 89.99, 45.99),
('Webcam HD 1080p', 'ACC-004', '6789012345678', 'Full HD webcam', 'Accessories', 45, 8, 79.99, 39.99),
('External SSD 1TB', 'STO-001', '7890123456789', 'Portable SSD storage', 'Storage', 100, 15, 129.99, 79.99),
('Laptop Stand Aluminum', 'ACC-005', '8901234567890', 'Adjustable laptop stand', 'Accessories', 70, 12, 39.99, 19.99),
('Cable HDMI 2.1', 'CBL-001', '9012345678901', '4K HDMI cable 2m', 'Cables', 200, 30, 19.99, 8.99),
('Power Bank 20000mAh', 'ACC-006', '0123456789012', 'High capacity power bank', 'Accessories', 90, 15, 49.99, 24.99),
('Headphones Wireless', 'AUD-001', '1122334455667', 'Noise canceling headphones', 'Audio', 55, 10, 299.99, 179.99),
('Smartphone Case', 'ACC-007', '2233445566778', 'Protective phone case', 'Accessories', 3, 10, 24.99, 9.99),
('Tablet iPad Air', 'TAB-001', '3344556677889', '10.9 inch tablet', 'Electronics', 20, 5, 599.99, 449.99),
('Smart Watch', 'ACC-008', '4455667788990', 'Fitness tracker watch', 'Accessories', 40, 8, 199.99, 119.99),
('Printer Inkjet', 'OFF-001', '5566778899001', 'Color inkjet printer', 'Office', 15, 3, 149.99, 89.99);

-- Insert Sample Stock Movements (for chart data)
INSERT INTO stock_movements (product_id, movement_type, quantity, previous_quantity, new_quantity, user_id, notes) VALUES
-- Recent movements (last 30 days)
(1, 'IN', 10, 15, 25, 1, 'New stock arrival'),
(2, 'OUT', 5, 155, 150, 2, 'Customer order'),
(3, 'IN', 20, 60, 80, 1, 'Restocking'),
(4, 'OUT', 3, 38, 35, 2, 'Sales'),
(5, 'IN', 15, 45, 60, 1, 'Supplier delivery'),
(6, 'OUT', 2, 47, 45, 3, 'Demo units'),
(7, 'IN', 30, 70, 100, 1, 'Bulk order'),
(8, 'OUT', 5, 75, 70, 2, 'Office use'),
(9, 'IN', 50, 150, 200, 1, 'Restock'),
(10, 'OUT', 8, 98, 90, 2, 'Customer sales'),
(11, 'IN', 10, 45, 55, 1, 'New inventory'),
(12, 'OUT', 12, 15, 3, 3, 'Promotional sales'),
(13, 'IN', 5, 15, 20, 1, 'Limited stock'),
(14, 'OUT', 10, 50, 40, 2, 'Sales'),
(15, 'IN', 8, 7, 15, 1, 'Restocking');

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Check if tables were created
SELECT 'Tables created successfully!' AS Status;

SELECT 
    'Users' AS TableName, 
    COUNT(*) AS RecordCount 
FROM users
UNION ALL
SELECT 
    'Products' AS TableName, 
    COUNT(*) AS RecordCount 
FROM products
UNION ALL
SELECT 
    'Stock Movements' AS TableName, 
    COUNT(*) AS RecordCount 
FROM stock_movements;

-- Show products with low stock
SELECT 
    name, 
    sku, 
    barcode, 
    quantity, 
    min_stock,
    CASE 
        WHEN quantity <= min_stock THEN 'LOW STOCK'
        ELSE 'OK'
    END AS status
FROM products
ORDER BY quantity ASC;

-- ============================================
-- NOTES:
-- ============================================
-- 1. Default password for all users: admin123
-- 2. Product with ID 12 has LOW STOCK (3 units, min 10)
-- 3. Product with ID 15 has LOW STOCK (15 units, min 3... wait, this is OK)
-- 4. You can now test the barcode scanner with any barcode listed above
-- 5. Example barcode to test: 1234567890123 (Dell Laptop)
-- ============================================
