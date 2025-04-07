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
$stmt = $pdo->prepare("SELECT id, fname, lname, email, mobile, attachment_link FROM iss_persons WHERE id = ?");
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
    $mobile = $_POST['mobile'];

    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Check if the file is an image
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = 'uploads/profile_pics/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newFileName = uniqid() . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;

            // Resize and crop the image to a square before uploading
            $imageResource = null;
            if ($fileExtension == 'jpg' || $fileExtension == 'jpeg') {
                $imageResource = imagecreatefromjpeg($fileTmpPath);
            } elseif ($fileExtension == 'png') {
                $imageResource = imagecreatefrompng($fileTmpPath);
            } elseif ($fileExtension == 'gif') {
                $imageResource = imagecreatefromgif($fileTmpPath);
            }

            if ($imageResource) {
                // Get original image dimensions
                $width = imagesx($imageResource);
                $height = imagesy($imageResource);

                // Create a square image
                $size = min($width, $height);
                $squareImage = imagecreatetruecolor($size, $size);

                // Copy and resize the image into the square
                imagecopyresized($squareImage, $imageResource, 0, 0, 0, 0, $size, $size, $width, $height);

                // Save the resized image
                if ($fileExtension == 'jpg' || $fileExtension == 'jpeg') {
                    imagejpeg($squareImage, $destPath);
                } elseif ($fileExtension == 'png') {
                    imagepng($squareImage, $destPath);
                } elseif ($fileExtension == 'gif') {
                    imagegif($squareImage, $destPath);
                }

                // Free memory
                imagedestroy($imageResource);
                imagedestroy($squareImage);

                // Save the path of the uploaded file in the attachment_link column
                $updateStmt = $pdo->prepare("UPDATE iss_persons SET attachment_link = ? WHERE id = ?");
                $updateStmt->execute([$destPath, $userId]);
            }
        }
    }

    // Update other profile details
    $updateStmt = $pdo->prepare("UPDATE iss_persons SET fname = ?, lname = ?, email = ?, mobile = ? WHERE id = ?");
    $updateStmt->execute([$firstName, $lastName, $email, $mobile, $userId]);

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
        <!-- Profile Picture Section -->
        <div class="mb-6 flex justify-center">
            <!-- Display Profile Picture -->
            <label for="profile_pic_input" class="relative cursor-pointer">
                <img src="<?= $user['attachment_link'] ? $user['attachment_link'] : 'uploads/default-profile.png' ?>" 
                     alt="Profile Picture" 
                     class="w-32 h-32 rounded-full border-4 border-blue-500 hover:border-blue-700 transition-all">
                <span class="absolute inset-0 flex justify-center items-center text-white text-xl font-bold opacity-0 hover:opacity-100 transition-opacity">
                    Click to Upload
                </span>
                <input type="file" id="profile_pic_input" name="profile_pic" class="hidden">
            </label>
            <div class="text-center mt-2 text-black">Click to Upload</div> <!-- Move the text below the circle -->
        </div>

        <!-- Profile Information Form -->
        <form action="profile_edit.php" method="POST" enctype="multipart/form-data" class="space-y-4">
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
                <label for="mobile" class="block text-gray-700">Mobile</label>
                <input type="text" name="mobile" id="mobile" value="<?= htmlspecialchars($user['mobile']) ?>" class="w-full p-2 border border-gray-300 rounded">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Save Changes</button>
            </div>
        </form>

        <a href="profile_view.php" class="text-blue-500 hover:text-blue-700 mt-4 inline-block">Back to Profile</a>
    </div>

</body>
</html>
