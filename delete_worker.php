<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();
$user = current_user($mysqli);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('workers.php');
}

$worker_id = isset($_POST['worker_id']) ? (int)$_POST['worker_id'] : 0;
if (!$worker_id) {
    $_SESSION['flash_error'] = 'Worker ID is required';
    redirect('workers.php');
}

// First, check if there are any related records
$has_absences = false;
$has_payments = false;

// Check for absences
$stmt = $mysqli->prepare('SELECT COUNT(*) as count FROM worker_absences WHERE worker_id = ? AND user_id = ?');
$stmt->bind_param('ii', $worker_id, $user['id']);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$has_absences = $result['count'] > 0;

// Check for payments
$stmt = $mysqli->prepare('SELECT COUNT(*) as count FROM worker_payments WHERE worker_id = ? AND user_id = ?');
$stmt->bind_param('ii', $worker_id, $user['id']);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$has_payments = $result['count'] > 0;

// If there are related records, show an error
if ($has_absences || $has_payments) {
    $message = 'Cannot delete worker because there are ';
    if ($has_absences) $message .= 'absences';
    if ($has_absences && $has_payments) $message .= ' and ';
    if ($has_payments) $message .= 'payments';
    $message .= ' associated with this worker. Please delete those records first.';
    
    $_SESSION['flash_error'] = $message;
    redirect('workers.php');
}

// If no related records, proceed with deletion
$stmt = $mysqli->prepare('DELETE FROM workers WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $worker_id, $user['id']);

if ($stmt->execute()) {
    $_SESSION['flash_success'] = 'Worker deleted successfully';
} else {
    $_SESSION['flash_error'] = 'Failed to delete worker';
}

redirect('workers.php');
