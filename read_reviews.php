<?php
/**
 * Read Reviews - Fetch all reviews
 * Returns all records ordered by date (newest first)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Prepare SQL query to fetch all reviews ordered by date (newest first)
    $sql = "SELECT review_id, restaurant_name, food_name, price, rating, review_date, photo_path 
            FROM reviews 
            ORDER BY review_date DESC, review_id DESC";
    
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $reviews = [];
    
    while ($row = $result->fetch_assoc()) {
        // Convert string values to appropriate types
        $reviews[] = [
            'review_id' => (int)$row['review_id'],
            'restaurant_name' => $row['restaurant_name'],
            'food_name' => $row['food_name'],
            'price' => (int)$row['price'],
            'rating' => (int)$row['rating'],
            'review_date' => $row['review_date'],
            'photo_path' => $row['photo_path'],
            'photo_url' => $row['photo_path'] ? 'uploads/' . $row['photo_path'] : null
        ];
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($reviews),
        'data' => $reviews
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
