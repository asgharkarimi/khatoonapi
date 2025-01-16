<?php
// Set header to allow cross-origin resource sharing (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Directory to store uploaded files
$upload_dir = "uploads/";

// Debugging: Log the $_FILES array
error_log("Files received: " . print_r($_FILES, true));

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debugging: Log the request method
    error_log("Request method: " . $_SERVER["REQUEST_METHOD"]);

    // Check if a file was uploaded
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['file']['name']);
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_size = $_FILES['file']['size'];
        $file_type = $_FILES['file']['type'];

        // Debugging: Log file details
        error_log("File name: " . $file_name);
        error_log("File size: " . $file_size);
        error_log("File type: " . $file_type);

        // Validate file type (allow only images)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file_type, $allowed_types)) {
            http_response_code(400);
            echo json_encode(array("message" => "Only JPEG, PNG, and GIF files are allowed."));
            exit();
        }

        // Validate file size (limit to 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file_size > $max_size) {
            http_response_code(400);
            echo json_encode(array("message" => "File size exceeds the maximum limit of 5MB."));
            exit();
        }

        // Generate a unique file name to avoid overwriting
        $unique_file_name = uniqid() . "_" . $file_name;

        // Move the uploaded file to the upload directory
        if (move_uploaded_file($file_tmp, $upload_dir . $unique_file_name)) {
            // File uploaded successfully
            http_response_code(201);
            echo json_encode(array(
                "message" => "File uploaded successfully.",
                "file_name" => $unique_file_name,
                "file_path" => $upload_dir . $unique_file_name
            ));
        } else {
            // Failed to move the file
            http_response_code(500);
            echo json_encode(array("message" => "Failed to upload file."));
        }
    } else {
        // No file uploaded or upload error
        error_log("File upload error: " . ($_FILES['file']['error'] ?? 'No file uploaded'));
        http_response_code(400);
        echo json_encode(array("message" => "No file uploaded or file upload error."));
    }
} else {
    // Invalid request method
    http_response_code(405); // Method Not Allowed
    echo json_encode(array("message" => "Method not allowed."));
}
?>