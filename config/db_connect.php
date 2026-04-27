<?php
// db_connect.php

// Define the path to your SQLite database file
$db_file = __DIR__ . '/../carbase.db';

try {
    // Create (or open) the SQLite database connection using PDO
    $pdo = new PDO("sqlite:" . $db_file);
    
    // Set errormode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If there is an error, kill the process and print the message
    die("Database Connection failed: " . $e->getMessage());
}
?>
