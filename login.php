
<?php
require_once 'auth.php';

// Database connection
$db = new mysqli('localhost', 'username', 'password', 'logistics_db');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$auth = new Auth($db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($auth->login($username, $password)) {
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Logistics Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Login</h1>
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    ?>
    <form action="login.php" method="post">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>
