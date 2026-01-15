<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();
$user = current_user($mysqli);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('workers.php');
}
$worker_id = isset($_POST['worker_id']) ? (int)$_POST['worker_id'] : 0;
$start_date = sanitize($_POST['start_date'] ?? '');
$end_date = sanitize($_POST['end_date'] ?? '');
$reason_type = sanitize($_POST['reason_type'] ?? 'other');
$reason_note = sanitize($_POST['reason_note'] ?? '');
if (!$worker_id || !$start_date) {
    $_SESSION['flash_error'] = 'Worker and start date are required';
    redirect('workers.php');
}
$res = add_worker_absence($mysqli, (int)$user['id'], $worker_id, $start_date, $end_date ?: null, $reason_type, $reason_note ?: null);
if ($res['success']) {
    $_SESSION['flash_success'] = 'Absence saved';
} else {
    $_SESSION['flash_error'] = $res['message'] ?? 'Failed to save absence';
}
redirect('workers.php');
