<?php
session_start();
require_once __DIR__ . '/database/database.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user info
$userId = $_SESSION['user_id'];
$isAdmin = $_SESSION['is_admin'] ?? false;

// Validate inputs
if (!isset($_POST['comment_id'], $_POST['issue_id'])) {
    echo "Error: Missing parameters.";
    exit();
}

$commentId = $_POST['comment_id'];
$issueId = $_POST['issue_id'];

$pdo = Database::connect();
$stmt = $pdo->prepare("SELECT per_id FROM iss_comments WHERE id = ?");
$stmt->execute([$commentId]);
$comment = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if comment exists and user is authorized to delete
if ($comment && ($comment['per_id'] == $userId || $isAdmin)) {
    $deleteStmt = $pdo->prepare("DELETE FROM iss_comments WHERE id = ?");
    $deleteStmt->execute([$commentId]);
    Database::disconnect();
    header("Location: issue_view.php?id=$issueId");
    exit();
} else {
    echo "Error: Unauthorized action.";
    Database::disconnect();
    exit();
}
?>
