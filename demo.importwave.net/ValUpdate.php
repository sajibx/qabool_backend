<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; include __DIR__ . "/conn.php"; 
?>
<?php
// include ("./sessionchk2.php");
// include ("./refchk.php");
// include ("./conn.php");


$PID = intval($_POST["PID"]); 
$TARGET = $_POST["TARGET"];
$ShipID = $_POST["ShipID"];


$VAL = mysqli_real_escape_string($conn, $_POST['VAL']); 

$TYPE = "XX";
$sql = "";
$sql1 = "";
$stmt = null;


if($TARGET == "NM"){
    $TYPE = "NAME";

    $sql = "UPDATE `packinglist` SET `CLIENT` = ? WHERE `packinglist`.`SN` = ?;";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $VAL, $PID);
        $result = $stmt->execute();
        $stmt->close();
    }
    
    // include("cron2.php");
        include __DIR__ . "/cron2.php"; 

}

else{
    if($TARGET == "PT"){
        $TYPE = "U Price";
        $sql = "UPDATE `packinglist` SET `UNIT_PRICE` = ? WHERE `packinglist`.`SN` = ?;";
    }
    elseif ($TARGET == "WT") {
        $TYPE = "P Weight";
        $sql = "UPDATE `packinglist` SET `P.WT` = ? WHERE `packinglist`.`SN` = ?;";
    }
    
    if (!empty($sql) && $stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $VAL, $PID);
        $result = $stmt->execute();
        $stmt->close();
    }

    $sql1 = "UPDATE `packinglist` SET `TOTAL_AMOUNT` = `P.WT`*`UNIT_PRICE` WHERE `packinglist`.`SN` = ?;";
    if ($stmt1 = $conn->prepare($sql1)) {
        $stmt1->bind_param("i", $PID);
        $result1 = $stmt1->execute();
        $stmt1->close();
    }
}

date_default_timezone_set('Asia/Dhaka');
$EVENT = "Edit Data"; 
$REMARKS = "PID : ".$PID." | =".$TYPE." | Value : ".$VAL;
$UID = $_SESSION['user']; 
$date = date("y-m-d H:i:s");

$sql_log = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`) VALUES (NULL, ?, ?, ?, ?);";

if ($stmt_log = $conn->prepare($sql_log)) {
    $stmt_log->bind_param("ssss", $date, $UID, $EVENT, $REMARKS);
    $result_log = $stmt_log->execute();
    $stmt_log->close();
}

?>