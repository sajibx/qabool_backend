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

// Sanitize string inputs using real_escape_string
$CNFName     = $conn->real_escape_string($_REQUEST["CNFName"] ?? '');
$TxTSHIPMENT = $conn->real_escape_string($_REQUEST["TxTSHIPMENT"] ?? '');
$TxTAWBNO    = $conn->real_escape_string($_REQUEST["TxTAWBNO"] ?? '');
$TxTRemarks  = $conn->real_escape_string($_REQUEST["TxTRemarks"] ?? '');

// Sanitize numeric inputs using float casting
$TxTTotalBill = (float)($_REQUEST["TxTTotalBill"] ?? 0.00);
$TxTPayment   = (float)($_REQUEST["TxTPayment"] ?? 0.00);
$TxTDiscount  = (float)($_REQUEST["TxTDiscount"] ?? 0.00);

// User ID for logging (sanitize session variable)
$UID = $conn->real_escape_string($_SESSION['user'] ?? 'N/A');


// --- 2. Date Formatting ---
$input_date = $_REQUEST["date"] ?? '';

// Create a DateTime object using the expected format 'm-d-Y'
$datex = DateTime::createFromFormat('m-d-Y', $input_date);

if ($datex === false) {
    http_response_code(400);
    die("Invalid date format provided.");
}

// Format the date to 'Y-m-d' for MySQL
$datef = $datex->format('Y-m-d');


// --- 3. Fetch Current Total Balance ---

// Query uses the sanitized $CNFName
$sqlT = "SELECT SUM(`AMOUNT`) AS TOTAL FROM `AdminLedger` WHERE `CNFNAME` = '$CNFName'";

$resultT = $conn->query($sqlT);

$TotalT = 0.00; // Initialize total as float
if ($resultT && $rowT = $resultT->fetch_assoc()) {
    $TotalT = (float)($rowT['TOTAL'] ?? 0.00);
}

$current_outstanding = $TotalT; // Use a variable for the running balance


// --- 4. Function to Insert Ledger Entry Safely (using Prepared Statements) ---

/**
 * Inserts a transaction into the AdminLedger using Prepared Statements.
 * @param mysqli $conn Database connection object.
 * @param string $CNFName The CNF agent's name.
 * @param string $datef The transaction date (Y-m-d).
 * @param float $amount The amount of the transaction.
 * @param string $type The type of transaction (BILL, PAYMENT, DISCOUNT).
 * @param string $TxTRemarks The transaction remarks.
 * @param float $TotalT The outstanding balance after this transaction.
 * @param string $TxTSHIPMENT The shipment ID (only for BILL type).
 * @param string $TxTAWBNO The AWB No (only for BILL type).
 * @return bool True on success, false on failure.
 */
function insert_admin_ledger($conn, $CNFName, $datef, $amount, $type, $TxTRemarks, $TotalT, $TxTSHIPMENT, $TxTAWBNO) {
    
    // Default values for non-BILL types
    $shipment_val = ($type === 'BILL') ? $TxTSHIPMENT : '';
    $awbno_val    = ($type === 'BILL') ? $TxTAWBNO : '';

    $sql = "INSERT INTO `AdminLedger` (`SHIPMENT`, `CNFNAME`, `DATE`, `AWBNO`, `AMOUNT`, `TYPE`, `REMARKS`, `OUTSTANDING`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        // Handle preparation error (e.g., table structure issue)
        return false;
    }

    // Bind parameters: 'ssssdsds' (4 strings, 1 float, 1 string, 1 float, 1 string)
    // s: shipment, s: cnfname, s: date, s: awbno, d: amount, s: type, s: remarks, d: outstanding
    $stmt->bind_param("ssssdssd", 
        $shipment_val, 
        $CNFName, 
        $datef, 
        $awbno_val, 
        $amount, 
        $type, 
        $TxTRemarks, 
        $TotalT
    );

    $success = $stmt->execute();
    $stmt->close();
    return $success;
}


// --- 5. Process and Insert Transactions Sequentially ---

// --- A. BILL Entry (increases outstanding) ---
if ($TxTTotalBill > 0) {
    // Update running balance BEFORE insert
    $current_outstanding += $TxTTotalBill; 

    insert_admin_ledger(
        $conn, $CNFName, $datef, $TxTTotalBill, 'BILL', $TxTRemarks, 
        $current_outstanding, $TxTSHIPMENT, $TxTAWBNO
    );
}

// --- B. DISCOUNT Entry (decreases outstanding) ---
if ($TxTDiscount > 0) {
    $TxTDiscount_neg = $TxTDiscount * (-1);
    
    // Update running balance BEFORE insert
    $current_outstanding -= $TxTDiscount; 

    insert_admin_ledger(
        $conn, $CNFName, $datef, $TxTDiscount_neg, 'DISCOUNT', $TxTRemarks, 
        $current_outstanding, '', '' // Shipment/AWB are empty for non-BILL entries
    );
}

// --- C. PAYMENT Entry (decreases outstanding) ---
if ($TxTPayment > 0) {
    $TxTPayment_neg = $TxTPayment * (-1);
    
    // Update running balance BEFORE insert
    $current_outstanding -= $TxTPayment; 

    insert_admin_ledger(
        $conn, $CNFName, $datef, $TxTPayment_neg, 'PAYMENT', $TxTRemarks, 
        $current_outstanding, '', '' // Shipment/AWB are empty for non-BILL entries
    );
}


// --- 6. Generate Log Entry (Secured) ---

$EVENT    = "CNF Entry";
$REMARKS  = "CNF Name: " . $CNFName . 
            " | Bill: " . $TxTTotalBill . 
            " | Payment: " . $TxTPayment . 
            " | Discount: " . $TxTDiscount .
            " | New Balance: " . $current_outstanding;

$date_log = date("Y-m-d H:i:s"); // Use Y-m-d H:i:s

$sql_log = "INSERT INTO `log` (`date`, `user`, `event`, `comment`)
            VALUES (?, ?, ?, ?)";

$stmt_log = $conn->prepare($sql_log);

// Bind parameters: 'ssss'
$stmt_log->bind_param("ssss", $date_log, $UID, $EVENT, $REMARKS);

$result_log = $stmt_log->execute();
$stmt_log->close();


// --- 7. Final Output ---
echo "Entry Submited.";

// Close the connection
$conn->close();
?>