<?php
session_start();
require_once __DIR__ . '/database/database.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id']; // Store the logged-in user's ID
$isAdmin = $_SESSION['is_admin'] ?? false; // Check if the user is an admin

// Validate if issue ID is provided in the URL
if (!isset($_GET['id'])) {
    echo "Error: No issue ID provided.";
    exit();
}

$issueId = $_GET['id']; // Get the issue ID from the URL

$pdo = Database::connect(); // Connect to the database
// Fetch the issue details from the database
$stmt = $pdo->prepare("SELECT i.id, p.fname, p.lname, i.short_description, i.long_description, i.org, i.project, i.open_date, i.priority, i.per_id AS creator_id, i.attachment_link, p.attachment_link AS creator_attachment
                       FROM iss_issues i
                       JOIN iss_persons p ON i.per_id = p.id
                       WHERE i.id = ?");
$stmt->execute([$issueId]);
$issue = $stmt->fetch(PDO::FETCH_ASSOC); // Get the issue details as an associative array

// Fetch the comments related to the issue from the database
$commentsStmt = $pdo->prepare("SELECT c.id, c.short_comment, c.long_comment, c.posted_date, c.per_id, c.attachment_link, p.fname, p.lname, p.attachment_link AS comment_attachment
                               FROM iss_comments c
                               JOIN iss_persons p ON c.per_id = p.id
                               WHERE c.iss_id = ?
                               ORDER BY c.posted_date DESC");
$commentsStmt->execute([$issueId]);
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC); // Get all comments as an associative array

// If the issue is not found, display an error message
if (!$issue) {
    echo "Error: Issue not found.";
    exit();
}

Database::disconnect(); // Disconnect from the database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Issue - DSR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen p-4">

    <h1 class="text-3xl font-semibold my-4">Issue Details</h1>

    <div class="bg-white shadow-lg rounded-lg p-6 border border-gray-300">
        <!-- Display creator information (profile picture, name, date, priority) -->
        <div class="flex items-center mb-4">
            <img src="<?= !empty($issue['creator_attachment']) ? htmlspecialchars($issue['creator_attachment']) : 'default-profile.png' ?>" alt="Creator Profile" class="w-12 h-12 rounded-full mr-4">
            <div>
                <p class="text-sm text-gray-500">Created by: <?= htmlspecialchars($issue['fname']) ?> <?= htmlspecialchars($issue['lname']) ?></p>
                <p class="text-sm text-gray-500"><?= htmlspecialchars($issue['open_date']) ?> | <?= htmlspecialchars($issue['priority']) ?></p>
            </div>
        </div>
        
        <!-- Display issue short description -->
        <h2 class="text-2xl font-semibold text-gray-900"><?= htmlspecialchars($issue['short_description']) ?></h2>

        <!-- Display issue details (organization, project, priority, open date) -->
        <div class="mt-6">
            <h3 class="font-semibold text-lg text-gray-700">Details</h3>
            <p class="text-gray-600"><strong>Organization:</strong> <?= htmlspecialchars($issue['org']) ?></p>
            <p class="text-gray-600"><strong>Project:</strong> <?= htmlspecialchars($issue['project']) ?></p>
            <p class="text-gray-600"><strong>Priority:</strong> <?= htmlspecialchars($issue['priority']) ?></p>
            <p class="text-gray-600"><strong>Open Date:</strong> <?= htmlspecialchars($issue['open_date']) ?></p>
        </div>

        <!-- Display issue long description -->
        <div class="mt-6">
            <h3 class="font-semibold text-lg text-gray-700">Long Description</h3>
            <p class="text-gray-600"><?= nl2br(htmlspecialchars($issue['long_description'])) ?></p>
        </div>

        <!-- Display issue attachment if available -->
        <?php if (!empty($issue['attachment_link'])): ?>
            <div class="mt-6">
                <h3 class="font-semibold text-lg text-gray-700">Attachment</h3>
                <?php 
                    $filePath = htmlspecialchars($issue['attachment_link']);
                    $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                ?>
                <?php if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                    <img src="<?= $filePath ?>" alt="Attachment" class="max-w-full h-auto border rounded-lg shadow-md">
                <?php elseif ($fileExt === 'pdf'): ?>
                    <a href="<?= $filePath ?>" target="_blank" class="text-blue-500 hover:underline"><i class="fas fa-file-pdf"></i> View PDF</a>
                <?php else: ?>
                    <a href="<?= $filePath ?>" target="_blank" class="text-blue-500 hover:underline">Download Attachment</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Display comments section -->
        <div class="mt-6">
            <h3 class="font-semibold text-lg text-gray-700">Comments</h3>
            <div class="space-y-4">
                <?php foreach ($comments as $comment): ?>
                    <!-- Display individual comment -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-300 relative">
                        <div class="flex items-center mb-2">
                            <img src="<?= !empty($comment['comment_attachment']) ? htmlspecialchars($comment['comment_attachment']) : 'default-profile.png' ?>" alt="Commentator Profile" class="w-10 h-10 rounded-full mr-4">
                            <div>
                                <p class="font-semibold"><?= htmlspecialchars($comment['fname']) ?> <?= htmlspecialchars($comment['lname']) ?> - <?= htmlspecialchars($comment['posted_date']) ?></p>
                            </div>
                        </div>
                        <p class="text-gray-600"><?= nl2br(htmlspecialchars($comment['short_comment'])) ?></p>

                        <!-- Display comment attachment if available -->
                        <?php if (!empty($comment['attachment_link'])): ?>
                            <div class="mt-2">
                                <?php 
                                    $cFile = htmlspecialchars($comment['attachment_link']);
                                    $cExt = strtolower(pathinfo($cFile, PATHINFO_EXTENSION));
                                ?>
                                <?php if (in_array($cExt, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                    <img src="<?= $cFile ?>" alt="Comment Attachment" class="max-w-xs mt-2 border rounded shadow">
                                <?php elseif ($cExt === 'pdf'): ?>
                                    <a href="<?= $cFile ?>" target="_blank" class="text-blue-500 hover:underline"><i class="fas fa-file-pdf"></i> View PDF Attachment</a>
                                <?php else: ?>
                                    <a href="<?= $cFile ?>" target="_blank" class="text-blue-500 hover:underline">Download Attachment</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Allow the user to delete or edit their own comments or if they are an admin -->
                        <?php if ($comment['per_id'] == $userId || $isAdmin): ?>
                            <form action="comment_delete.php" method="POST" class="absolute top-2 right-2">
                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                <input type="hidden" name="issue_id" value="<?= $issueId ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                            </form>

                            <!-- Edit Button -->
                            <a href="comment_edit.php?comment_id=<?= $comment['id'] ?>&issue_id=<?= $issueId ?>" class="absolute top-2 right-10 text-yellow-500 hover:text-yellow-600">
                                <i class="fas fa-edit"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Post Comment Form -->
            <form action="comment_create.php?id=<?= $issueId ?>" method="POST" enctype="multipart/form-data" class="flex items-center space-x-2 mt-4">
                <textarea name="comment" class="w-full p-2 border border-gray-300 rounded-lg resize-none h-12" placeholder="Write a comment..." required></textarea>
                <button type="button" onclick="document.getElementById('commentAttachment').click()" class="text-gray-600 hover:text-blue-500"><i class="fas fa-paperclip"></i></button>
                <input type="file" name="attachment" id="commentAttachment" style="display:none;">
                <button type="submit" class="bg-blue-500 text-white p-2 rounded-lg">Post Comment</button>
            </form>
        </div>

        <!-- Link back to the homepage -->
        <a href="homepage.php" class="text-blue-500 hover:text-blue-700 mt-4 inline-block">Back to Issues List</a>
    </div>

</body>
</html>
