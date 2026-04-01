<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; 
?>
<?php
// include ("./sessionchk.php");
// include("./refchk.php");
?>


<style type="text/css">

tbody tr:hover {
/*    background-color: #8cff32;*/
    cursor: pointer;
    font-weight: bold;
 /*   box-shadow: 0px 0px 15px 0px #8cff32;
    -webkit-box-shadow: 0px 0px 15px 0px #8cff32;
    -moz-box-shadow: 0px 0px 15px 0px #8cff32;*/
}

/* Disable hover effect for the specific table */
.no-hover tbody tr:hover {
    background-color: transparent !important;
    cursor: default !important;
    font-weight: normal !important;
    box-shadow: none !important;
    -webkit-box-shadow: none !important;
    -moz-box-shadow: none !important;
}

    
    #scrolling-text {
      width: 100%;
      overflow: hidden;
      white-space: nowrap;
    }

    .tddel{
        border-top: 0px;
        border-left: 0px;
        border-bottom: 0px;
        width: 5px;
    }
</style>
<!-- --new entry -->

<script>
    

</script>




<div id="EntryBox" style="width:100%; border:0px solid red; padding-top:5px;float:right; text-align: right; padding-bottom:5px;">
    
    <div class="StanTxt">   |  Press 'L' to open Ledger.  |  Press 'R' to Reload.  |  Press 'S' for Summary.  |  Press 'A' to Add New Entry  |  Press 'I' for invoice  |   </div>
    <span></span>
</div> 


<div style="1px solid red; width: 100%; min-height: 50px; padding-top:20px; padding-bottom: 75px;">

       <table border="0"  class="StanTxt"  style="padding: 0px; width:100%;text-align: center; border-left:0px; border-top:0px; " id="export">
              <tr>
                        <th class="tddel"></th>
                        <th> - </th>
                        <th style="background-color: #c0d5ff;">CTN NO</th>
                        <th style="background-color: #c0d5ff;">CLIENT</th>
                        <th style="background-color: #c0d5ff;">SHIPPING MARK</th>
                        <th style="background-color: #c0d5ff;">ITEM LIST</th>
                        <th style="background-color: #c0d5ff;">WEIGHT</th>
                        <th style="background-color: #c0d5ff;">P.WT</th>
                        <th style="background-color: #c0d5ff;">UNIT PRICE</th>
                        <th style="background-color: #c0d5ff;">TOTAL</th>

                </tr>
<?php 

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



// --------------------------------------------------------------------------------------------

include ("./conn.php");


function generateGUID()
{
    if (function_exists('com_create_guid') === true) {
        return trim(com_create_guid(), '{}');
    }

    $data = openssl_random_pseudo_bytes(16);
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}


$guid = generateGUID();

$FClient           = $conn->escape_string($_REQUEST["FClient"]);
$Fitem             = $conn->escape_string($_REQUEST["Fitem"]);
$Fmark             = $conn->escape_string($_REQUEST["Fmark"]);
$ShipID            = $conn->escape_string($_REQUEST["ShipID"]);

$wSQL = "SELECT `WAREHOUSE` FROM `ShipmentLog` WHERE `SHIPMENT` = '$ShipID';";

    $Wresult = $conn->query($wSQL);

    $WHNAME_DB = '';
    if ($Wresult && $Wrow = $Wresult->fetch_assoc()) {
        $WHNAME_DB = $Wrow['WAREHOUSE'];
    }
    

if($FClient == 'CLIENT'){

    $LstSHipment = "";
    
}
else{
    $sql_lstShip = "SELECT `LAST_SHIPMENT` FROM `last_shipment` WHERE `CLIENT` = '$FClient'";
    
    $result1 = $conn->query($sql_lstShip);

    $LstSHipment = '';
    if ($result1 && $row = $result1->fetch_assoc()) {
        $LstSHipment = $row['LAST_SHIPMENT'];
    }

    // echo $sql_lstShip;
    // print_r($LstSHipment);

    ?>

                    <input type="hidden" id="LstSHipment" value="<?php echo htmlspecialchars($LstSHipment); ?>">
                    <input id="guid" type="hidden" name="guid" value="<?php echo htmlspecialchars($guid); ?>">
                    

<?php

}



$sn = 0;

$sql = "SELECT `SN`, `SHIPMENT`, `CTN_NO`, `CTN`,`WEIGHT`,`P.WT`, `CLIENT`, `SHIPPING_MARK`,
             `ITEM_LIST`, `UNIT_PRICE`,`TOTAL_AMOUNT`,`STATUS`, `DELIVERY`, `DEL_DATE`, `DEL_USER` FROM `packinglist` 
                    WHERE `SHIPMENT` = '$ShipID'";
//  AND `WEIGHT` <> 0";

// $sql1 = $sql;







if ($FClient<>"CLIENT") {
        
    $temp = " AND `CLIENT` = '$FClient'";

    $sql = $sql.$temp;
}

if ($Fitem<>"ITEM") {

    $temp = " AND `ITEM_LIST` = '$Fitem'";

    $sql = $sql.$temp;
}

if ($Fmark<>"MARK") {

    $temp = " AND `SHIPPING_MARK` = '$Fmark'";

    $sql = $sql.$temp;
}
// echo $sql;

$sql = $sql." ORDER BY `CLIENT` ASC, `CTN_NO` ASC";

$SubTotal = 0;
$Tweight = 0;
$TotalCTN = 0;

$result = $conn->query($sql);


if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            
            $PID                = $row['SN'];
            $SHIPMENT           = $row['SHIPMENT'];
            
            $CTN_NO             = $row['CTN_NO'];
            $CTN                = $row['CTN'];
            
            $WEIGHT             = $row['WEIGHT'];
            $PWT                = $row['P.WT'];
            $WGAP               = $WEIGHT-$PWT;
            
            // $chkMIX         = "";
            $chkMIX         = strpos($CTN_NO, 'MIX');
            
            // $chkPickUp      = "";
            $chkPickUp      = strpos($CTN_NO, 'PICKUP');
            
            // $chkRMB         = "";
            $chkRMB         = strpos($CTN_NO, 'RMB');
            
            $PWTx           = $PWT;
            
            $sn = $sn+1;

            if ($chkRMB !== false or $chkPickUp !== false ) {
                $CTN = 0;
                $PWTx = 0;
            } else if($chkMIX !== false){
                $CTN = 0;
            }
    
                $Tweight    = number_format((float)($Tweight+$PWTx), 2, '.', '');
                $TotalCTN   = $TotalCTN +   $CTN;

            
            


            $UNIT_PRICE         = $row['UNIT_PRICE'];

            // if($UNIT_PRICE = "0.00"){$UNIT_PRICE = ""};

            $CLIENT             = $row['CLIENT'];

            $SHIPPING_MARK      = $row['SHIPPING_MARK'];
            $ITEM_LIST          = $row['ITEM_LIST'];

            $TOTAL_AMOUNT       = number_format((float)$row['TOTAL_AMOUNT'], 2, '.', '');
            $SubTotal           = number_format((float)($SubTotal+$TOTAL_AMOUNT), 2, '.', '');

            $STATUS             = $row['STATUS'];

            $DEL_STAT           = $row['DELIVERY'];

            if (!empty($row['DEL_DATE'])) {
                $DEL_DATE       = date("d M y", strtotime($row['DEL_DATE']));
                $DEL_USER       = $row['DEL_USER'];
            } else {
                $DEL_DATE       = ""; // or NULL or whatever default you want
                $DEL_USER       = "";

            }
                        

            
             
?>
<script>
    function showButton(TARGET) {

        document.getElementById(TARGET).style.display = "inline";

    }

    function hideButton(TARGET) {

        document.getElementById(TARGET).style.display = "none";

    }


    function ShowCHK(TARGET) {

        var numberPart  = TARGET.substring(7);

        var TgCHKBOX    = 'CHKTggl'+numberPart;


        document.getElementById(TgCHKBOX).style.display = "inline";

        document.getElementById(TARGET).style.display = "none";

    }

function ToggleCHK(TARGET) {

    var numberPart = TARGET.substring(7);

    let checkboxElement = document.getElementById(TARGET);
    let isChecked = checkboxElement.checked;

    var DelStat;

    if (isChecked) {
        DelStat = 'Y';
    } else {
        DelStat = 'N';
    }

    var link = "DeliveryToggle.php";
    
    var arr = {
        NumberPart: numberPart, // The extracted number (e.g., '238954')
        DelStatus: DelStat    // The determined status ('Y' or 'N')
    };
    

    $.ajax({
        url: link,
        type: "POST",
        data: arr, // Now includes DelStat and numberPart
        cache: false,
        success: function(data) {
            
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);

        }
    });
}

   


function DelItemOTG(TARGET) {
  Swal.fire({
    title: 'Confirm Delete',
    text: `Are you sure you want to delete the selected item ?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      var link = "DelItemOTG.php";
      var arr = { TARGET: TARGET };

      $.ajax({
        url: link,
        type: "POST",
        data: arr,
        cache: false,
        success: function(data) {
          Reload();
        }
      });
    }
  });
}

    
</script>
        <tr style="">
            <td class="tddel" style="border-right: 0;">
                <div style="height:15px; width:15px; cursor:pointer; font-size: 12px;" 
                    onclick="DelItemOTG(<?php echo $PID; ?>)"
                    onmouseover="showButton('BTNdel<?php echo $PID; ?>')" 
                    onmouseout="hideButton('BTNdel<?php echo $PID; ?>')">
                        <div style="display:none; vertical-align: middle; text-align:center;" id="BTNdel<?php echo $PID; ?>"  >
                        &#10060;</div>
                </div>
            </td>

            <td class="tddel">
                <?php if($DEL_STAT == "Y"){?>

                            <img title="Delivered : <?php echo $DEL_DATE.' | '.$DEL_USER; ?>"  src='./images/check.png' style='cursor: hand; width: 13px; height: 14px; display:inline;                               border: 0px solid green;
                                padding-left: 4px; padding-right: 4px;'

                                id="CHKDeli<?php echo $PID; ?>"

                            ondblclick="ShowCHK('CHKDeli<?php echo $PID; ?>')">



                            <input type="checkbox"
                               id="CHKTggl<?php echo $PID; ?>"
                               checked
                               onchange="ToggleCHK(this.id)"
                               style="display: none;">




                   <?php } else{ ?>

                        <img title="in Warehouse"  src='./images/circle2.png' style='cursor: hand; width: 11px; height: 11px; display:inline;                               
                                                border: 0px solid green;padding-left: 4px; padding-right: 4px;'>




                  <?php } ?>
            </td>
            
                <?php 
                    if($CTN == "1"){?>
                        <td style=" text-align:left; padding-left:3px;" width="100px" title="CTN = <?php echo $CTN ?>"><?php echo htmlspecialchars($CTN_NO); ?>
<?php 
                    }
                    else{?>
                        <td style="background-color: #ffffa5; text-align:left;  padding-left:3px;" width="100px" title="CTN = <?php echo $CTN ?>"><?php echo htmlspecialchars($CTN_NO); ?>
<?php                       
                    }
                    
                    
                ?>


                
                    <input style="display: none;" type="text" id="PID<?php echo $sn; ?>" value="<?php echo $PID ?>">
                </td>

                <!--<td width="150px" style="text-align:left;  padding-left:3px;"><?php echo $CLIENT; ?></td>-->
                
                <td width="150px" style="text-align:left;  padding-left:3px; background-color: #fff8e5;">
                    
                    
                    <input style="background-color: #fff8e5; width:100%; text-align:left;" id="<?php echo $PID."NM" ?>" 
                            onChange="UpdateVal('<?php echo $PID; ?>','NM', <?php echo $sn; ?> );" 
                        class="EdTxt" type="text"  value="<?php echo htmlspecialchars($CLIENT); ?>">
                
                
                </td>
                
                
                <td width="" style="text-align: left;  padding-left:3px;"><?php echo htmlspecialchars($SHIPPING_MARK); ?></td>
                <td width=";" style="text-align: left;  padding-left:3px;"><?php echo htmlspecialchars($ITEM_LIST); ?></td>
                
                <?php
                
                    $ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/App_Data/app.ini', true);
        
                    $WMIN   = $ini["CONFIG"]["WEIGHT_THRESHOLD_MIN"];
                    $WMAX   = $ini["CONFIG"]["WEIGHT_THRESHOLD_MAX"];
                    
                    if($WGAP < $WMIN){
                        ?>
                        <td title="<?php echo $WGAP*(-1); ?>" width="70px" style="text-align:center; background-color: blue; color:white;"><?php echo $WEIGHT; ?></td>
                        <?php
                    }
                    
                    else if($WGAP > $WMAX){
                        ?>
                        <td title="<?php echo $WGAP; ?>"  width="70px" style="text-align:center; background-color: red; color:white;"><?php echo $WEIGHT; ?></td>
                        <?php
                    }
                    else{
                        ?>
                            <td width="70px" style="text-align:center;"><?php echo $WEIGHT; ?></td>
                        <?php
                    }
                ?>
                
                

                <td width="70px" style="background-color: #fff8e5; " >
                        <input style="background-color: #fff8e5; width:100%; text-align:right;" id="<?php echo $PID."WT" ?>" 
                            onChange="UpdateVal('<?php echo $PID; ?>','WT', <?php echo $sn; ?> );" 
                        class="EdTxt" type="text"  value="<?php echo $PWT; ?>" 
                        
                        onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57))"
                       
                        >
                    
                </td>

                <td width="100px" style="background-color: #fff8e5;" >      

                        <input style="background-color: #fff8e5; width:100%; text-align:right;" id="<?php echo $PID."PT" ?>" onChange="UpdateVal('<?php echo $PID; ?>','PT', <?php echo $sn; ?> );" 
                        class="EdTxt" type="text"  value="<?php echo $UNIT_PRICE; ?>"        
                        onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57))"
                        >
                    
                </td>

                <td width="100px" ondblclick="CopyTxt(<?php echo $sn; ?>);">
                        <input style="width:100%; color:blue;" id="<?php echo $PID."TT" ?>" 
                        
                        class="EdTxt" type="text"  value="<?php echo moneyFormat(strval($TOTAL_AMOUNT)); ?>"    disabled    >


                    
                </td> 
        </tr>


<?php  }} ?>

        <tr>
            
            <td style="border-top:0px; border-left:0px; border-bottom:0px;"></td>
            <td> - </td>          
            <td style="background-color: #c0d5ff;"></td>            
            <td style="background-color: #c0d5ff;"></td>  
            <td style="background-color: #c0d5ff;"></td>   
            <td style="background-color: #c0d5ff; text-align:center; font-weight: bolder; ">CTN : <?php echo moneyFormat(strval($TotalCTN)); ?> </td>
            <td style="background-color: #c0d5ff; text-align:center; font-weight: bolder; ">Item : <?php echo moneyFormat(strval($sn)); ?> </td>
            
            

            <td style="background-color: #c0d5ff; text-align:right; font-weight: bolder; "><?php echo moneyFormat(strval($Tweight)); ?> </td>
            <td style="background-color: #c0d5ff;"></td>
            <td style="background-color: #c0d5ff;"></td>
        </tr>

    </table>
<span style="font-size: 8pt; text-align: left; float: left; color: darkgray;">
    CTN NO with MIX, PICKUP, or RMB are not included in the total carton count.<br>
</span>


<?php 

$PrevDue = 0.00;

    if($ShipID == $LstSHipment){

        $sql_due = "SELECT SUM(`AMOUNT`) 'AMOUNT' FROM `billledger` WHERE `CLIENT` = '$FClient' AND `SHIPMENT` <> '$ShipID'";

        $result = $conn->query($sql_due);

        $PrevDue = 0.00;
        if ($result && $row = $result->fetch_assoc()) {
            $PrevDue = $row['AMOUNT'] ?? 0.00;
        }


    }
    else {
    }

    

    $GrandTotal = number_format((float)($SubTotal + $PrevDue), 2, '.', '');


 ?>
<table class="no-hover" style="width: 100%; margin-top: 20px; border-collapse: collapse;">
    <tr>
        <!-- Left Side: Warehouse, Select, and No Pending Dues in One Row -->
        <td style="width: 65%; vertical-align: top; padding: 10px; border: none;">
            <div>
                <div>
                    <strong>Warehouse : </strong>
                    <select class="classic" style="width: 160px; margin-top: 5px; margin-left: 10px;" onchange="WHUpdate(this.value, 'WHNAME');">
                        <option value="<?php echo htmlspecialchars($WHNAME_DB); ?>"><?php echo htmlspecialchars($WHNAME_DB); ?></option>
                        <?php 
                            $sql_item = "SELECT `WHNAME` FROM `WareHouse`";
                            $result = $conn->query($sql_item);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="'.htmlspecialchars($row['WHNAME']).'">'.htmlspecialchars($row['WHNAME']).'</option>';
                                }
                            }
                        ?>
                    </select>
                </div>
<br>
                <?php
                    $sql_item = "SELECT DISTINCT(`CLIENT`) AS CLIENTN FROM `packinglist` 
                                WHERE `SHIPMENT` = '$ShipID' 
                                AND `CLIENT` NOT IN 
                                (SELECT `CLIENT` FROM `billledger` WHERE `SHIPMENT` = '$ShipID' AND `TYPE` LIKE 'SHIPMENT-BILL')";
                    $result = $conn->query($sql_item);
                    $RowCnt = $result->num_rows;

                    if ($RowCnt > 0) {
                ?>
                <div>
                    <strong>Pending (<?php echo $RowCnt; ?>) : </strong>
                    <select class="classic" id="ClientName" style="width: 160px; margin-left: 10px;" onchange="FilterUpdate(this.value, 'CLIENT');">
                        <option value="CLIENT">Select Client</option>
                        <?php 
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="'.htmlspecialchars($row['CLIENTN']).'">'.htmlspecialchars($row['CLIENTN']).'</option>';
                            }
                        ?>
                    </select>
                </div>
                <?php } else { ?>
                    <div><strong>...</strong></div>
                <?php } ?>
            </div>
        </td>

        <!-- Right Side: Sub Total, Previous Due, Total Receivable -->
        <td style="width: 34%; vertical-align: top; padding: 10px; border: none;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="background-color: #6FA8DC; padding: 5px 20px; border: none;">Sub Total</td>
                    <td style="text-align: right; background-color: #b7d3ed; padding: 5px 20px; border: none;">
                        <?php echo moneyFormat(strval($SubTotal)); ?>
                    </td>
                </tr>

            <?php if ($PrevDue < 0) { ?>

                    <td style="background-color: #6FA8DC; padding: 5px 20px; border: none;">Paid</td>
                    <td style="text-align: right; color: green; background-color: #b7d3ed; padding: 5px 20px; border: none;">
                        <?php echo moneyFormat(strval(number_format(abs($PrevDue), 2, '.', ''))); ?>
                    </td>
            <?php } else { ?>

                    <td style="background-color: #6FA8DC; padding: 5px 20px; border: none;">Previous Due</td>
                    <td style="text-align: right; color: red; background-color: #b7d3ed; padding: 5px 20px; border: none;">
                        <?php echo moneyFormat(strval($PrevDue)); ?>
                    </td>

            <?php } ?>

                <tr style="font-weight: bold;">
                    <td style="background-color: #6FA8DC; padding: 5px 20px; border: none;">Total Receivable</td>
                    <td style="text-align: right; background-color: #b7d3ed; padding: 5px 20px; border: none;">
                        <?php echo moneyFormat(strval(number_format($GrandTotal, 2, ".", ""))); ?>
                        

                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>




        <!-- Yellow marked CTN NO will not be shown in Invoice as their CTN = 0 -->

    <div  style="width:100%; float:right;  color:blueviolet; border: 0px solid red;">
        
            
        <?php if($FClient != 'CLIENT'){ ?>
        
            <button  onclick="ShowBill();" class="glow-on-hover" id="BtnShowBill"
                        style="float:right; margin-top:10px;  " >Invoice</button>
            
            <button  onclick="LedgerUpdate();" class="glow-on-hover" id="BtnLedger" style="margin-bottom:10px; margin-right:10px;
                        float:left; margin-top:10px;" >Update Ledger</button>

        <?PHP } ?>   

        




        
         
         <!--<button  onclick="ShowSummary();" class="flat_button" id="BtnLedger" style="display: block; margin-bottom:10px; margin-right:10px;-->
         <!--height:33px; float:left; margin-top:10px; color: white; background-color:blue; border:1px solid black;" >SUMMARY</button>-->
         
        <!--<button  onclick="tableToExcel('export','Packing_List');" class="flat_button" id="BtnLedger" style="margin-right: 200px; display: block; margin-bottom:10px;-->
        <!-- height:33px; float:left; margin-top:10px; color: blue;" >Export</button>-->
         
        <!--<input class="flat_button" type="button" onclick="tableToExcel('export', 'EXPORT')" value="Export" >-->
         
         <div id="InfoBox" style="min-width:40px; height: 20px; border:0px solid red; float: left;"></div>
    </div>




    <div style="float:right; display:none;">
        <input style="" type="text" id="CLINTNN" value="<?php echo htmlspecialchars($FClient) ?>">
        <input id="LastRow" type="text" name="" value="PID<?php echo $sn; ?>" >
        <input type="text" id="Tweight" value="<?php echo $Tweight ?>">
        <input type="text" id="SubTotal" value="<?php echo $SubTotal ?>">

    </div>



</div>