<?php
// Shared helper functions: session checks, escaping, redirects, flash messages.
// Include this at the top of every page:  require_once __DIR__ . '/includes/auth.php';
// (inside the admin folder use:  require_once __DIR__ . '/../includes/auth.php';)

require_once __DIR__ . '/../config.php';

// Safe output of text inside HTML (prevents XSS)
if (!function_exists('e')) {
    function e($text) {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
}

// Redirect to a path inside the project, e.g. redirect('/login.php');
if (!function_exists('redirect')) {
    function redirect($path) {
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}

// Voter pages: call require_voter() at the top
function require_voter() {
    if (!isset($_SESSION['voter_id'])) {
        redirect('/login.php');
    }
}

// Admin pages: call require_admin() at the top
function require_admin() {
    if (!isset($_SESSION['admin_id'])) {
        redirect('/admin/login.php');
    }
}

// One-time message shown on the next page (success or error)
function flash_set($message, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $message, 'type' => $type];
}

function flash_show() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="msg ' . e($f['type']) . '">' . e($f['msg']) . '</div>';
    }
}

// Returns 'Upcoming', 'Active' or 'Ended' for an election
if (!function_exists('election_status')) {
    function election_status($start_time, $end_time) {
        $now = time();
        if ($now < strtotime($start_time)) { return 'Upcoming'; }
        if ($now > strtotime($end_time))   { return 'Ended'; }
        return 'Active';
    }
}
