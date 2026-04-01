<?php
// Logout: end current session ONLY. Do not clear remember_token (per requirement).
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Destroy PHP session fully
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Redirect to login with a flag to skip auto-login once
header("Location: index1.php?logout=1&msg=" . urlencode("You have been logged out."));
exit;
?>
