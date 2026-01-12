<?php
/**
 * Create Review - Add new food review
 * Handles POST data and image file upload
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Validate required fields
    $required_fields = ['restaurant_name', 'food_name', 'price', 'rating', 'review_date'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Field '{$field}' is required");
        }
    }

    // Sanitize and validate input
    $restaurant_name = trim($_POST['restaurant_name']);
    $food_name = trim($_POST['food_name']);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_INT);
    $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT);
    $review_date = trim($_POST['review_date']);

    // Validate price
    if ($price === false || $price < 0) {
        throw new Exception('Invalid price value');
    }

    // Validate rating (1-5)
    if ($rating === false || $rating < 1 || $rating > 5) {
        throw new Exception('Rating must be between 1 and 5');
    }

    // Validate date format
    $date_obj = DateTime::createFromFormat('Y-m-d', $review_date);
    if (!$date_obj || $date_obj->format('Y-m-d') !== $review_date) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }

    // Handle file upload
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        
        // Create uploads directory if it doesn't exist
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

        $photo_path = $new_filename;
    }

    // Prepare SQL statement
    $sql = "INSERT INTO reviews (restaurant_name, food_name, price, rating, review_date, photo_path) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $stmt->bind_param('ssiiss', $restaurant_name, $food_name, $price, $rating, $review_date, $photo_path);

    if ($stmt->execute()) {
        $review_id = $stmt->insert_id;
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Review created successfully',
            'data' => [
                'review_id' => $review_id,
                'restaurant_name' => $restaurant_name,
                'food_name' => $food_name,
                'price' => $price,
                'rating' => $rating,
                'review_date' => $review_date,
                'photo_path' => $photo_path
            ]
        ]);
    } else {
        throw new Exception('Failed to create review: ' . $stmt->error);
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
