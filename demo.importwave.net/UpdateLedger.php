<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; include __DIR__ . "/conn.php"; 
?>

<?php
// include ("./sessionchk2.php");
// include ("./refchk.php");
// include ("./conn.php");

date_default_timezone_set('Asia/Dhaka');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method not allowed");
}

$ShipID            = $_POST["ShipID"];
$FClient           = $_POST["FClient"];
// $UID               = $_POST["UID"];
$UID = $_SESSION['user'];

$DATE = date("y-m-d");

$REMARKS = "";

// --- CHARG_WEIGHT ---
$sql_val = "SELECT COALESCE(SUM(`P.WT`),0) AS PWT
            FROM `packinglist`
            WHERE `SHIPMENT` = ? AND `CLIENT` = ? AND `CTN_NO` != 'RMB'";
$stmt_sql_val = $conn->prepare($sql_val);
$stmt_sql_val->bind_param('ss', $ShipID, $FClient);
$stmt_sql_val->execute();
$result0 = $stmt_sql_val->get_result();
$stmt_sql_val->close();

$row = $result0->fetch_assoc();
$CHARG_WEIGHT = isset($row['PWT']) ? (float)$row['PWT'] : 0.0;




// --- BILLING_AMOUNT ---
$sql_val = "SELECT COALESCE(SUM(`P.WT`*`UNIT_PRICE`),0) AS TOTAL_BILL
            FROM `packinglist`
            WHERE `SHIPMENT` = ? AND `CLIENT` = ? AND `CTN` = 1";
$stmt_sql_val2 = $conn->prepare($sql_val);
$stmt_sql_val2->bind_param('ss', $ShipID, $FClient);
$stmt_sql_val2->execute();
$result0 = $stmt_sql_val2->get_result();
$stmt_sql_val2->close();

$row = $result0->fetch_assoc();
$BILLING_AMOUNT = isset($row['TOTAL_BILL']) ? (float)$row['TOTAL_BILL'] : 0.0;





    // $sqlc = "SELECT COUNT(`SHIPMENT`) CNT FROM `billledger` WHERE `SHIPMENT` = '$ShipID'  AND `CLIENT` = '$FClient';";

$sqlc = "SELECT COUNT(*) AS CNT FROM `billledger` WHERE `SHIPMENT` = ? AND `CLIENT` = ?";


    // $resultc = $conn->query($sqlc);

$stmt_sqlc = $conn->prepare($sqlc);
$stmt_sqlc->bind_param('ss', $ShipID, $FClient);
$stmt_sqlc->execute();
$resultc = $stmt_sqlc->get_result();
$stmt_sqlc->close();


    $rowc = $resultc->fetch_assoc();
    
    $CNT = ($rowc['CNT']);


if($CNT > 0){
    $REMARKS = "Edited";
}


// $sql_delete = "DELETE FROM `billledger` WHERE `SHIPMENT` = '$ShipID'  AND `CLIENT` = '$FClient';";

$sql_delete = "DELETE FROM `billledger` WHERE `SHIPMENT` = ? AND `CLIENT` = ?";

$stmt_del = $conn->prepare($sql_delete);
$stmt_del->bind_param('ss', $ShipID, $FClient);
$result1 = $stmt_del->execute();
$stmt_del->close();


// --- OUTSTANDING base ---
$sql3 = "SELECT COALESCE(SUM(`AMOUNT`),0) AS TOTAL FROM `billledger` WHERE `CLIENT` = ?";
$stmt_sql3 = $conn->prepare($sql3);
$stmt_sql3->bind_param('s', $FClient);
$stmt_sql3->execute();
$result3 = $stmt_sql3->get_result();
$stmt_sql3->close();

$row3   = $result3->fetch_assoc();
$Total3 = isset($row3['TOTAL']) ? (float)$row3['TOTAL'] : 0.0;
$Total3 = $Total3 + $BILLING_AMOUNT;
    
$REMARKS = $REMARKS." | ".$UID;


// Replace your existing INSERT block that starts with: $sql = "INSERT INTO `billledger` ...
$sql = "INSERT INTO `billledger`
        (`sn`,`SHIPMENT`,`CLIENT`,`CHARG_WEIGHT`,`DATE`,`AMOUNT`,`TYPE`,`REMARKS`,`OUTSTANDING`)
        VALUES (NULL, ?, ?, ?, ?, ?, 'SHIPMENT-BILL', ?, ?)";

$CHARG_WEIGHT   = (float)$CHARG_WEIGHT;
$BILLING_AMOUNT = (float)$BILLING_AMOUNT;
$Total3         = (float)$Total3;

$stmt_ins = $conn->prepare($sql);
$stmt_ins->bind_param(
    'ssdsdsd',          // s=string, d=double/float
    $ShipID,            // SHIPMENT
    $FClient,           // CLIENT
    $CHARG_WEIGHT,      // CHARG_WEIGHT (your earlier SUM(P.WT) -> $CHARG_WEIGHT) 
    $DATE,              // DATE (the same $DATE you already set)
    $BILLING_AMOUNT,    // AMOUNT (your TOTAL_BILL)
    $REMARKS,           // REMARKS
    $Total3             // OUTSTANDING (you already compute this)
);
$result = $stmt_ins->execute();
$stmt_ins->close();


        // generate log ----------------------------------------------------
        // date_default_timezone_set('Asia/Dhaka'); // already set at top
        $EVENT   = "Ledger Update";
        $COMMENT = "Shipment : " . $ShipID . " | CLIENT : " . $FClient;

        $UID     = $_SESSION['user'];
        $dateDT  = date("y-m-d H:i:s");

        $sql_log = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`)
                    VALUES (NULL, ?, ?, ?, ?)";
        if ($stmt_log = $conn->prepare($sql_log)) {
            $stmt_log->bind_param('ssss', $dateDT, $UID, $EVENT, $COMMENT);
            $stmt_log->execute();
            $stmt_log->close();
        }
        // generate log ----------------------------------------------------


echo "Updated.";

 ?>

