<?php
// remember_diag.php — temporary diagnostics (remove after testing)
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/conn.php';

$host = $_SERVER['HTTP_HOST'] ?? '';
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'on' : 'off';
$xfp = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';

echo "<h3>Remember Me Diagnostics</h3>";
echo "<pre>";
echo "Host: $host\n";
echo "HTTPS: $https\n";
echo "X-Forwarded-Proto: $xfp\n";
echo "Cookie[remember_token]: " . (isset($_COOKIE['remember_token']) ? $_COOKIE['remember_token'] : '(none)') . "\n";
echo "</pre>";

if (!empty($_COOKIE['remember_token'])) {
    $tok = $_COOKIE['remember_token'];
    if ($stmt = $conn->prepare("SELECT id, name, type, FCP, UL FROM user_info WHERE remember_token = ? LIMIT 1")) {
        $stmt->bind_param("s", $tok);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            echo "<p style='color:green'>DB match found for token. User id: <b>{$row['id']}</b>, name: <b>{$row['name']}</b>, type: <b>{$row['type']}</b>, FCP: <b>{$row['FCP']}</b></p>";
        } else {
            echo "<p style='color:red'>No DB match for cookie token. Likely stale cookie.</p>";
        }
    } else {
        echo "<p style='color:red'>Prepare failed.</p>";
    }
} else {
    echo "<p>No remember_token cookie present.</p>";
}

echo "<hr><p><small>Remove this file after testing.</small></p>";
?>