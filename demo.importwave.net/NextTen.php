<?php
include ("./sessionchk2.php");

$StP = $_POST['NSTP'];
$SKEY = $_POST['SKEY'];

// Read the button count per page from the INI file
$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/App_Data/app.ini', true);
$buttonCountPerPage = isset($ini["GENERAL"]["LAST10LIMIT"]) ? $ini["GENERAL"]["LAST10LIMIT"] : (isset($ini["GENERAL"]["LAST12LIMIT"]) ? $ini["GENERAL"]["LAST12LIMIT"] : 12);

// Calculate pagination
$StP1 = ($StP / $buttonCountPerPage) + 1;
$StPs = ($StP1 - 1) * $buttonCountPerPage + 1;
$StPe = $StPs + $buttonCountPerPage - 1; // Display $buttonCountPerPage buttons
$StPs = sprintf("%02d", $StPs);

include ("./conn.php");

// Get total shipment count
$stmt1 = $conn->prepare("SELECT Count(SHIPMENT) AS COUNT FROM ShipmentLog");
$stmt1->execute();
$result1 = $stmt1->get_result();
$row1 = $result1->fetch_assoc();
$cnt = $row1['COUNT'];

$SLen = strlen($SKEY);
$SQL_FIRST = "SELECT `SHIPMENT` FROM `ShipmentLog`";

if ($SLen > 2) {        
    $sql_mid = " WHERE `SHIPMENT` LIKE '%" . $conn->real_escape_string($SKEY) . "%'";
    $SQL_FIRST .= $sql_mid;

    $stmt1 = $conn->prepare("SELECT Count(SHIPMENT) AS COUNT FROM ShipmentLog WHERE `SHIPMENT` LIKE ?");
    $likeSKEY = "%$SKEY%";
    $stmt1->bind_param("s", $likeSKEY);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $row1 = $result1->fetch_assoc();
    $cnt = $row1['COUNT'];
}

$SQL_LAST = " ORDER BY `DATE` DESC, `SN` DESC LIMIT ? OFFSET ?";
$sql = $SQL_FIRST . $SQL_LAST;

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $buttonCountPerPage, $StP);
$stmt->execute();
$result = $stmt->get_result();
$Nrows = $result->num_rows;

$sn = 0;
$StPs1 = 0;
$StPe1 = 0;

if ($Nrows > 0) {
    // Calculate display range
    $StPs1 = max($cnt - $StPs + 1, 0);
    $StPe1 = max($cnt - $StPe + 1, 0);
    $StPs1 = min($StPs1, $cnt);
    $StPe1 = min($StPe1, $cnt);

    while ($row = $result->fetch_assoc()) {
        $value1 = $row['SHIPMENT'];
        $sn++;
        $BtnFace = $row['SHIPMENT'];
?>
<button id="<?php echo $sn; ?>" value="<?php echo $value1; ?>" class="button-6" style="margin-left: 10px; margin-top: 10px; border: 1px solid gray;" 
    onclick="getShipment(<?php echo $sn; ?>);"><?php echo $BtnFace; ?></button>
<?php 
    }
} else {
    echo "No Shipments here.";
}

$conn->close();
?>

<div style="border: 0px solid red; width:100%; margin-top:20px; float:left; text-align:right; bottom:0; font-size: 15px;">
    <?php echo "Showing: $StPs1-$StPe1"; ?>
</div>

<style type="text/css">
    /* CSS */
    .button-6 {
        min-width: 170px;
        height: 35px;
        align-items: center;
        background-color: #FFFFFF;
        border: 0px solid rgba(0, 0, 0, 0.1);
        border-radius: .25rem;
        box-shadow: rgba(0, 0, 0, 0.02) 0 1px 3px 0;
        box-sizing: border-box;
        color: rgba(0, 0, 0, 0.85);
        cursor: pointer;
        display: inline-flex;
        font-family: system-ui,-apple-system,system-ui,"Helvetica Neue",Helvetica,Arial,sans-serif;
        font-size: 16px;
        font-weight: 600;
        justify-content: center;
        line-height: 1.25;
        margin: 0;
        padding: calc(.875rem - 1px) calc(1.5rem - 1px);
        position: relative;
        text-decoration: none;
        transition: all 250ms;
        user-select: none;
        -webkit-user-select: none;
        touch-action: manipulation;
        vertical-align: baseline;
    }
    .button-6:hover,
    .button-6:focus {
        border-color: rgba(0, 0, 0, 0.15);
        box-shadow: rgba(0, 0, 0, 0.1) 0 4px 12px;
        color: rgba(0, 0, 0, 0.65);
    }
    .button-6:hover {
        transform: translateY(-1px);
    }
    .button-6:active {
        background-color: #F0F0F1;
        border-color: rgba(0, 0, 0, 0.15);
        box-shadow: rgba(0, 0, 0, 0.06) 0 2px 4px;
        color: rgba(0, 0, 0, 0.65);
        transform: translateY(0);
    }
</style>