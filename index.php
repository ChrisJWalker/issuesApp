<?php
session_start(); // Start the session to manage user login status

// If the user is already logged in, redirect them to the homepage
if (isset($_SESSION['user_id'])) {
    header("Location: homepage.php"); // Redirect to homepage
    exit(); // Stop the execution of the script
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Set character encoding to UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ensure responsive design for different screen sizes -->
    <title>Welcome - DSR</title> <!-- Title for the webpage -->
    <script src="https://cdn.tailwindcss.com"></script> <!-- Include Tailwind CSS library -->
</head>
<body class="bg-gray-100 h-screen flex flex-col justify-center items-center"> <!-- Apply Tailwind CSS for styling the body -->

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md text-center"> <!-- Centered white box for the content -->
        <h1 class="text-3xl font-bold mb-4">Welcome to Department Status Report</h1> <!-- Heading text -->
        <p class="text-gray-600 mb-6">Please login or register to continue.</p> <!-- Informational text -->
        
        <div class="space-y-4"> <!-- Create space between the login and register buttons -->
            <a href="login.php" class="block w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600">
                Login
            </a> <!-- Login button, links to login.php -->
            <a href="register.php" class="block w-full bg-green-500 text-white py-2 rounded-md hover:bg-green-600">
                Register
            </a> <!-- Register button, links to register.php -->
        </div>
    </div>

</body>
</html>
