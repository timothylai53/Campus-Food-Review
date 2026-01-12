<?php
/**
 * API Proxy - TheMealDB Integration
 * Fetches random meal data from TheMealDB API and returns simplified JSON
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // TheMealDB API endpoint
    $api_url = 'https://www.themealdb.com/api/json/v1/1/random.php';

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'User-Agent: Campus-Food-Reviewer/1.0'
    ]);

    // Execute cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        throw new Exception('API request failed: ' . $error_msg);
    }

    // Get HTTP status code
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check HTTP status
    if ($http_code !== 200) {
        throw new Exception('API returned status code: ' . $http_code);
    }

    // Decode JSON response
    $data = json_decode($response, true);

    // Check if JSON decoding was successful
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from API');
    }

    // Validate response structure
    if (!isset($data['meals']) || !is_array($data['meals']) || empty($data['meals'])) {
        throw new Exception('Invalid API response structure');
    }

    // Get the first meal from the response
    $meal = $data['meals'][0];

    // Extract required fields
    $meal_name = isset($meal['strMeal']) ? $meal['strMeal'] : null;
    $instructions = isset($meal['strInstructions']) ? $meal['strInstructions'] : null;

    // Validate extracted data
    if ($meal_name === null || $instructions === null) {
        throw new Exception('Required fields missing from API response');
    }

    // Return clean JSON response with only the two required fields
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => [
            'strMeal' => $meal_name,
            'strInstructions' => $instructions
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
