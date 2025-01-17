<?php
// Include the database connection class
require_once 'connecttodb.php';

// Set header to allow cross-origin resource sharing (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
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

// Check if the request method is GET
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        // Fetch all vehicles from the database
        $query = "SELECT id, car_name, license_plate FROM vehicles";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        // Check if any vehicles were found
        if ($stmt->rowCount() > 0) {
            $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Return the vehicles in the response
            http_response_code(200);
            echo json_encode(array(
                "message" => "Vehicles retrieved successfully.",
                "data" => $vehicles
            ));
        } else {
            // No vehicles found
            http_response_code(404);
            echo json_encode(array("message" => "No vehicles found."));
        }
    } catch (PDOException $e) {
        error_log("Query error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(array("message" => "Internal Server Error. Please try again."));
    }
} else {
    // Invalid request method
    http_response_code(405); // Method Not Allowed
    echo json_encode(array("message" => "Method not allowed."));
}

// Close the connection
$conn = null;
?>