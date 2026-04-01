    

<!--ExpLedgerTable-->

<?php

$DelExp = "";
$DelRec = "";
$Received = []; 
$Expense = [];

    // include ("./sessionchk2.php"); 

    include ("./refchk.php");

    

    include ("./conn.php");

    

    date_default_timezone_set('Asia/Dhaka');

    

    if (isset($_POST['XDATE1']) && isset($_POST['XDATE2'])) {

        $date = $_POST['XDATE1'];

        $EDate = $_POST['XDATE2'];

        $TYPE = $_POST['TYPE'];

    } else {

        $date = date("Y-m-d");

        $EDate = date("Y-m-d");

        $TYPE = "D";

    }



$CurDate = date("Y-m-d");



$Today = "N";



if($CurDate == $date && $TYPE == "D"){

    $Today = "Y";

}



    $sql = "SELECT `sn`, `date`, `type`, `name`, `pay_method`, `amount`, `CashIH`, `remarks`, `user` , `BANK` 

            FROM `Daily3xp` 

            WHERE `date` between '$date' and '$EDate'

            ORDER BY `sn` ASC";

            

    $sqlY = "SELECT `sn`, `CashIH` from `Daily3xp` WHERE `date` < '$date' ORDER BY `sn` DESC LIMIT 1;";



    $resultY = $conn->query($sqlY);



	$rowY = $resultY->fetch_assoc();

	

	// $CashIH = ($rowY['CashIH']);
    $CashIH = isset($rowY['CashIH']) ? $rowY['CashIH'] : null;



$Showdate = strtotime($date);

$ShowD1 = date("d M Y | D", $Showdate);



$ShowEdate = strtotime($EDate);

$ShowD2 = date("d M Y | D", $ShowEdate);



$TotalRec = 0;

$TotalExp = 0;



$RecAmB = 0;

$RecAmC = 0;

$ExpAmB = 0;

$ExpAmC = 0;



    $result = $conn->query($sql);

        

    if ($result->num_rows > 0) {

        

        while($row = $result->fetch_assoc()) {

            

            $TYPEx      = $row["type"];

            $TMPamount  = $row["amount"];

            

            $BANK  = $row["BANK"];



            if($TYPEx == "Rec"){

                $Received[] = $row;

                

                // echo "</br>".$TMPamount;

                // echo "</br>Rec ".$TMPamount."|".$CashIH += $TMPamount;

                $CashIH += $TMPamount;

                

                if($BANK == 1){

                    $RecAmB += $TMPamount;

                }

                else{

                    $RecAmC += $TMPamount;

                }

            }

            

            if($TYPEx == "Exp"){

                $Expense[] = $row;

                

                // echo "</br>".$TMPamount;

                // echo "</br>Exp ".$TMPamount."|".$CashIH -= $TMPamount;

                $CashIH -= $TMPamount;

                

                if($BANK == 1){

                    $ExpAmB += $TMPamount;

                }

                else{

                    $ExpAmC += $TMPamount;

                }

            }

        }

    }

    else{

        // echo "No Records.";

        // goto P;

        // exit();

    }


$BCHK = 0;

if($ExpAmB == $ExpAmC){

}else{
    $BCHK = 1;
}




?>

<div style="width:100%; min-height:200px; border:0px solid blue; padding-bottom:50px;">

    

    <div style="width:49%; height:40px; border:0px solid red; float:left;">

        <span style="color:blue;"><?php echo $ShowD1; ?></span>

        

        <?php if($TYPE!="D"){ ?>

          &#8608 <span style="color:green;"><?php echo $ShowD2; ?></span>

        

        <?php } ?>

    </div>

    

    <div style="width:50%; height:40px; border:0px solid red; float:Right; text-align:right;">

        <span style="color:#004581; font-size:22; margin-right:30px; font-family: Calibri; font-weight: bold;"><?php echo "&#2547; ".moneyFormat($CashIH); ?></span>

    </div>





<div style="width:100%; min-height:100px; border:0px solid red;">

<!-- <table class="default-table" width="100%" style="border-bottom:0px solid black; background-color: #fff8e5;"> -->
<table class="default-table" width="100%" style="border-bottom: 1px dotted; black; background-color: #fff8e5; border-radius: 6px; overflow: hidden;">

            <tr style="height:2px;">

                <td class="BWhite"></td>

                <td id="BoxRec" class="BNActive" colspan=3></td>

                

                <td class=""><div style="height:1px; background-color: #004d71"></div></td>

                

                <td id="BoxExp" class="BActive" colspan=2></td>

                <td class="BWhite"></td>

                

            </tr>

            <tr>

                <th class="BWhite"></th>

                <?php if($TYPE != "D"){ ?>

                    <th style="background-color: #004d71; width:50px;"></th>

                <?php } ?>

                <th colspan=3 width="49.99%" style="background-color: #004d71; color:white;

                    cursor: pointer;" onclick="GetForm('Rec');">Received</th>

                    

                    

                <th style="width: 1px; background-color: #004d71" class=""></th>

                

                <?php if($TYPE != "D"){ ?>

                    <th style="background-color: #00bac6;"></th>

                <?php } ?>

                <th class="CYellow" colspan=2 width="49.99%" style="background-color: #00bac6;  cursor: pointer;" onclick="GetForm('Exp');">Expense</th>

                

                <th class="BWhite"></th>

                    

            </tr>

            <!--class=""-->

            <tr style="text-align:left; cursor: pointer;">

                <td class="BWhite"></td>

                <?php if($TYPE != "D"){ ?>

                    <th style="padding-left:10px;" class="Thead2 BBottomB">Date</th>

                <?php } ?>

                <th onclick="GetForm('Rec');" style="padding-left:10px;" class="Thead2 BBottomB">Client Name</th>

                <th onclick="GetForm('Rec');" class="Thead2 BBottomB">Payment Method</th>

                <th onclick="GetForm('Rec');" style="text-align:right; padding-right:10px;" class="Thead2 BBottomB">Amount</th>

                <th class="BBlack "></th>

                <?php if($TYPE != "D"){ ?>

                    <th style="padding-left:10px;" class="Thead1 BBottomB">Date</th>

                <?php } ?>

                <th  onclick="GetForm('Exp');" style="padding-left:10px;" class="Thead1 BBottomB">Item Name</th>

                <th  onclick="GetForm('Exp');" style="text-align:right; padding-right:10px;" class="Thead1 BBottomB">Amount</th>

                <td class="BWhite"></td>

            </tr>

            

            <?php

            

            if (isset($Received)){

                $CountRec   = count($Received);

            }else{$CountRec   = 0;}

            

            if (isset($Expense)){

                $CountExp   = count($Expense);

            }else{$CountExp   = 0;}

            

            $MaxV       = max($CountRec, $CountExp)+1;


            

            if($MaxV <8) {$MaxV = 8;}

            

            // print_r($Received);

            

                for ($i = 1; $i < $MaxV; $i++) {

                    $RecRemLen = 0;
                    $ExpRemLen = 0;

                    if (isset($Received[$i - 1])) {
                        $TotalRec += $Received[$i - 1]['amount'];
                        $RecName = $Received[$i - 1]['name'];
                        $RecPayMeth = $Received[$i - 1]['pay_method'];
                        $RecAmount = $Received[$i - 1]['amount'];
                        $Recsn = $Received[$i - 1]['sn'];
                        $RecRem = $Received[$i - 1]['remarks'];
                        $RecRemLen = strlen($RecRem);
                        $RecBNK         = $Received[$i-1]['BANK'];
                        $RecRemLen      = strlen($RecRem);
                    }
                    else{
                        $RecName = "";
                        $RecPayMeth = "";
                        $Recsn = 0;
                    }
                
                    if (isset($Expense[$i - 1])) {
                        $TotalExp += $Expense[$i - 1]['amount'];
                        $ExpName = $Expense[$i - 1]['name'];
                        $ExpAmount = $Expense[$i - 1]['amount'];
                        $Expsn = $Expense[$i - 1]['sn'];
                        $ExpRem = $Expense[$i - 1]['remarks'];
                        $ExpRemLen = strlen($ExpRem);
                        $ExpBNK         = $Expense[$i-1]['BANK'];
                        $ExpRemLen      = strlen($ExpRem);
                    }
                    else{
                        $ExpName = "";
                        $Expsn = 0;
                        
                    }

                    if($RecAmount =0){$RecRem = "";}

                    

                    

                    

                    

                    

                    

                    // echo $RecName;

                    

                    if($RecName != "" ){

                    

                        if($RecBNK == 1){

                            $RecName = "&#127974;&nbsp;&nbsp;".$RecName;

                        }

                        else{

                            

                            $RecName = "&#128181;&nbsp;&nbsp;".$RecName;

                            

                        }

                        // echo $RecName;

                    }

                    

                    if($ExpName != ""){

                        

                        if($ExpBNK == 1){

                            $ExpName = "&#127974;&nbsp;&nbsp;".$ExpName;

                        }

                        else{

                            $ExpName = "&#128181;&nbsp;&nbsp;".$ExpName;

                        }

                    }

                    

                    if($RecRemLen>0){

                        $RecName = $RecName."&nbsp;&nbsp;&#128488;";

                    }

                    

                    if($ExpRemLen>0){

                        $ExpName = $ExpName."&nbsp;&nbsp;&#128488;";

                    }

                    

                    

                    

                    // if($RecAmount>0){

                    //     $Tdate1 = strtotime($Received[$i-1]['date']);  //<<line 498

                    //     $Sdate1 = date("d M", $Tdate1);

                        

                    // }else {

                    //     $Sdate1 = ""; 

                    //     $DelRec = "N";

                        

                    // }
                    if (isset($Received[$i - 1])) {
                        $RecAmount = $Received[$i - 1]['amount'];
                        if ($RecAmount > 0) {
                            $Tdate1 = strtotime($Received[$i - 1]['date']);
                            $Sdate1 = date("d M", $Tdate1);
                        } else {
                            $Sdate1 = ""; 
                            $DelRec = "N";
                        }
                    } else {
                        // Handle the case where the index doesn't exist
                        // For example, set default values or log a message
                        $RecAmount = 0;
                        $Sdate1 = ""; 
                        $DelRec = "N";
                    }
                    

                    // if($ExpAmount>0){

                    //     $Tdate2 = strtotime($Expense[$i-1]['date']);

                    //     $Sdate2 = date("d M", $Tdate2);

                    // }else {

                    //     $Sdate2 = ""; 

                    //     $DelExp = "N";

                    // }
                    if (isset($Expense[$i - 1])) {
                        $ExpAmount = $Expense[$i - 1]['amount'];
                        if ($ExpAmount > 0) {
                            $Tdate2 = strtotime($Expense[$i - 1]['date']);
                            $Sdate2 = date("d M", $Tdate2);
                        } else {
                            $Sdate2 = ""; 
                            $DelExp = "N";
                        }
                    } else {
                        // Handle the case where the index doesn't exist
                        // For example, set default values or log a message
                        $ExpAmount = 0;
                        $Sdate2 = ""; 
                        $DelExp = "N";
                    }




                // Define $RecRem and $ExpRem variables before using them
$RecRem = isset($RecRem) ? $RecRem : "";
$ExpRem = isset($ExpRem) ? $ExpRem : "";

                ?>



<tr>
    <td class="BWhite">
        <?php if ($DelRec != "N" && $Today == "Y" && $TYPE == "D"): ?>
            <div style="height:15px; width:15px; cursor:pointer; font-size: 12px;" title="Delete" class="exclude"
                onclick="ExpDelete(<?php echo $Recsn; ?>,'R')"
                onmouseover="showButton('BTNdelRec<?php echo $i; ?>')" 
                onmouseout="hideButton('BTNdelRec<?php echo $i; ?>')">
                <div style="display:none; vertical-align: middle; text-align:center;" id="BTNdelRec<?php echo $i; ?>">
                    &#10060;
                </div>
            </div>
        <?php endif; ?>
    </td>
    <?php if ($TYPE != "D"): ?>
        <td class="BBottom" title="<?php echo $RecRem; ?>" style="padding-left:3px; padding-left:10px;">
            <span id="DT<?php echo $Recsn; ?>"><?php echo $Sdate1; ?></span>
        </td>
    <?php endif; 
    




?>


    <td style="padding-left:10px;" id="NM<?php echo $Recsn; ?>" class="BBottom" title="<?php echo $RecRem; ?>">
        <?php echo $RecName; ?>
    </td>
    
    <td id="PM<?php echo $Recsn; ?>" class="BBottom" title="<?php echo $RecRem; ?>">
        <span><?php echo $RecPayMeth; ?></span>
    </td>
    
    <td id="AM<?php echo $Recsn; ?>" class="BBottom" title="<?php echo $RecRem; ?>" style="text-align:right; padding-right:10px;">
        <?php if ($RecAmount != 0): ?>
            <span><?php echo moneyFormat($RecAmount); ?></span>
        <?php endif; ?>
    </td>
    
    <td class="BLeftB BRightB">|</td>
    
    <?php if ($TYPE != "D"): ?>
        <td id="DT<?php echo $Expsn; ?>" class="BBottom" title="<?php echo $ExpRem; ?>" style="padding-left:10px;">
            <span><?php echo $Sdate2; ?></span>
        </td>
    <?php endif; ?>
    
    <td style="padding-left:10px;" id="NM<?php echo $Expsn; ?>" class="BBottom" title="<?php echo $ExpRem; ?>">
        <?php echo $ExpName; ?>
    </td>
    
    <td id="AM<?php echo $Expsn; ?>" class="BBottom" title="<?php echo $ExpRem; ?>" style="text-align:right; padding-right:10px;">
        <?php if ($ExpAmount != 0): ?>
            <span><?php echo moneyFormat($ExpAmount); ?></span>
        <?php endif; ?>
    </td>
    
    <td class="BWhite">
        <?php if ($DelExp != "N" && $Today == "Y" && $TYPE == "D"): ?>
            <div style="height:15px; width:15px; cursor:pointer; font-size: 12px;" title="Delete" class="exclude"
                onclick="ExpDelete(<?php echo $Expsn; ?>,'E')"
                onmouseover="showButton('BTNdelExp<?php echo $i; ?>')" 
                onmouseout="hideButton('BTNdelExp<?php echo $i; ?>')">
                <div style="display:none; vertical-align: middle; text-align:center;" id="BTNdelExp<?php echo $i; ?>">
                    &#10060;
                </div>
            </div>
        <?php endif; ?>
    </td>
</tr>


               <?php     

                    

                }

            

            

            

            ?>

            

            <tr style="text-align:right; font-weight: bold;" >

                <td class="BWhite"></td>

                <?php if($TYPE != "D"){ ?>

                <td></td>

                <?php } ?>

                <!--<td class="BBottomB BTopB" colspan=1 style=""></td>-->

                

                <td class="BBottomB BTopB" colspan=3 style="text-align:right; padding-right:10px; ">

                    Total Received :&nbsp; &nbsp; &nbsp; <?php echo "&#2547; ".moneyFormat($TotalRec)."&nbsp;( &#127974; ".moneyFormat($RecAmB)." | &#128181; ".moneyFormat($RecAmC)." )"; ?>

                </td>

                

                <!--bank &#127974-->

                <!--cash &#128181-->

                            

                <td class="BBottomB BLeftB BRightB BTopB" style="">|</td>

                <?php if($TYPE != "D"){ ?>

                    <td></td>

                <?php } ?>

                

                

                <td class="BBottomB BTopB" colspan=2 style="text-align:right; padding-right:10px; ">

                    Total Expense :&nbsp; &nbsp; &nbsp; <?php echo "&#2547; ".moneyFormat($TotalExp)." ( &#127974; ".moneyFormat($ExpAmB)." | &#128181; ".moneyFormat($ExpAmC)." )"; ?>

                </td>

                

                <td class="BWhite"></td>

            </tr>

            

            <tr>

                <td class="BWhite"></td>

                <td colspan=8 style="background-color:#004d71"></td>

                <td class="BWhite"></td>

            </tr>



            

            

            

            

        </table>



    </div> 



<div>
    <div class="StanTxt" style="margin-top: 20px; font-size:12;">| Use arrow (&#8592;&#x2192;) to change date | Press 'R' for Reset |
                 Press 'D' for Daily. | Press 'W' for Weekly. | Press 'M' for Monthly |</div>
</div>


<script>

    function GetForm(TYPE){

        

        var BoxRec = document.getElementById('BoxRec');

        var BoxExp = document.getElementById('BoxExp');

        

        if(TYPE == 'Rec'){

            

            BoxRec.classList.remove('BNActive');

            BoxRec.classList.add('BActive');

            

            BoxExp.classList.remove('BActive');

            BoxExp.classList.add('BNActive');

            

        }

        else{

            BoxRec.classList.remove('BActive');

            BoxRec.classList.add('BNActive');

            

            BoxExp.classList.remove('BNActive');

            BoxExp.classList.add('BActive');

        }

        

        

        

        

        

        var tframe = "#ExpForm";

        $(tframe).html("");

        

        var link       = "./ExpNewForm.php";

        

        var arr = {};

        arr["TYPE"]  = TYPE;

                        

          $.ajax({ url:link,type:"POST", data: arr, cache: false, 

                    success:function(data){                              

                        $(tframe).html(data);

                    } 

                });

    }

</script>

<script>
  function fetchSuggestions(inputValue) {
    if (inputValue.length < 2) {
      document.getElementById("suggestionList").innerHTML = "";
      return;
    } else {
      // Make an AJAX request to the PHP script to fetch suggestions
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
          document.getElementById("suggestionList").innerHTML = this.responseText;
        }
      };
      xmlhttp.open("GET", "get_suggestions.php?q=" + inputValue, true);
      xmlhttp.send();
    }
  }

  function selectClient(clientName) {
    document.getElementById("TxTName").value = clientName;
    document.getElementById("suggestionList").innerHTML = "";
  }

</script>



    <div style="width:100%; height:50px; border:0px solid green; margin-top:20px;">

        

        <?php 

            

            if($Today == "Y" && $TYPE == "D"){ ?>

        

        <div id="ExpForm">

            <div style="float:right; ">

                <table width:100%; style="">

                    <tr>

                    

                    <td style="padding-left:5px;"><input style="min-width: 140px; text-align: left; float: left;"

                                                          placeholder="Item Name"

                                                          type="text"

                                                          id="TxTName"

                                                          value=""

                                                        /></td>

                    

                                

                    <td style="padding-left:10px;">

                        <input style="width: 140px; text-align:right; float: right;" type="text" id="TxTAmount" value="" Placeholder="Amount"

                            onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"       >

                    </td style="padding-left:20px;">

                    

                    <td style="padding-left:10px;">

                        <textarea id="TxtRem"  rows="2" cols="20" style="resize: none;" onkeypress="" placeholder="Remarks"></textarea>

                    </td>

                    

                     <td style="text-align:center;" colspan=5 >

                       

                        <button id="BtnSave" class="glow-on-hover" onclick="SaveExpense('Exp');" style="min-width:110px;">Add Expense</button>

                    </td>

                    

                    </tr>

            

                </table>

            

            </div>

            

            

            

        </div>





<script type="text/javascript">

    function SaveExpense(TYPE){

        

            var TxTName        = document.getElementById('TxTName').value;            

            var TxTAmount       = document.getElementById('TxTAmount').value;

            var TxtRem            = document.getElementById('TxtRem').value;

            

            if(TxTName == ""){

            alert("Name is blank");

            return;

            }

            

            if(TxTAmount == "" || TxTAmount == 0){

            alert("Amount is zero");

            return;

            }

// debugger;
var RTYPE = "";

            if(TYPE == "Rec"){

                //its receive

                var TxTMethod        = document.getElementById('TxTMethod').value;                

                var checkbox = document.getElementById("myonoffswitch");

                if (checkbox.checked) {
                    
                    //checkbox checked true. so this is bank
                    RTYPE        = 'BANK';

                    if(TxTMethod == ''){
                        alert("Enter Bank Name.");
                        return 0;
                    }

                    TxTMethod    += " &#8594 Bank";
                    

                  

                } else {
                    
                    //checkbox checked false; so its cash.

                    RTYPE        = 'CASH';
                    TxTMethod = 'CASH';

                }


            }

            else{
                //its expense
                var TxTMethod        = "";

            }

    

            var link       = "./ExpenseSave.php";

            var tframe = "#BtnSave";

            

            

            

            $(tframe).html("Saving...");

            

            var arr = {};

                        arr["TxTName"]      = TxTName;

                        arr["TxTAmount"]    = TxTAmount;  

                        arr["TxTMethod"]    = TxTMethod;

                        arr["TxtRem"]       = TxtRem;

                        arr["TYPE"]         = TYPE;

                        arr["RTYPE"]        = RTYPE;



                          $.ajax({ url:link,type:"POST", data: arr, cache: false, 

                                    success:function(data){

                                        // alert("New Entry Complete.")

                                        GetPage('ExpenseLedger');

                                        // GetForm('Rec');

                                        // $(tframe).html(data);

                                    } 

                                });

    

    

    

    

    } 

   

   function RemarksSize(){

    var RMVAL       = document.getElementById('TxtArea').value;

    var TxtLen      = RMVAL.length;

    var RemLen      = 39-TxtLen;

    

    var tframe = "#RMLimit";

    

    $(tframe).html(RemLen);

    

    

    

}         

</script>

            <?php } 

            ?>

            



            

            

            

        

    </div>



<div id="ExpInfo" style="height:30px; width:400px; margin:0 auto; border:0px solid red; text-align:center;"></div>

 

</div>




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

 ?>




        

<style>

.default-table {

  border-collapse: collapse;

  font-family: Calibri;

  font-size: 13px;

}



.default-table tbody tr:hover {

  background-color: #c0d5ff;

}

  

.custom-table {

  border: 1px solid black;

}

  

  .DDisplay{

      display="none";

  }

  

 .Thead1{

      background-color:#004d71; 

      color:white;

  }

  

.Thead2{

      background-color:#00bac6; 

      color:black;

  }



.BBottom{

  border-bottom: 1px solid #d8d8d8;

}



.BBottomB{

  border-bottom: 1px solid black;

}



.BRight{

  border-right: 1px solid #d8d8d8;

}



.BRightB{

  border-right: 1px solid black;

}



.BLeft{

    border-left: 1px solid #d8d8d8;

}



.BLeftB{

    border-left: 1px solid black;

}



.BTop{

    border-top: 1px solid #d8d8d8;

}



/*.BTopB{*/

/*    border-top: 1px solid black;*/

/*}*/



.BAll{

    border:1px solid black;

}



.BWhite{

    background-color: white; 

}



.BBlack{

    background-color: #004d71; 

}



.CYellow{

    color:black;

}



.BActive{

    background-color: #1CFF0B; 

}



.BNActive{

    background-color: white; 

}

</style>



<script>

function showButton(TARGET) {

  document.getElementById(TARGET).style.display = "inline";

  

//   console.log(TARGET);

}



function hideButton(TARGET) {

  document.getElementById(TARGET).style.display = "none";

}



function ExpDelete(TARGET, TYPE){

    // alert(Target);

    

    var D = 'DT'+TARGET;

    var N = 'NM'+TARGET;

    var A = 'AM'+TARGET;

    var P = 'PM'+TARGET;

    

            // var DT = document.getElementById(D).innerText;

            var NM = document.getElementById(N).innerText;

            var AM = document.getElementById(A).innerText;

    

    if(TYPE == 'R'){

            var PM = document.getElementById(P).innerText;

    }else{

            var PM = "";

    }

    

    const response = confirm("Confirm Delete : \nName : "+NM+"\nAmount : "+AM);



    if (response) {

        var link       = "./ExpDel.php";

            var tframe = "#ExpInfo";

            

            $(tframe).html("Removing Entry...Please Wait.");

            

            var arr = {};

                        arr["TARGET"]       = TARGET;

                        arr["AM"]           = AM;

                        arr["TYPE"]         = TYPE;



                          $.ajax({ url:link,type:"POST", data: arr, cache: false, 

                                    success:function(data){ 

                                      GetPage('ExpenseLedger');

                                    // $(tframe).html(data);

                                    } 

                                });

        

    }

    else{

        return;

    }







    

    

}

</script>







