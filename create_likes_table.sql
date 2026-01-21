-- SQL query to create the review_likes table
-- Database: campus_food_reviewer

CREATE TABLE IF NOT EXISTS review_likes (
    like_id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    user_id VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_review_like (review_id, user_id),
    FOREIGN KEY (review_id) REFERENCES reviews(review_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;