<?php
// Set the header to return JSON data
header('Content-Type: application/json');

// Retrieve the JSON data from the request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Check if the JSON data is valid
if (json_last_error() === JSON_ERROR_NONE) {
    // Prepare the response array
    $response = [];

    // Populate the response with form fields and their values
    foreach ($data as $key => $value) {
        // Ensure data is properly escaped to prevent XSS
        $response[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
} else {
    // If the JSON data is invalid, return an error response
    $response = [
        'status' => 'error',
        'message' => 'Invalid JSON data'
    ];
}

// Return the JSON response
echo json_encode($response);
?>
