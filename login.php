<?php
// Include the database connection class and JWT library
require_once 'connecttodb.php';
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Set header to allow cross-origin resource sharing (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// JWT Secret Key (replace with a strong, unique key)
$secret_key = "@asgharkarimi1367121@";

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
    if (isset($data->phone_number) && isset($data->password)) {
        $phone_number = trim($data->phone_number);
        $password = trim($data->password);

        try {
            // Fetch user from the database by phone_number
            $query = "SELECT id, phone_number, password, isadmin FROM users WHERE phone_number = :phone_number";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $db_password = $row['password'];
                $id = $row['id'];
                $isadmin = $row['isadmin'];

                // Debugging: Log input and database data
                error_log("Phone number: " . $phone_number);
                error_log("Password: " . $password);
                error_log("DB Password: " . $db_password);
                error_log("Password verification result: " . password_verify($password, $db_password));

                // Verify the password
                if (password_verify($password, $db_password)) {
                    // Login successful
                    // Generate JWT
                    $issued_at = time();
                    $expiration_time = $issued_at + 3600; // Token valid for 1 hour
                    $payload = array(
                        "iat" => $issued_at,
                        "exp" => $expiration_time,
                        "data" => array(
                            "user_id" => $id,
                            "phone_number" => $phone_number,
                            "isadmin" => $isadmin
                        )
                    );

                    try {
                        // Generate the JWT
                        $jwt = JWT::encode($payload, $secret_key, 'HS256');

                        // Debugging: Log the JWT
                        error_log("JWT generated: " . $jwt);

                        // Return the JWT in the response
                        http_response_code(200);
                        echo json_encode(array(
                            "message" => "Login successful.",
                            "jwt" => $jwt
                        ));
                    } catch (Exception $e) {
                        error_log("JWT generation error: " . $e->getMessage());
                        http_response_code(500);
                        echo json_encode(array("message" => "JWT generation failed."));
                    }
                } else {
                    // Invalid password
                    http_response_code(401);
                    echo json_encode(array("message" => "Invalid credentials."));
                }
            } else {
                // User not found
                http_response_code(404);
                echo json_encode(array("message" => "User not found."));
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(array("message" => "Internal Server Error. Please try again."));
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