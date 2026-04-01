<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; include __DIR__ . "/conn.php"; 
?>
<?php
// include ("./sessionchk2.php");
// include ("./refchk.php");
include ("./conn.php");

$OldName = $_REQUEST["OldName"];
$NewName = $_REQUEST["NewName"];

$sql_packing = "UPDATE `packinglist` SET `CLIENT` = ? WHERE `CLIENT` = ?;";

if ($stmt = $conn->prepare($sql_packing)) {
    $stmt->bind_param("ss", $NewName, $OldName);
    $result_packing = $stmt->execute();
    
    echo "</br>Change in Shipment Data: " . $stmt->affected_rows;
    
    $stmt->close();
} else {
    echo "</br>Change in Shipment Data: Error";
}

$sql_ledger = "UPDATE `billledger` SET `CLIENT` = ? WHERE `CLIENT` = ?;";

if ($stmt = $conn->prepare($sql_ledger)) {
    $stmt->bind_param("ss", $NewName, $OldName);
    $result_ledger = $stmt->execute();
    
    echo "</br>Change in Ledger: " . $stmt->affected_rows;
    
    $stmt->close();
} else {
    echo "</br>Change in Ledger: Error";
}

include("cron2.php");

date_default_timezone_set('Asia/Dhaka');

$EVENT = "Name Change"; 
$REMARKS = "Old Name :".$OldName." | New Name : ".$NewName; 
$UID = $_SESSION['user']; 
$date = date("y-m-d H:i:s");

$sql_log = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`) VALUES (NULL, ?, ?, ?, ?);";

if ($stmt_log = $conn->prepare($sql_log)) {
    $stmt_log->bind_param("ssss", $date, $UID, $EVENT, $REMARKS);
    $result_log = $stmt_log->execute();
    $stmt_log->close();
} else {

}

?>