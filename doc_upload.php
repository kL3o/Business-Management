<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('documents.php');
}

$user_id = (int)$_SESSION['user_id'];
$display_name = trim($_POST['display_name'] ?? '');

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['flash_error'] = 'Please choose a file to upload.';
    redirect('documents.php');
}

$file = $_FILES['file'];
$origName = $display_name !== '' ? $display_name : $file['name'];
$mime = $file['type'] ?: null;
$size = (int)$file['size'];

// Create user uploads directory
$partner = current_partner_id();
$pDir = $partner ? (int)$partner : 0;
$uploadsDir = __DIR__ . '/uploads/documents/' . $user_id . '/' . $pDir;
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0775, true);
}

// Sanitize stored name and ensure uniqueness
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$storedName = bin2hex(random_bytes(8)) . ($ext ? ('.' . strtolower($ext)) : '');
$targetPath = $uploadsDir . '/' . $storedName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    $_SESSION['flash_error'] = 'Failed to save uploaded file.';
    redirect('documents.php');
}

$res = add_document($mysqli, $user_id, $origName, $storedName, $mime, $size);
if ($res['success']) {
    // Keep user on listing page; do not auto-open document
    $_SESSION['flash_success'] = 'Document uploaded.';
} else {
    // Rollback file if DB failed
    @unlink($targetPath);
    $_SESSION['flash_error'] = $res['message'] ?? 'Failed to save document.';
}
redirect('documents.php');
