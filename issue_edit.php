<?php
session_start(); // Start the session to check if the user is logged in
require_once __DIR__ . '../database/database.php'; // Include the database connection file

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if the user is not logged in
    exit(); // Stop script execution
}

// Check if the issue ID is provided in the URL
if (!isset($_GET['id'])) {
    echo "Error: No issue ID provided."; // Display error message if no issue ID is provided
    exit(); // Stop script execution
}

$issueId = $_GET['id']; // Get the issue ID from the URL

// Fetch the issue data from the database
$pdo = Database::connect(); // Establish database connection
$stmt = $pdo->prepare("SELECT * FROM iss_issues WHERE id = ?"); // Prepare the query to fetch the issue
$stmt->execute([$issueId]); // Execute the query with the provided issue ID
$issue = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the issue details

if (!$issue) {
    echo "Error: Issue not found."; // Display error message if issue is not found
    exit(); // Stop script execution
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data if the form is submitted
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $org = $_POST['org'];
    $project = $_POST['project'];
    $priority = $_POST['priority'];
    $filePath = $issue['attachment_link']; // Set the default file path to the current file path

    // File handling logic: Check if a new file is uploaded
    if (!empty($_FILES['attachment']['name'])) {
        // New file uploaded
        $uploadDir = 'uploads/'; // Set the upload directory
        $fileName = basename($_FILES['attachment']['name']); // Get the original file name
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // Get the file extension
        $newFileName = uniqid() . "_" . $fileName; // Generate a unique name for the file
        $targetFile = $uploadDir . $newFileName; // Set the target file path

        // Allowed file types
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        if (in_array($fileExtension, $allowedTypes)) {
            // Check if the file size is within the 2MB limit
            if ($_FILES['attachment']['size'] <= 2 * 1024 * 1024) { // 2MB limit
                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
                    // Delete the old file if it's not the default file path
                    if ($issue['attachment_link'] && file_exists($issue['attachment_link'])) {
                        unlink($issue['attachment_link']); // Delete the old file
                    }
                    $filePath = $targetFile; // Set the new file path
                } else {
                    echo "Error: File upload failed."; // Display error message if file upload fails
                    exit(); // Stop script execution
                }
            } else {
                echo "Error: File size exceeds 2MB."; // Display error message if file size exceeds 2MB
                exit(); // Stop script execution
            }
        } else {
            echo "Error: Invalid file type. Only JPG, JPEG, PNG, GIF, and PDF are allowed."; // Display error if invalid file type
            exit(); // Stop script execution
        }
    } elseif (isset($_POST['remove_attachment']) && $issue['attachment_link']) {
        // Check if the user wants to remove the file
        if (file_exists($issue['attachment_link'])) {
            unlink($issue['attachment_link']); // Delete the file from the server
            $filePath = NULL; // Set the file path to NULL in the database
        }
    }

    // Update the issue in the database
    $updateStmt = $pdo->prepare("UPDATE iss_issues 
                                 SET short_description = ?, long_description = ?, org = ?, project = ?, priority = ?, attachment_link = ? 
                                 WHERE id = ?"); // Prepare the SQL query to update the issue
    $updateStmt->execute([$short_description, $long_description, $org, $project, $priority, $filePath, $issueId]); // Execute the query with the new data

    Database::disconnect(); // Disconnect from the database

    // Redirect to the homepage with a success message
    header("Location: homepage.php?message=Issue%20has%20been%20updated"); // Redirect to homepage after the update
    exit(); // Stop script execution
}

Database::disconnect(); // Disconnect from the database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Issue - DSR</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4">

    <h1 class="text-3xl font-semibold my-4">Edit Issue</h1>

    <div class="w-full max-w-4xl bg-white shadow-md rounded-lg p-6">
        <form action="issue_edit.php?id=<?= $issueId ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <!-- Short Description Input -->
            <div>
                <label for="short_description" class="block text-gray-700">Short Description</label>
                <input type="text" name="short_description" id="short_description" value="<?= htmlspecialchars($issue['short_description']) ?>" required class="w-full p-2 border border-gray-300 rounded">
            </div>
            <!-- Long Description Input -->
            <div>
                <label for="long_description" class="block text-gray-700">Long Description</label>
                <textarea name="long_description" id="long_description" required class="w-full p-2 border border-gray-300 rounded"><?= htmlspecialchars($issue['long_description']) ?></textarea>
            </div>
            <!-- Organization Input -->
            <div>
                <label for="org" class="block text-gray-700">Organization</label>
                <input type="text" name="org" id="org" value="<?= htmlspecialchars($issue['org']) ?>" required class="w-full p-2 border border-gray-300 rounded">
            </div>
            <!-- Project Input -->
            <div>
                <label for="project" class="block text-gray-700">Project</label>
                <input type="text" name="project" id="project" value="<?= htmlspecialchars($issue['project']) ?>" required class="w-full p-2 border border-gray-300 rounded">
            </div>
            <!-- Priority Input -->
            <div>
                <label for="priority" class="block text-gray-700">Priority</label>
                <input type="text" name="priority" id="priority" value="<?= htmlspecialchars($issue['priority']) ?>" required class="w-full p-2 border border-gray-300 rounded">
            </div>

            <!-- Display current attachment if exists -->
            <?php if (!empty($issue['attachment_link'])): ?>
                <div>
                    <p class="text-gray-700">Current Attachment:</p>
                    <a href="<?= $issue['attachment_link'] ?>" class="text-blue-500 hover:underline" target="_blank">
                        View Current File
                    </a>
                    <div class="mt-2">
                        <label for="remove_attachment" class="text-red-500 hover:text-red-700">
                            <input type="checkbox" name="remove_attachment" id="remove_attachment">
                            Remove this file
                        </label>
                    </div>
                </div>
            <?php endif; ?>

            <!-- New File Upload Input -->
            <div>
                <label for="attachment" class="block text-gray-700">Upload New File (Max 2MB, JPG, PNG, GIF, PDF)</label>
                <input type="file" name="attachment" id="attachment" class="w-full p-2 border border-gray-300 rounded">
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Update Issue</button>
            </div>
        </form>
    </div>

</body>
</html>
