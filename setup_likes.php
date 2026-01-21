<?php
require_once 'db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS review_likes (
    like_id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    user_id VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_review_like (review_id, user_id),
    FOREIGN KEY (review_id) REFERENCES reviews(review_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table review_likes created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}
$conn->close();
?>