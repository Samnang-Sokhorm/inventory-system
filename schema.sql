-- 1. Add barcode and min_stock columns to products table
ALTER TABLE products 
ADD COLUMN barcode VARCHAR(100) UNIQUE,
ADD COLUMN min_stock INT DEFAULT 5;

-- 2. Create stock_movements table for tracking history
CREATE TABLE IF NOT EXISTS stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    movement_type ENUM('IN', 'OUT') NOT NULL,
    quantity INT NOT NULL,
    previous_quantity INT NOT NULL,
    new_quantity INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_product_date (product_id, created_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Sample data (optional - for testing)
-- UPDATE products SET barcode = '1234567890123' WHERE id = 1;
-- UPDATE products SET barcode = '9876543210987' WHERE id = 2;
-- UPDATE products SET min_stock = 10 WHERE id = 1;