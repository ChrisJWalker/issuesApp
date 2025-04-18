<?php
// Start the session to store user information
session_start();

// Include the database connection file
require '../database/database.php';

// Establish a connection to the database
$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Initialize an error variable to display error messages
$error = '';

// Check if the form has been submitted via POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim the email and password inputs
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Ensure that both email and password are provided
    if (!empty($email) && !empty($password)) {
        // Prepare a query to fetch user information based on the provided email
        $stmt = $pdo->prepare('SELECT id, pwd_hash, pwd_salt, admin FROM iss_persons WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the user was found in the database
        if ($user) {
            // Hash the entered password with the stored salt and compare it with the stored hash
            $computed_hash = md5($password . $user['pwd_salt']);
            if ($computed_hash === $user['pwd_hash']) {
                // Set session variables for user ID and admin status
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['is_admin'] = ($user['admin'] === 'Yes');
                // Redirect to the homepage
                header('Location: homepage.php');
                exit();
            } else {
                // If password does not match, set an error message
                $error = 'Invalid email or password.';
            }
        } else {
            // If user is not found, set an error message
            $error = 'Invalid email or password.';
        }        
    } else {
        // If email or password is empty, set an error message
        $error = 'Please enter both email and password.';
    }
}

// Disconnect from the database
Database::disconnect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login - DSR</title>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <!-- Main login form container -->
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <!-- Page title -->
        <h2 class="text-2xl font-bold mb-6 text-center">Department Status Report - Login</h2>
        <!-- Display error message if there's an error -->
        <?php if ($error): ?>
            <p class="text-red-500 text-sm mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <!-- Login form -->
        <form method="post" action="login.php" class="space-y-4">
            <!-- Email input field -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                <input type="email" name="email" id="email" required class="mt-1 p-2 w-full border rounded-md">
            </div>
            <!-- Password input field -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                <input type="password" name="password" id="password" required class="mt-1 p-2 w-full border rounded-md">
            </div>
            <!-- Submit button for the login form -->
            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600">Login</button>
        </form>

        <!-- Register Button -->
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600">Don't have an account?</p>
            <!-- Link to the registration page -->
            <a href="register.php" class="inline-block mt-2 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md">
                Register
            </a>
        </div>
    </div>
</body>
</html>
