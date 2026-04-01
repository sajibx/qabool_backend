<?php
include ("./sessionchk2.php");
include ("./refchk.php");
include ("./conn.php");

$CNFName = $_POST["CNFName"];

$UID = $_SESSION['user'];

$sql3 = "INSERT INTO `CNF_AGENT` (`SN`, `NAME`) VALUES (NULL, ?)";

if ($stmt3 = $conn->prepare($sql3)) {
    $stmt3->bind_param("s", $CNFName);
    $result3 = $stmt3->execute();
    $stmt3->close();
} else {
}

date_default_timezone_set('Asia/Dhaka');

$EVENT = "New CNF"; 
$REMARKS = "CNF Name :".$CNFName; 
$date = date("y-m-d H:i:s");

$sql_log = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`) VALUES (NULL, ?, ?, ?, ?);";

if ($stmt_log = $conn->prepare($sql_log)) {
    $stmt_log->bind_param("ssss", $date, $UID, $EVENT, $REMARKS);
    $result_log = $stmt_log->execute();
    $stmt_log->close();
} else {
}

echo "New Entry complete.";

?>