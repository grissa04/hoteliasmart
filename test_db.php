<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db = new mysqli('localhost', 'root', '', 'hotel_forum');

if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
} else {
    echo "Database connection successful!<br>";
    
    // Test table existence
    $result = $db->query("SHOW TABLES LIKE 'forum_posts'");
    if ($result->num_rows > 0) {
        echo "forum_posts table exists!";
    } else {
        echo "forum_posts table does not exist!";
    }
}
?>