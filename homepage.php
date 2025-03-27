<?php
session_start();
require_once __DIR__ . '/database/database.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pdo = Database::connect();
// Update SQL query to join 'iss_persons' and get the comment count
$stmt = $pdo->query("SELECT i.id, p.fname, p.lname, i.short_description, i.long_description, i.org, i.project, i.open_date, i.priority, i.per_id AS creator_id,
                             (SELECT COUNT(*) FROM iss_comments c WHERE c.iss_id = i.id) AS comment_count
                     FROM iss_issues i
                     JOIN iss_persons p ON i.per_id = p.id
                     ORDER BY i.open_date DESC");
$issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
Database::disconnect();

// Check for success message in the URL
$message = isset($_GET['message']) ? $_GET['message'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issues List - DSR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Include FontAwesome for the icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4">

    <h1 class="text-3xl font-semibold my-4">Issues List</h1>

    <!-- Display success message if set -->
    <?php if ($message): ?>
        <div id="success-message" class="text-green-500 bg-green-100 border border-green-500 p-2 mb-4 rounded">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Hamburger menu (top right) -->
    <div class="fixed top-4 right-4 z-50">
        <button id="hamburger-icon" class="text-2xl text-gray-800 focus:outline-none">
            <i class="fas fa-bars"></i>
        </button>
        <!-- Dropdown menu -->
        <div id="hamburger-menu" class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-lg hidden">
            <a href="profile_view.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">View Profile</a> <!-- View Profile link -->
            <a href="directory.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Directory</a> <!-- Directory link -->
            <a href="logout.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Logout</a>
        </div>
    </div>

    <!-- Button to create a new issue -->
    <div class="fixed bottom-4 right-4 z-50">
        <a href="issue_create.php" class="bg-blue-500 text-white p-4 rounded-full shadow-lg">
            <i class="fas fa-plus text-2xl"></i>
        </a>
    </div>

    <div class="w-full max-w-4xl bg-white shadow-md rounded-lg p-6 space-y-6">
        <!-- Loop through each issue and display as a card -->
        <?php foreach ($issues as $issue): ?>
        <div class="bg-white shadow-lg rounded-lg p-6 border border-gray-300">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <p class="font-semibold text-lg"><?= htmlspecialchars($issue['fname']) ?> <?= htmlspecialchars($issue['lname']) ?></p>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($issue['open_date']) ?> | <?= htmlspecialchars($issue['priority']) ?></p>
                </div>
                <div class="flex space-x-4">
                    <a href="issue_view.php?id=<?= $issue['id'] ?>" class="text-blue-500 hover:text-blue-700">
                        <i class="fas fa-eye"></i> <!-- Eye icon for View -->
                    </a>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] || $_SESSION['user_id'] == $issue['creator_id']): ?>
                    <a href="issue_edit.php?id=<?= $issue['id'] ?>" class="text-yellow-500 hover:text-yellow-700">
                        <i class="fas fa-edit"></i> <!-- Edit icon -->
                    </a>
                    <a href="issue_delete.php?id=<?= $issue['id'] ?>" onclick="return confirm('Are you sure you want to delete this issue? This will delete all comments too.');" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash"></i> <!-- Trash icon for Delete -->
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <p class="text-gray-700"><?= htmlspecialchars($issue['short_description']) ?></p>
            <div class="mt-4">
                <div class="flex items-center space-x-2">
                    <!-- Comment icon now serves as the clickable link to the view page -->
                    <a href="issue_view.php?id=<?= $issue['id'] ?>" class="flex items-center space-x-2 text-gray-500 hover:text-blue-700">
                        <i class="fas fa-comment-alt"></i>
                        <span><?= $issue['comment_count'] ?> Comments</span>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        const hamburgerIcon = document.getElementById('hamburger-icon');
        const hamburgerMenu = document.getElementById('hamburger-menu');
        const successMessage = document.getElementById('success-message');

        // Toggle the menu visibility when the hamburger icon is clicked
        hamburgerIcon.addEventListener('click', () => {
            hamburgerMenu.classList.toggle('hidden');
        });

        // Close the menu if clicked outside
        document.addEventListener('click', (e) => {
            if (!hamburgerIcon.contains(e.target) && !hamburgerMenu.contains(e.target)) {
                hamburgerMenu.classList.add('hidden');
            }
        });

        // Hide success message after 3 seconds if it exists
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 3000); // 3000 milliseconds = 3 seconds
        }
    </script>
</body>
</html>
