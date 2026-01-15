<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$type = sanitize($_POST['type'] ?? '');
$category = sanitize($_POST['category'] ?? '');
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
$occurred_on = sanitize($_POST['occurred_on'] ?? '');
$description = sanitize($_POST['description'] ?? '');

if ($id <= 0 || $type === '' || $category === '' || $amount <= 0 || $occurred_on === '') {
    $_SESSION['flash_error'] = 'Please provide valid values for all fields.';
    redirect('dashboard.php');
}

$res = update_transaction($mysqli, (int)$_SESSION['user_id'], $id, $type, $category, $amount, $occurred_on, $description);
if ($res['success']) {
    $_SESSION['flash_success'] = 'Transaction updated.';
} else {
    $_SESSION['flash_error'] = $res['message'] ?? 'Failed to update transaction.';
}
redirect('dashboard.php');
