<?php
session_start();
// echo "hello";
// exit();
// Ensure HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}
// echo "hello";
// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include database connection
require "./conn.php";

// Validate and sanitize user input
$TxTID = filter_input(INPUT_POST, 'TxTID', FILTER_SANITIZE_STRING);
$TxTPASS = filter_input(INPUT_POST, 'TxTPASS', FILTER_SANITIZE_STRING);
$REM = filter_input(INPUT_POST, 'remember', FILTER_VALIDATE_INT) ?? 0;
// exit();
// Validate inputs
if (empty($TxTID) || empty($TxTPASS)) {
    die("<script>alert('User ID and Password are required.'); location.href='./index1.php';</script>");
}

// CSRF token validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("<script>alert('Invalid CSRF token.'); location.href='./index1.php';</script>");
}

// Load configuration for static salt (for existing users)
$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/App_Data/app.ini', true);
$salt = $ini["GENERAL"]["SALT"] ?? ''; // Fallback if not set
$timezone = $ini["GENERAL"]["TIMEZONE"] ?? 'Asia/Dhaka'; // Configurable timezone
date_default_timezone_set($timezone);

// Secure "remember me" functionality (avoid storing password in cookie)
if ($REM == 1) {
    // Generate a secure token instead of storing the password
    $remember_token = bin2hex(random_bytes(16));
    setcookie("remember_token", $remember_token, time() + (86400 * 30), "/", null, true, true); // Secure, HttpOnly
    // Store token in database (requires a new column, e.g., `remember_token`)
    $stmt_token = $conn->prepare("UPDATE user_info SET remember_token = ? WHERE id = ?");
    $stmt_token->bind_param("ss", $remember_token, $TxTID);
    $stmt_token->execute();
}

// Fetch user data
try {
    $stmt = $conn->prepare("SELECT `id` AS ID, `pass` AS PASS, `type` AS TYPE, `name` AS NAME, `FCP`, `LogError`, `UL` FROM `user_info` WHERE `id` = ?");
    $stmt->bind_param("s", $TxTID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Log failed attempt
        $date = date("Y-m-d H:i:s");
        $EVENT = "login";
        $REMARKS = "User not found";
        $stmt_log = $conn->prepare("INSERT INTO `log` (`date`, `user`, `event`, `comment`) VALUES (?, ?, ?, ?)");
        $stmt_log->bind_param("ssss", $date, $TxTID, $EVENT, $REMARKS);
        $stmt_log->execute();

        die("<script>alert('User not found.'); location.href='./index1.php';</script>");
    }

    $row = $result->fetch_assoc();
    $ID = $row['ID'];
    $PASS = $row['PASS'];
    $TYPE = $row['TYPE'];
    $NAME = $row['NAME'];
    $FCP = $row['FCP'];
    $LogError = $row['LogError'];
    $UL = $row['UL'];

    // Check account lockout
    if ($LogError > 2) {
        // Log lockout
        $date = date("Y-m-d H:i:s");
        $EVENT = "login";
        $REMARKS = "Account locked due to too many failed attempts";
        $stmt_log = $conn->prepare("INSERT INTO `log` (`date`, `user`, `event`, `comment`) VALUES (?, ?, ?, ?)");
        $stmt_log->bind_param("ssss", $date, $TxTID, $EVENT, $REMARKS);
        $stmt_log->execute();

        session_destroy();
        die("<script>alert('Too many wrong attempts. Account is locked. Please contact an administrator.'); location.href='./index1.php';</script>");
    }

    // Verify password
    $password_verified = false;
    if (strlen($PASS) === 64 && ctype_xdigit($PASS)) {
        // Likely a sha3-256 hash (existing user)
        $password = $TxTPASS . $salt;
        $hash_val = hash('sha3-256', $password);
        $password_verified = ($hash_val === $PASS);

        // Migrate to BCRYPT on successful login
        if ($password_verified) {
            $new_hash = password_hash($TxTPASS, PASSWORD_BCRYPT);
            $stmt_update = $conn->prepare("UPDATE user_info SET pass = ? WHERE id = ?");
            $stmt_update->bind_param("ss", $new_hash, $TxTID);
            $stmt_update->execute();
        }
    } else {
        // Likely a BCRYPT/ARGON2ID hash (new user)
        $password_verified = password_verify($TxTPASS, $PASS);
    }

    if ($password_verified) {
        // Successful login
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['user'] = $TxTID;
        $_SESSION['TYPE'] = $TYPE;
        $_SESSION['NAME'] = $NAME;
        $_SESSION['FCP'] = $FCP;
        $_SESSION['UL'] = $UL;
        $_SESSION['time'] = time();

        // Reset LogError
        $stmt_error = $conn->prepare("UPDATE user_info SET LogError = 0 WHERE id = ?");
        $stmt_error->bind_param("s", $TxTID);
        $stmt_error->execute();

        // Log successful login
        $date = date("Y-m-d H:i:s");
        $EVENT = "login";
        $REMARKS = "Successful login";
        $stmt_log = $conn->prepare("INSERT INTO `log` (`date`, `user`, `event`, `comment`) VALUES (?, ?, ?, ?)");
        $stmt_log->bind_param("ssss", $date, $TxTID, $EVENT, $REMARKS);
        $stmt_log->execute();

        // Update LastLog
        $stmt_ll = $conn->prepare("UPDATE user_info SET LastLog = ? WHERE id = ?");
        $stmt_ll->bind_param("ss", $date, $TxTID);
        $stmt_ll->execute();

        echo "<script>location.href='./index3.php'</script>";
        exit();
    } else {
        // Failed login
        $date = date("Y-m-d H:i:s");
        $EVENT = "login";
        $REMARKS = "Password error";
        $stmt_log = $conn->prepare("INSERT INTO `log` (`date`, `user`, `event`, `comment`) VALUES (?, ?, ?, ?)");
        $stmt_log->bind_param("ssss", $date, $TxTID, $EVENT, $REMARKS);
        $stmt_log->execute();

        // Increment LogError
        $stmt_error = $conn->prepare("UPDATE user_info SET LogError = LogError + 1 WHERE id = ?");
        $stmt_error->bind_param("s", $TxTID);
        $stmt_error->execute();

        session_destroy();
        die("<script>alert('Password error.'); location.href='./index1.php';</script>");
    }
} catch (Exception $e) {
    // Log error securely
    error_log("Login error: " . $e->getMessage());
    die("<script>alert('An error occurred. Please try again later.'); location.href='./index1.php';</script>");
}

// Close database connection
$conn->close();
?>