<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register.php');
}

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($name === '' || $email === '' || $password === '' || $confirm === '') {
    $_SESSION['flash_error'] = 'Please fill in all fields.';
    redirect('register.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_error'] = 'Invalid email address.';
    redirect('register.php');
}

if ($password !== $confirm) {
    $_SESSION['flash_error'] = 'Passwords do not match.';
    redirect('register.php');
}

$result = register_user($mysqli, $name, $email, $password);
if ($result['success']) {
    $_SESSION['flash_success'] = $result['message'];
    redirect('login.php');
} else {
    $_SESSION['flash_error'] = $result['message'];
    redirect('register.php');
}
