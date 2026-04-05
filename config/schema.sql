-- ============================================================
-- Dhoti Mahal - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS dhoti_mahal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dhoti_mahal;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(15),
    password VARCHAR(255) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    sku VARCHAR(100),
    stock INT DEFAULT 0,
    fabric VARCHAR(100),
    color VARCHAR(100),
    size VARCHAR(200),
    occasion VARCHAR(150),
    image VARCHAR(255),
    gallery TEXT,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Banners Table
CREATE TABLE IF NOT EXISTS banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    subtitle VARCHAR(300),
    image VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    button_text VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart Table (session-based, optional DB persistence)
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(255),
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    size VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT,
    guest_name VARCHAR(100),
    guest_email VARCHAR(150),
    guest_phone VARCHAR(15),
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_state VARCHAR(100) NOT NULL,
    shipping_pincode VARCHAR(10) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    discount DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'upi',
    payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    payment_ref VARCHAR(200),
    order_status ENUM('placed','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'placed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_image VARCHAR(255),
    size VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Order Tracking Table
CREATE TABLE IF NOT EXISTS order_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status VARCHAR(100) NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Site Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- Sample Data
-- ============================================================

-- Default Admin
INSERT INTO admins (name, email, password) VALUES
('Admin', 'admin@dhotimahal.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- password: password

-- Categories
INSERT INTO categories (name, slug, description, is_active, sort_order) VALUES
('Traditional Dhotis', 'traditional-dhotis', 'Classic handwoven traditional dhotis for all occasions', 1, 1),
('Silk Dhotis', 'silk-dhotis', 'Premium pure silk dhotis for weddings and festivals', 1, 2),
('Cotton Dhotis', 'cotton-dhotis', 'Comfortable everyday cotton dhotis', 1, 3),
('Designer Dhotis', 'designer-dhotis', 'Modern designer dhotis with contemporary patterns', 1, 4),
('Kurta Sets', 'kurta-sets', 'Matching kurta and dhoti sets for complete look', 1, 5),
('Accessories', 'accessories', 'Angavastram, gamchas and other accessories', 1, 6);

-- Products
INSERT INTO products (category_id, name, slug, description, short_description, price, sale_price, stock, fabric, color, size, occasion, is_featured, is_active) VALUES
(2, 'Kanjivaram Pure Silk Dhoti', 'kanjivaram-pure-silk-dhoti', 'Authentic Kanjivaram pure silk dhoti with zari border. Handwoven by master weavers from Tamil Nadu. Perfect for weddings, festivals and special occasions. The rich golden zari work gives it a regal appearance.', 'Authentic Kanjivaram pure silk with golden zari border', 3499.00, 2999.00, 50, 'Pure Silk', 'Cream with Gold Border', '4 Meters, 4.5 Meters, 5 Meters', 'Wedding, Festival, Puja', 1, 1),
(2, 'Banarasi Silk Dhoti', 'banarasi-silk-dhoti', 'Exquisite Banarasi silk dhoti featuring intricate brocade patterns. Made from the finest silk sourced from Varanasi. The brocade work is done by traditional weavers maintaining age-old techniques.', 'Luxurious Banarasi silk with brocade patterns', 4200.00, 3799.00, 30, 'Banarasi Silk', 'Off-White with Silver Border', '4 Meters, 5 Meters', 'Wedding, Reception, Festival', 1, 1),
(1, 'Bengal Handloom Dhoti', 'bengal-handloom-dhoti', 'Traditional Bengali dhoti handwoven on traditional looms. Features the classic white with red/blue border combination beloved in West Bengal. Ideal for Durga Puja and other Bengali festivals.', 'Classic Bengal handloom with traditional border', 899.00, 749.00, 100, 'Cotton Handloom', 'White with Red Border', '4.5 Meters, 5 Meters', 'Festival, Daily, Puja', 1, 1),
(3, 'Kerala Kasavu Dhoti', 'kerala-kasavu-dhoti', 'Authentic Kerala Kasavu dhoti with traditional golden kasavu border. Made from fine cotton grown in Kerala. An essential garment for Onam, Vishu and all Kerala festivals.', 'Traditional Kerala cotton with golden kasavu border', 1299.00, 1099.00, 75, 'Fine Cotton', 'White with Gold Border', '4 Meters, 4.5 Meters', 'Onam, Festival, Puja', 1, 1),
(4, 'Embroidered Designer Dhoti', 'embroidered-designer-dhoti', 'Contemporary designer dhoti with hand embroidery work. A modern take on traditional dhoti perfect for mehendi, engagement and sangeet ceremonies. Pairs beautifully with designer kurtas.', 'Modern dhoti with intricate hand embroidery', 2199.00, 1899.00, 40, 'Cotton Silk Blend', 'Ivory with Maroon Embroidery', '4 Meters, 4.5 Meters', 'Engagement, Mehendi, Sangeet', 1, 1),
(5, 'Royal Wedding Dhoti Kurta Set', 'royal-wedding-dhoti-kurta-set', 'Complete royal wedding set including premium silk dhoti and matching embroidered kurta. The set is adorned with delicate threadwork and comes with an angavastram. A complete traditional ensemble for the groom.', 'Complete bridal set with dhoti, kurta and angavastram', 8999.00, 7499.00, 20, 'Silk Blend', 'Cream and Gold', 'S, M, L, XL, XXL', 'Wedding, Engagement', 1, 1),
(3, 'Soft Cotton Daily Dhoti', 'soft-cotton-daily-dhoti', 'Premium soft cotton dhoti ideal for daily wear. Lightweight, breathable and comfortable for all-day wear. Easy to drape and maintain. Available in multiple colors.', 'Comfortable everyday cotton dhoti', 399.00, NULL, 200, 'Soft Cotton', 'White', '4 Meters, 4.5 Meters, 5 Meters', 'Daily Wear, Home', 0, 1),
(6, 'Silk Angavastram', 'silk-angavastram', 'Pure silk angavastram to complement your dhoti. Adds grace and elegance to traditional attire. Available in multiple colors to match different dhotis.', 'Pure silk angavastram for traditional attire', 999.00, 849.00, 60, 'Pure Silk', 'Multiple Colors', '2.5 Meters', 'Wedding, Festival, Puja', 0, 1);

-- Settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Dhoti Mahal'),
('site_tagline', 'The House of Traditional Indian Attire'),
('site_email', 'info@dhotimahal.com'),
('site_phone', '+91 98765 43210'),
('site_address', '12, Silk Market, Barasat, Kolkata - 700124, West Bengal'),
('upi_id', 'dhotimahal@upi'),
('upi_name', 'Dhoti Mahal'),
('free_shipping_above', '999'),
('shipping_cost', '99'),
('currency_symbol', '₹'),
('facebook_url', 'https://facebook.com/dhotimahal'),
('instagram_url', 'https://instagram.com/dhotimahal'),
('whatsapp_number', '919876543210'),
('gst_number', '19ABCDE1234F1Z5'),
('meta_description', 'Dhoti Mahal - Your destination for premium traditional Indian dhotis. Shop pure silk, cotton handloom and designer dhotis for weddings and festivals.');
