<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('materials.php');
}

$material_name = sanitize($_POST['material_name'] ?? '');
$unit = sanitize($_POST['unit'] ?? 'pcs');
$quantity = isset($_POST['quantity']) ? (float)$_POST['quantity'] : 0;
$unit_price = isset($_POST['unit_price']) ? (float)$_POST['unit_price'] : 0;
$purchased_on = sanitize($_POST['purchased_on'] ?? '');
$description = sanitize($_POST['description'] ?? '');

if ($material_name === '' || $unit === '' || $quantity <= 0 || $unit_price < 0 || $purchased_on === '') {
    $_SESSION['flash_error'] = 'Please fill in all required fields with valid values.';
    redirect('materials.php');
}

$res = add_material_purchase($mysqli, (int)$_SESSION['user_id'], $material_name, $unit, $quantity, $unit_price, $purchased_on, $description);
if ($res['success']) {
    // Mirror material purchase as an expense transaction for accurate totals
    $total = $quantity * $unit_price;
    $category = 'Materials: ' . $material_name;
    add_transaction($mysqli, (int)$_SESSION['user_id'], 'expense', $category, (float)$total, $purchased_on, $description ?: '');
    $_SESSION['flash_success'] = 'Material purchase saved.';
} else {
    $_SESSION['flash_error'] = $res['message'];
}
redirect('materials.php');
