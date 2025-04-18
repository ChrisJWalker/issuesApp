<?php
session_start(); // Start the session to manage user login status
require_once __DIR__ . '../database/database.php'; // Include the database connection file

// Ensure the user is logged in by checking the session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit(); // Stop script execution
}

// Process the form when it's submitted via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $short_description = $_POST['short_description']; // Short description from the form
    $long_description = $_POST['long_description']; // Long description from the form
    $org = $_POST['org']; // Organization from the form
    $project = $_POST['project']; // Project from the form
    $priority = $_POST['priority']; // Priority from the form
    $creator_id = $_SESSION['user_id']; // Logged-in user ID
    $open_date = date('Y-m-d'); // Current date as the open date
    $close_date = '0000-00-00'; // Default close date (no close yet)
    $filePath = NULL; // Default file path is NULL (no file uploaded)

    // Check if a file has been uploaded
    if (!empty($_FILES['attachment']['name'])) {
        $uploadDir = 'uploads/'; // Directory to store the uploaded files
        $fileName = basename($_FILES['attachment']['name']); // Get the original file name
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // Get the file extension in lowercase
        $newFileName = uniqid() . "_" . $fileName; // Create a unique name for the file to prevent duplicates
        $targetFile = $uploadDir . $newFileName; // Full path to store the file

        // Allowed file types for upload
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        
        // Validate file extension
        if (in_array($fileExtension, $allowedTypes)) {
            // Check if the file size is within the limit (2MB)
            if ($_FILES['attachment']['size'] <= 2 * 1024 * 1024) {
                // Move the uploaded file to the desired directory
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
                    $filePath = $targetFile; // Set the file path if the upload was successful
                } else {
                    echo "Error: File upload failed."; // Display error message if file upload failed
                    exit(); // Stop the script
                }
            } else {
                echo "Error: File size exceeds 2MB."; // Display error if the file exceeds 2MB
                exit(); // Stop the script
            }
        } else {
            echo "Error: Invalid file type. Only JPG, JPEG, PNG, GIF, and PDF are allowed."; // Display error for invalid file type
            exit(); // Stop the script
        }
    }

    // Connect to the database and insert the new issue
    $pdo = Database::connect(); // Get the database connection
    $sql = "INSERT INTO iss_issues (short_description, long_description, org, project, priority, per_id, open_date, close_date, attachment_link) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"; // SQL query to insert the new issue
    $stmt = $pdo->prepare($sql); // Prepare the query
    $stmt->execute([$short_description, $long_description, $org, $project, $priority, $creator_id, $open_date, $close_date, $filePath]); // Execute the query with the form data
    Database::disconnect(); // Disconnect from the database

    // Redirect to the homepage with a success message
    header("Location: homepage.php?message=Issue%20has%20been%20created");
    exit(); // Stop the script
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Set the character encoding for the page -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ensure responsive design for different screen sizes -->
    <title>Create Issue - DSR</title> <!-- Title for the page -->
    <script src="https://cdn.tailwindcss.com"></script> <!-- Include Tailwind CSS for styling -->
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4"> <!-- Apply Tailwind CSS for styling the body -->

    <h1 class="text-3xl font-semibold my-4">Create New Issue</h1> <!-- Page heading -->

    <div class="w-full max-w-4xl bg-white shadow-md rounded-lg p-6"> <!-- Form container with Tailwind styling -->
        <form action="issue_create.php" method="POST" enctype="multipart/form-data" class="space-y-4"> <!-- Form for creating an issue -->
            <div>
                <label for="short_description" class="block text-gray-700">Short Description</label>
                <input type="text" name="short_description" id="short_description" required class="w-full p-2 border border-gray-300 rounded"> <!-- Input for short description -->
            </div>
            <div>
                <label for="long_description" class="block text-gray-700">Long Description</label>
                <textarea name="long_description" id="long_description" required class="w-full p-2 border border-gray-300 rounded"></textarea> <!-- Textarea for long description -->
            </div>
            <div>
                <label for="org" class="block text-gray-700">Organization</label>
                <input type="text" name="org" id="org" required class="w-full p-2 border border-gray-300 rounded"> <!-- Input for organization -->
            </div>
            <div>
                <label for="project" class="block text-gray-700">Project</label>
                <input type="text" name="project" id="project" required class="w-full p-2 border border-gray-300 rounded"> <!-- Input for project -->
            </div>
            <div>
                <label for="priority" class="block text-gray-700">Priority</label>
                <input type="text" name="priority" id="priority" required class="w-full p-2 border border-gray-300 rounded"> <!-- Input for priority -->
            </div>
            <div>
                <label for="attachment" class="block text-gray-700">Upload File (Max 2MB, JPG, PNG, GIF, PDF)</label>
                <input type="file" name="attachment" id="attachment" class="w-full p-2 border border-gray-300 rounded"> <!-- File input for attachment -->
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Create Issue</button> <!-- Submit button to create the issue -->
            </div>
        </form>
    </div>
</body>
</html>
