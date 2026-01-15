<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name('pasqyra_sess');
    session_start();
}

// (language processing moved below after $params/$response are initialized)

// Determine if this is an AJAX request (XHR) or normal form POST
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax) {
    header('Content-Type: application/json');
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Accept both POST and GET to simplify navbar actions
$params = array_merge($_GET ?? [], $_POST ?? []);

// Get the database connection
require_once __DIR__ . '/includes/db.php';
// Ensure schema for user preferences
ensure_user_prefs_schema($mysqli);

$response = ['success' => false, 'message' => ''];

// Process theme update
if (isset($params['theme'])) {
    $theme = in_array($params['theme'], ['light', 'dark', 'system']) ? $params['theme'] : 'system';
    $stmt = $mysqli->prepare('UPDATE users SET theme = ? WHERE id = ?');
    $stmt->bind_param('si', $theme, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['user_prefs']['theme'] = $theme;
        $response['success'] = true;
        $response['message'] = 'Theme updated successfully';
    } else {
        $response['message'] = 'Failed to update theme';
    }
}

// Process language update (en <-> sq)
if (isset($params['language'])) {
    $language = in_array(strtolower($params['language']), ['en','sq']) ? strtolower($params['language']) : 'en';
    $stmt = $mysqli->prepare('UPDATE users SET language = ? WHERE id = ?');
    $stmt->bind_param('si', $language, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $_SESSION['user_prefs']['language'] = $language;
        $response['success'] = true;
        $response['message'] = $response['message'] ? $response['message'] . ' Language updated.' : 'Language updated successfully';
    } else {
        $response['message'] = $response['message'] ?: 'Failed to update language';
    }
}

// Process currency update
if (isset($params['currency'])) {
    $currency = in_array($params['currency'], ['EUR', 'ALL']) ? $params['currency'] : 'EUR';
    $stmt = $mysqli->prepare('UPDATE users SET currency = ? WHERE id = ?');
    $stmt->bind_param('si', $currency, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['user_prefs']['currency'] = $currency;
        $response['success'] = true;
        $response['message'] = $response['message'] ? $response['message'] . ' Currency updated.' : 'Currency updated successfully';
    } else {
        $response['message'] = $response['message'] ?: 'Failed to update currency';
    }
}

// If no valid updates were made
if (!isset($params['theme']) && !isset($params['currency']) && !isset($params['language'])) {
    $response['message'] = 'No valid preferences to update';
}

if ($isAjax) {
    echo json_encode($response);
    exit;
}

// For normal POST form, redirect back to the referring page (or dashboard)
$back = $_SERVER['HTTP_REFERER'] ?? base_url('dashboard.php');
header('Location: ' . $back);
exit;
