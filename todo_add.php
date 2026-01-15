<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('todos.php');
}

$title = sanitize($_POST['title'] ?? '');
$notes = sanitize($_POST['notes'] ?? '');
$due_date = sanitize($_POST['due_date'] ?? '');

$res = add_todo_with_due_date($mysqli, (int)$_SESSION['user_id'], $title, $notes ?: null, $due_date ?: null);
if ($res['success']) {
    $_SESSION['flash_success'] = 'Task added.';
} else {
    $_SESSION['flash_error'] = $res['message'] ?? 'Failed to add task';
}
redirect('todos.php');
