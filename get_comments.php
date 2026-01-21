<?php
/**
 * Get Comments API
 * Fetches comments for a specific review
 */

header('Content-Type: application/json');
require_once 'db_connect.php';

if (!isset($_GET['review_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Review ID is required']);
    exit();
}

$review_id = (int)$_GET['review_id'];

try {
    $sql = "SELECT comment_id, user_id, comment_text, created_at 
            FROM review_comments 
            WHERE review_id = ? 
            ORDER BY created_at ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $review_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $comments
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>