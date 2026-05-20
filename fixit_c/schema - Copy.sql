-- FixIt Database Schema
-- Database: fixitC_db

CREATE DATABASE IF NOT EXISTS fixitC_db;
USE fixitC_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('citizen', 'admin', 'superadmin') DEFAULT 'citizen',
    barangay VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Reports Table
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    issue_type VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    severity ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Low',
    status ENUM('Open', 'Acknowledged', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Open',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    address VARCHAR(255),
    upvotes INT DEFAULT 0,
    assigned_department VARCHAR(100) DEFAULT 'Unassigned',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed Data (Run these to create the default admin)
-- Password for admin@fixit.com is 'admin123'
-- INSERT INTO users (full_name, email, password, role) VALUES ('System Admin', 'admin@fixit.com', '$2y$10$8v8zJj6yS/W5zM9p4v7e.eW7O9f6O9f6O9f6O9f6O9f6O9f6O9f6O', 'admin');

-- 3. Report Images Table
CREATE TABLE IF NOT EXISTS report_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Status Logs Table
CREATE TABLE IF NOT EXISTS status_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    changed_by INT NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50),
    notes TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- 5. Upvotes Table
CREATE TABLE IF NOT EXISTS upvotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (report_id, user_id),
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Report Comments Table
CREATE TABLE IF NOT EXISTS report_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
