-- SQL query to create the reviews table for Campus Food Reviewer app
-- Database: campus_food_reviewer

CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_name VARCHAR(255) NOT NULL,
    food_name VARCHAR(255) NOT NULL,
    price INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_date DATE NOT NULL,
    photo_path VARCHAR(500) DEFAULT NULL,
    UNIQUE KEY unique_review_id (review_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
