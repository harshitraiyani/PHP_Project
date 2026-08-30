<?php
// ==========================================================
// Database Configuration
// ==========================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'raiyani_7272');
define('DB_NAME', 'vehicle_service_portal');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

session_start();

define('BASE_URL', '/');
