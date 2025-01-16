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
    if (
        isset($data->phone_number) &&
        isset($data->password)
    ) {
        $phone_number = trim($data->phone_number);
        $password = trim($data->password);
        $isadmin = isset($data->isadmin) ? (int)$data->isadmin : 0; // Default to 0 (regular user)

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Insert the new user into the database
            $query = "INSERT INTO users (phone_number, password, isadmin) VALUES (:phone_number, :password, :isadmin)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':isadmin', $isadmin);

            if ($stmt->execute()) {
                // User added successfully
                http_response_code(201); // 201 Created
                echo json_encode(array("message" => "User added successfully."));
            } else {
                // Failed to add user
                http_response_code(500);
                echo json_encode(array("message" => "Unable to add user."));
            }
        } catch (PDOException $e) {
            // Handle duplicate phone number or other errors
            if ($e->getCode() == 23000) { // Duplicate entry error code
                http_response_code(400);
                echo json_encode(array("message" => "Phone number already exists."));
            } else {
                error_log("Query error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(array("message" => "Internal Server Error. Please try again."));
            }
        }
    } else {
        // Missing required fields
        http_response_code(400); // Bad Request
        echo json_encode(array("message" => "Phone number and password are required."));
    }
} else {
    // Invalid request method
    http_response_code(405); // Method Not Allowed
    echo json_encode(array("message" => "Method not allowed."));
}

// Close the connection
$conn = null;
?>