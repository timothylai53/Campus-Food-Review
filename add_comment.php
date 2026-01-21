<?php
/**
 * Add Comment API
 * Handles adding a new comment to a review
 */

header('Content-Type: application/json');
require_once 'check_session.php'; // Ensures user is logged in
require_once 'db_connect.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['review_id']) || !isset($input['comment_text'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Review ID and comment text are required']);
    exit();
}

$review_id = (int)$input['review_id'];
$comment_text = trim($input['comment_text']);
$user_id = $_SESSION['user_id'];

if (empty($comment_text)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit();
}

try {
    $sql = "INSERT INTO review_comments (review_id, user_id, comment_text) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $review_id, $user_id, $comment_text);
    
    if ($stmt->execute()) {
        // Fetch the new comment to return it
        $comment_id = $stmt->insert_id;
        $created_at = date('Y-m-d H:i:s'); // Appropriation of current time
        
        echo json_encode([
            'success' => true,
            'message' => 'Comment added successfully',
            'data' => [
                'comment_id' => $comment_id,
                'user_id' => $user_id,
                'comment_text' => $comment_text,
                'created_at' => $created_at
            ]
        ]);
    } else {
        throw new Exception('Failed to insert comment');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>