<?php
// FR-6: Voter logs out (the voter session ends).
require_once __DIR__ . '/includes/auth.php';

unset($_SESSION['voter_id'], $_SESSION['voter_name']);
flash_set('You have been logged out.');

redirect('/login.php');
