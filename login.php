<?php
session_start();
include('config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // For now using hardcoded admin login
    if ($username === "admin" && $password === "admin123") {
        $_SESSION['admin'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid Credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - KVC Exam Management</title>
</head>
<body>
    <h2>Admin Login</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        <label>Username:</label><input type="text" name="username" required><br><br>
        <label>Password:</label><input type="password" name="password" required><br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>