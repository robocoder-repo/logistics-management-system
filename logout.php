
<?php
require_once 'auth.php';

// Include database configuration
require_once 'db_config.php';

$auth = new Auth($db);

// Logout the user
$auth->logout();

// Redirect to the login page
header("Location: login.php?logout=success");
exit();
?>
