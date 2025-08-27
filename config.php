<?php
// VulnShop Database Configuration
// WARNING: This is intentionally vulnerable for training purposes only!

// VULNERABILITY: Security Misconfiguration - Hardcoded credentials
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'vulnshop';

// VULNERABILITY: Security Misconfiguration - Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    // VULNERABILITY: Information Disclosure - Exposing database errors
    die("Connection failed: " . $conn->connect_error);
}

// VULNERABILITY: Session Fixation - No session regeneration
session_start();

// VULNERABILITY: No CSRF protection
// No CSRF tokens implemented anywhere

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to get current user
function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) return null;
    
    $user_id = $_SESSION['user_id'];
    // VULNERABILITY: SQL Injection possible if session is manipulated
    $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
    return $result->fetch_assoc();
}
?>