<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; include __DIR__ . "/conn.php"; 
?>
<?php
// include("./sessionchk2.php");
// include("./refchk.php");
// include("./conn.php");

$NumberPart = intval($_POST["NumberPart"]);
$DelStatus  = $_POST["DelStatus"];

$sql1 = "UPDATE `packinglist` SET `DELIVERY` = ? WHERE `SN` = ?;";

if ($stmt1 = $conn->prepare($sql1)) {
    $stmt1->bind_param("si", $DelStatus, $NumberPart);
    $result1 = $stmt1->execute();
    $stmt1->close();
} else {
}

date_default_timezone_set('Asia/Dhaka');

$EVENT = "Delivery Toggle";
$REMARKS = "Item ID : " . $NumberPart . " | New Status : " . $DelStatus;
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