<?php
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "your_secret_key_here";

// Get the JWT from the request headers
$headers = getallheaders();
if (isset($headers['Authorization'])) {
    $auth_header = $headers['Authorization'];
    $jwt = str_replace('Bearer ', '', $auth_header);

    try {
        // Decode the JWT
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

        // Access the payload data
        $user_id = $decoded->data->user_id;
        $phone_number = $decoded->data->phone_number;
        $isadmin = $decoded->data->isadmin;

        // Proceed with the request
        http_response_code(200);
        echo json_encode(array(
            "message" => "Access granted.",
            "user_id" => $user_id,
            "phone_number" => $phone_number,
            "isadmin" => $isadmin
        ));
    } catch (Exception $e) {
        // Invalid JWT
        http_response_code(401);
        echo json_encode(array("message" => "Access denied. Invalid token."));
    }
} else {
    // No JWT provided
    http_response_code(401);
    echo json_encode(array("message" => "Access denied. No token provided."));
}
?>