<?php
/**
 * Register - Create new user account
 * Saves user to database
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
    // Get form data
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    // Validate input
    if (empty($username) || empty($password)) {
        throw new Exception('Username and password are required');
    }

    if (strlen($username) < 3) {
        throw new Exception('Username must be at least 3 characters');
    }

    if (strlen($password) < 4) {
        throw new Exception('Password must be at least 4 characters');
    }

    // Check if username already exists
    $check_sql = "SELECT user_id FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception('Username already exists');
    }
    $check_stmt->close();

    // Insert new user (storing password as plain text for simplicity - in production, use password_hash!)
    $insert_sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sss", $username, $password, $email);

    if ($insert_stmt->execute()) {
        $insert_stmt->close();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'username' => $username
            ]
        ]);
    } else {
        throw new Exception('Failed to create user');
    }

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
