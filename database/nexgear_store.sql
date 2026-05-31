CREATE DATABASE IF NOT EXISTS nexgear_store
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE nexgear_store;

-- ─────────────────────────────────────────────────────────────────────────
-- This file is the COMPLETE, authoritative schema (all tables + columns up to
-- and including the Duitku payment columns on `cart`). Importing it gives you
-- the full current schema in one shot.
--
-- NOTE on migrations: after importing this dump you do NOT need to run
-- `php spark migrate` — the schema is already current. The files in
-- app/Database/Migrations/ are the incremental history; running them on top of
-- a fresh import can error ("table already exists") because this dump does not
-- populate CodeIgniter's `migrations` tracking table. For a fresh setup, import
-- this SQL and stop. See README → "Setup Database".
-- ─────────────────────────────────────────────────────────────────────────

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS search_logs;
DROP TABLE IF EXISTS stock_alerts;
DROP TABLE IF EXISTS abandoned_carts;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS coupons;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS wishlists;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS ci_sessions;
SET FOREIGN_KEY_CHECKS = 1;

-- ── Users ───────────────────────────────────────────────
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  totp_secret VARCHAR(64) NULL,
  totp_enabled TINYINT UNSIGNED NOT NULL DEFAULT 0,
  role ENUM('admin', 'staff', 'user') NOT NULL DEFAULT 'user',
  created_at DATETIME NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB;

-- ── Categories ──────────────────────────────────────────
CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(100) NOT NULL,
  description VARCHAR(500) NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uniq_categories_slug (slug)
) ENGINE=InnoDB;

-- ── Products ────────────────────────────────────────────
CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  description TEXT NULL,
  category_id INT UNSIGNED NULL,
  price DECIMAL(12, 2) NOT NULL DEFAULT 0,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  image VARCHAR(255) NOT NULL DEFAULT 'default-product.svg',
  image_secondary VARCHAR(255) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  INDEX idx_products_name (name),
  INDEX idx_products_price (price),
  INDEX idx_products_category (category_id),
  FULLTEXT INDEX ft_products_search (name, description),
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Product Images (multi-image gallery) ────────────────
CREATE TABLE product_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  path VARCHAR(255) NOT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  INDEX idx_product_images_product_order (product_id, sort_order),
  CONSTRAINT fk_product_images_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Cart (Orders) ───────────────────────────────────────
CREATE TABLE cart (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  status ENUM('active', 'checked_out', 'paid', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'active',
  total DECIMAL(12, 2) NOT NULL DEFAULT 0,
  coupon_code VARCHAR(60) NULL,
  discount DECIMAL(12, 2) NOT NULL DEFAULT 0,
  payment_status VARCHAR(20) NOT NULL DEFAULT 'unpaid',
  payment_ref VARCHAR(64) NULL,
  payment_token VARCHAR(100) NULL,
  payment_method VARCHAR(50) NULL,
  paid_at DATETIME NULL,
  shipping_name VARCHAR(120) NULL,
  shipping_phone VARCHAR(20) NULL,
  shipping_address VARCHAR(500) NULL,
  shipping_city VARCHAR(100) NULL,
  shipping_postal_code VARCHAR(10) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_cart_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  INDEX idx_cart_user_status_created (user_id, status, created_at),
  INDEX idx_cart_status_created (status, created_at),
  INDEX idx_cart_payment_ref (payment_ref)
) ENGINE=InnoDB;

-- ── Cart Items ──────────────────────────────────────────
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

-- ── Sessions (for DatabaseHandler in production) ────────
CREATE TABLE ci_sessions (
  id VARCHAR(128) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  data BLOB NOT NULL,
  PRIMARY KEY (id),
  INDEX ci_sessions_timestamp (timestamp)
) ENGINE=InnoDB;

-- ── Wishlists ───────────────────────────────────────────
CREATE TABLE wishlists (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uniq_wishlist_user_product (user_id, product_id),
  CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Reviews ─────────────────────────────────────────────
CREATE TABLE reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  title VARCHAR(160) NULL,
  body TEXT NULL,
  verified_purchase TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uniq_review_user_product (user_id, product_id),
  INDEX idx_reviews_product (product_id),
  CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Coupons ─────────────────────────────────────────────
CREATE TABLE coupons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(60) NOT NULL,
  type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
  value DECIMAL(12, 2) NOT NULL DEFAULT 0,
  min_total DECIMAL(12, 2) NOT NULL DEFAULT 0,
  max_uses INT UNSIGNED NULL,
  used INT UNSIGNED NOT NULL DEFAULT 0,
  expires_at DATETIME NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uniq_coupons_code (code)
) ENGINE=InnoDB;

-- ── Addresses (saved shipping book) ─────────────────────
CREATE TABLE addresses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  label VARCHAR(60) NULL,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  address VARCHAR(500) NOT NULL,
  city VARCHAR(100) NOT NULL,
  postal_code VARCHAR(10) NOT NULL,
  is_default TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  INDEX idx_addresses_user_default (user_id, is_default),
  CONSTRAINT fk_addresses_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Newsletter subscribers ──────────────────────────────
CREATE TABLE newsletter_subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(160) NOT NULL,
  confirmed TINYINT UNSIGNED NOT NULL DEFAULT 0,
  token VARCHAR(64) NULL,
  unsubscribed_at DATETIME NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uniq_newsletter_email (email)
) ENGINE=InnoDB;

-- ── Contact messages ────────────────────────────────────
CREATE TABLE contact_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  subject VARCHAR(160) NULL,
  message TEXT NOT NULL,
  status ENUM('new','read','archived') NOT NULL DEFAULT 'new',
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  INDEX idx_contact_messages_status (status)
) ENGINE=InnoDB;

-- ── Audit logs ──────────────────────────────────────────
CREATE TABLE audit_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  actor_label VARCHAR(120) NULL,
  action VARCHAR(80) NOT NULL,
  target_type VARCHAR(60) NULL,
  target_id INT UNSIGNED NULL,
  meta TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  INDEX idx_audit_action (action),
  INDEX idx_audit_target (target_type, target_id),
  INDEX idx_audit_created (created_at)
) ENGINE=InnoDB;

-- ── Abandoned cart snapshots ────────────────────────────
CREATE TABLE abandoned_carts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  items_json TEXT NOT NULL,
  total DECIMAL(12, 2) NOT NULL DEFAULT 0,
  item_count INT UNSIGNED NOT NULL DEFAULT 0,
  last_activity_at DATETIME NOT NULL,
  reminded_at DATETIME NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uniq_abandoned_carts_user (user_id),
  INDEX idx_abandoned_carts_activity (last_activity_at, reminded_at),
  CONSTRAINT fk_abandoned_carts_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Stock alerts (notify-when-back-in-stock) ────────────
CREATE TABLE stock_alerts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  email VARCHAR(160) NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  notified_at DATETIME NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uniq_stock_alerts_email_product (email, product_id),
  INDEX idx_stock_alerts_product_notify (product_id, notified_at),
  CONSTRAINT fk_stock_alerts_product FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Search logs (B10 trending searches) ─────────────────
CREATE TABLE search_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  query VARCHAR(120) NOT NULL,
  count INT UNSIGNED NOT NULL DEFAULT 1,
  last_seen_at DATETIME NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uniq_search_logs_query (query),
  INDEX idx_search_logs_count_seen (count, last_seen_at)
) ENGINE=InnoDB;

-- ── Seed Data ───────────────────────────────────────────
INSERT INTO categories (name, slug, description, sort_order, created_at, updated_at) VALUES
('Keyboards',   'keyboards',   'Mechanical & low-profile decks.',    1, NOW(), NOW()),
('Mice',        'mice',        'Wireless precision and ergo.',       2, NOW(), NOW()),
('Headsets',    'headsets',    'Surround audio for gaming.',         3, NOW(), NOW()),
('Mousepads',   'mousepads',   'Surfaces with lighting.',            4, NOW(), NOW()),
('Microphones', 'microphones', 'Streaming-grade vocal capture.',     5, NOW(), NOW()),
('Controllers', 'controllers', 'Pads and docking stations.',         6, NOW(), NOW());

INSERT INTO coupons (code, type, value, min_total, max_uses, used, expires_at, created_at, updated_at) VALUES
('WELCOME10',   'percent', 10,        0,      NULL, 0, NULL, NOW(), NOW()),
('NEXGEAR50K',  'fixed',   50000, 500000,      100, 0, NULL, NOW(), NOW());

INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES
('Admin NexGear', 'admin@nexgear.my.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC1fRmtn3MowM9ATQeJe', 'admin', NOW(), NOW()),
('Demo User', 'user@nexgear.my.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC1fRmtn3MowM9ATQeJe', 'user', NOW(), NOW());

INSERT INTO products (name, description, category_id, price, stock, image, created_at, updated_at) VALUES
('Nebula K87 Mechanical Keyboard', 'Compact hot-swappable keyboard with RGB lighting and tactile switches.',  1, 899000, 14, 'default-product.svg', NOW(), NOW()),
('Pulsefire Ultra Mouse',          'Lightweight wireless mouse with low-latency sensor and textured side grip.', 2, 549000, 22, 'default-product.svg', NOW(), NOW()),
('EchoStrike 7.1 Headset',         'Closed-back headset with virtual surround, soft pads, and detachable mic.', 3, 729000, 18, 'default-product.svg', NOW(), NOW()),
('Orbit RGB Mousepad XL',          'Wide desk mat with stitched edges, soft glide surface, and RGB edge lighting.', 4, 319000, 30, 'default-product.svg', NOW(), NOW()),
('Aegis Controller Dock',          'Dual charging dock for wireless controllers with LED battery indicators.',  6, 279000, 16, 'default-product.svg', NOW(), NOW()),
('Vector Stream Mic',              'USB condenser microphone with cardioid pickup and tap-to-mute control.',    5, 639000, 12, 'default-product.svg', NOW(), NOW());
