<?php
if(session_status()!==PHP_SESSION_ACTIVE){session_start();}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$host    = $_SERVER['HTTP_HOST'] ?? '';
$ok = true;

if (strtoupper($method) === 'POST') {
    if (!empty($referer)) {
        $refHost = parse_url($referer, PHP_URL_HOST);
        if (!empty($refHost) && strcasecmp($refHost, $host) !== 0) {
            $ok = false;
        }
    } else {
        // Missing Referer on POST — allow (CSRF is handled via your CSRF token on forms)
        $ok = true;
    }
} else {
    // GET requests: allow
    $ok = true;
}

if (!$ok) {
    header("HTTP/1.1 403 Forbidden");
    echo "Forbidden";
    exit;
}
?>