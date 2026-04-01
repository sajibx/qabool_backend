<?php
include __DIR__ . "/conn.php"; 
// include ("./conn.php");

// Ensure consistent use of $conn throughout
// Assuming $conn is defined in conn.php

function isValidGUID($guid) {
    // Check length
    if (strlen($guid) !== 36) {
        return false;
    }
    // Check format
    if (!preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $guid)) {
        return false;
    }
    // Check version
    $versionByte = substr($guid, 14, 1);
    if ($versionByte !== '4') {
        return false;
    }
    // Check variant
    $variantByte = substr($guid, 19, 1);
    if (!in_array($variantByte, ['8', '9', 'a', 'b'])) {
        return false;
    }
    return true;
}


// include("./MoneyFormat.php");
include __DIR__ . "/MoneyFormat.php"; 



$guid = $_REQUEST["iid"] ?? '';

if (!isValidGUID($guid)) {
    echo "<br>The key is broken.";
    exit();
}

$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/App_Data/app.ini', true);
$DAYS = $ini["CONFIG"]["LINK_EXPIRE_IN_DAYS"];
$hours = $DAYS * 24;

$stmt = $conn->prepare("SELECT `SID`, `CID`, `TIME` FROM `guid` WHERE `guid` = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo "Database error. Please try again later.";
    exit();
}
$stmt->bind_param("s", $guid);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row === null) {
    echo "Link is not valid.";
    exit();
}

$SID = $row['SID'];
$CID = $row['CID'];
$FClient = $CID;
$ShipID = $SID;

date_default_timezone_set('Asia/Dhaka');
$TIMENOW = date("Y-m-d H:i:s");
$TIME = $row['TIME'];

$date1 = new DateTime($TIMENOW);
$date2 = new DateTime($TIME);

$interval = $date1->diff($date2);
$diffInHours = $interval->h + ($interval->days * 24);

if ($diffInHours > $hours) {
    die("Link Expired.");
}

if ($FClient !== 'CLIENT') {
    $stmt1 = $conn->prepare("SELECT `LAST_SHIPMENT` FROM `last_shipment` WHERE `CLIENT` = ?");
    if (!$stmt1) {
        error_log("Prepare failed: " . $conn->error);
        echo "Database error. Please try again later.";
        exit();
    }
    $stmt1->bind_param("s", $FClient);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $row = $result1->fetch_assoc();
    $stmt1->close();
    $LstSHipment = $row['LAST_SHIPMENT'] ?? '';
}

$sql = "SELECT `SN`, `SHIPMENT`, `CTN_NO`, `WEIGHT`, `P.WT`, `CLIENT`, `CTN`, `SHIPPING_MARK`, `ITEM_LIST`, `UNIT_PRICE`, `TOTAL_AMOUNT` FROM `packinglist` WHERE `SHIPMENT` = ? AND `CTN` = 1";
if ($FClient !== "CLIENT") {
    $sql .= " AND `CLIENT` = ?";
}
$sql .= " ORDER BY `CLIENT` ASC, `SN` ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo "Database error. Please try again later.";
    exit();
}
if ($FClient !== "CLIENT") {
    $stmt->bind_param("ss", $ShipID, $FClient);
} else {
    $stmt->bind_param("s", $ShipID);
}

// Corrected $wSQL query with prepared statement
$wSQL = "SELECT `WHLOC`, `WHNAME` FROM `WareHouse` WHERE `WHNAME` IN (SELECT `WAREHOUSE` FROM `ShipmentLog` WHERE `SHIPMENT` LIKE ?)";
$stmt2 = $conn->prepare($wSQL);
if (!$stmt2) {
    error_log("Prepare failed for wSQL: " . $conn->error);
    $WHNAME_DB = 'Unknown'; // Fallback value
} else {
    $likeShipID = "%$ShipID%"; // Add wildcards for LIKE
    $stmt2->bind_param("s", $likeShipID);
    $stmt2->execute();
    $Wresult = $stmt2->get_result();
    $Wrow = $Wresult->fetch_assoc();
    $stmt2->close();
    $WHNAME_DB = $Wrow['WHLOC'] ?? 'Unknown'; // Fallback if no results
}

$SubTotal = 0;
$TCTN = 0;
$Tweight = 0;
?>

<!DOCTYPE html>
<html>
<title>Invoice - <?php echo htmlspecialchars($FClient . "- " . $ShipID); ?></title>
<head>
<link rel="shortcut icon" href="./images/logo_small.png" type="image/x-icon"/>
</head>
<body onkeydown="handleKeyPress(event);">
<div class="BillBody">
    <div style="float: left; border:0px solid red; width: 49%;">
        <img src='./images/logo_bill.png' style='width: 270px; height: 100px; margin-top: 9px; margin-left: 20px;'>
    </div>
    <div style="float: right; border:0px solid red; width: 49%;">
        <div onclick="window.print();" style="cursor:pointer; display:block; float: right; border:0px solid blue; margin-top: 20px; margin-right: 50px; font-size: 30pt; width:200px; text-align: right; color:#990000;">
            Invoice
        </div>
        <div style="float: right; border:0px solid red; margin-top: 20px; margin-right: 50px; font-size: 11pt; width:300px; text-align: right;">
            Shipment ID: <?php echo htmlspecialchars($ShipID); ?><br>
            Date: 
            <script type="text/javascript">
                var today = new Date();
                var dd = String(today.getDate()).padStart(2, '0');
                var mm = String(today.getMonth() + 1).padStart(2, '0');
                var yyyy = today.getFullYear();
                today = dd + '-' + mm + '-' + yyyy;
                document.write(today);
            </script><br>
        </div>
    </div>
    <div style="width:99%; float: left; margin-top: 25px; margin-bottom: 20px; margin-left: 10px; font-weight: bold; color:#990000; font-size: 14pt;">
        <div style="text-align:left; min-width: 300px; float: left; border: 0px solid red; padding-left: 5px;">
            Bill To: <?php echo htmlspecialchars($FClient); ?>
        </div>
        <div style="text-align:right; min-width: 300px; float: right; border: 0px solid blue; padding-right: 50px;">
            Warehouse: <?php echo htmlspecialchars($WHNAME_DB); ?>
        </div>
    </div>
    <div style="border: 0px solid red;">
        <table width="100%">
            <tr class="printHead">
                <th>DESCRIPTION</th>
                <th>SHIPPING MARK</th>
                <th width="80px">CTN NO</th>
                <th width="50px">CTN</th>
                <th width="80px">WEIGHT</th>
                <th width="100px">UNIT PRICE</th>
                <th width="125px">TOTAL AMOUNT</th>
            </tr>
            <?php
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $ITEM_LIST = $row['ITEM_LIST'];
                    $CTN_NO = $row['CTN_NO'];
                    $CTN = $row['CTN'];
                    $SHIPPING_MARK = $row['SHIPPING_MARK'];
                    $PWT = $row['P.WT'];
                    $chkMIX = strpos($CTN_NO, 'MIX');
                    $chkPickUp = strpos($CTN_NO, 'PICKUP');
                    $chkRMB = strpos($CTN_NO, 'RMB');
                    $PWTx = $PWT;

                    if ($chkRMB !== false || $chkPickUp !== false) {
                        $CTN = 0;
                        $PWTx = 0;
                    } elseif ($chkMIX !== false) {
                        $CTN = 0;
                    }

                    $TCTN += $CTN;
                    $Tweight = number_format((float)($Tweight + $PWTx), 2, '.', '');
                    $UNIT_PRICE = number_format((float)$row['UNIT_PRICE'], 2, '.', '');
                    $TOTAL_AMOUNT = number_format((float)$row['TOTAL_AMOUNT'], 2, '.', '');
                    $SubTotal = number_format((float)($SubTotal + $TOTAL_AMOUNT), 2, '.', '');
            ?>
            <tr>
                <td style="padding-left:5px;"><?php echo htmlspecialchars($ITEM_LIST); ?></td>
                <td style="padding-left:5px;"><?php echo htmlspecialchars($SHIPPING_MARK); ?></td>
                <td style="text-align:center;"><?php echo htmlspecialchars($CTN_NO); ?></td>
                <td style="text-align:center;"><?php echo htmlspecialchars($CTN); ?></td>
                <td style="text-align:right; padding-right:5px;"><?php echo moneyFormat($PWT); ?></td>
                <td style="text-align:right; padding-right:5px;"><?php echo moneyFormat($UNIT_PRICE); ?></td>
                <td style="text-align:right; padding-right:5px;"><?php echo moneyFormat($TOTAL_AMOUNT); ?></td>
            </tr>
            <?php
                }
            } else {
                echo '<tr><td colspan="7" style="text-align:center;">No data found for this shipment.</td></tr>';
            }
            $stmt->close();
            ?>
            <tr><td colspan="7">-</td></tr>
            <tr style="font-weight:bold; text-align: center;">
                <td colspan="3">Total</td>
                <td style="padding-right:5px;"><?php echo htmlspecialchars($TCTN); ?></td>
                <td style="text-align:right; padding-right:5px;"><?php echo moneyFormat($Tweight); ?></td>
                <td colspan="2" style="text-align:right; padding-right:5px;">&#2547;&nbsp;&nbsp;<?php echo moneyFormat(strval($SubTotal)); ?></td>
            </tr>
        </table>
        <?php  
        $PrevDue = 0.00;
        if ($ShipID === $LstSHipment) {
            $sql_due = "SELECT SUM(`AMOUNT`) AS AMOUNT FROM `billledger` WHERE `CLIENT` = ? AND `SHIPMENT` <> ?";
            $stmt3 = $conn->prepare($sql_due);
            if (!$stmt3) {
                error_log("Prepare failed for billledger: " . $conn->error);
            } else {
                $stmt3->bind_param("ss", $FClient, $ShipID);
                $stmt3->execute();
                $result = $stmt3->get_result();
                $row = $result->fetch_assoc();
                $PrevDue = $row['AMOUNT'] ?? 0.00;
                $stmt3->close();
            }
        }
        $GrandTotal = number_format((float)($SubTotal + $PrevDue), 2, '.', '');
        ?>
        <table style="border: none !important; float:right; margin-top:20px; margin-bottom: 30px; color:black; -webkit-print-color-adjust: exact;">
            <?php if ($PrevDue < 0) { ?>
            <tr>
                <td style="padding-left: 20px; background-color: #e5e5e5; font-weight: bold;">Paid</td>
                <td style="width:100px; text-align: right; color:blue; background-color: #e5e5e5; padding-right:5px;">&#2547;&nbsp;&nbsp;<?php echo moneyFormat(strval(number_format(abs($PrevDue), 2, '.', ''))); ?></td>
            </tr>
            <?php } else { ?>
            <tr>
                <td style="padding-right:30px; padding-left: 20px; background-color: #e5e5e5; font-weight: bold;">Previous Due</td>
                <td style="width:100px; text-align: right; color:red; background-color: #e5e5e5; padding-right:5px;">&#2547;&nbsp;&nbsp;
                                <?php echo moneyFormat(strval($PrevDue)); ?></td>
            </tr>
            <?php } ?>
            <tr style="font-weight: bold;">
                <td style="padding-right:30px; padding-left: 20px; background-color: #e5e5e5; font-weight: bold;">Total Receivable</td>
                <td style="width:100px; text-align: right; background-color: #e5e5e5; padding-right:5px;">&#2547;&nbsp;&nbsp;<?php echo moneyFormat(strval($GrandTotal)); ?></td>
            </tr>
        </table>
        <table width="100%" style="text-align:center; border: none; font-style: italic;">
            <tr>
                <td>Thank You for your business with us!</td>
            </tr>
        </table>
        <div style="text-align:left; float:left; font-size:10px; padding-top:5px;">This invoice is computer-generated and does not require a signature.</div>
    </div>
</div>
</body>
<script type="text/javascript">
function handleKeyPress(event) {
    if (event.keyCode === 80) {
        window.print();
    }
}
</script>
<style type="text/css">
.EdTxt {
    border: 0px;
    text-align: right;
    width: 90px;
    margin: 0 auto;
}
table, th, td {
    border: 1px solid gray;
    border-collapse: collapse;
    print-color-adjust: exact;
    padding-top: 3px;
    font-family: Calibri, sans-serif;
    font-size: 12px !important;
}
.printHead {
    background-color: #3f4e4f !important;
    font-family: Calibri, sans-serif;
    font-size: 12px !important;
    color: white;
    height: 30px;
}
@media print {
    .printHead {
        font-family: Calibri, sans-serif;
        color: white;
        -webkit-print-color-adjust: exact;
        height: 30px;
    }
    .BillBody {
        width: 800px;
        min-height: 500px;
        font-family: Calibri, sans-serif;
        font-size: 14px !important;
        border: 1px solid slategray;
        border-radius: 5px;
        margin: 0 auto;
        padding: 30px;
    }
}
.BillBody {
    background-color: #FDFCF8;
    width: 800px;
    min-height: 500px;
    border: 1px solid slategray;
    border-radius: 5px;
    margin: 0 auto;
    padding: 30px;
    font-family: Helvetica, Arial, sans-serif;
    font-size: 14px !important;
}
</style>
</html>