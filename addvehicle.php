<?php
// Include the database connection class
require_once 'connecttodb.php';

// Set header to allow cross-origin resource sharing (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try {
    // Create a new instance of the Database class
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => $e->getMessage()));
    exit(); // Terminate the script if connection fails
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get POST data
    $data = json_decode(file_get_contents("php://input"));

    // Validate input
    if (isset($data->car_name) && isset($data->license_plate)) {
        $car_name = trim($data->car_name);
        $license_plate = trim($data->license_plate);

        try {
            // Insert the new vehicle into the database
            $query = "INSERT INTO vehicles (car_name, license_plate) VALUES (:car_name, :license_plate)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':car_name', $car_name);
            $stmt->bindParam(':license_plate', $license_plate);

            if ($stmt->execute()) {
                // Vehicle added successfully
                http_response_code(201); // 201 Created
                echo json_encode(array("message" => "Vehicle added successfully."));
            } else {
                // Failed to add vehicle
                http_response_code(500);
                echo json_encode(array("message" => "Unable to add vehicle."));
            }
        } catch (PDOException $e) {
            // Handle duplicate license plate or other errors
            if ($e->getCode() == 23000) { // Duplicate entry error code
                http_response_code(400);
                echo json_encode(array("message" => "License plate already exists."));
            } else {
                error_log("Query error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(array("message" => "Internal Server Error. Please try again."));
            }
        }
    } else {
        // Missing required fields
        http_response_code(400); // Bad Request
        echo json_encode(array("message" => "Car name and license plate are required."));
    }
} else {
    // Invalid request method
    http_response_code(405); // Method Not Allowed
    echo json_encode(array("message" => "Method not allowed."));
}

// Close the connection
$conn = null;
?>