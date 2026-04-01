<?php
include ("./sessionchk2.php");
include ("./refchk.php");
include ("./conn.php");

// 1. Input Processing and Sanitization
$TxTShipment = $_POST["TxTShipment"];
$TxTType     = $_POST["TxTType"];
// Treat amount as a decimal/float for safety
$TxTAmount   = floatval($_POST["TxTAmount"]); 

$UID         = $_SESSION['user'];

date_default_timezone_set('Asia/Dhaka');

// Use 4-digit year format 'Y-m-d' for robust date handling in SQL
$date = date("Y-m-d");


// ---

// 2. Insert into `ExpenseLedger`
// SECURITY FIX: Use a prepared statement.
$sql = "INSERT INTO `ExpenseLedger` (`SN`, `DATE`, `SHIPMENT`, `TYPE`, `AMOUNT`, `UID`)
        VALUES (NULL, ?, ?, ?, ?, ?);";

if ($stmt = $conn->prepare($sql)) {
    // Bind parameters: date(s), shipment(s), type(s), amount(d), UID(s)
    $stmt->bind_param("sssds", $date, $TxTShipment, $TxTType, $TxTAmount, $UID);
    $result = $stmt->execute();
    $stmt->close();
}


// ---

// 3. Generate Log
$EVENT    = "Expense Entry"; 
$REMARKS  = "Shipment :".$TxTShipment." | Type : ".$TxTType." | Amount : ".$TxTAmount; 
// $UID is already set
$date_log = date("y-m-d H:i:s"); // Log time including seconds

// SECURITY FIX: Use a prepared statement for the log INSERT query.
$sql_log = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`) VALUES (NULL, ?, ?, ?, ?);";

if ($stmt_log = $conn->prepare($sql_log)) {
    // Bind parameters: date(s), user(s), event(s), remarks(s)
    $stmt_log->bind_param("ssss", $date_log, $UID, $EVENT, $REMARKS);
    $result_log = $stmt_log->execute();
    $stmt_log->close();
}

// ---

// 4. Output (If needed, although commented out in original)
// echo "New Entry complete.";

?>