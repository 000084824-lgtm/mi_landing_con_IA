CREATE DATABASE IF NOT EXISTS landing_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE landing_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_date DATE NOT NULL,
    customer_name VARCHAR(120) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    description VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sale_date (sale_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ejemplo de carga inicial de ventas:
-- INSERT INTO sales (sale_date, customer_name, total_amount, description) VALUES
-- ('2026-07-09', 'Ana', 180.00, 'Ramo sorpresa');
