<?php
session_start();
require_once __DIR__ . '/database/database.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pdo = Database::connect();
// Get the user's profile information (assuming session contains the user_id)
$stmt = $pdo->prepare("SELECT id, fname, lname, mobile, email, admin FROM iss_persons WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
Database::disconnect();

if (!$user) {
    echo "User not found!";
    exit();
}

// Check if the user is an admin
$isAdmin = $user['admin'] === 'Yes'; // Assuming 'Yes' means the user is an admin
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - DSR</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4">

    <h1 class="text-3xl font-semibold my-4">User Profile</h1>

    <!-- Back Button -->
    <div class="mb-4">
        <a href="homepage.php" class="text-blue-500 hover:text-blue-700">
            &larr; Back to Issues List
        </a>
    </div>

    <div class="w-full max-w-2xl bg-white shadow-md rounded-lg p-6">
        <div class="mb-4">
            <!-- Display the [ADMIN] tag if the user is an admin -->
            <p class="font-semibold text-lg">
                <?= htmlspecialchars($user['fname']) ?> <?= htmlspecialchars($user['lname']) ?>
                <?php if ($isAdmin): ?>
                    <span class="text-red-500 font-bold text-sm">[ADMIN]</span> <!-- Special Admin tag -->
                <?php endif; ?>
            </p>
            <p class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
        </div>

        <div class="mb-4">
            <p><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile']) ?></p>
        </div>

    </div>

</body>
</html>
