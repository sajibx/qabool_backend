<?php
include("./sessionchk2.php");
include("./refchk.php");
require "./conn.php";

// 1. Get Inputs securely
$ClientName = filter_input(INPUT_POST, 'ClientName', FILTER_SANITIZE_STRING); // User ID
$NewRole    = filter_input(INPUT_POST, 'NewRole', FILTER_SANITIZE_STRING);    // New Role Value

if ($NewRole>6) {$NewRole = 1;}

// 2. Validate Input
if (!$ClientName || !$NewRole) {
    echo "<span style='color:red'>Error: Missing User ID or Role.</span>";
    exit;
}

try {
    // 3. Update the Database
    // We update the 'UL' (User Level) column
    $sql = "UPDATE `user_info` SET `UL` = ? WHERE `id` = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $NewRole, $ClientName);

    if ($stmt->execute()) {
        
        // 4. Log the action
        date_default_timezone_set('Asia/Dhaka');
        $EVENT      = "Role Update"; 
        $REMARKS    = "User ID : " . $ClientName . " role changed to " . $NewRole;
        $UID        = $_SESSION['user'] ?? 'ADMIN'; 
        $date       = date("Y-m-d H:i:s");
        
        $sql_log    = "INSERT INTO `log` (`date`, `user`, `event`, `comment`) VALUES (?, ?, ?, ?)";
        $stmt_log   = $conn->prepare($sql_log);
        $stmt_log->bind_param("ssss", $date, $UID, $EVENT, $REMARKS);
        $stmt_log->execute();
        $stmt_log->close();
        
        echo "<span style='color:green; font-weight:bold;'>Success: User $ClientName role updated to $NewRole.</span>";
    } else {
        echo "<span style='color:red'>Error: Database update failed.</span>";
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo "System Error: " . $e->getMessage();
}

$conn->close();
?>
