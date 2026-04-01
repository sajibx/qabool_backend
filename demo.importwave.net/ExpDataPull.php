<?php
include ("./refchk.php");
include ("./conn.php");

$shipment = $_GET['s'];


function highlightMatchedKeyword($inputValue, $clientName) {
    return preg_replace("/($inputValue)/i", "<span style='color: green;'>$1</span>", $clientName);
}


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


// function() { // mouse in
//                     $(this).find('.delete-icon').show();
//                 }, function() { // mouse out
//                     $(this).find('.delete-icon').hide();
//                 }


if (isset($_GET['s'])) {
    $inputValue = $_GET['s'];

    // Prepared statement for security
    $stmt = $conn->prepare("SELECT `SN`, `TYPE`, `AMOUNT` FROM `ExpenseLedger` WHERE `SHIPMENT` = ? ORDER BY `SN` ASC");
    $stmt->bind_param("s", $inputValue);
    $stmt->execute();
    $result = $stmt->get_result();

    $stmt1 = $conn->prepare("SELECT `DATE` FROM `ShipmentLog` WHERE `SHIPMENT` = ?");
    $stmt1->bind_param("s", $inputValue);
    $stmt1->execute();
    $dates = $stmt1->get_result();

    $datess = $dates->fetch_assoc();
    $DATE = $datess['DATE'];

    $formattedDate = date("d-M-y", strtotime($DATE));

?>

<div style="float: left; width: 300px; text-align: center; font-weight: bold;">
<?php echo $shipment; ?> (<?php echo $formattedDate; ?>)
<br><hr>
<table border="0" width="300px">
    <tr>
        <th style="text-align: left;">Expense Type</th>
        <th style="text-align: right;">Amount</th>
    </tr>
    

    <tr>
        <td colspan="2"><hr></td>
    </tr>




<?php



    $Total = 0;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $TYPE = htmlspecialchars($row['TYPE']);
            $AMOUNT = htmlspecialchars($row['AMOUNT']);

            $sn = htmlspecialchars($row['SN']);


            $Total+= $AMOUNT;
            ?>
            <tr>
                <td style="text-align: left;"><?php echo $TYPE; ?></td>
                <td style="text-align: right;"><?php echo moneyFormat($AMOUNT); ?></td>
                <td>                    
                    <div class="icon-container">
                        <span class="delete-icon" onclick="DeleteRecExp1184('<?php echo $sn; ?>','<?php echo $TYPE; ?>');">&#10060;</span>
                        <!-- Function in Pannel 3 -->
                    </div>
                </td>
            </tr>
            <?php
        }
    } else {
        echo "<tr><td colspan='2' style='text-align: center;'>No Expense found.</td></tr>";
    }

    $stmt->close();
}



$sql = "SELECT  SUM(`AMOUNT`) TOTAL FROM `billledger` WHERE `SHIPMENT` = '$inputValue' AND `TYPE` = 'SHIPMENT-BILL'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();


    $Bill = ($row['TOTAL']);

    $Profit = $Bill - $Total;

$conn->close();
?>



    <tr>
        <td colspan="2"><hr></td>
    </tr>

<!--  <tr style="height: 3px;">
    <td colspan="2"></td>
</tr> -->




    <tr style="font-weight: ;">
        <td >Total Expense</td>
        <td style="text-align: right;"><?php echo moneyFormat($Total); ?></td>
    </tr>


    <tr style="font-weight: ;">
        <td >Shipment Bill</td>
        <td style="text-align: right;"><?php echo moneyFormat($Bill); ?></td>
    </tr>




    <tr>
        <td colspan="2"><hr></td>
    </tr>

    <tr style="font-weight: bold;">
        <td >Profit</td>
        <td style="text-align: right;"><?php echo moneyFormat($Profit); ?></td>
    </tr>

</table>

</div>

<style type="text/css">
    .icon-container {
  position: relative; /* Ensure the container is positioned for hover effects */
}

.delete-icon {
  font-size: 8px;
  cursor: pointer;
  visibility: hidden; /* Initially hidden */
  opacity: 0; /* Make it transparent */
  transition: visibility 0.2s, opacity 0.2s; /* Smooth transition */
}

.icon-container:hover .delete-icon {
  visibility: visible; /* Make it visible on hover */
  opacity: 1; /* Fully opaque */
}

</style>
