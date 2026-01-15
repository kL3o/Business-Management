<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();
$user = current_user($mysqli);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('workers.php');
}
$worker_id = isset($_POST['worker_id']) ? (int)$_POST['worker_id'] : 0;
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
$paid_on = sanitize($_POST['paid_on'] ?? '');
$period_start = sanitize($_POST['period_start'] ?? '');
$period_end = sanitize($_POST['period_end'] ?? '');
$note = sanitize($_POST['note'] ?? '');
if (!$worker_id || !$paid_on || $amount <= 0) {
    $_SESSION['flash_error'] = 'Worker, amount, and paid date are required';
    redirect('workers.php');
}
$res = add_worker_payment($mysqli, (int)$user['id'], $worker_id, $amount, $paid_on, $period_start ?: null, $period_end ?: null, $note ?: null);
if ($res['success']) {
    // Also add an expense transaction so totals reflect the payment
    $worker = get_worker_by_id($mysqli, (int)$user['id'], $worker_id);
    $category = 'Worker Payment' . ($worker ? (': ' . $worker['name']) : '');
    add_transaction($mysqli, (int)$user['id'], 'expense', $category, $amount, $paid_on, $note ?: '');
    $_SESSION['flash_success'] = 'Payment saved';
} else {
    $_SESSION['flash_error'] = $res['message'] ?? 'Failed to save payment';
}
redirect('workers.php');
