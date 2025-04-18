<?php
// Start a session to maintain user login state
session_start();

// Include the database connection file
require_once __DIR__ . '/database/database.php';

// Check if the user is logged in by verifying if 'user_id' is set in the session
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Check if the form is submitted via POST and the 'comment' and 'id' parameters are provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_GET['id'])) {
    // Get the comment text, issue ID, and user ID from the POST and session data
    $comment = $_POST['comment'];
    $issueId = $_GET['id'];
    $userId = $_SESSION['user_id'];
    $filePath = NULL; // Initialize the file path to null in case no file is uploaded

    // File upload logic: Check if a file is attached to the comment
    if (!empty($_FILES['attachment']['name'])) {
        // Define the directory where files will be uploaded
        $uploadDir = 'uploads/';
        // Get the file name and its extension
        $fileName = basename($_FILES['attachment']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        // Generate a unique file name for the uploaded file
        $newFileName = uniqid() . "_" . $fileName;
        // Define the target file path where the file will be stored
        $targetFile = $uploadDir . $newFileName;

        // Define allowed file types for upload
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

        // Check if the file type is allowed
        if (in_array($fileExtension, $allowedTypes)) {
            // Check if the file size is less than or equal to 2MB
            if ($_FILES['attachment']['size'] <= 2 * 1024 * 1024) {
                // Try to move the uploaded file to the target directory
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
                    // If the file upload is successful, set the file path
                    $filePath = $targetFile;
                } else {
                    // If file upload fails, display an error message
                    echo "Error: File upload failed.";
                    exit();
                }
            } else {
                // If file size exceeds 2MB, display an error message
                echo "Error: File size exceeds 2MB.";
                exit();
            }
        } else {
            // If the file type is not allowed, display an error message
            echo "Error: Invalid file type.";
            exit();
        }
    }

    // Connect to the database
    $pdo = Database::connect();
    // Prepare the SQL query to insert the new comment into the database
    $stmt = $pdo->prepare("INSERT INTO iss_comments (per_id, iss_id, short_comment, long_comment, posted_date, attachment_link) 
                           VALUES (?, ?, ?, ?, NOW(), ?)");
    // Execute the query with the user ID, issue ID, truncated comment, full comment, and file path (if any)
    $stmt->execute([$userId, $issueId, substr($comment, 0, 255), $comment, $filePath]);
    // Disconnect from the database
    Database::disconnect();

    // Redirect to the issue view page after the comment is posted
    header("Location: issue_view.php?id=" . $issueId);
    exit();
}
?>
