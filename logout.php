<?php
// logout.php - ends the voter session (FR-6)
require_once __DIR__ . '/config.php';

// Remove only voter data, so an admin logged in on the same browser stays logged in
unset($_SESSION['voter_id'], $_SESSION['voter_name']);

if (empty($_SESSION)) {
    session_destroy();      // nothing left in the session, so end it fully
}

header('Location: ' . BASE_URL . '/login.php');
exit;
