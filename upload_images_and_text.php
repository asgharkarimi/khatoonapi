<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection class
require_once 'connecttodb.php';

// Set header to allow cross-origin resource sharing (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Directory to store uploaded files
$upload_dir = "uploads/";

try {
    // Create a new instance of the Database class
    $database = new Database();
    $conn = $database->getConnection(); // Initialize the database connection
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => $e->getMessage()));
    exit(); // Terminate the script if connection fails
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are provided
    if (
        isset($_FILES['image1']) && isset($_FILES['image2']) && isset($_FILES['image3']) &&
        isset($_POST['text1']) && isset($_POST['text2']) && isset($_POST['text3'])
    ) {
        // Validate and process the uploaded files
        $file_paths = [];
        $errors = [];

        // Loop through each file
        for ($i = 1; $i <= 3; $i++) {
            $file_key = 'image' . $i;
            if ($_FILES[$file_key]['error'] == UPLOAD_ERR_OK) {
                $file_name = basename($_FILES[$file_key]['name']);
                $file_tmp = $_FILES[$file_key]['tmp_name'];
                $file_type = $_FILES[$file_key]['type'];

                // Validate file type (allow only images)
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($file_type, $allowed_types)) {
                    $errors[] = "Only JPEG, PNG, and GIF files are allowed for $file_key.";
                    continue;
                }

                // Generate a unique file name to avoid overwriting
                $unique_file_name = uniqid() . "_" . $file_name;
                $file_path = $upload_dir . $unique_file_name;

                // Move the uploaded file to the upload directory
                if (move_uploaded_file($file_tmp, $file_path)) {
                    $file_paths[$file_key] = $file_path;
                } else {
                    $errors[] = "Failed to upload $file_key.";
                }
            } else {
                $errors[] = "Error uploading $file_key.";
            }
        }

        // If there are errors, return them
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(array("message" => "File upload errors.", "errors" => $errors));
            exit();
        }

        // Get the text inputs
        $text1 = trim($_POST['text1']);
        $text2 = trim($_POST['text2']);
        $text3 = trim($_POST['text3']);

        try {
            // Insert the data into the database
            $query = "INSERT INTO images_and_text (image1_path, image2_path, image3_path, text1, text2, text3)
                      VALUES (:image1_path, :image2_path, :image3_path, :text1, :text2, :text3)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':image1_path', $file_paths['image1']);
            $stmt->bindParam(':image2_path', $file_paths['image2']);
            $stmt->bindParam(':image3_path', $file_paths['image3']);
            $stmt->bindParam(':text1', $text1);
            $stmt->bindParam(':text2', $text2);
            $stmt->bindParam(':text3', $text3);

            if ($stmt->execute()) {
                // Data inserted successfully
                http_response_code(201);
                echo json_encode(array("message" => "Files and text uploaded successfully."));
            } else {
                // Failed to insert data
                http_response_code(500);
                echo json_encode(array("message" => "Failed to store data in the database."));
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(array("message" => "Internal Server Error. Please try again."));
        }
    } else {
        // Missing required fields
        http_response_code(400); // Bad Request
        echo json_encode(array("message" => "All fields are required."));
    }
} else {
    // Invalid request method
    http_response_code(405); // Method Not Allowed
    echo json_encode(array("message" => "Method not allowed."));
}

// Close the connection
$conn = null;
?>