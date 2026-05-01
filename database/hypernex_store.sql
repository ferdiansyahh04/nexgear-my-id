CREATE DATABASE IF NOT EXISTS hypernex_store
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE hypernex_store;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  created_at DATETIME NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  description TEXT NULL,
  price DECIMAL(12, 2) NOT NULL DEFAULT 0,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  image VARCHAR(255) NOT NULL DEFAULT 'default-product.svg',
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  INDEX idx_products_name (name),
  INDEX idx_products_price (price)
) ENGINE=InnoDB;

CREATE TABLE cart (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  status ENUM('active', 'checked_out', 'cancelled') NOT NULL DEFAULT 'active',
  total DECIMAL(12, 2) NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_cart_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  INDEX idx_cart_user_status (user_id, status)
) ENGINE=InnoDB;

CREATE TABLE cart_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cart_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NULL,
  quantity INT UNSIGNED NOT NULL,
  price DECIMAL(12, 2) NOT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_cart_items_cart
    FOREIGN KEY (cart_id) REFERENCES cart(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_cart_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
  INDEX idx_cart_items_cart (cart_id),
  INDEX idx_cart_items_product (product_id)
) ENGINE=InnoDB;

INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES
('Admin Hypernex', 'admin@hypernex.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC1fRmtn3MowM9ATQeJe', 'admin', NOW(), NOW()),
('Demo User', 'user@hypernex.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC1fRmtn3MowM9ATQeJe', 'user', NOW(), NOW());

INSERT INTO products (name, description, price, stock, image, created_at, updated_at) VALUES
('Nebula K87 Mechanical Keyboard', 'Compact hot-swappable keyboard with RGB lighting and tactile switches.', 899000, 14, 'default-product.svg', NOW(), NOW()),
('Pulsefire Ultra Mouse', 'Lightweight wireless mouse with low-latency sensor and textured side grip.', 549000, 22, 'default-product.svg', NOW(), NOW()),
('EchoStrike 7.1 Headset', 'Closed-back headset with virtual surround, soft pads, and detachable mic.', 729000, 18, 'default-product.svg', NOW(), NOW()),
('Orbit RGB Mousepad XL', 'Wide desk mat with stitched edges, soft glide surface, and RGB edge lighting.', 319000, 30, 'default-product.svg', NOW(), NOW()),
('Aegis Controller Dock', 'Dual charging dock for wireless controllers with LED battery indicators.', 279000, 16, 'default-product.svg', NOW(), NOW()),
('Vector Stream Mic', 'USB condenser microphone with cardioid pickup and tap-to-mute control.', 639000, 12, 'default-product.svg', NOW(), NOW());
