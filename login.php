<?php
// Start the session
session_start();

// Check if the user is already logged in, redirect to the main page
if (isset($_SESSION['user'])) {
    header("Location: display_data.php");
    exit();
}

// Check if the login form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Hardcoded username and password (replace with your actual authentication logic)
    $validUsername = "admin";
    $validPassword = "password";

    // Retrieve user input
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the input matches the valid credentials
    if ($username === $validUsername && $password === $validPassword) {
        // Determine the user role (e.g., 'user' or 'admin')
        $userRole = ($username === 'admin') ? 'admin' : 'user';

        // Store user information in the session
        $_SESSION['user'] = $username;
        $_SESSION['role'] = $userRole;

        // Redirect to the main page
        header("Location: display_data.php");
        exit();
    } else {
        // Authentication failed, show an error message
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-container {
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        button {
            background-color: #4caf50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Login</h2>

        <?php
        // Display error message if authentication failed
        if (isset($error)) {
            echo "<p style='color: red;'>$error</p>";
        }
        ?>

        <form method="post" action="">
            <label for="username">Username:</label>
            <input type="text" name="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>

</body>
</html>
