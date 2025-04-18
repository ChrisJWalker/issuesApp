<?php
// Start the session and include the database connection
session_start();
require_once __DIR__ . '/database/database.php';
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's ID and admin status
$loggedInUserId = $_SESSION['user_id'];
$isAdmin = $_SESSION['is_admin'] ?? false;

// If the user is an admin, allow editing any profile; otherwise, allow editing only their own profile
$editUserId = $isAdmin && isset($_GET['id']) ? (int)$_GET['id'] : $loggedInUserId;

// Establish database connection
$pdo = Database::connect();

// Prepare and execute query to fetch user data based on the profile ID
$stmt = $pdo->prepare("SELECT id, fname, lname, email, mobile, attachment_link FROM iss_persons WHERE id = ?");
$stmt->execute([$editUserId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If the user is not found, display an error message
if (!$user) {
    echo "Error: User not found.";
    exit();
}

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data, with empty default values
    $firstName = $_POST['fname'] ?? '';
    $lastName = $_POST['lname'] ?? '';
    $email = $_POST['email'] ?? '';
    $mobile = $_POST['mobile'] ?? '';

    // Check if a profile picture is uploaded and validate it
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Check if the file extension is allowed
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = 'uploads/profile_pics/';
            // Create the upload directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate a unique filename and move the uploaded file
            $newFileName = uniqid() . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Update the profile with the new image link in the database
                $updateStmt = $pdo->prepare("UPDATE iss_persons SET attachment_link = ? WHERE id = ?");
                $updateStmt->execute([$destPath, $editUserId]);
                // Update the user's attachment link in the session
                $user['attachment_link'] = $destPath;
            }
        }
    }

    // Update the user's profile with the new data from the form
    $updateStmt = $pdo->prepare("UPDATE iss_persons SET fname = ?, lname = ?, email = ?, mobile = ? WHERE id = ?");
    $updateStmt->execute([$firstName, $lastName, $email, $mobile, $editUserId]);

    // Redirect to the profile view page after saving the changes
    header("Location: profile_view.php?id=" . $editUserId);
    exit();
}

// Disconnect from the database
Database::disconnect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - DSR</title>
    <!-- Include Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles for the profile picture upload section */
        .profile-img-wrapper {
            position: relative;
            width: 128px;
            height: 128px;
            border-radius: 9999px;
            overflow: hidden;
            border: 4px solid #3B82F6;
            transition: border-color 0.3s;
        }
        .profile-img-wrapper:hover {
            border-color: #1D4ED8;
        }
        .profile-img-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.3);
            color: #e5e7eb;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.875rem;
            font-weight: 500;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .profile-img-wrapper:hover .profile-img-overlay {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-4">
    <!-- Page title -->
    <h1 class="text-3xl font-semibold my-4 text-center">Edit Profile</h1>

    <!-- Main profile editing form -->
    <div class="bg-white shadow-lg rounded-lg p-6 border border-gray-300 max-w-xl mx-auto">
        <form action="profile_edit.php<?= $isAdmin ? '?id=' . $editUserId : '' ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <!-- Profile picture upload section -->
            <div class="flex justify-center">
                <label for="profile_pic_input" class="profile-img-wrapper cursor-pointer group">
                    <img 
                        src="<?= htmlspecialchars($user['attachment_link'] ?: 'uploads/default-profile.png') ?>" 
                        alt="Profile Picture" 
                        class="w-full h-full object-cover"
                    >
                    <div class="profile-img-overlay">Change</div>
                    <input type="file" name="profile_pic" id="profile_pic_input" class="hidden">
                </label>
            </div>

            <!-- First name input -->
            <div>
                <label for="fname" class="block text-gray-700">First Name</label>
                <input type="text" name="fname" id="fname" value="<?= htmlspecialchars($user['fname']) ?>" class="w-full p-2 border border-gray-300 rounded" required>
            </div>

            <!-- Last name input -->
            <div>
                <label for="lname" class="block text-gray-700">Last Name</label>
                <input type="text" name="lname" id="lname" value="<?= htmlspecialchars($user['lname']) ?>" class="w-full p-2 border border-gray-300 rounded" required>
            </div>

            <!-- Email input -->
            <div>
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full p-2 border border-gray-300 rounded" required>
            </div>

            <!-- Mobile input -->
            <div>
                <label for="mobile" class="block text-gray-700">Mobile</label>
                <input type="text" name="mobile" id="mobile" value="<?= htmlspecialchars($user['mobile']) ?>" class="w-full p-2 border border-gray-300 rounded">
            </div>

            <!-- Action buttons: Cancel and Save Changes -->
            <div class="flex justify-end space-x-2">
                <a href="profile_view.php<?= $isAdmin ? '?id=' . $editUserId : '' ?>" class="bg-gray-300 text-gray-800 py-2 px-4 rounded hover:bg-gray-400">Cancel</a>
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Save Changes</button>
            </div>
        </form>
    </div>
</body>
</html>
