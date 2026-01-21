<?php
/**
 * Search Reviews - Search/filter reviews
 * Accepts query string and filters by food_name or restaurant_name
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

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
    $sql = "SELECT r.review_id, r.restaurant_name, r.food_name, r.price, r.rating, r.review_date, r.photo_path,
            (SELECT COUNT(*) FROM review_likes WHERE review_id = r.review_id) as likes_count,
            (SELECT COUNT(*) FROM review_likes WHERE review_id = r.review_id AND user_id = ?) as is_liked,
            (SELECT COUNT(*) FROM review_comments WHERE review_id = r.review_id) as comments_count
            FROM reviews r
            WHERE r.food_name LIKE ? OR r.restaurant_name LIKE ?
            ORDER BY r.review_date DESC, r.review_id DESC";
    
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $stmt->bind_param('sss', $current_user_id, $search_param, $search_param);
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
            'photo_url' => $row['photo_path'] ? 'uploads/' . $row['photo_path'] : null,
            'likes_count' => (int)$row['likes_count'],
            'is_liked' => (bool)$row['is_liked'],
            'comments_count' => (int)$row['comments_count']
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
