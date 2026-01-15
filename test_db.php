<?php
// Test database connection
require_once __DIR__ . '/config/config.php';

// Try to connect to MySQL without selecting a database first
$link = @new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);

if ($link->connect_error) {
    die("Connection failed: " . $link->connect_error);
}

echo "Successfully connected to MySQL server!<br>";

// Check if database exists
$result = $link->query("SHOW DATABASES LIKE '" . DB_NAME . "'");

if ($result->num_rows > 0) {
    echo "Database '" . DB_NAME . "' exists!<br>";
    
    // Select the database
    if ($link->select_db(DB_NAME)) {
        echo "Successfully selected database '" . DB_NAME . "'<br>";
        
        // Check if users table exists
        $result = $link->query("SHOW TABLES LIKE 'users'");
        if ($result->num_rows > 0) {
            echo "Users table exists!<br>";
        } else {
            echo "Users table does not exist.<br>";
        }
    } else {
        echo "Failed to select database: " . $link->error . "<br>";
    }
} else {
    echo "Database '" . DB_NAME . "' does not exist.<br>";
}

$link->close();
?>
