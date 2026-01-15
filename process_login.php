<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['flash_error'] = 'Please enter both email and password.';
    redirect('login.php');
}

$result = login_user($mysqli, $email, $password);
if ($result['success']) {
    redirect('dashboard.php');
} else {
    $_SESSION['flash_error'] = $result['message'];
    redirect('login.php');
}
