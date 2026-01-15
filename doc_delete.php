<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('documents.php');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid document id.';
    redirect('documents.php');
}

$res = delete_document($mysqli, (int)$_SESSION['user_id'], $id);
if ($res['success']) {
    $_SESSION['flash_success'] = 'Document deleted.';
} else {
    $_SESSION['flash_error'] = $res['message'] ?? 'Failed to delete document.';
}
redirect('documents.php');
