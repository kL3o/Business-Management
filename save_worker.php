<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();
$user = current_user($mysqli);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('workers.php');
}
$name = sanitize($_POST['name'] ?? '');
$worker_type = sanitize($_POST['worker_type'] ?? '');
$monthly_salary = $_POST['monthly_salary'] ?? '';
$daily_rate = $_POST['daily_rate'] ?? '';
$monthly_salary = $monthly_salary !== '' ? (float)$monthly_salary : null;
$daily_rate = $daily_rate !== '' ? (float)$daily_rate : null;
$res = add_worker($mysqli, (int)$user['id'], $name, $worker_type, $monthly_salary, $daily_rate);
if ($res['success']) {
    $_SESSION['flash_success'] = 'Worker added successfully';
} else {
    $_SESSION['flash_error'] = $res['message'] ?? 'Failed to add worker';
}
redirect('workers.php');
