<?php
session_start(); // Start the session to manage user login status
require_once __DIR__ . '../database/database.php'; // Include the database connection file

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit(); // Stop script execution
}

// Check if issue ID is provided in the URL
if (!isset($_GET['id'])) {
    header("Location: homepage.php?message=No issue ID provided"); // Redirect if no issue ID is provided
    exit(); // Stop script execution
}

$issueId = $_GET['id']; // Get the issue ID from the URL
$pdo = Database::connect(); // Get the database connection

// Fetch the issue to verify permissions by checking if the logged-in user can delete it
$stmt = $pdo->prepare("SELECT per_id FROM iss_issues WHERE id = ?"); // SQL query to get the issue's creator ID
$stmt->execute([$issueId]); // Execute the query
$issue = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the issue details

// Check if the issue exists
if (!$issue) {
    Database::disconnect(); // Disconnect from the database
    header("Location: homepage.php?message=Issue not found"); // Redirect if the issue is not found
    exit(); // Stop script execution
}

// Check if the user is an admin or the creator of the issue
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    // If the user is not an admin, check if they are the creator of the issue
    if ($_SESSION['user_id'] != $issue['per_id']) {
        Database::disconnect(); // Disconnect from the database
        header("Location: homepage.php?message=You do not have permission to delete this issue"); // Redirect if no permission
        exit(); // Stop script execution
    }
}

// Delete comments related to the issue first due to foreign key constraints
$deleteComments = $pdo->prepare("DELETE FROM iss_comments WHERE iss_id = ?"); // SQL query to delete comments for the issue
$deleteComments->execute([$issueId]); // Execute the query

// Delete the issue from the database
$deleteIssue = $pdo->prepare("DELETE FROM iss_issues WHERE id = ?"); // SQL query to delete the issue
$deleteIssue->execute([$issueId]); // Execute the query

Database::disconnect(); // Disconnect from the database
header("Location: homepage.php?message=Issue deleted successfully"); // Redirect to the homepage with a success message
exit(); // Stop script execution
?>
