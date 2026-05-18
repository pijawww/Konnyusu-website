-- Database Konnyusu - E-Commerce Pemesanan Minuman
-- Created for Konnyusu Project

CREATE DATABASE IF NOT EXISTS konnyusu DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE konnyusu;

-- Table: USERS
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: MENU
CREATE TABLE IF NOT EXISTS menu (
    menu_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    image VARCHAR(255),
    stock INT DEFAULT 0,
    is_new BOOLEAN DEFAULT FALSE,
    is_best BOOLEAN DEFAULT FALSE,
    sold INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: CART
CREATE TABLE IF NOT EXISTS cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'checkout') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: CART_ITEM
CREATE TABLE IF NOT EXISTS cart_item (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    menu_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price INT NOT NULL,
    subtotal INT NOT NULL,
    ice_level VARCHAR(50),
    sugar_level VARCHAR(50),
    size VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES cart(cart_id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: ORDERS
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    repeat_order_id INT,
    order_status VARCHAR(50) DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    order_type VARCHAR(50) DEFAULT 'dine_in',
    total INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: ORDER_ITEM
CREATE TABLE IF NOT EXISTS order_item (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_id INT NOT NULL,
    quantity INT NOT NULL,
    price INT NOT NULL,
    ice_level VARCHAR(50),
    sugar_level VARCHAR(50),
    size VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: PAYMENT
CREATE TABLE IF NOT EXISTS payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_status VARCHAR(50) DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    payment_method VARCHAR(50),
    amount INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Admin User
INSERT INTO users (name, username, email, password, role) VALUES 
('Admin Konnyusu', 'admin', 'admin@konnyusu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert Demo Products
INSERT INTO menu (name, description, price, category, image, stock, is_new, is_best, sold) VALUES
('Caramel Tart Latte', 'Espresso lembut dengan sirup karamel asli dan foam susu tebal. Manis di lidah, hangat di hati.', 32000, 'coffee', 'caramel-tart.jpeg', 100, FALSE, TRUE, 238),
('Sea Salt Cream Latte', 'Perpaduan espresso segar dengan krim garam laut yang menggoda. Sensasi gurih-manis yang unik.', 28000, 'coffee', 'oat-latte.jpg', 100, FALSE, TRUE, 195),
('Signetone Oat Latte', 'Single origin arabika dengan susu oat organik. Pilihan sehat tanpa kompromi rasa.', 30000, 'coffee', 'oat-latte.jpg', 100, TRUE, FALSE, 140),
('Signature Series', 'Racikan eksklusif barista terbaik kami. Blend sempurna dari tiga varietas biji kopi premium.', 45000, 'coffee', 'signature.jpg', 50, FALSE, TRUE, 89),
('Matcha Oat Latte', 'Matcha ceremonial grade dari Jepang dengan susu oat hangat. Sehat, segar, dan menenangkan.', 28000, 'non-coffee', 'oat-latte.jpg', 100, FALSE, TRUE, 312),
('Hojicha Milk', 'Teh hojicha panggang asal Kyoto dengan susu full cream. Aroma sangit yang khas dan memikat.', 25000, 'tea', 'savory.jpg', 100, TRUE, FALSE, 167);

-- Note: Password admin is 'admin123' (hashed)
