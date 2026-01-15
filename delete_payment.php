<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();
$user = current_user($mysqli);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('workers.php');
}

$payment_id = isset($_POST['payment_id']) ? (int)$_POST['payment_id'] : 0;
if (!$payment_id) {
    $_SESSION['flash_error'] = 'Payment ID is required';
    redirect('workers.php');
}

// Delete the payment
$stmt = $mysqli->prepare('DELETE FROM worker_payments WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $payment_id, $user['id']);

if ($stmt->execute()) {
    // Also delete the corresponding expense transaction if it exists
    $category = 'Worker Payment%';
    $stmt2 = $mysqli->prepare('DELETE FROM transactions WHERE user_id = ? AND type = "expense" AND category LIKE ? LIMIT 1');
    $stmt2->bind_param('is', $user['id'], $category);
    $stmt2->execute();
    
    $_SESSION['flash_success'] = 'Payment record deleted successfully';
} else {
    $_SESSION['flash_error'] = 'Failed to delete payment record';
}

redirect('workers.php');
