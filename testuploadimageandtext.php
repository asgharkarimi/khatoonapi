<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Images and Text</title>
</head>
<body>
    <h1>Upload Images and Text</h1>
    <form action="http://localhost/khatoonbar/upload_images_and_text.php" method="post" enctype="multipart/form-data">
        <!-- Image 1 -->
        <label for="image1">Image 1:</label>
        <input type="file" name="image1" id="image1" required><br><br>

        <!-- Image 2 -->
        <label for="image2">Image 2:</label>
        <input type="file" name="image2" id="image2" required><br><br>

        <!-- Image 3 -->
        <label for="image3">Image 3:</label>
        <input type="file" name="image3" id="image3" required><br><br>

        <!-- Text 1 -->
        <label for="text1">Text 1:</label>
        <input type="text" name="text1" id="text1" required><br><br>

        <!-- Text 2 -->
        <label for="text2">Text 2:</label>
        <input type="text" name="text2" id="text2" required><br><br>

        <!-- Text 3 -->
        <label for="text3">Text 3:</label>
        <input type="text" name="text3" id="text3" required><br><br>

        <!-- Submit Button -->
        <button type="submit">Upload</button>
    </form>

    <!-- Display API Response -->
    <h2>API Response:</h2>
    <pre id="response"></pre>

    <!-- JavaScript to Display API Response -->
   <script>
       document.querySelector('form').addEventListener('submit', async function (e) {
           e.preventDefault(); // Prevent the form from submitting normally

           const formData = new FormData(this); // Create FormData object from the form

           try {
               const response = await fetch(this.action, {
                   method: this.method,
                   body: formData
               });

               // Log the raw response for debugging
               const rawResponse = await response.text();
               console.log("Raw Response:", rawResponse);

               // Try to parse the response as JSON
               try {
                   const result = JSON.parse(rawResponse);
                   document.getElementById('response').textContent = JSON.stringify(result, null, 2);
               } catch (jsonError) {
                   console.error("JSON Parsing Error:", jsonError);
                   document.getElementById('response').textContent = "Error: Invalid JSON response. Raw response: " + rawResponse;
               }
           } catch (error) {
               console.error("Fetch Error:", error);
               document.getElementById('response').textContent = "Error: " + error.message;
           }
       });
   </script>
</body>
</html>