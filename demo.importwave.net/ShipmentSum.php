<?php
include ("./sessionchk2.php");
include ("./refchk.php");
include ("./conn.php");

// money format latest
function moneyFormat($num) {                                                                   
    // Convert input to float and format to exactly 2 decimal places
    $formattedNum = number_format((float)$num, 2, '.', '');
    
    // Split into integer and decimal parts
    $parts = explode('.', $formattedNum);
    $integerPart = $parts[0];
    $decimalPart = '.' . $parts[1];

    // Handle negative numbers
    $isNegative = $integerPart[0] === '-';
    if ($isNegative) {
        $integerPart = substr($integerPart, 1); // Remove the negative sign
    }

    $explrestunits = "";
    if (strlen($integerPart) > 3) {
        $lastthree = substr($integerPart, -3);
        $restunits = substr($integerPart, 0, -3); // Extracts all but the last three digits
        $restunits = (strlen($restunits) % 2 == 1) ? "0" . $restunits : $restunits; // Add leading zero if needed
        $expunit = str_split($restunits, 2);
        for ($i = 0; $i < sizeof($expunit); $i++) {
            // Creates each of the 2's group and adds a comma
            if ($i == 0) {
                $explrestunits .= (int)$expunit[$i] . ","; // Remove leading zeros for first group
            } else {
                $explrestunits .= $expunit[$i] . ",";
            }
        }
        $thecash = $explrestunits . $lastthree . $decimalPart;
    } else {
        $thecash = $integerPart . $decimalPart;
    }

    return $isNegative ? '-' . $thecash : $thecash; // Add negative sign if needed
}


$ShipID         = $_REQUEST["ShipID"];

$sn             = 0;

$data           = array();
$dataView       = array();

$TotalCTN       = 0;
$TotalWeight    = 0;
$TotalBill      = 0;


$sql = "
    SELECT 
         CLIENT
        ,GROUP_CONCAT(DISTINCT IF(`CTN_NO` LIKE '%RMB%'  OR `CTN_NO` LIKE '%PICK%UP%', '', ROUND(`UNIT_PRICE`,0)) ORDER BY `UNIT_PRICE` ASC SEPARATOR '/') UNIT_PRICE
        ,SUM(IF(`CTN_NO` LIKE '%RMB%' OR `CTN_NO` LIKE '%MIX%' OR `CTN_NO` LIKE '%PICK%UP%', 0, `CTN`)) CTN
        ,SUM(IF(`CTN_NO` LIKE '%RMB%'  OR `CTN_NO` LIKE '%PICK%UP%', 0, `P.WT`)) WEIGHT
        ,SUM(`TOTAL_AMOUNT`) TOTAL
    FROM `packinglist` WHERE `SHIPMENT` LIKE '$ShipID'
    GROUP BY CLIENT
";


$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $dataView[] = $row;
    }
}



?>

<style type="text/css">
    tbody tr:hover {
        background-color: #8cff32;
        cursor:pointer;
        box-shadow:         0px 0px 15px 0px #8cff32;
        -webkit-box-shadow: 0px 0px 15px 0px #8cff32;
        -moz-box-shadow:    0px 0px 15px 0px #8cff32;
        /* box-shadow: inset 0px 5px 9px -7px rgba(0, 0, 0, 0.83), inset 0px -5px 9px -7px rgba(0, 0, 0, 0.83); */
        /* box-shadow: 0px 5px 9px -7px rgba(0, 0, 0, 0.83), /* Top shadow */
              /* 0px -5px 9px -7px rgba(0, 0, 0, 0.83); */ */
    }
</style>

<div id="EXPORT1" style="1px solid red; width: 100%; min-height: 50px; padding-top:20px; padding-bottom: 75px;">
    <table style="padding: 0px; width:100%;text-align: center; font-size: 13px; font-family: Calibri, sans-serif; " id="">
        <tr>
            <th style="background-color: #c0d5ff;" colspan="6">SHIPMENT: <?php echo $ShipID; ?></th>
        </tr>
        <tr>
            <th style="background-color: #c0d5ff;">SN</th>
            <th style="background-color: #c0d5ff;">CLIENT</th>
            <th style="background-color: #c0d5ff;">WEIGHT</th>
            <th style="background-color: #c0d5ff;">CTN</th>
            <th style="background-color: #c0d5ff;">RATE</th>
            <th style="background-color: #c0d5ff;">BILLING AMOUNT</th>
        </tr>

        <?php
        $i = 1;

        foreach ($dataView as $row) {
            
            $TotalCTN       += $row['CTN'];
            $TotalWeight    += $row['WEIGHT'];
            $TotalBill      += $row['TOTAL'];
            $modified_price = preg_replace('/(^\/+|\/{2,}|\/+$)/', '', $row['UNIT_PRICE']);


        ?>
            <tr style="text-align:left;">
                
                <td><?php echo $i; ?></td>
                <td><?php echo $row['CLIENT']; ?> </td>
                <td style="text-align:right;"><?php echo moneyFormat(number_format($row['WEIGHT'], 2, '.', '')); ?></td>
                <td style="text-align:right;"><?php echo moneyFormat(number_format($row['CTN'], 2, '.', '')); ?></td>
                <td style="text-align:center;"><?php echo $modified_price; ?></td>
                <td style="text-align:right;"><?php echo moneyFormat(number_format($row['TOTAL'], 2, '.', '')); ?></td>
            </tr>
        <?php
            $i++;
        }
        // Loop Ends here
        ?>
        
        <tr >
            <th style="background-color: #c0d5ff;">Total : </th>
            <th style="background-color: #c0d5ff;"></th>
            <th style="background-color: #c0d5ff; text-align:right;"><?php echo moneyFormat(number_format($TotalWeight, 2, '.', '')); ?></th>
            <th style="background-color: #c0d5ff; text-align:right;"><?php echo moneyFormat(number_format($TotalCTN, 2, '.', '')); ?></th>
            <th style="background-color: #c0d5ff;"></th>
            <th style="background-color: #c0d5ff; text-align:right;"><?php echo moneyFormat(number_format($TotalBill, 2, '.', '')); ?></th>
        </tr>
        


    </table>
    
</div>

<?php

            // generate log ----------------------------------------------------
            date_default_timezone_set('Asia/Dhaka');

            $EVENT      = "Shipment Summary"; 
            $REMARKS    = "shipment : ".$ShipID;

            
            $UID        = $_SESSION['user']; 
            $date = date("y-m-d H:i:s");
            $sql_log    = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`)
                                VALUES (NULL, '$date', '$UID', '$EVENT', '$REMARKS');";
            $result_log = $conn->query($sql_log);
            
            // generate log ----------------------------------------------------

?>

<!--<input class="flat_button" type="button" onclick= "exportToExcelUpdated('EXPORT1', 'Summary-<?php echo $ShipID; ?>')" value="Export" >-->

<script src="./js/xlsx.full.min.js"></script>
<script src="./js/FileSaver.min.js"></script>
<script>

function exportToExcelUpdated(tableId, filename) {
  // Retrieve the table HTML content by ID
  var tableHtml = $('#' + tableId).html();

  // Convert the HTML table to a worksheet
  var worksheet = XLSX.utils.table_to_sheet(document.getElementById(tableId));

  // Create a new workbook and add the worksheet
  var workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Sheet 1');

  // Convert the workbook to an XLSX file
  var xlsxFile = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' });

  // Save the file
  var blob = new Blob([xlsxFile], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
  saveAs(blob, filename + '.xlsx');
}


</script>





