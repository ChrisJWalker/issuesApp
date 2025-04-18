<?php
session_start();
require_once __DIR__ . '/database/database.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit(); // If not logged in, redirect to login page
}

$pdo = Database::connect();

// Handle filters from the GET parameters
$priorityFilter = $_GET['priority'] ?? ''; // Filter for priority (High, Medium, Low)
$myIssuesOnly = isset($_GET['my_issues']) && $_GET['my_issues'] === 'on'; // If checked, only show the user's issues

// Handle sorting, default is 'open_date_desc' (newest first)
$sort = $_GET['sort'] ?? 'open_date_desc';
$orderBy = match ($sort) {
    'open_date_asc' => 'i.open_date ASC',
    'open_date_desc' => 'i.open_date DESC',
    'comments_asc' => 'comment_count ASC',
    'comments_desc' => 'comment_count DESC',
    default => 'i.open_date DESC', // Default sort order
};

// Build the base query for fetching issues
$sql = "SELECT i.id, p.fname, p.lname, i.short_description, i.long_description, i.org, i.project, i.open_date, i.priority, i.per_id AS creator_id,
               (SELECT COUNT(*) FROM iss_comments c WHERE c.iss_id = i.id) AS comment_count
        FROM iss_issues i
        JOIN iss_persons p ON i.per_id = p.id";

// Initialize conditions array and parameters array for SQL
$conditions = [];
$params = [];

// Apply filters if they exist
if ($priorityFilter) {
    $conditions[] = "i.priority = :priority"; // Add priority filter condition
    $params[':priority'] = $priorityFilter; // Bind the priority parameter
}

if ($myIssuesOnly) {
    $conditions[] = "i.per_id = :user_id"; // Filter to show only the logged-in user's issues
    $params[':user_id'] = $_SESSION['user_id']; // Bind the user ID parameter
}

// Add the WHERE clause if there are any conditions
if ($conditions) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Apply sorting order to the query
$sql .= " ORDER BY $orderBy";

// Prepare and execute the query with the provided parameters
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$issues = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all matching issues

Database::disconnect();

// Retrieve success message from the GET parameters
$message = $_GET['message'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Issues List - DSR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4">

    <h1 class="text-3xl font-semibold my-4">Issues List</h1>

    <!-- Display success message if available -->
    <?php if ($message): ?>
        <div id="success-message" class="text-green-500 bg-green-100 border border-green-500 p-2 mb-4 rounded">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Hamburger menu button for navigation -->
    <div class="fixed top-4 right-4 z-50">
        <button id="hamburger-icon" class="text-2xl text-gray-800 focus:outline-none">
            <i class="fas fa-bars"></i>
        </button>
        <div id="hamburger-menu" class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-lg hidden">
            <a href="profile_view.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">View Profile</a>
            <a href="directory.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Directory</a>
            <a href="logout.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Logout</a>
        </div>
    </div>

    <!-- Floating create issue button -->
    <div class="fixed bottom-4 right-4 z-50">
        <a href="issue_create.php" class="bg-blue-500 text-white p-4 rounded-full shadow-lg">
            <i class="fas fa-plus text-2xl"></i>
        </a>
    </div>

    <!-- Filter and Sort Form -->
    <form method="GET" class="mb-6 w-full max-w-4xl bg-white shadow-md rounded-lg p-4 flex flex-wrap gap-4 items-center">
        <!-- Priority filter dropdown -->
        <div class="flex items-center space-x-2">
            <label for="priority" class="font-medium">Priority:</label>
            <select name="priority" id="priority" class="border border-gray-300 rounded p-2">
                <option value="">All</option>
                <option value="High" <?= $priorityFilter === 'High' ? 'selected' : '' ?>>High</option>
                <option value="Medium" <?= $priorityFilter === 'Medium' ? 'selected' : '' ?>>Medium</option>
                <option value="Low" <?= $priorityFilter === 'Low' ? 'selected' : '' ?>>Low</option>
            </select>
        </div>

        <!-- Filter for showing only user's issues -->
        <div class="flex items-center space-x-2">
            <label for="my_issues" class="font-medium">My Issues:</label>
            <input type="checkbox" name="my_issues" id="my_issues" <?= $myIssuesOnly ? 'checked' : '' ?> class="w-4 h-4">
        </div>

        <!-- Sort by dropdown -->
        <div class="flex items-center space-x-2">
            <label for="sort" class="font-medium">Sort By:</label>
            <select name="sort" id="sort" class="border border-gray-300 rounded p-2">
                <option value="open_date_desc" <?= $sort === 'open_date_desc' ? 'selected' : '' ?>>Newest</option>
                <option value="open_date_asc" <?= $sort === 'open_date_asc' ? 'selected' : '' ?>>Oldest</option>
                <option value="comments_desc" <?= $sort === 'comments_desc' ? 'selected' : '' ?>>Most Comments</option>
                <option value="comments_asc" <?= $sort === 'comments_asc' ? 'selected' : '' ?>>Fewest Comments</option>
            </select>
        </div>

        <!-- Submit button to apply filters -->
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Apply</button>
    </form>

    <div class="w-full max-w-4xl space-y-6">
        <!-- Loop through the fetched issues and display them -->
        <?php foreach ($issues as $issue): ?>
        <div class="bg-white shadow-lg rounded-lg p-6 border border-gray-300">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <p class="font-semibold text-2xl text-gray-800"><?= htmlspecialchars($issue['short_description']) ?></p>
                    <p class="text-sm text-gray-500">
                        <?= htmlspecialchars($issue['fname']) ?> <?= htmlspecialchars($issue['lname']) ?> |
                        <?= htmlspecialchars($issue['open_date']) ?> |
                        <?= htmlspecialchars($issue['priority']) ?>
                    </p>
                </div>
                <div class="flex space-x-4">
                    <a href="issue_view.php?id=<?= $issue['id'] ?>" class="text-blue-500 hover:text-blue-700">
                        <i class="fas fa-eye"></i>
                    </a>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] || $_SESSION['user_id'] == $issue['creator_id']): ?>
                    <!-- Show edit and delete options if admin or issue creator -->
                    <a href="issue_edit.php?id=<?= $issue['id'] ?>" class="text-yellow-500 hover:text-yellow-700">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="issue_delete.php?id=<?= $issue['id'] ?>" onclick="return confirm('Are you sure you want to delete this issue? This will delete all comments too.');" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-4">
                <a href="issue_view.php?id=<?= $issue['id'] ?>" class="flex items-center space-x-2 text-gray-500 hover:text-blue-700">
                    <i class="fas fa-comment-alt"></i>
                    <span><?= $issue['comment_count'] ?> Comments</span>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        // JavaScript to toggle the hamburger menu
        const hamburgerIcon = document.getElementById('hamburger-icon');
        const hamburgerMenu = document.getElementById('hamburger-menu');
        hamburgerIcon.addEventListener('click', function() {
            hamburgerMenu.classList.toggle('hidden');
        });

        // Hide success message after 3 seconds
        if (document.getElementById('success-message')) {
            setTimeout(function() {
                document.getElementById('success-message').style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>
