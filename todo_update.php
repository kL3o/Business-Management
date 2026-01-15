<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('todos.php');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$title = sanitize($_POST['title'] ?? '');
$notes = sanitize($_POST['notes'] ?? '');
$is_done = isset($_POST['is_done']) ? (int)$_POST['is_done'] : 0;
$due_date = sanitize($_POST['due_date'] ?? '');

if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid task id.';
    redirect('todos.php');
}

$res = update_todo($mysqli, (int)$_SESSION['user_id'], $id, $title, $notes, $is_done, $due_date ?: null);
if ($res['success']) {
    $_SESSION['flash_success'] = 'Task updated.';
} else {
    $_SESSION['flash_error'] = $res['message'] ?? 'Failed to update task';
}
redirect('todos.php');
