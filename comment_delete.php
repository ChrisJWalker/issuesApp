<?php
// Start the session to maintain user login state
session_start();

// Include the database connection file
require_once __DIR__ . '../database/database.php';

// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the user is logged in by checking if 'user_id' exists in the session
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Fetch the logged-in user's ID and admin status from the session
$userId = $_SESSION['user_id'];
$isAdmin = $_SESSION['is_admin'] ?? false; // Default to false if 'is_admin' is not set in the session

// Validate the input parameters (comment_id and issue_id must be provided)
if (!isset($_POST['comment_id'], $_POST['issue_id'])) {
    // If parameters are missing, display an error message and exit
    echo "Error: Missing parameters.";
    exit();
}

// Get the comment ID and issue ID from the POST request
$commentId = $_POST['comment_id'];
$issueId = $_POST['issue_id'];

// Connect to the database
$pdo = Database::connect();

// Prepare the SQL query to fetch the 'per_id' (user ID) associated with the comment
$stmt = $pdo->prepare("SELECT per_id FROM iss_comments WHERE id = ?");
$stmt->execute([$commentId]);

// Fetch the comment details
$comment = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the comment exists and if the current user is authorized to delete it
// The user can delete their own comment or if they are an admin
if ($comment && ($comment['per_id'] == $userId || $isAdmin)) {
    // If authorized, prepare the SQL query to delete the comment
    $deleteStmt = $pdo->prepare("DELETE FROM iss_comments WHERE id = ?");
    $deleteStmt->execute([$commentId]);

    // Disconnect from the database
    Database::disconnect();

    // Redirect the user to the issue view page after deleting the comment
    header("Location: issue_view.php?id=$issueId");
    exit();
} else {
    // If not authorized, display an error message
    echo "Error: Unauthorized action.";

    // Disconnect from the database
    Database::disconnect();
    exit();
}
?>
