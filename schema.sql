-- Schema for Pasqyra app
CREATE DATABASE IF NOT EXISTS pasqyra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pasqyra;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User preferences are added by app migration; avoid duplicate ALTERs here

-- Calendar support for To-Do items
ALTER TABLE todo_items
  ADD COLUMN IF NOT EXISTS due_date DATE NULL;

-- Work In (Partners) support
CREATE TABLE IF NOT EXISTS partners (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(190) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add partner_id columns to scope data per partner
-- Transactions: add partner_id and index (split statements for wider MariaDB compatibility)
ALTER TABLE transactions
  ADD COLUMN IF NOT EXISTS partner_id INT UNSIGNED NULL;
CREATE INDEX IF NOT EXISTS idx_tx_user_partner ON transactions (user_id, partner_id);
-- Optional: add FK manually if needed (some MariaDB versions don't support IF NOT EXISTS on constraints)
-- ALTER TABLE transactions ADD CONSTRAINT fk_tx_partner FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE SET NULL;

-- Materials: add partner_id and index
ALTER TABLE material_purchases
  ADD COLUMN IF NOT EXISTS partner_id INT UNSIGNED NULL;
CREATE INDEX IF NOT EXISTS idx_mp_user_partner ON material_purchases (user_id, partner_id);
-- Optional FK (add once):
-- ALTER TABLE material_purchases ADD CONSTRAINT fk_mat_partner FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE SET NULL;

-- To-Do: add partner_id and index
ALTER TABLE todo_items
  ADD COLUMN IF NOT EXISTS partner_id INT UNSIGNED NULL;
CREATE INDEX IF NOT EXISTS idx_todo_user_partner ON todo_items (user_id, partner_id);
-- Optional FK (add once):
-- ALTER TABLE todo_items ADD CONSTRAINT fk_todo_partner FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE SET NULL;

-- Documents table is created dynamically; ensure partner FK will be added there too

-- Business profile (one per user)
CREATE TABLE IF NOT EXISTS business_profile (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  business_name VARCHAR(255) NOT NULL,
  address VARCHAR(255) NULL,
  year_of_creation YEAR NULL,
  nipti VARCHAR(50) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional branding files (logo, favicon)
ALTER TABLE business_profile
  ADD COLUMN IF NOT EXISTS logo_path VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS favicon_path VARCHAR(255) NULL;

-- Bank IBANs for the business (many per user)
CREATE TABLE IF NOT EXISTS bank_ibans (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  iban VARCHAR(64) NOT NULL,
  bank_name VARCHAR(190) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- To-Do items (simple personal task list per user)
CREATE TABLE IF NOT EXISTS todo_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  notes TEXT NULL,
  is_done TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_done (user_id, is_done)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Materials purchases (track materials bought by the business)
CREATE TABLE IF NOT EXISTS material_purchases (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  material_name VARCHAR(190) NOT NULL,
  unit VARCHAR(50) NOT NULL DEFAULT 'pcs',
  quantity DECIMAL(14,3) NOT NULL,
  unit_price DECIMAL(12,4) NOT NULL,
  total DECIMAL(14,4) NOT NULL,
  purchased_on DATE NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_date (user_id, purchased_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Transactions table (basic skeleton for future features)
CREATE TABLE IF NOT EXISTS transactions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('income','expense') NOT NULL,
  category VARCHAR(100) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  description TEXT NULL,
  occurred_on DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
