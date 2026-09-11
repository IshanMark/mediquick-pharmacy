-- MediQuick Pharmacy — MySQL schema (XAMPP / MariaDB compatible)
-- Import in phpMyAdmin: Import > choose this file > Go

CREATE DATABASE IF NOT EXISTS mediquick_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mediquick_db;

-- One table for all three roles. Roles are fixed, so an ENUM is enough.
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role          ENUM('customer','staff','admin') NOT NULL DEFAULT 'customer',
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  phone         VARCHAR(15)  NOT NULL,
  password_hash VARCHAR(255) NOT NULL,              -- password_hash() output
  address       VARCHAR(255) NULL,
  city          VARCHAR(60)  NULL,
  status        ENUM('active','suspended') NOT NULL DEFAULT 'active',
  last_login_at DATETIME NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(80)  NOT NULL UNIQUE,
  slug        VARCHAR(80)  NOT NULL UNIQUE,
  description VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE products (
  id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id           INT UNSIGNED NOT NULL,
  name                  VARCHAR(150) NOT NULL,
  generic_name          VARCHAR(150) NULL,          -- e.g. Amoxicillin
  brand                 VARCHAR(100) NULL,
  description           TEXT NULL,
  dosage_guidelines     TEXT NULL,
  safety_info           TEXT NULL,
  price                 DECIMAL(10,2) NOT NULL CHECK (price >= 0),   -- LKR
  stock_qty             INT NOT NULL DEFAULT 0 CHECK (stock_qty >= 0),
  reorder_level         INT NOT NULL DEFAULT 10,
  requires_prescription TINYINT(1) NOT NULL DEFAULT 0,               -- 1 = Rx only
  expiry_date           DATE NULL,
  image                 VARCHAR(255) NULL,
  is_active             TINYINT(1) NOT NULL DEFAULT 1,
  created_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id),
  INDEX idx_products_name (name),
  INDEX idx_products_generic (generic_name)
) ENGINE=InnoDB;

CREATE TABLE prescriptions (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id   INT UNSIGNED NOT NULL,
  file_path     VARCHAR(255) NOT NULL,              -- random name inside uploads/rx/
  doctor_name   VARCHAR(100) NULL,
  doctor_reg_no VARCHAR(20)  NULL,                  -- SLMC registration number
  notes         VARCHAR(255) NULL,
  status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  reviewed_by   INT UNSIGNED NULL,                  -- staff user
  review_note   VARCHAR(255) NULL,
  reviewed_at   DATETIME NULL,
  uploaded_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES users(id),
  FOREIGN KEY (reviewed_by) REFERENCES users(id),
  INDEX idx_rx_status (status)
) ENGINE=InnoDB;

CREATE TABLE orders (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_no         VARCHAR(20) NOT NULL UNIQUE,     -- e.g. MQ-260914-0001
  customer_id      INT UNSIGNED NOT NULL,
  prescription_id  INT UNSIGNED NULL,               -- required when any item is Rx only
  status           ENUM('pending_verification','awaiting_payment','confirmed',
                        'packed','dispatched','delivered','cancelled')
                   NOT NULL DEFAULT 'awaiting_payment',
  payment_method   ENUM('card','cod') NOT NULL,
  payment_status   ENUM('unpaid','paid','failed') NOT NULL DEFAULT 'unpaid',
  subtotal         DECIMAL(10,2) NOT NULL,
  delivery_fee     DECIMAL(10,2) NOT NULL DEFAULT 0,
  total            DECIMAL(10,2) NOT NULL,
  delivery_address VARCHAR(255) NOT NULL,
  delivery_city    VARCHAR(60)  NOT NULL,
  contact_phone    VARCHAR(15)  NOT NULL,
  handled_by       INT UNSIGNED NULL,               -- staff who approved / packed
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id)     REFERENCES users(id),
  FOREIGN KEY (prescription_id) REFERENCES prescriptions(id),
  FOREIGN KEY (handled_by)      REFERENCES users(id),
  INDEX idx_orders_status (status)
) ENGINE=InnoDB;

-- unit_price is copied at order time so later price changes don't rewrite history.
CREATE TABLE order_items (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id   INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity   INT NOT NULL CHECK (quantity > 0),
  unit_price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id)   REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE payments (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id    INT UNSIGNED NOT NULL,
  gateway     ENUM('payhere','cod') NOT NULL,
  gateway_ref VARCHAR(100) NULL,                    -- PayHere payment_id
  amount      DECIMAL(10,2) NOT NULL,
  status      ENUM('success','failed','pending') NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB;

CREATE TABLE inquiries (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED NULL,                    -- NULL = guest from contact form
  name        VARCHAR(100) NOT NULL,
  email       VARCHAR(150) NOT NULL,
  subject     VARCHAR(150) NOT NULL,
  message     TEXT NOT NULL,
  status      ENUM('open','answered','closed') NOT NULL DEFAULT 'open',
  reply       TEXT NULL,
  replied_by  INT UNSIGNED NULL,
  replied_at  DATETIME NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES users(id),
  FOREIGN KEY (replied_by)  REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  title      VARCHAR(120) NOT NULL,
  message    VARCHAR(255) NOT NULL,
  link       VARCHAR(255) NULL,
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_notif_user (user_id, is_read)
) ENGINE=InnoDB;

CREATE TABLE articles (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  author_id    INT UNSIGNED NOT NULL,
  title        VARCHAR(150) NOT NULL,
  slug         VARCHAR(150) NOT NULL UNIQUE,
  body         TEXT NOT NULL,
  image        VARCHAR(255) NULL,
  published_at DATETIME NULL,                       -- NULL = draft
  FOREIGN KEY (author_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE testimonials (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED NOT NULL,
  rating      TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  message     VARCHAR(500) NOT NULL,
  is_approved TINYINT(1) NOT NULL DEFAULT 0,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NULL,
  action     VARCHAR(60) NOT NULL,                  -- e.g. rx.approve, product.update
  entity     VARCHAR(40) NULL,
  entity_id  INT UNSIGNED NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_audit_time (created_at)
) ENGINE=InnoDB;

-- Starter data
INSERT INTO categories (name, slug, description) VALUES
 ('Prescription Medicines', 'prescription', 'Rx-only medicines, need a valid prescription'),
 ('Over-the-Counter',       'otc',          'Pain relief, cold & flu, allergy'),
 ('Wellness & Vitamins',    'wellness',     'Vitamins, supplements, immunity'),
 ('Personal Care',          'personal-care','Skin, hair, oral and baby care');

-- First admin: register through the site, then run:
-- UPDATE users SET role = 'admin' WHERE email = 'you@example.com';
