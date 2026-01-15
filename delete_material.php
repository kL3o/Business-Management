<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('materials.php');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid material id.';
    redirect('materials.php');
}

$res = delete_material_purchase($mysqli, (int)$_SESSION['user_id'], $id);
if ($res['success']) {
    $_SESSION['flash_success'] = 'Material entry deleted.';
} else {
    $_SESSION['flash_error'] = $res['message'] ?? 'Failed to delete material.';
}
redirect('materials.php');
