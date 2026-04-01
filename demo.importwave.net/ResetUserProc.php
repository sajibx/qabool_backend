<?php
include ("./sessionchk2.php");
include ("./refchk.php");
?>

<?php

// Include database connection
require "./conn.php";

// --- Input Validation and Sanitization ---

// Securely retrieve and sanitize user input (the ID of the user to reset)
$ClientName = filter_input(INPUT_POST, 'ClientName', FILTER_SANITIZE_STRING);

if (empty($ClientName)) {
    die("User ID is required for reset.");
}

// --- Password Hashing ---

// Load configuration for default password
$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/App_Data/app.ini', true);
$DEFAULTPASS = $ini["GENERAL"]["DEFAULTPASS"] ?? 'defaultpass'; // Use a fallback

// Hash the default password using BCRYPT (Do NOT use the static salt here)
$Newhash_val_bcrypt = password_hash($DEFAULTPASS, PASSWORD_BCRYPT);

// --- Update User Info (Using Prepared Statements) ---
// Set new BCRYPT hash, FCP='1' (Force Change Password), LogError='0'
try {
    $FCP = '1';
    $LogError = '0';
    
    $sql = "UPDATE `user_info` 
            SET `pass` = ?, `FCP` = ?, `LogError` = ?
            WHERE `id` = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $Newhash_val_bcrypt, $FCP, $LogError, $ClientName);

    if ($stmt->execute()) {
        $rows_affected = $stmt->affected_rows;
        $stmt->close();
        
        if ($rows_affected > 0) {
            // --- Generate Log (Using Prepared Statements) ---
            date_default_timezone_set('Asia/Dhaka'); // Ensure timezone consistency

            $EVENT      = "User Reset"; 
            $REMARKS    = "User ID : " . $ClientName . " reset to default password.";
            
            // The admin/logged-in user performing the action
            $UID        = $_SESSION['user'] ?? 'ADMIN_CONSOLE'; 
            $date       = date("Y-m-d H:i:s");
            
            $sql_log    = "INSERT INTO `log` (`date`, `user`, `event`, `comment`)
                           VALUES (?, ?, ?, ?)";
            
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->bind_param("ssss", $date, $UID, $EVENT, $REMARKS);
            $stmt_log->execute();
            $stmt_log->close();
            
            // --- Display Success Message ---
            echo "<div>
            </br>User ID Reset complete. User ID : " . htmlspecialchars($ClientName) . "User Default Pass : " . htmlspecialchars($DEFAULTPASS) . " Please Change password after first login.
            </div>";
        } else {
            // No rows were updated (User ID might not exist)
            echo "<div style=''>
            User ID " . htmlspecialchars($ClientName) . " not found or no change necessary.
            </div>";
        }
    } else {
        // SQL execution error
        error_log("User reset failed for ID: " . $ClientName . ". Error: " . $stmt->error);
        die("An error occurred during the reset process.");
    }
} catch (Exception $e) {
    // Catch database connection or other exceptions
    error_log("ResetUserProc exception: " . $e->getMessage());
    die("A system error occurred. Please try again later.");
}

// Close database connection
$conn->close();
?>
