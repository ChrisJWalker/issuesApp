<?php
session_start();
require_once __DIR__ . '/database/database.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $org = $_POST['org'];
    $project = $_POST['project'];
    $priority = $_POST['priority'];
    $creator_id = $_SESSION['user_id']; // Logged-in user ID
    $open_date = date('Y-m-d'); // Current date as the open date
    $close_date = '0000-00-00'; // Default close date
    $filePath = NULL; // Default to NULL if no file is uploaded

    // File upload logic
    if (!empty($_FILES['attachment']['name'])) {
        $uploadDir = 'uploads/';
        $fileName = basename($_FILES['attachment']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = uniqid() . "_" . $fileName; // Prevent duplicate names
        $targetFile = $uploadDir . $newFileName;

        // Allowed file types
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        if (in_array($fileExtension, $allowedTypes)) {
            if ($_FILES['attachment']['size'] <= 2 * 1024 * 1024) { // 2MB limit
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
                    $filePath = $targetFile;
                } else {
                    echo "Error: File upload failed.";
                    exit();
                }
            } else {
                echo "Error: File size exceeds 2MB.";
                exit();
            }
        } else {
            echo "Error: Invalid file type. Only JPG, JPEG, PNG, GIF, and PDF are allowed.";
            exit();
        }
    }

    // Insert into database
    $pdo = Database::connect();
    $sql = "INSERT INTO iss_issues (short_description, long_description, org, project, priority, per_id, open_date, close_date, attachment_link) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$short_description, $long_description, $org, $project, $priority, $creator_id, $open_date, $close_date, $filePath]);
    Database::disconnect();

    // Redirect to the issues list with a success message
    header("Location: homepage.php?message=Issue%20has%20been%20created");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Issue - DSR</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4">
    <h1 class="text-3xl font-semibold my-4">Create New Issue</h1>

    <div class="w-full max-w-4xl bg-white shadow-md rounded-lg p-6">
        <form action="issue_create.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="short_description" class="block text-gray-700">Short Description</label>
                <input type="text" name="short_description" id="short_description" required class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div>
                <label for="long_description" class="block text-gray-700">Long Description</label>
                <textarea name="long_description" id="long_description" required class="w-full p-2 border border-gray-300 rounded"></textarea>
            </div>
            <div>
                <label for="org" class="block text-gray-700">Organization</label>
                <input type="text" name="org" id="org" required class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div>
                <label for="project" class="block text-gray-700">Project</label>
                <input type="text" name="project" id="project" required class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div>
                <label for="priority" class="block text-gray-700">Priority</label>
                <input type="text" name="priority" id="priority" required class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div>
                <label for="attachment" class="block text-gray-700">Upload File (Max 2MB, JPG, PNG, GIF, PDF)</label>
                <input type="file" name="attachment" id="attachment" class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Create Issue</button>
            </div>
        </form>
    </div>
</body>
</html>
