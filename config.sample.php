<?php
// config.php - database connection and session start
if (session_status() === PHP_SESSION_NONE) { session_start(); }

define('BASE_URL', '/Online-Voting-System');   // must match your project folder name in htdocs

$conn = new mysqli('localhost', 'root', '', 'online_voting');
if ($conn->connect_error) {
    die('Database connection failed.');
}
