<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; include __DIR__ . "/conn.php"; 
?>
<?php
// include("./sessionchk2.php");
// include("./refchk.php");
// include("./conn.php");

$TARGET = intval($_POST["sn"]);
$TYPE   = $_POST["type"];

$result1 = false; 

if ($TYPE == 'N') {
    $sql1 = "DELETE FROM `delete_aprv` WHERE `SN` = ?";

    if ($stmt1 = $conn->prepare($sql1)) {
        $stmt1->bind_param("i", $TARGET);
        $result1 = $stmt1->execute();
        $stmt1->close();
    }
} else {

    $sql1_delete = "DELETE FROM `billledger` WHERE `SN` = ?";

    if ($stmt_delete = $conn->prepare($sql1_delete)) {
        $stmt_delete->bind_param("i", $TARGET);
        $result1 = $stmt_delete->execute();
        $stmt_delete->close();
    }


    $sql1_update = "UPDATE `delete_aprv` SET `approve` = 'Y' WHERE `sn` = ?";

    if ($stmt_update = $conn->prepare($sql1_update)) {
        $stmt_update->bind_param("i", $TARGET);
        $result1 = $stmt_update->execute();
        $stmt_update->close();
    }
    
    date_default_timezone_set('Asia/Dhaka');

    $EVENT = "Delete Ledger";
    $REMARKS = "SN : " . $TARGET;
    $UID = $_SESSION['user'];
    $date = date("y-m-d H:i:s");

    $sql_log = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`) VALUES (NULL, ?, ?, ?, ?);";

    if ($stmt_log = $conn->prepare($sql_log)) {
        $stmt_log->bind_param("ssss", $date, $UID, $EVENT, $REMARKS);
        $result_log = $stmt_log->execute();
        $stmt_log->close();
    }
}


?>