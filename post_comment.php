<?php
session_start();
require_once __DIR__ . '/database/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_GET['id'])) {
    $comment = $_POST['comment'];
    $issueId = $_GET['id'];
    $userId = $_SESSION['user_id'];

    $pdo = Database::connect();
    $stmt = $pdo->prepare("INSERT INTO iss_comments (per_id, iss_id, short_comment, long_comment, posted_date) 
                           VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $issueId, substr($comment, 0, 255), $comment]);

    Database::disconnect();

    // Corrected redirect URL
    header("Location: view_issue.php?id=" . $issueId);
    exit();
}
?>
