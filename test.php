<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Step 1: Starting test<br>";

// Test session
session_start();
echo "Step 2: Session started<br>";

// Test database connection
require_once 'config/database.php';
echo "Step 3: Database connection included<br>";

// Test database query
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Step 4: Database connected successfully<br>";
    echo "Available tables:<br>";
    foreach ($tables as $table) {
        echo "- $table<br>";
    }
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?> 