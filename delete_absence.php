<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();
$user = current_user($mysqli);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('workers.php');
}

$absence_id = isset($_POST['absence_id']) ? (int)$_POST['absence_id'] : 0;
if (!$absence_id) {
    $_SESSION['flash_error'] = 'Absence ID is required';
    redirect('workers.php');
}

// Delete the absence
$stmt = $mysqli->prepare('DELETE FROM worker_absences WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $absence_id, $user['id']);

if ($stmt->execute()) {
    $_SESSION['flash_success'] = 'Absence record deleted successfully';
} else {
    $_SESSION['flash_error'] = 'Failed to delete absence record';
}

redirect('workers.php');
