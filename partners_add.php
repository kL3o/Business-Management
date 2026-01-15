<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('profile.php');
}

$name = sanitize($_POST['name'] ?? '');
if ($name === '') {
    $_SESSION['flash_error'] = 'Partner name is required.';
    redirect('profile.php');
}

$res = add_partner($mysqli, (int)$_SESSION['user_id'], $name);
if ($res['success']) {
    $_SESSION['flash_success'] = 'Partner added.';
} else {
    $_SESSION['flash_error'] = $res['message'] ?? 'Failed to add partner.';
}
redirect('profile.php');
