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

$pdo = Database::connect();
$stmt = $pdo->prepare("SELECT id, fname, lname, email, phone, org FROM iss_persons WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Error: User not found.";
    exit();
}

// Handle form submission to update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['fname'];
    $lastName = $_POST['lname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $organization = $_POST['org'];

    $updateStmt = $pdo->prepare("UPDATE iss_persons SET fname = ?, lname = ?, email = ?, phone = ?, org = ? WHERE id = ?");
    $updateStmt->execute([$firstName, $lastName, $email, $phone, $organization, $userId]);

    // Redirect to the profile page after update
    header("Location: profile_view.php");
    exit();
}

Database::disconnect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - DSR</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-4">

    <h1 class="text-3xl font-semibold my-4">Edit Profile</h1>

    <div class="bg-white shadow-lg rounded-lg p-6 border border-gray-300">
        <form action="profile_edit.php" method="POST" class="space-y-4">
            <div>
                <label for="fname" class="block text-gray-700">First Name</label>
                <input type="text" name="fname" id="fname" value="<?= htmlspecialchars($user['fname']) ?>" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div>
                <label for="lname" class="block text-gray-700">Last Name</label>
                <input type="text" name="lname" id="lname" value="<?= htmlspecialchars($user['lname']) ?>" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div>
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div>
                <label for="phone" class="block text-gray-700">Phone</label>
                <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div>
                <label for="org" class="block text-gray-700">Organization</label>
                <input type="text" name="org" id="org" value="<?= htmlspecialchars($user['org']) ?>" class="w-full p-2 border border-gray-300 rounded">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Save Changes</button>
            </div>
        </form>

        <a href="profile_view.php" class="text-blue-500 hover:text-blue-700 mt-4 inline-block">Back to Profile</a>
    </div>

</body>
</html>
