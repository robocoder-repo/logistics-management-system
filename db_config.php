<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'your_username');
define('DB_PASSWORD', 'your_password');
define('DB_NAME', 'logistics_db');

// Attempt to connect to MySQL database
$db = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($db === false){
    die("ERROR: Could not connect. " . $db->connect_error);
}

function initializeDatabase($db) {
    $sql = file_get_contents(__DIR__ . '/database_schema.sql');
    
    if ($db->multi_query($sql)) {
        do {
            // Store first result set
            if ($result = $db->store_result()) {
                $result->free();
            }
            // Prepare next result set
        } while ($db->more_results() && $db->next_result());
    }
    
    if ($db->errno) {
        echo "Error initializing database: " . $db->error;
        return false;
    }
    
    return true;
}

// Uncomment the following line to initialize the database (only do this once)
// initializeDatabase($db);
?>
