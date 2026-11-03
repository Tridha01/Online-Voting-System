<?php
// includes/auth.php - checks that a user is logged in
require_once __DIR__ . '/../config.php';

function require_voter() {
    if (!isset($_SESSION['voter_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function require_admin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}
