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
    $filePath = NULL;

    // File upload logic
    if (!empty($_FILES['attachment']['name'])) {
        $uploadDir = 'uploads/';
        $fileName = basename($_FILES['attachment']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = uniqid() . "_" . $fileName;
        $targetFile = $uploadDir . $newFileName;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        if (in_array($fileExtension, $allowedTypes)) {
            if ($_FILES['attachment']['size'] <= 2 * 1024 * 1024) {
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
                    $filePath = $targetFile;
                } else {
                    echo "Error: File upload failed.";
                    exit();
                }
            } else {
                echo "Error: File size exceeds 2MB.";
                exit();
            }
        } else {
            echo "Error: Invalid file type.";
            exit();
        }
    }

    $pdo = Database::connect();
    $stmt = $pdo->prepare("INSERT INTO iss_comments (per_id, iss_id, short_comment, long_comment, posted_date, attachment_link) 
                           VALUES (?, ?, ?, ?, NOW(), ?)");
    $stmt->execute([$userId, $issueId, substr($comment, 0, 255), $comment, $filePath]);
    Database::disconnect();

    header("Location: issue_view.php?id=" . $issueId);
    exit();
}
?>
