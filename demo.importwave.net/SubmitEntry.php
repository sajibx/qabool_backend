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

// Sanitize all inputs using real_escape_string
$ShipID      = $conn->real_escape_string($_REQUEST["ShipID"] ?? '');
$nCTN        = $conn->real_escape_string($_REQUEST["nCTN"] ?? '');
$nCLIENT     = $conn->real_escape_string($_REQUEST["nCLIENT"] ?? '');
$nSHIP       = $conn->real_escape_string($_REQUEST["nSHIP"] ?? '');
$nITEM       = $conn->real_escape_string($_REQUEST["nITEM"] ?? '');
$nWEIGHT     = $conn->real_escape_string($_REQUEST["nWEIGHT"] ?? '0'); // Treat as string/numeric
$nPWT        = $conn->real_escape_string($_REQUEST["nPWT"] ?? '0'); // Treat as string/numeric
$nUNIT_PRICE = $conn->real_escape_string($_REQUEST["nUNIT_PRICE"] ?? '0'); // Treat as string/numeric
$nTOTAL      = $conn->real_escape_string($_REQUEST["nTOTAL"] ?? '0'); // Treat as string/numeric

// User ID for logging (sanitize session variable)
$UID = $conn->real_escape_string($_SESSION['user'] ?? 'N/A');


// --- 2. Check if Client already exists in this Shipment (Secured SELECT) ---

$SQLCnt = "SELECT COUNT(*) AS CNT FROM `packinglist` WHERE `SHIPMENT` = ? AND `CLIENT` = ?";

$stmt_count = $conn->prepare($SQLCnt);
if ($stmt_count === false) {
    // Log error but continue with the rest of the script if possible
    error_log("Error preparing count statement: " . $conn->error);
} else {
    // Bind parameters: 'ss' for string, string
    $stmt_count->bind_param("ss", $ShipID, $nCLIENT);
    $stmt_count->execute();
    $resultCnt = $stmt_count->get_result();
    $rowCnt    = $resultCnt->fetch_assoc();
    $CNT       = (int)($rowCnt['CNT'] ?? 0);
    $stmt_count->close();
}


// --- 3. Optional: Update Last Shipment Log (Secured SELECT & INSERT/UPDATE) ---

if ($CNT == 0) {
    // --- Get Last Shipment ID (Secured SELECT) ---
    $SQLlSTsHIP = "SELECT `SHIPMENT`, `DATE` FROM `ShipmentLog` ORDER BY `DATE` DESC LIMIT 1";
    $resLstShip = $conn->query($SQLlSTsHIP); // Simple query, no user input, so query() is acceptable
    $rowLst     = $resLstShip->fetch_assoc();
    $LlSTsHIP   = $rowLst['SHIPMENT'] ?? ''; // Default to empty string

    if ($ShipID === $LlSTsHIP) {
        
        // --- Update last_shipment table (Secured INSERT ON DUPLICATE KEY UPDATE) ---
        $SQLUpdate = "INSERT INTO `last_shipment` (`CLIENT`, `LAST_SHIPMENT`)
                      VALUES (?, ?)
                      ON DUPLICATE KEY UPDATE `LAST_SHIPMENT` = VALUES(`LAST_SHIPMENT`)";

        $stmt_update = $conn->prepare($SQLUpdate);
        if ($stmt_update === false) {
            error_log("Error preparing last_shipment update: " . $conn->error);
        } else {
            // Bind parameters: 'ss' for string, string
            $stmt_update->bind_param("ss", $nCLIENT, $ShipID);
            $stmt_update->execute();
            $stmt_update->close();
        }
    }
}


// --- 4. Determine Delivery Status ---
if (strpos($nCTN, 'RMB') !== false || strpos($nCTN, 'PICKUP') !== false) {
    $DEL = 'Y';
} else {
    $DEL = 'N';
}

// --- 5. Insert New Packing List Item (Secured INSERT) ---

$sql1 = "INSERT INTO `packinglist` 
         (`SHIPMENT`, `CTN_NO`, `CTN`, `P.WT`, `CLIENT`, `SHIPPING_MARK`, `ITEM_LIST`, `WEIGHT`, `UNIT_PRICE`, `TOTAL_AMOUNT`, `STATUS`, `DELIVERY`) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Added', ?)";

$stmt_insert = $conn->prepare($sql1);
if ($stmt_insert === false) {
    http_response_code(500);
    die("Error preparing packinglist insert: " . $conn->error);
}

$CTN_static = '1'; // The static value '1' for the `CTN` column

// Bind parameters: 'sssssssssss' (all inputs are sanitized strings from $_REQUEST)
// s:ShipID, s:nCTN, s:CTN_static, s:nPWT, s:nCLIENT, s:nSHIP, s:nITEM, s:nWEIGHT, s:nUNIT_PRICE, s:nTOTAL, s:DEL
$stmt_insert->bind_param("sssssssssss", 
    $ShipID, 
    $nCTN, 
    $CTN_static, 
    $nPWT, 
    $nCLIENT, 
    $nSHIP, 
    $nITEM, 
    $nWEIGHT, 
    $nUNIT_PRICE, 
    $nTOTAL, 
    $DEL
);

$result = $stmt_insert->execute();
$stmt_insert->close();


// --- 6. Generate Log Entry (Secured INSERT) ---

$EVENT    = "Add Entry";
$REMARKS  = "Shipment: " . $ShipID . " | Item: " . $nITEM . " | Client: " . $nCLIENT;

$date_log = date("Y-m-d H:i:s"); // Use Y-m-d H:i:s

$sql_log = "INSERT INTO `log` (`date`, `user`, `event`, `comment`)
            VALUES (?, ?, ?, ?)";

$stmt_log = $conn->prepare($sql_log);

// Bind parameters: 'ssss'
$stmt_log->bind_param("ssss", $date_log, $UID, $EVENT, $REMARKS);

$result_log = $stmt_log->execute();
$stmt_log->close();


// --- 7. Final Output ---
if ($result) {
    echo "New packing list entry added successfully.";
} else {
    http_response_code(500);
    echo "Error adding packing list entry: " . $conn->error;
}

// Close the connection
$conn->close();
?>