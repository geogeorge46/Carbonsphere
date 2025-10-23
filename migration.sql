-- Migration script to update existing Carbonsphere database
-- Run this if you have existing user data and need to add new columns

USE Carbonsphere;

-- Check and add columns only if they don't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'Carbonsphere' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'user_id') = 0,
    'ALTER TABLE users ADD COLUMN user_id VARCHAR(20) UNIQUE AFTER id;',
    'SELECT "user_id column already exists";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'Carbonsphere' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'first_name') = 0,
    'ALTER TABLE users ADD COLUMN first_name VARCHAR(50) NOT NULL AFTER user_id;',
    'SELECT "first_name column already exists";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'Carbonsphere' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'last_name') = 0,
    'ALTER TABLE users ADD COLUMN last_name VARCHAR(50) NOT NULL AFTER first_name;',
    'SELECT "last_name column already exists";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'Carbonsphere' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'phone') = 0,
    'ALTER TABLE users ADD COLUMN phone VARCHAR(10) UNIQUE AFTER last_name;',
    'SELECT "phone column already exists";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add role column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'Carbonsphere' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role') = 0,
    'ALTER TABLE users ADD COLUMN role ENUM(\'user\', \'seller\', \'admin\') DEFAULT \'user\';',
    'ALTER TABLE users MODIFY COLUMN role ENUM(\'user\', \'seller\', \'admin\') DEFAULT \'user\';'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Generate user_ids for existing users
SET @row_number = 0;
UPDATE users SET user_id = CONCAT('USER', DATE_FORMAT(NOW(), '%Y'), LPAD(@row_number:=@row_number+1, 5, '0')) WHERE user_id IS NULL;

-- Set default values for existing users (you may want to update these manually)
UPDATE users SET first_name = 'FirstName', last_name = 'LastName', phone = '0000000000' WHERE first_name IS NULL OR last_name IS NULL OR phone IS NULL;

-- Make columns NOT NULL after setting defaults
ALTER TABLE users MODIFY COLUMN user_id VARCHAR(20) NOT NULL UNIQUE;
ALTER TABLE users MODIFY COLUMN first_name VARCHAR(50) NOT NULL;
ALTER TABLE users MODIFY COLUMN last_name VARCHAR(50) NOT NULL;
ALTER TABLE users MODIFY COLUMN phone VARCHAR(10) NOT NULL UNIQUE;