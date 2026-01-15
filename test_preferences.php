<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name('pasqyra_sess');
    session_start();
}

// Simulate a logged-in user for testing
$_SESSION['user_id'] = 1; // Replace with an actual user ID from your database

// Get user preferences
$prefs = get_user_preferences($mysqli, $_SESSION['user_id']);

echo "<h1>User Preferences Test</h1>";
echo "<pre>";
echo "Current User ID: " . $_SESSION['user_id'] . "\n";
echo "Current Preferences: \n";
print_r($prefs);

echo "\nFormatting test (1000): " . format_currency(1000) . "\n";

echo "</pre>";

// Test setting preferences
echo "<h2>Test Setting Preferences</h2>";
$result = set_user_preferences($mysqli, $_SESSION['user_id'], 'dark', 'ALL');
echo "Set dark theme and ALL currency: " . ($result['success'] ? 'Success' : 'Failed') . "\n";

// Get updated preferences
$prefs = get_user_preferences($mysqli, $_SESSION['user_id']);
echo "<pre>Updated Preferences: \n";
print_r($prefs);
echo "Formatting test (1000): " . format_currency(1000) . "\n";

// Reset to defaults
set_user_preferences($mysqli, $_SESSION['user_id'], 'system', 'EUR');
echo "</pre>";

// Test with session-based preferences
echo "<h2>Session-Based Preferences Test</h2>";
$_SESSION['user_prefs'] = [
    'theme' => 'light',
    'currency' => 'EUR'
];

echo "<pre>";
echo "Session Theme: " . ($_SESSION['user_prefs']['theme'] ?? 'not set') . "\n";
echo "Session Currency: " . ($_SESSION['user_prefs']['currency'] ?? 'not set') . "\n";
echo "Formatting test (1000): " . format_currency(1000) . "\n";

echo "\nChanging to dark theme and ALL currency via session...\n";
$_SESSION['user_prefs']['theme'] = 'dark';
$_SESSION['user_prefs']['currency'] = 'ALL';

echo "Session Theme: " . $_SESSION['user_prefs']['theme'] . "\n";
echo "Session Currency: " . $_SESSION['user_prefs']['currency'] . "\n";
echo "Formatting test (1000): " . format_currency(1000) . "\n";

echo "</pre>";
