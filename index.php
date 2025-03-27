<?php
session_start();

// If the user is already logged in, send them to the homepage
if (isset($_SESSION['user_id'])) {
    header("Location: homepage.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - DSR</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex flex-col justify-center items-center">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <h1 class="text-3xl font-bold mb-4">Welcome to Department Status Report</h1>
        <p class="text-gray-600 mb-6">Please login or register to continue.</p>
        
        <div class="space-y-4">
            <a href="login.php" class="block w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600">
                Login
            </a>
            <a href="register.php" class="block w-full bg-green-500 text-white py-2 rounded-md hover:bg-green-600">
                Register
            </a>
        </div>
    </div>

</body>
</html>
