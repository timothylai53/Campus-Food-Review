<?php
/**
 * Toggle Like API
 * Handles liking/unliking a review
 */

header('Content-Type: application/json');
require_once 'check_session.php'; // Ensures user is logged in
require_once 'db_connect.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['review_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Review ID is required']);
    exit();
}

$review_id = (int)$input['review_id'];
$user_id = $_SESSION['user_id'];

try {
    // Check if like exists
    $checkSql = "SELECT like_id FROM review_likes WHERE review_id = ? AND user_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("is", $review_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $liked = false;
    
    if ($result->num_rows > 0) {
        // Already liked, so UNLIKE
        $deleteSql = "DELETE FROM review_likes WHERE review_id = ? AND user_id = ?";
        $delStmt = $conn->prepare($deleteSql);
        $delStmt->bind_param("is", $review_id, $user_id);
        $delStmt->execute();
        $liked = false;
    } else {
        // Not liked, so LIKE
        $insertSql = "INSERT INTO review_likes (review_id, user_id) VALUES (?, ?)";
        $insStmt = $conn->prepare($insertSql);
        $insStmt->bind_param("is", $review_id, $user_id);
        $insStmt->execute();
        $liked = true;
    }

    // Get new count
    $countSql = "SELECT COUNT(*) as count FROM review_likes WHERE review_id = ?";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("i", $review_id);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $countRow = $countResult->fetch_assoc();
    $newCount = $countRow['count'];

    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'likes_count' => $newCount
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>