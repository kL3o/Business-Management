<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid document id';
    exit;
}

$user_id = (int)$_SESSION['user_id'];
ensure_documents_table($mysqli);
$partner = current_partner_id();
if ($partner) {
    $stmt = $mysqli->prepare('SELECT original_name, stored_name, mime_type FROM documents WHERE id = ? AND user_id = ? AND partner_id = ? LIMIT 1');
    $stmt->bind_param('iii', $id, $user_id, $partner);
} else {
    $stmt = $mysqli->prepare('SELECT original_name, stored_name, mime_type FROM documents WHERE id = ? AND user_id = ? AND partner_id IS NULL LIMIT 1');
    $stmt->bind_param('ii', $id, $user_id);
}
$stmt->execute();
$res = $stmt->get_result();
$doc = $res->fetch_assoc();
if (!$doc) {
    http_response_code(404);
    echo 'Document not found';
    exit;
}

$pDir = $partner ? (int)$partner : 0;
$uploadsDir = __DIR__ . '/uploads/documents/' . $user_id . '/' . $pDir;
$path = realpath($uploadsDir . '/' . $doc['stored_name']);
if (!$path || !str_starts_with($path, realpath($uploadsDir)) || !is_file($path)) {
    http_response_code(404);
    echo 'File missing';
    exit;
}

$mime = $doc['mime_type'] ?: 'application/octet-stream';
$inline = isset($_GET['inline']) && (int)$_GET['inline'] === 1;
header('Content-Type: ' . $mime);
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . basename($doc['original_name']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
