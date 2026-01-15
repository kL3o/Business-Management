<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

$type = sanitize($_POST['type'] ?? '');
$category = sanitize($_POST['category'] ?? '');
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
$occurred_on = sanitize($_POST['occurred_on'] ?? '');
$description = sanitize($_POST['description'] ?? '');

if ($type === '' || $category === '' || $amount <= 0 || $occurred_on === '') {
    $_SESSION['flash_error'] = 'Please fill in all required fields with valid values.';
    redirect('dashboard.php');
}

$res = add_transaction($mysqli, (int)$_SESSION['user_id'], $type, $category, $amount, $occurred_on, $description);
if ($res['success']) {
    $_SESSION['flash_success'] = 'Transaction saved.';
} else {
    $_SESSION['flash_error'] = $res['message'];
}
redirect('dashboard.php');
