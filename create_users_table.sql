-- Create Users Table
-- Run this SQL in phpMyAdmin to create the users table

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert a default test user (password: 1234)
INSERT INTO users (username, password, email) 
VALUES ('user', '1234', 'user@example.com')
ON DUPLICATE KEY UPDATE username=username;
