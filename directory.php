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

// Connect to the database
$pdo = Database::connect();

// Prepare and execute the SQL query to fetch user details (id, first name, last name, email) from the 'iss_persons' table
$stmt = $pdo->query("SELECT id, fname, lname, email FROM iss_persons");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all user records as an associative array

// Disconnect from the database after fetching data
Database::disconnect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Directory - DSR</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Navigation Bar -->
    <nav class="bg-blue-500 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="font-bold">DSR</a>
            <div class="space-x-4">
                <!-- Navigation links for Home, Directory, and Logout -->
                <a href="homepage.php" class="hover:text-gray-200">Home</a>
                <a href="directory.php" class="hover:text-gray-200">Directory</a>
                <a href="logout.php" class="hover:text-gray-200">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-6">User Directory</h1>

        <!-- Display a list of users if available -->
        <?php if (count($users) > 0): ?>
            <ul class="space-y-4">
                <?php foreach ($users as $user): ?>
                    <li class="p-4 bg-white shadow-md rounded-lg flex justify-between items-center">
                        <div>
                            <!-- Display user's full name and email -->
                            <p class="font-semibold"><?= htmlspecialchars($user['fname']) ?> <?= htmlspecialchars($user['lname']) ?></p>
                            <p class="text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                        </div>

                        <!-- Admin user can edit any other user's profile -->
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <a href="profile_edit.php?id=<?= $user['id'] ?>" class="text-blue-500 hover:underline">Edit</a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <!-- If no users are found, display a message -->
            <p>No users found.</p>
        <?php endif; ?>
    </div>

</body>
</html>
