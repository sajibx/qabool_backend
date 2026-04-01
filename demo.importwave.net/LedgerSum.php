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


date_default_timezone_set('Asia/Dhaka');



$sn = 0;

$GrandTotal = 0;


$sql = "SELECT 
            A.CLIENT,
            A.TOTAL_DUE,
            C.LAST_SHIPMENT,
            B.PAYMENT_DATE,
            B.TOTAL_PAID
        FROM (
            SELECT 
                CLIENT,
                SUM(AMOUNT) AS TOTAL_DUE
            FROM billledger
            GROUP BY CLIENT
        ) A
        LEFT JOIN (
            SELECT 
                X.CLIENT,
                X.`DATE` AS PAYMENT_DATE,
                ABS(SUM(X.AMOUNT)) AS TOTAL_PAID
            FROM billledger X
            WHERE X.TYPE = 'PAYMENT'
            AND X.`DATE` = (
                SELECT MAX(Y.`DATE`)
                FROM billledger Y
                WHERE Y.CLIENT = X.CLIENT AND Y.TYPE = 'PAYMENT'
            )
            GROUP BY X.CLIENT, X.`DATE`
        ) B ON A.CLIENT = B.CLIENT
        LEFT JOIN last_shipment C ON A.CLIENT = C.CLIENT
        WHERE A.TOTAL_DUE > 0
        ORDER BY A.TOTAL_DUE DESC;";


$result = $conn->query($sql);

$data = array(); // Initialize $data as an empty array

$dataView = array(); // Initialize $dataView as an empty array



$date = date("F j, Y, l");



if ($result->num_rows > 0) {

    while($row = $result->fetch_assoc()) {

        $data[] = $row;

    }

}



foreach ($data as $row) {

    $CLIENT = $row['CLIENT'];

    $TOTAL_DUE = ($row['TOTAL_DUE']);

    $LAST_SHIPMENT = $row['LAST_SHIPMENT'];

    $PAYMENT_DATE = $row['PAYMENT_DATE'];

    $TOTAL_PAID = ($row['TOTAL_PAID']);



    $GrandTotal += $TOTAL_DUE;

    

    $date1 = $PAYMENT_DATE;

    $dataView[$sn]['CLIENT'] = $CLIENT;

    $dataView[$sn]['TOTAL_DUE'] = $TOTAL_DUE;

    $dataView[$sn]['LAST_SHIPMENT'] = $LAST_SHIPMENT;

    

    $dataView[$sn]['PAYMENT_DATE'] = $PAYMENT_DATE;

    

    $dataView[$sn]['TOTAL_PAID'] = $TOTAL_PAID;



    $sn++;

}



?>



<style type="text/css">

    tbody tr:hover {

        background-color: #c0d5ff;
        font-weight: bold;

    }

    

    tbody tr.no-border {

        border: none;

    }

</style>



<div id="EXPORT1" style="1px solid red; width: 100%; min-height: 50px; padding-top:20px; padding-bottom: 75px;">

    <table style="padding: 0px; width:100%;text-align: center; font-size: 13px; font-family: Calibri, sans-serif; cursor: pointer; " id="">

        <tr>

            <th style="background-color: #c0d5ff;" colspan="5">LEDGER SUMMARY (  <?PHP echo $date?>  )</th>

        </tr>

        <tr>

            <!--<th style="background-color: #c0d5ff;">SN</th>-->

            <th style="background-color: #c0d5ff;">CLIENT</th>

            <th style="background-color: #c0d5ff;">TOTAL DUE</th>

            <th style="background-color: #c0d5ff;">LAST SHIPMENT</th>

            <th style="background-color: #c0d5ff;">LAST PAYMENT DATE</th>

            <th style="background-color: #c0d5ff;">LAST PAID</th>

        </tr>



        <?php

        $i = 1;

        foreach ($dataView as $row) {

            

            $x = $i % 2;

            

            if($x == 1){

        ?>

            <tr style="text-align:left;">

            

            <?php

        

            }

            else{

                ?>

                

            <tr style="text-align:left; background-color:#cccccc;"

                    onmouseover="this.style.backgroundColor='#c0d5ff';" onmouseout="this.style.backgroundColor='#cccccc';">

                <!--<td><?php echo $i; ?></td>-->

                

     <?php } 

     

                $dateV = $row['PAYMENT_DATE'];

                

                if(!empty($dateV)){

                    // echo "OK";  

                    $formattedDate = date("M d, Y, D", strtotime($row['PAYMENT_DATE']));

                    $MoneyF = moneyFormat(number_format($row['TOTAL_PAID'], 2, '.', ''));

                }

                else{

                    // echo "NO";

                    $formattedDate = "";

                    $MoneyF = "";

                }

     ?>

                

                

                <td><?php echo $row['CLIENT']; ?> </td>

                <td style="text-align:right;"><?php echo moneyFormat(number_format($row['TOTAL_DUE'], 2, '.', '')); ?></td>

                <td style="text-align:center;"><?php echo $row['LAST_SHIPMENT']; ?></td>

                <td style="text-align:left;"><?php echo $formattedDate; ?></td>

                <td style="text-align:right;"><?php echo $MoneyF ?></td>

            </tr>

                

                

                <?php

            $i++;

        }

        ?>

        

        <tr>

            <!--<th style="background-color: #c0d5ff;"></th>-->

            <th style="background-color: #c0d5ff;">GRAND TOTAL:</th>

            <th style="background-color: #c0d5ff; text-align:right;"><?php echo moneyFormat(number_format($GrandTotal, 2, '.', '')); ?></th>

            <th style="background-color: #c0d5ff;"></th>

            <th style="background-color: #c0d5ff;"></th>

            <th style="background-color: #c0d5ff;"></th>

            

        </tr>

    </table>

</div>



<!--<input class="flat_button" type="button" onclick= "exportToExcelUpdated('EXPORT1', 'Summary-<?php
//  echo $ShipID; 
 ?>')" value="Export" >-->



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



<?php



            // generate log ----------------------------------------------------
            date_default_timezone_set('Asia/Dhaka');

            $EVENT      = "LedgerSum"; 
            $REMARKS    = "Ledger Sum Generated"; 
            
            $UID        = $_SESSION['user']; 
            $date = date("y-m-d H:i:s");
            $sql_log    = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`)
                                VALUES (NULL, '$date', '$UID', '$EVENT', '$REMARKS');";
            $result_log = $conn->query($sql_log);
            
            // generate log ----------------------------------------------------

// exit();
// ------------------------------------------------------



?>

