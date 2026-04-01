<?php
include ("./sessionchk2.php");
include ("./refchk.php");
include ("./conn.php");


$TxTName    = $_POST["TxTName"];
$TxTAmount  = floatval($_POST["TxTAmount"]);
$TxTMethod  = $_POST["TxTMethod"];
$RMVAL      = substr($_POST['TxtRem'],0,40);

$UID        = $_SESSION['user'];
$Type       = $_POST["TYPE"];
$RT         = $_POST["RTYPE"];

date_default_timezone_set('Asia/Dhaka');
$date = date("Y-m-d"); // Use 4-digit year for consistency with most databases

$sql1 = "SELECT `CashIH` FROM `Daily3xp` WHERE `sn` = (SELECT MAX(`sn`) FROM `Daily3xp`)";

$result1 = $conn->query($sql1);
$row1 = $result1->fetch_assoc();

$CashIH = isset($row1['CashIH']) ? floatval($row1['CashIH']) : 0;

$BANK = 0; 

if($Type == 'Rec'){
    
    $CashIH = $CashIH + $TxTAmount;
    
    if($RT == "BANK"){
        
        $BANK = 1; // Set BANK flag for both entries

        $CashIH = $CashIH - $TxTAmount;
        
        $sql4 = "INSERT INTO `Daily3xp` (`sn`, `date`, `type`, `name`, `pay_method`, `amount`, `CashIH`, `remarks`, `user`, `BANK`)
                 VALUES (NULL, ?, 'Exp', ?, ?, ?, ?, ?, ?, ?);";
        
        if ($stmt4 = $conn->prepare($sql4)) {
            $stmt4->bind_param("sssdsssi", $date, $TxTName, $TxTMethod, $TxTAmount, $CashIH, $RMVAL, $UID, $BANK);
            $result4 = $stmt4->execute();
            $stmt4->close();
        }
        
    }
    
    $sql3 = "INSERT INTO `Daily3xp` (`sn`, `date`, `type`, `name`, `pay_method`, `amount`, `CashIH`, `remarks`, `user`, `BANK`)
             VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
             
    if ($stmt3 = $conn->prepare($sql3)) {
        $stmt3->bind_param("ssssdsssi", $date, $Type, $TxTName, $TxTMethod, $TxTAmount, $CashIH, $RMVAL, $UID, $BANK);
        $result3 = $stmt3->execute();
        $stmt3->close();
    }
}
else{ // $Type == 'Exp'
    

    $CashIH = $CashIH - $TxTAmount;
    

    $sql3 = "INSERT INTO `Daily3xp` (`sn`, `date`, `type`, `name`, `pay_method`, `amount`, `CashIH`, `remarks`, `user`, `BANK`)
             VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, '0');"; // '0' is hardcoded for BANK flag

    if ($stmt3 = $conn->prepare($sql3)) {
        $stmt3->bind_param("ssssdsss", $date, $Type, $TxTName, $TxTMethod, $TxTAmount, $CashIH, $RMVAL, $UID);
        $result3 = $stmt3->execute();
        $stmt3->close();
    }
}


$EVENT    = $Type." Entry";
$REMARKS  = "Type :".$Type." | Name :".$TxTName." | Method :".$TxTMethod." | Amount :".$TxTAmount;
$UID      = $_SESSION['user'];
$date_log = date("y-m-d H:i:s");

$sql_log = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`) VALUES (NULL, ?, ?, ?, ?);";

if ($stmt_log = $conn->prepare($sql_log)) {
    $stmt_log->bind_param("ssss", $date_log, $UID, $EVENT, $REMARKS);
    $result_log = $stmt_log->execute();
    $stmt_log->close();
}

echo "Save Complete.";

?>