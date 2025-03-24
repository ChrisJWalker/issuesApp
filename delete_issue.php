<?php
session_start();
require_once __DIR__ . '/database/database.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if issue ID is provided
if (!isset($_GET['id'])) {
    header("Location: list.php?message=No issue ID provided");
    exit();
}

$issueId = $_GET['id'];
$pdo = Database::connect();

// Fetch the issue to verify permissions
$stmt = $pdo->prepare("SELECT per_id FROM iss_issues WHERE id = ?");
$stmt->execute([$issueId]);
$issue = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$issue) {
    Database::disconnect();
    header("Location: list.php?message=Issue not found");
    exit();
}

// Check if the user is an admin or the creator
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    if ($_SESSION['user_id'] != $issue['per_id']) {
        Database::disconnect();
        header("Location: list.php?message=You do not have permission to delete this issue");
        exit();
    }
}

// Delete comments first due to foreign key constraint
$deleteComments = $pdo->prepare("DELETE FROM iss_comments WHERE iss_id = ?");
$deleteComments->execute([$issueId]);

// Delete the issue
$deleteIssue = $pdo->prepare("DELETE FROM iss_issues WHERE id = ?");
$deleteIssue->execute([$issueId]);

Database::disconnect();
header("Location: list.php?message=Issue deleted successfully");
exit();
