-- ============================================================
-- ACID Compliance Migration for Dhoti Mahal
-- Ensures all tables use InnoDB engine for ACID transactions
-- Adds constraints for data integrity (with existence checks)
-- ============================================================

USE dhoti_mahal;

-- Convert all tables to InnoDB for ACID compliance
ALTER TABLE users ENGINE = InnoDB;
ALTER TABLE admins ENGINE = InnoDB;
ALTER TABLE categories ENGINE = InnoDB;
ALTER TABLE products ENGINE = InnoDB;
ALTER TABLE banners ENGINE = InnoDB;
ALTER TABLE cart ENGINE = InnoDB;
ALTER TABLE orders ENGINE = InnoDB;
ALTER TABLE order_items ENGINE = InnoDB;
ALTER TABLE order_tracking ENGINE = InnoDB;
ALTER TABLE settings ENGINE = InnoDB;

-- Add check constraints for data integrity (only if they don't exist)
-- MySQL doesn't have a simple IF NOT EXISTS for constraints, so we use a workaround

-- Ensure stock is never negative
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS
     WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'chk_stock_non_negative') = 0,
    'ALTER TABLE products ADD CONSTRAINT chk_stock_non_negative CHECK (stock >= 0)',
    'SELECT "Check constraint chk_stock_non_negative already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure order totals are positive
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS
     WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'chk_order_total_positive') = 0,
    'ALTER TABLE orders ADD CONSTRAINT chk_order_total_positive CHECK (total > 0)',
    'SELECT "Check constraint chk_order_total_positive already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure order item quantities are positive
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS
     WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'chk_item_quantity_positive') = 0,
    'ALTER TABLE order_items ADD CONSTRAINT chk_item_quantity_positive CHECK (quantity > 0)',
    'SELECT "Check constraint chk_item_quantity_positive already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure order item totals are positive
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS
     WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'chk_item_total_positive') = 0,
    'ALTER TABLE order_items ADD CONSTRAINT chk_item_total_positive CHECK (total > 0)',
    'SELECT "Check constraint chk_item_total_positive already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes for better performance (only if they don't exist)
CREATE INDEX IF NOT EXISTS idx_orders_payment_status ON orders(payment_status);
CREATE INDEX IF NOT EXISTS idx_orders_order_status ON orders(order_status);
CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders(user_id);
CREATE INDEX IF NOT EXISTS idx_order_items_order_id ON order_items(order_id);
CREATE INDEX IF NOT EXISTS idx_order_tracking_order_id ON order_tracking(order_id);
CREATE INDEX IF NOT EXISTS idx_products_stock ON products(stock);
CREATE INDEX IF NOT EXISTS idx_cart_user_session ON cart(user_id, session_id);

-- Add unique constraint to prevent duplicate order numbers (only if it doesn't exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
     WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND CONSTRAINT_NAME = 'uk_order_number') = 0,
    'ALTER TABLE orders ADD CONSTRAINT uk_order_number UNIQUE (order_number)',
    'SELECT "Unique constraint uk_order_number already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;