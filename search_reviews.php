<?php
/**
 * Search Reviews - Search/filter reviews
 * Accepts query string and filters by food_name or restaurant_name
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
    // Get search query from URL parameter
    if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
        throw new Exception('Search query parameter "q" is required');
    }

    $search_query = trim($_GET['q']);
    $search_param = '%' . $search_query . '%';

    // Prepare SQL query to search in food_name and restaurant_name
    $sql = "SELECT review_id, restaurant_name, food_name, price, rating, review_date, photo_path 
            FROM reviews 
            WHERE food_name LIKE ? OR restaurant_name LIKE ?
            ORDER BY review_date DESC, review_id DESC";
    
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $stmt->bind_param('ss', $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();

    $reviews = [];
    
    while ($row = $result->fetch_assoc()) {
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
        'query' => $search_query,
        'count' => count($reviews),
        'data' => $reviews
    ]);

    $stmt->close();

} catch (Exception $e) {
    http_response_code(400);
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
