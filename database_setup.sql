-- Create database
CREATE DATABASE IF NOT EXISTS Carbonsphere;

-- Use the database
USE Carbonsphere;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(10) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'seller', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample user (optional)
-- INSERT INTO users (user_id, first_name, last_name, email, phone, password, role) VALUES ('USER202500001', 'Admin', 'User', 'admin@example.com', '9876543210', '$2y$10$examplehashedpassword', 'admin');

-- If you have existing data, run these ALTER TABLE statements to add new columns:
-- ALTER TABLE users ADD COLUMN user_id VARCHAR(20) NOT NULL UNIQUE AFTER id;
-- ALTER TABLE users ADD COLUMN first_name VARCHAR(50) NOT NULL AFTER user_id;
-- ALTER TABLE users ADD COLUMN last_name VARCHAR(50) NOT NULL AFTER first_name;
-- ALTER TABLE users ADD COLUMN phone VARCHAR(10) NOT NULL UNIQUE AFTER last_name;
-- ALTER TABLE users MODIFY COLUMN role ENUM('user', 'seller', 'admin') DEFAULT 'user';