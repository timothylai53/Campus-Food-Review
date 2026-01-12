<?php
/**
 * Update Review - Update existing review
 * Accepts review_id and new data to update
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Accept both POST and PUT methods
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Validate review_id
    if (!isset($_POST['review_id']) || empty($_POST['review_id'])) {
        throw new Exception('Review ID is required');
    }

    $review_id = filter_var($_POST['review_id'], FILTER_VALIDATE_INT);
    
    if ($review_id === false || $review_id <= 0) {
        throw new Exception('Invalid review ID');
    }

    // Check if review exists
    $check_sql = "SELECT review_id, photo_path FROM reviews WHERE review_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('i', $review_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Review not found');
    }

    $existing_review = $result->fetch_assoc();
    $check_stmt->close();

    // Build dynamic update query based on provided fields
    $update_fields = [];
    $params = [];
    $param_types = '';

    // Check and validate each field
    if (isset($_POST['restaurant_name']) && !empty(trim($_POST['restaurant_name']))) {
        $update_fields[] = 'restaurant_name = ?';
        $params[] = trim($_POST['restaurant_name']);
        $param_types .= 's';
    }

    if (isset($_POST['food_name']) && !empty(trim($_POST['food_name']))) {
        $update_fields[] = 'food_name = ?';
        $params[] = trim($_POST['food_name']);
        $param_types .= 's';
    }

    if (isset($_POST['price'])) {
        $price = filter_var($_POST['price'], FILTER_VALIDATE_INT);
        if ($price === false || $price < 0) {
            throw new Exception('Invalid price value');
        }
        $update_fields[] = 'price = ?';
        $params[] = $price;
        $param_types .= 'i';
    }

    if (isset($_POST['rating'])) {
        $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT);
        if ($rating === false || $rating < 1 || $rating > 5) {
            throw new Exception('Rating must be between 1 and 5');
        }
        $update_fields[] = 'rating = ?';
        $params[] = $rating;
        $param_types .= 'i';
    }

    if (isset($_POST['review_date']) && !empty(trim($_POST['review_date']))) {
        $review_date = trim($_POST['review_date']);
        $date_obj = DateTime::createFromFormat('Y-m-d', $review_date);
        if (!$date_obj || $date_obj->format('Y-m-d') !== $review_date) {
            throw new Exception('Invalid date format. Use YYYY-MM-DD');
        }
        $update_fields[] = 'review_date = ?';
        $params[] = $review_date;
        $param_types .= 's';
    }

    // Handle new photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['photo']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and GIF allowed');
        }

        // Validate file size (max 5MB)
        $max_size = 5 * 1024 * 1024;
        if ($_FILES['photo']['size'] > $max_size) {
            throw new Exception('File size exceeds 5MB limit');
        }

        // Generate unique filename
        $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $new_filename = 'review_' . uniqid() . '_' . time() . '.' . $file_extension;
        $target_path = $upload_dir . $new_filename;

        // Move uploaded file
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
            throw new Exception('Failed to upload file');
        }

        // Delete old photo if exists
        if ($existing_review['photo_path'] && file_exists($upload_dir . $existing_review['photo_path'])) {
            unlink($upload_dir . $existing_review['photo_path']);
        }

        $update_fields[] = 'photo_path = ?';
        $params[] = $new_filename;
        $param_types .= 's';
    }

    // Check if there are fields to update
    if (empty($update_fields)) {
        throw new Exception('No fields to update');
    }

    // Add review_id to params
    $params[] = $review_id;
    $param_types .= 'i';

    // Build and execute update query
    $sql = "UPDATE reviews SET " . implode(', ', $update_fields) . " WHERE review_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    // Bind parameters dynamically
    $stmt->bind_param($param_types, ...$params);

    if ($stmt->execute()) {
        // Fetch updated review
        $fetch_sql = "SELECT * FROM reviews WHERE review_id = ?";
        $fetch_stmt = $conn->prepare($fetch_sql);
        $fetch_stmt->bind_param('i', $review_id);
        $fetch_stmt->execute();
        $updated_review = $fetch_stmt->get_result()->fetch_assoc();
        $fetch_stmt->close();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => [
                'review_id' => (int)$updated_review['review_id'],
                'restaurant_name' => $updated_review['restaurant_name'],
                'food_name' => $updated_review['food_name'],
                'price' => (int)$updated_review['price'],
                'rating' => (int)$updated_review['rating'],
                'review_date' => $updated_review['review_date'],
                'photo_path' => $updated_review['photo_path']
            ]
        ]);
    } else {
        throw new Exception('Failed to update review: ' . $stmt->error);
    }

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
