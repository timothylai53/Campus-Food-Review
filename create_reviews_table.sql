-- SQL query to create the reviews table for Campus Food Reviewer app
-- Database: campus_food_reviewer

CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(100) NOT NULL,
    restaurant_name VARCHAR(255) NOT NULL,
    food_name VARCHAR(255) NOT NULL,
    review_text TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    location VARCHAR(255) DEFAULT NULL,
    review_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    photo_path VARCHAR(500) DEFAULT NULL,
    UNIQUE KEY unique_review_id (review_id),
    INDEX idx_user_id (user_id),
    INDEX idx_rating (rating),
    INDEX idx_review_date (review_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
