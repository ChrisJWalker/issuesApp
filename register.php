<?php
session_start();
require_once __DIR__ . '/database/database.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user input
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if all fields are filled
    if (!empty($fname) && !empty($lname) && !empty($mobile) && !empty($email) && !empty($password)) {
        
        // Check if email already exists
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT id FROM iss_persons WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email is already taken!';
        } else {
            // Generate a salt
            $salt = bin2hex(random_bytes(32)); // Generate a random 32-byte salt

            // Hash the password with MD5 and the salt
            $pwd_hash = md5($password . $salt); 

            // Insert user into the database
            $stmt = $pdo->prepare("INSERT INTO iss_persons (fname, lname, mobile, email, pwd_hash, pwd_salt, admin) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$fname, $lname, $mobile, $email, $pwd_hash, $salt, 'No']); // Default to 'No' for admin

            $success = 'Registration successful! You can now <a href="login.php">login</a>.';
        }
        Database::disconnect();
    } else {
        $error = 'Please fill all fields!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DSR</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Register - Department Status Report</h2>

        <!-- Display error or success message -->
        <?php if ($error): ?>
            <p class="text-red-500 text-sm mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="text-green-500 text-sm mb-4"><?= $success ?></p>
        <?php endif; ?>

        <form method="post" action="register.php" class="space-y-4">
            <div>
                <label for="fname" class="block text-sm font-medium text-gray-700">First Name</label>
                <input type="text" name="fname" id="fname" required class="mt-1 p-2 w-full border rounded-md">
            </div>
            <div>
                <label for="lname" class="block text-sm font-medium text-gray-700">Last Name</label>
                <input type="text" name="lname" id="lname" required class="mt-1 p-2 w-full border rounded-md">
            </div>
            <div>
                <label for="mobile" class="block text-sm font-medium text-gray-700">Mobile</label>
                <input type="text" name="mobile" id="mobile" required class="mt-1 p-2 w-full border rounded-md">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" required class="mt-1 p-2 w-full border rounded-md">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 p-2 w-full border rounded-md">
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600">Register</button>
        </form>

        <p class="mt-4 text-center">
            Already have an account? <a href="login.php" class="text-blue-500 hover:text-blue-700">Login here</a>.
        </p>
    </div>
</body>
</html>
