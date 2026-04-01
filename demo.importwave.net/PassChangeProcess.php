<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; include __DIR__ . "/conn.php"; 


// Load configuration for static salt (needed to check old passwords)
$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/App_Data/app.ini', true);
$salt = $ini["GENERAL"]["SALT"] ?? ''; // Fallback if not set
$timezone = $ini["GENERAL"]["TIMEZONE"] ?? 'Asia/Dhaka'; // Configurable timezone
date_default_timezone_set($timezone);

// Include database connection
// require "./conn.php";

// --- Input Validation and Sanitization ---

// Securely retrieve and sanitize user input
$OldPass = filter_input(INPUT_POST, 'OldPass', FILTER_SANITIZE_STRING);
$NewPass = filter_input(INPUT_POST, 'NewPass', FILTER_SANITIZE_STRING);
$UID     = filter_input(INPUT_POST, 'UID', FILTER_SANITIZE_STRING); // The user whose pass is being changed

// Validate inputs
if (empty($OldPass) || empty($NewPass) || empty($UID)) {
    die("User ID, Old Password, and New Password are required.");
}

// Ensure the user is logged in and changing their own password (or an admin is doing it)
// A more robust check might be needed here, but for now, we rely on the UID passed via POST.

// --- Fetch User Data (Using Prepared Statements) ---
$stmt = null;
try {
    $stmt = $conn->prepare("SELECT `pass`, `UL`, `type`, `FCP` FROM `user_info` WHERE `id` = ?");
    $stmt->bind_param("s", $UID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("User not found.");
    }

    $row = $result->fetch_assoc();
    $PASS_HASH_DB   = $row['pass']; // Hashed password from DB
    $UL             = $row['UL'];
    $type           = $row['type'];
    $FCP_STATUS     = $row['FCP'];

    $stmt->close();
    
    // --- Verify Old Password ---
    $password_verified = false;

    // 1. Check if the stored hash is the OLD SHA3-256 format (64 hex chars)
    if (strlen($PASS_HASH_DB) === 64 && ctype_xdigit($PASS_HASH_DB)) {
        // Verification using the OLD method (Password + Static Salt)
        $passwordx = $OldPass . $salt;
        $hash_val_old = hash('sha3-256', $passwordx);
        $password_verified = ($hash_val_old === $PASS_HASH_DB);
    } 
    
    // 2. Check if the stored hash is the NEW BCRYPT format
    if (!$password_verified) {
        // Verification using the NEW method (password_verify)
        $password_verified = password_verify($OldPass, $PASS_HASH_DB);
    }


    if ($password_verified) {
        
        // --- Hash New Password (Always use BCRYPT) ---
        $Newhash_val_bcrypt = password_hash($NewPass, PASSWORD_BCRYPT);

        // --- Update Password and FCP/UL status (Using Prepared Statements) ---
        
        // Start transaction for atomic updates
        $conn->begin_transaction();

        // 1. Update the password
        $sql_update_pass = "UPDATE `user_info` SET `pass` = ? WHERE `id` = ?";
        $stmt_pass = $conn->prepare($sql_update_pass);
        $stmt_pass->bind_param("ss", $Newhash_val_bcrypt, $UID);
        $stmt_pass->execute();
        $chk1 = $stmt_pass->affected_rows;
        $stmt_pass->close();

        $message = "Password Changed. Please Logout and Login again.";
        
        if ($chk1 >= 1) { // Check if update was successful (>= 1 to handle case where old hash was BCRYPT)
            
            // 2. Handle Account Activation (UL update)
            if ($UL == 0) {
                $ActUL = 1;
                $sqlUL = "UPDATE `user_info` SET `UL` = ? WHERE `id` = ?";
                $stmtUL = $conn->prepare($sqlUL);
                $stmtUL->bind_param("ss", $ActUL, $UID);
                $stmtUL->execute();
                $chkUL = $stmtUL->affected_rows;
                $stmtUL->close();
                
                if ($chkUL >= 1) {
                    $message .= "</br>Account Activated (UL updated).";
                }
            }
            
            // 3. Reset Force Change Password (FCP)
            if ($FCP_STATUS == 1) {
                $sql_fcp = "UPDATE `user_info` SET `FCP` = '0' WHERE `id` = ?";
                $stmt_fcp = $conn->prepare($sql_fcp);
                $stmt_fcp->bind_param("s", $UID);
                $stmt_fcp->execute();
                $stmt_fcp->close();
            }

            $conn->commit();
            echo $message;

            // --- Generate Log (Using Prepared Statements) ---
            $EVENT      = "Pass Changed"; 
            $REMARKS    = "Password changed successfully for user: " . $UID;
            $LID        = $_SESSION['user'] ?? 'SYSTEM'; // Logged-in user ID
            $date       = date("Y-m-d H:i:s");
            
            $sql_log    = "INSERT INTO `log` (`date`, `user`, `event`, `comment`)
                                VALUES (?, ?, ?, ?)";
            
            $stmt_log = $conn->prepare($sql_log);
            // sn is typically AUTO_INCREMENT, so we don't include it in the VALUES list
            $stmt_log->bind_param("ssss", $date, $LID, $EVENT, $REMARKS);
            $stmt_log->execute();
            $stmt_log->close();

            // Log generation end
    
        } else {
            // Password update failed
            $conn->rollback();
            echo "No change to password occurred.";
        }
    
    } else {
        // Password Verification Failed
        echo "Password Error.";
        exit();
    }

} catch (Exception $e) {
    // Log error and handle transaction rollback if needed
    if ($conn->in_transaction) {
        $conn->rollback();
    }
    error_log("Password Change error: " . $e->getMessage());
    die("An unexpected error occurred. Please try again.");

} finally {
    // Ensure connection is closed
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        // $stmt->close();
    }
    $conn->close();
}

?>


