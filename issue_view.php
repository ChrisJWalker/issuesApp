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

if (!isset($_GET['id'])) {
    echo "Error: No issue ID provided.";
    exit();
}

$issueId = $_GET['id'];

$pdo = Database::connect();
$stmt = $pdo->prepare("SELECT i.id, p.fname, p.lname, i.short_description, i.long_description, i.org, i.project, i.open_date, i.priority, i.per_id AS creator_id
                       FROM iss_issues i
                       JOIN iss_persons p ON i.per_id = p.id
                       WHERE i.id = ?");
$stmt->execute([$issueId]);
$issue = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch comments for the issue
$commentsStmt = $pdo->prepare("SELECT c.id, c.short_comment, c.long_comment, c.posted_date, c.per_id, p.fname, p.lname 
                               FROM iss_comments c
                               JOIN iss_persons p ON c.per_id = p.id
                               WHERE c.iss_id = ?
                               ORDER BY c.posted_date DESC");
$commentsStmt->execute([$issueId]);
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

if (!$issue) {
    echo "Error: Issue not found.";
    exit();
}

Database::disconnect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Issue - DSR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen p-4">

    <h1 class="text-3xl font-semibold my-4">Issue Details</h1>

    <div class="bg-white shadow-lg rounded-lg p-6 border border-gray-300">
        <h2 class="text-2xl font-semibold text-gray-900"><?= htmlspecialchars($issue['short_description']) ?></h2>
        <p class="text-sm text-gray-500">Created by: <?= htmlspecialchars($issue['fname']) ?> <?= htmlspecialchars($issue['lname']) ?> | <?= htmlspecialchars($issue['open_date']) ?> | <?= htmlspecialchars($issue['priority']) ?></p>

        <div class="mt-6">
            <h3 class="font-semibold text-lg text-gray-700">Details</h3>
            <p class="text-gray-600"><strong>Organization:</strong> <?= htmlspecialchars($issue['org']) ?></p>
            <p class="text-gray-600"><strong>Project:</strong> <?= htmlspecialchars($issue['project']) ?></p>
            <p class="text-gray-600"><strong>Priority:</strong> <?= htmlspecialchars($issue['priority']) ?></p>
            <p class="text-gray-600"><strong>Open Date:</strong> <?= htmlspecialchars($issue['open_date']) ?></p>
        </div>

        <div class="mt-6">
            <h3 class="font-semibold text-lg text-gray-700">Long Description</h3>
            <p class="text-gray-600"><?= nl2br(htmlspecialchars($issue['long_description'])) ?></p>
        </div>

        <div class="mt-6">
            <h3 class="font-semibold text-lg text-gray-700">Comments</h3>
            <!-- Comments Section -->
            <div class="space-y-4">
                <?php foreach ($comments as $comment): ?>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-300 relative">
                        <p class="font-semibold"><?= htmlspecialchars($comment['fname']) ?> <?= htmlspecialchars($comment['lname']) ?> - <?= htmlspecialchars($comment['posted_date']) ?></p>
                        <p class="text-gray-600"><?= nl2br(htmlspecialchars($comment['short_comment'])) ?></p>
                        

                        <!-- Show delete button if user is comment creator or admin -->
                        <?php if ($comment['per_id'] == $userId || $isAdmin): ?>
                            <form action="comment_delete.php" method="POST" class="absolute top-2 right-2">
                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                <input type="hidden" name="issue_id" value="<?= $issueId ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Post Comment Form -->
            <form action="comment_create.php?id=<?= $issueId ?>" method="POST">
                <textarea name="comment" class="w-full p-2 border border-gray-300 rounded-lg mt-2" placeholder="Write a comment..."></textarea>
                <button type="submit" class="bg-blue-500 text-white p-2 rounded-lg mt-2">Post Comment</button>
            </form>
        </div>

        <a href="homepage.php" class="text-blue-500 hover:text-blue-700 mt-4 inline-block">Back to Issues List</a>
    </div>

</body>
</html>
