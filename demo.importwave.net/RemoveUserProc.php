<?php
include("./sessionchk2.php");
include("./refchk.php");
require "./conn.php";

// 1. Get Input Securely
$ClientName = filter_input(INPUT_POST, 'ClientName', FILTER_SANITIZE_STRING);

// 2. Validate
if (!$ClientName) {
    echo "<span style='color:red; font-weight:bold;'>Error: Invalid User ID.</span>";
    exit;
}

try {
    // 3. DELETE Query
    // We are deleting the row where the ID matches
    $sql = "DELETE FROM `user_info` WHERE `id` = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ClientName);

    if ($stmt->execute()) {
        $rows_affected = $stmt->affected_rows;
        $stmt->close();
        
        if ($rows_affected > 0) {
            // --- Generate Log ---
            date_default_timezone_set('Asia/Dhaka'); 

            $EVENT      = "User Removed"; 
            $REMARKS    = "User ID : " . $ClientName . " was permanently removed.";
            $UID        = $_SESSION['user'] ?? 'ADMIN_CONSOLE'; 
            $date       = date("Y-m-d H:i:s");
            
            $sql_log    = "INSERT INTO `log` (`date`, `user`, `event`, `comment`) VALUES (?, ?, ?, ?)";
            $stmt_log   = $conn->prepare($sql_log);
            $stmt_log->bind_param("ssss", $date, $UID, $EVENT, $REMARKS);
            $stmt_log->execute();
            $stmt_log->close();
            
            echo "<span style='color:red; font-weight:bold;'>Success: User " . htmlspecialchars($ClientName) . " has been removed.</span>";
            
            // Optional: Add a script to hide the row immediately
            echo "<script>$('#RoleID_" . htmlspecialchars($ClientName) . "').closest('tr').fadeOut();</script>";
            
        } else {
            echo "<span style='color:orange;'>User ID " . htmlspecialchars($ClientName) . " not found or already deleted.</span>";
        }
    } else {
        error_log("User remove failed for ID: " . $ClientName . ". Error: " . $stmt->error);
        echo "<span style='color:red;'>Database Error: Could not delete user.</span>";
    }
} catch (Exception $e) {
    error_log("RemoveUserProc exception: " . $e->getMessage());
    echo "System Error. Please try again later.";
}

$conn->close();
?>
