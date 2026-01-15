<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('profile.php');
}

$pid = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($pid <= 0) {
    $_SESSION['flash_error'] = 'Invalid partner id.';
    redirect('profile.php');
}

$res = delete_partner($mysqli, (int)$_SESSION['user_id'], $pid);
if ($res['success']) {
    $_SESSION['flash_success'] = 'Partner deleted.';
} else {
    $_SESSION['flash_error'] = $res['message'] ?? 'Failed to delete partner.';
}
redirect('profile.php');
