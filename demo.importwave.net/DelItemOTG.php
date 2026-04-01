<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; include __DIR__ . "/conn.php"; 
?>
<?php
// include("./sessionchk2.php");
// include("./refchk.php");
// include("./conn.php");


$TARGET = $_POST["TARGET"];
$CNT = 0; 

$sql_check = "SELECT COUNT(*) AS CNT
              FROM `packinglist`
              WHERE 
                  `SHIPMENT` = (SELECT `SHIPMENT` FROM `packinglist` WHERE `SN` = ?)
              AND 
                  `CLIENT` = (SELECT `CLIENT` FROM `packinglist` WHERE `SN` = ?)";

$stmt_check = $conn->prepare($sql_check);

if ($stmt_check === false) {
    echo "Error preparing check statement.";
    exit;
}

$stmt_check->bind_param("ss", $TARGET, $TARGET);
$stmt_check->execute();

$result_check = $stmt_check->get_result();

if ($row = $result_check->fetch_assoc()) {
    $CNT = $row['CNT']; // Store the count
}

$stmt_check->close();


$sql1 = "DELETE FROM `packinglist` WHERE `SN` = ?";
$stmt_delete = $conn->prepare($sql1);

if ($stmt_delete === false) {
    echo "Error preparing delete statement.";
    exit;
}

$stmt_delete->bind_param("s", $TARGET);
$result1 = $stmt_delete->execute();
$stmt_delete->close();


if ($result1) {
    echo "Record deleted successfully. ";

    // Check the count retrieved in Step 1
    if ($CNT == 1) {
        include 'cron2.php'; 
    } else {
    }
} else {
    echo "Error deleting record: " . htmlspecialchars($conn->error);
}




    // generate log ----------------------------------------------------

    date_default_timezone_set('Asia/Dhaka');

    $EVENT = "Delete Item";

    $REMARKS = "Item ID : " . $TARGET;

    $UID = $_SESSION['user'];

    $date = date("y-m-d H:i:s");

    $sql_log = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`)

                VALUES (NULL, '$date', '$UID', '$EVENT', '$REMARKS');";

    $result_log = $conn->query($sql_log);

    // generate log ----------------------------------------------------


?>