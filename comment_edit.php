<?php
// Start the session to maintain the user login state
session_start();

// Include the database connection file
require_once __DIR__ . '/database/database.php';

// Check if the user is logged in by verifying the session variable 'user_id'
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Fetch the logged-in user's ID and admin status from the session
$userId = $_SESSION['user_id'];
$isAdmin = $_SESSION['is_admin'] ?? false; // Default to false if 'is_admin' is not set in the session

// Validate that 'comment_id' and 'issue_id' are present in the URL query string
if (!isset($_GET['comment_id'], $_GET['issue_id'])) {
    // If any of the parameters are missing, display an error and exit
    echo "Invalid request.";
    exit();
}

// Retrieve the comment ID and issue ID from the GET request
$commentId = $_GET['comment_id'];
$issueId = $_GET['issue_id'];

// Connect to the database
$pdo = Database::connect();

// Prepare the SQL query to fetch the comment from the database using the comment ID
$stmt = $pdo->prepare("SELECT * FROM iss_comments WHERE id = ?");
$stmt->execute([$commentId]);

// Fetch the comment details
$comment = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the comment exists and if the current user is authorized to edit it
// The user can edit their own comment or if they are an admin
if (!$comment || ($comment['per_id'] != $userId && !$isAdmin)) {
    // If the comment doesn't exist or the user is not authorized, display an error message
    echo "Unauthorized.";
    exit();
}

// If the form is submitted, handle the comment update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the new comment text from the POST request
    $newComment = $_POST['comment'] ?? '';

    // Ensure the comment is not empty
    if (trim($newComment) !== '') {
        // Prepare the SQL query to update the comment
        $updateStmt = $pdo->prepare("UPDATE iss_comments SET short_comment = ?, posted_date = CURRENT_TIMESTAMP WHERE id = ?");
        $updateStmt->execute([$newComment, $commentId]);

        // Redirect the user back to the issue view page with the updated comment
        header("Location: issue_view.php?id=" . urlencode($issueId));
        exit();
    }
}

// Disconnect from the database after processing
Database::disconnect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Comment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

    <!-- Comment Edit Form -->
    <div class="max-w-xl mx-auto bg-white shadow-lg p-6 rounded-lg border border-gray-300">
        <h2 class="text-2xl font-semibold mb-4">Edit Your Comment</h2>

        <!-- Form to edit the comment -->
        <form method="POST">
            <!-- Textarea for editing the comment, pre-filled with the existing comment text -->
            <textarea name="comment" class="w-full p-2 border rounded h-32 resize-none" required><?= htmlspecialchars($comment['short_comment']) ?></textarea>

            <!-- Buttons to either save or cancel the changes -->
            <div class="flex justify-end mt-4 space-x-2">
                <a href="issue_view.php?id=<?= $issueId ?>" class="text-gray-500 hover:underline">Cancel</a>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save</button>
            </div>
        </form>
    </div>

</body>
</html>
