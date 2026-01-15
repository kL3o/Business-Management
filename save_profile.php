<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('profile.php');
}

$business_name = sanitize($_POST['business_name'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$year_of_creation = sanitize($_POST['year_of_creation'] ?? '');
$nipti = sanitize($_POST['nipti'] ?? '');

$res = upsert_business_profile($mysqli, (int)$_SESSION['user_id'], $business_name, $address ?: null, $year_of_creation ?: null, $nipti ?: null);
if (!$res['success']) {
    $_SESSION['flash_error'] = $res['message'] ?? 'Failed to save profile';
    redirect('profile.php');
}

// Collect IBANs array
$ibans = [];
if (isset($_POST['ibans']) && is_array($_POST['ibans'])) {
    foreach ($_POST['ibans'] as $row) {
        $ibans[] = [
            'iban' => sanitize($row['iban'] ?? ''),
            'bank_name' => sanitize($row['bank_name'] ?? ''),
        ];
    }
}
$ires = replace_bank_ibans($mysqli, (int)$_SESSION['user_id'], $ibans);
if (!$ires['success']) {
    $_SESSION['flash_error'] = $ires['message'] ?? 'Failed to save IBANs';
    redirect('profile.php');
}

// Handle uploads for logo and favicon
$uploadLogoPath = null;
$uploadFaviconPath = null;
$imagesDir = __DIR__ . '/images';
if (!is_dir($imagesDir)) { @mkdir($imagesDir, 0777, true); }
$uid = (int)$_SESSION['user_id'];

if (isset($_FILES['logo_file']) && is_uploaded_file($_FILES['logo_file']['tmp_name'])) {
    $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['png','jpg','jpeg'])) {
        $fname = "brand_{$uid}_logo." . ($ext === 'jpeg' ? 'jpg' : $ext);
        $destFs = $imagesDir . '/' . $fname;
        if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $destFs)) {
            $uploadLogoPath = 'images/' . $fname;
        }
    }
}

if (isset($_FILES['favicon_file']) && is_uploaded_file($_FILES['favicon_file']['tmp_name'])) {
    $ext = strtolower(pathinfo($_FILES['favicon_file']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['ico','png'])) {
        $fname = "brand_{$uid}_favicon." . $ext;
        $destFs = $imagesDir . '/' . $fname;
        if (move_uploaded_file($_FILES['favicon_file']['tmp_name'], $destFs)) {
            $uploadFaviconPath = 'images/' . $fname;
        }
    }
}

if ($uploadLogoPath !== null || $uploadFaviconPath !== null) {
    $bres = update_branding_paths($mysqli, $uid, $uploadLogoPath, $uploadFaviconPath);
    if (!$bres['success']) {
        $_SESSION['flash_error'] = $bres['message'] ?? 'Failed to update branding';
        redirect('profile.php');
    }
}

$_SESSION['flash_success'] = 'Profile saved successfully.';
redirect('profile.php');
