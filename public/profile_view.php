<?php
// Start the session to track user login state
session_start();

// Include the database connection file
require_once __DIR__ . '../database/database.php';

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Connect to the database
$pdo = Database::connect();

// Prepare and execute query to fetch the logged-in user's details
$stmt = $pdo->prepare("SELECT id, fname, lname, mobile, email, admin, attachment_link FROM iss_persons WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Disconnect from the database
Database::disconnect();

// Check if user is found
if (!$user) {
    echo "User not found!";
    exit();
}

// Check if the logged-in user is an admin (based on 'admin' column)
$isAdmin = $user['admin'] === 'Yes';

// Set the profile picture URL, using default if not set
$profilePic = $user['attachment_link'] ?: 'uploads/default-profile.png'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - DSR</title>
    <!-- Include Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4">

    <!-- Page Title -->
    <h1 class="text-3xl font-semibold my-4">User Profile</h1>

    <!-- Back Button: Navigates to the homepage -->
    <div class="mb-4">
        <a href="homepage.php" class="text-blue-500 hover:text-blue-700">
            &larr; Back to Issues List
        </a>
    </div>

    <!-- Profile Display Section -->
    <div class="w-full max-w-2xl bg-white shadow-md rounded-lg p-6">
        <div class="flex items-start mb-4">
            <!-- Profile Image -->
            <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture"
                 class="w-24 h-24 rounded-full border-4 border-blue-500 object-cover mr-6">

            <div>
                <!-- Display User's Name and Email -->
                <p class="font-semibold text-lg">
                    <?= htmlspecialchars($user['fname']) ?> <?= htmlspecialchars($user['lname']) ?>
                    <!-- Show [ADMIN] if the user is an admin -->
                    <?php if ($isAdmin): ?>
                        <span class="text-red-500 font-bold text-sm">[ADMIN]</span>
                    <?php endif; ?>
                </p>
                <p class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                <p class="text-sm text-gray-500 mt-1"><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile']) ?></p>
            </div>
        </div>

        <!-- Edit Profile Button: Links to profile editing page -->
        <div class="mt-6 text-center">
            <a href="profile_edit.php" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                Edit Profile
            </a>
        </div>
    </div>

</body>
</html>
