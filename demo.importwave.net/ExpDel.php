<?php
include ("./sessionchk2.php");
include ("./refchk.php");
include ("./conn.php");


$TARGET = intval($_POST["TARGET"]);

$AM     = $_POST["AM"];
$TYPE   = $_POST["TYPE"];

date_default_timezone_set('Asia/Dhaka');

$CurDate = date("Y-m-d");

$sql1 = "DELETE FROM `Daily3xp` WHERE `sn` = ?";

if ($stmt1 = $conn->prepare($sql1)) {
    $stmt1->bind_param("i", $TARGET);
    $result1 = $stmt1->execute();
    $stmt1->close();
}

$sqlY = "SELECT `sn`, `CashIH` from `Daily3xp` WHERE `date` < ? ORDER BY `sn` DESC LIMIT 1;";
$CashIH = 0; 

if ($stmtY = $conn->prepare($sqlY)) {
    $stmtY->bind_param("s", $CurDate);
    $stmtY->execute();
    $resultY = $stmtY->get_result();
    
    if ($rowY = $resultY->fetch_assoc()) {
        $CashIH = floatval($rowY['CashIH']); 
    }
    $stmtY->close();
}

$sqlTRec = "SELECT SUM(`amount`) 'TREC' FROM `Daily3xp` WHERE `date` = ? AND `type` = 'Rec'";
$TRec = 0;

if ($stmtR = $conn->prepare($sqlTRec)) {
    $stmtR->bind_param("s", $CurDate);
    $stmtR->execute();
    $resultR = $stmtR->get_result();
    
    if ($rowR = $resultR->fetch_assoc()) {
        if(isset($rowR['TREC'])){
            $TRec = floatval($rowR['TREC']);
        }
    }
    $stmtR->close();
}

$sqlTExp = "SELECT SUM(`amount`) 'TEXP' FROM `Daily3xp` WHERE `date` = ? AND `type` = 'Exp'";
$TExp = 0;

if ($stmtE = $conn->prepare($sqlTExp)) {
    $stmtE->bind_param("s", $CurDate);
    $stmtE->execute();
    $resultE = $stmtE->get_result();
    
    if ($rowE = $resultE->fetch_assoc()) {
        if(isset($rowE['TEXP'])){
            $TExp = floatval($rowE['TEXP']);
        }
    }
    $stmtE->close();
}


$FCASH = $CashIH + $TRec - $TExp;

$sql0 = "UPDATE `Daily3xp`
            JOIN (
                SELECT MAX(`sn`) AS max_sn
                FROM `Daily3xp`
            ) AS temp
            ON `Daily3xp`.`sn` = temp.max_sn
            SET `CashIH` = ?;";

if ($stmt0 = $conn->prepare($sql0)) {
    $stmt0->bind_param("d", $FCASH);
    $result0 = $stmt0->execute();
    $stmt0->close();
}

date_default_timezone_set('Asia/Dhaka');

$EVENT = "ExpDel";
$REMARKS = "SN : ".$TARGET." | TYPE :".$TYPE." | Amount: ".$AM;

$UID = $_SESSION['user'];
$date = date("y-m-d H:i:s");

$sql_log = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`) VALUES (NULL, ?, ?, ?, ?);";

if ($stmt_log = $conn->prepare($sql_log)) {
    $stmt_log->bind_param("ssss", $date, $UID, $EVENT, $REMARKS);
    $result_log = $stmt_log->execute();
    $stmt_log->close();
}

?>