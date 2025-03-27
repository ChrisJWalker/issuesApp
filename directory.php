<?php
session_start();
require_once __DIR__ . '/database/database.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pdo = Database::connect();
$stmt = $pdo->query("SELECT id, fname, lname, email FROM iss_persons");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
Database::disconnect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directory - DSR</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Navbar (Add a link to Directory here) -->
    <nav class="bg-blue-500 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="font-bold">DSR</a>
            <div class="space-x-4">
                <a href="homepage.php" class="hover:text-gray-200">Home</a>
                <a href="directory.php" class="hover:text-gray-200">Directory</a> <!-- Link to Directory -->
                <a href="logout.php" class="hover:text-gray-200">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Directory Content -->
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-6">User Directory</h1>
        <?php if (count($users) > 0): ?>
            <ul class="space-y-4">
                <?php foreach ($users as $user): ?>
                    <li class="p-4 bg-white shadow-md rounded-lg">
                        <p class="font-semibold"><?= htmlspecialchars($user['fname']) ?> <?= htmlspecialchars($user['lname']) ?></p>
                        <p class="text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>

</body>
</html>
