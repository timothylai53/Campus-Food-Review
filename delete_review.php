<?php
/**
 * Delete Review - Remove a review record
 * Accepts review_id and deletes the record
 * PROTECTED: Requires user authentication
 */

// Check if user is logged in (must be first, before any output)
require_once 'check_session.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Accept both POST and DELETE methods
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get review_id from POST data or query string
    $review_id = null;
    
    if (isset($_POST['review_id'])) {
        $review_id = $_POST['review_id'];
    } elseif (isset($_GET['review_id'])) {
        $review_id = $_GET['review_id'];
    } else {
        throw new Exception('Review ID is required');
    }

    $review_id = filter_var($review_id, FILTER_VALIDATE_INT);
    
    if ($review_id === false || $review_id <= 0) {
        throw new Exception('Invalid review ID');
    }

    // Check if review exists and get photo path
    $check_sql = "SELECT review_id, photo_path FROM reviews WHERE review_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    
    if (!$check_stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $check_stmt->bind_param('i', $review_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Review not found');
    }

    $review = $result->fetch_assoc();
    $photo_path = $review['photo_path'];
    $check_stmt->close();

    // Delete the review from database
    $delete_sql = "DELETE FROM reviews WHERE review_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);

    if (!$delete_stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $delete_stmt->bind_param('i', $review_id);

    if ($delete_stmt->execute()) {
        // Delete associated photo file if exists
        if ($photo_path) {
            $file_path = 'uploads/' . $photo_path;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Review deleted successfully',
            'data' => [
                'review_id' => $review_id
            ]
        ]);
    } else {
        throw new Exception('Failed to delete review: ' . $delete_stmt->error);
    }

    $delete_stmt->close();

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
