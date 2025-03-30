<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'exam_management');

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if(!$conn){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if(mysqli_query($conn, $sql)){
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
} else{
    die("ERROR: Could not create database. " . mysqli_error($conn));
}

// Create tables if they don't exist
$sql = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(!mysqli_query($conn, $sql)){
    die("ERROR: Could not create admin_users table. " . mysqli_error($conn));
}

$sql = "CREATE TABLE IF NOT EXISTS classes (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(!mysqli_query($conn, $sql)){
    die("ERROR: Could not create classes table. " . mysqli_error($conn));
}

$sql = "CREATE TABLE IF NOT EXISTS exams (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    exam_name VARCHAR(100) NOT NULL,
    exam_date DATE NOT NULL,
    duration INT NOT NULL,
    total_marks INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id)
)";

if(!mysqli_query($conn, $sql)){
    die("ERROR: Could not create exams table. " . mysqli_error($conn));
}

// Insert default admin user if not exists
$sql = "INSERT IGNORE INTO admin_users (username, password) VALUES ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "')";
mysqli_query($conn, $sql);
?> 