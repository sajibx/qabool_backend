<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; include __DIR__ . "/conn.php";
$SID  = isset($_REQUEST["SID"])  ? trim((string)$_REQUEST["SID"])  : '';
$CID  = isset($_REQUEST["CID"])  ? trim((string)$_REQUEST["CID"])  : '';
$guid = isset($_REQUEST["guid"]) ? trim((string)$_REQUEST["guid"]) : '';
$TYPE = isset($_REQUEST['TYPE']) ? trim((string)$_REQUEST['TYPE']) : '';
$UID  = isset($_SESSION['user']) ? $_SESSION['user'] : '';

date_default_timezone_set('Asia/Dhaka');
$date = date("y-m-d H:i:s");

// Same logic, secured with a prepared statement
$sql = "INSERT INTO `guid` (`guid`, `SID`, `CID`, `TIME`, `user`, `TYPE`)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE `guid` = `guid`";

$result = false;
if ($stmt = $conn->prepare($sql)) {
    // bind all as strings to preserve current behavior
    $stmt->bind_param("ssssss", $guid, $SID, $CID, $date, $UID, $TYPE);
    $result = $stmt->execute();
    $stmt->close();
} else {
    // Optional: log the error without changing output behavior
    // error_log('Prepare failed: ' . $conn->error);
}
?>
