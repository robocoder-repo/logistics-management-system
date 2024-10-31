
<?php
require_once 'auth.php';

// Database connection
$db = new mysqli('localhost', 'username', 'password', 'logistics_db');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$auth = new Auth($db);

// Logout the user
$auth->logout();

// Redirect to the login page
header("Location: login.php");
exit();
?>
