-- Simple fix for role column
USE Carbonsphere;

-- Add role column if it doesn't exist
ALTER TABLE users ADD COLUMN role ENUM('user', 'seller', 'admin') DEFAULT 'user';

-- Verify it was added
DESCRIBE users;