<?php
/**
 * My Reviews - Fetch reviews for a specific user
 * Accepts user_id from GET parameter
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

// Get user_id from query parameter
$user_id = isset($_GET['user_id']) ? trim($_GET['user_id']) : null;

if (empty($user_id)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'user_id parameter is required'
    ]);
    exit();
}

try {
    
    // Prepare SQL query to fetch reviews for current user
    $sql = "SELECT review_id, user_id, restaurant_name, food_name, review_text, price, rating, location, review_date, created_at, photo_path 
            FROM reviews 
            WHERE user_id = ?
            ORDER BY review_date DESC, review_id DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $reviews = [];
    
    while ($row = $result->fetch_assoc()) {
        // Convert string values to appropriate types
        $reviews[] = [
            'review_id' => (int)$row['review_id'],
            'user_id' => $row['user_id'],
            'restaurant_name' => $row['restaurant_name'],
            'food_name' => $row['food_name'],
            'review_text' => $row['review_text'],
            'price' => (float)$row['price'],
            'rating' => (int)$row['rating'],
            'location' => $row['location'],
            'review_date' => $row['review_date'],
            'created_at' => $row['created_at'],
            'photo_path' => $row['photo_path'],
            'photo_url' => $row['photo_path'] ? 'uploads/' . $row['photo_path'] : null
        ];
    }

    $stmt->close();

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
