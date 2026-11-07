<?php
// Ends the admin session (voter session, if any, is not touched).
require_once __DIR__ . '/../config.php';

unset($_SESSION['admin_id'], $_SESSION['admin_username']);

header('Location: ' . BASE_URL . '/admin/login.php');
exit;
