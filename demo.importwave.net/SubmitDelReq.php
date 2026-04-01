<?php
include ("./sessionchk2.php");
include ("./refchk.php");
include ("./conn.php");

// Set timezone
date_default_timezone_set('Asia/Dhaka');

// Check for database connection
if (!isset($conn)) {
    http_response_code(500);
    die("Database connection error.");
}

// --- 1. Secure and Sanitize Input ---

$date = date("Y-m-d");

// Sanitize all inputs using real_escape_string
$sn       = $conn->real_escape_string($_REQUEST["sn"] ?? '');
$shipment = $conn->real_escape_string($_REQUEST["shipment"] ?? '');
$client   = $conn->real_escape_string($_REQUEST["client"] ?? '');
// Fixed the potential undefined index error
$type     = $conn->real_escape_string(isset($_REQUEST["type"]) ? $_REQUEST["type"] : '');
// Treat amount as a string for insertion since it might contain decimals, but sanitize it
$amount   = $conn->real_escape_string($_REQUEST["amount"] ?? '0');
$UID      = $conn->real_escape_string($_REQUEST["UID"] ?? 'N/A');

// --- 2. Check for Existing Approval Request (Secured with Prepared Statement) ---

$SQLCnt = "SELECT COUNT(*) AS CNT FROM `delete_aprv` WHERE `sn` = ? AND `approve` = 'N'";

$stmt_count = $conn->prepare($SQLCnt);
if ($stmt_count === false) {
    http_response_code(500);
    die("Error preparing count statement: " . $conn->error);
}

// Bind parameter: 's' for string (sn)
$stmt_count->bind_param("s", $sn); 
$stmt_count->execute();
$resultCnt = $stmt_count->get_result();
$rowCnt    = $resultCnt->fetch_assoc();
$CNT       = (int)($rowCnt['CNT'] ?? 0);
$stmt_count->close();


// --- 3. Conditional Insert (Secured with Prepared Statement) ---

if ($CNT == 0) {
    
    // Prepare INSERT statement with placeholders (?)
    $SQLUpdate = "INSERT INTO `delete_aprv` 
                  (`sn`, `date`, `type`, `shipment`, `client`, `amount`, `user`, `approve`) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'N')";

    $stmt_insert = $conn->prepare($SQLUpdate);
    if ($stmt_insert === false) {
        http_response_code(500);
        die("Error preparing insert statement: " . $conn->error);
    }
    
    // Bind parameters: 'sssssds' (s:sn, s:date, s:type, s:shipment, s:client, d:amount/string, s:user)
    // NOTE: Using 's' for amount is acceptable if the database column is VARCHAR/TEXT, but 'd' (double/float) is better if the column is numeric. Sticking to 's' as you used it as a string input.
    $stmt_insert->bind_param("sssssss", 
        $sn, 
        $date, 
        $type, 
        $shipment, 
        $client, 
        $amount, 
        $UID
    );

    $resultu = $stmt_insert->execute();
    $stmt_insert->close();
    
    if ($resultu) {
        echo "Delete request has been submitted for approval. \n Please contact with administrator for approval.";
    } else {
        http_response_code(500);
        echo "Error submitting delete request: " . $conn->error;
    }
    
    exit();

} else {
    echo "Delete request waiting for approval.\n Please contact with administrator.";
    exit();
}
?>