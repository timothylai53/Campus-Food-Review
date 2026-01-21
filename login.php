<?php
/**
 * Login - User Authentication
 * Authenticates users from database
 */

// Start session
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

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
    // Get username and password from POST data
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validate input
    if (empty($username) || empty($password)) {
        throw new Exception('Username and password are required');
    }

    // Check credentials in database
    $sql = "SELECT user_id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password (plain text for now - in production use password_verify!)
        if ($password === $user['password']) {
            // Update last login
            $update_sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user['user_id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Set session variables
            $_SESSION['user_logged_in'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['username']; // Use username as user_id for compatibility
            $_SESSION['login_time'] = time();

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'username' => $user['username'],
                    'logged_in' => true,
                    'session_id' => session_id()
                ]
            ]);
        } else {
            // Invalid password
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid username or password'
            ]);
        }
    } else {
        // User not found
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
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
