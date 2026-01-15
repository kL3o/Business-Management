<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    // Make Profile the main page after login
    redirect('profile.php');
} else {
    redirect('login.php');
}
