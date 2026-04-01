<?php
include ("./sessionchk2.php");
include ("./refchk.php");
?>

<script>

function fetchSuggestions(inputValue) {

    document.getElementById("suggestionList").innerHTML = "Please Wait...";

    document.getElementById("suggestionList").innerHTML = "... ... ...";

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
        xmlhttp.open("GET", "GetShipmentName.php?q=" + inputValue, true);
        xmlhttp.send();
    }
    return;
}

function selectClient(Shipment) {
    document.getElementById("TxTShipment").value = Shipment;
    document.getElementById("suggestionList").innerHTML = "";
    PullExp();
    document.getElementById('TxTType').focus();
    return;
}

function PullExp(){

var SBox                = document.getElementById('TxTShipment');

var TxTShipment         = SBox.value; 

document.getElementById("suggestionList").innerHTML = "Loading...";

var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("suggestionList").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "ExpDataPull.php?s=" + TxTShipment, true);
        xmlhttp.send();



}


function SubmitExp(){

// alert("Shipment Name is Blank.");
// exit();
//>>NewEntry>>

var TxTShipment       = document.getElementById('TxTShipment').value;            
var TxTType           = document.getElementById('TxTType').value;
var TxTAmount         = document.getElementById('TxTAmount').value;


var SBox          = document.getElementById('TxTShipment');
var TBox          = document.getElementById('TxTType');
var ABox          = document.getElementById('TxTAmount');
var MONITOR       = document.getElementById('suggestionList');



if (TxTShipment === "") {
    alert("Shipment Name is Blank.");
    document.getElementById('TxTShipment').focus();
    return;
}

if(TxTType == ""){
    alert("Expense Type is Blank.");
    document.getElementById('TxTType').focus();
    return;
}
else if(TxTAmount == 0){
    alert("Amount is zero.");
    document.getElementById('TxTAmount').focus();
    return;
}
else {

const response = confirm("Confirm New Entry : \nSHIPMENT : "+TxTShipment+"\nType : "+TxTType+"  | Amount : "+TxTAmount);

if (response) {

document.getElementById("suggestionList").innerHTML = "Please Wait...";

    var link       = "./ExpenseSubmit.php";
    
    var arr = {};
        arr["TxTShipment"]      = TxTShipment;
        arr["TxTType"]          = TxTType;
        arr["TxTAmount"]        = TxTAmount;
    
    
      $.ajax({ url:link,type:"POST", data: arr, cache: false, 
                success:function(data){ 
                    
                    // alert(data);
                    
                    SBox.value = "";
                    TBox.value = "";
                    ABox.value = "0";

                    SBox.focus();
                    MONITOR.innerHTML = "Entry Complete.";

                    SBox.value = TxTShipment;
                    TBox.focus();
                    PullExp();
                } 
            });
    
      return;
}
}
}

</script>

<?php 
    include ("./conn.php");
?>

<link rel="stylesheet" type="text/css" href="stylev2.css">
<div style="padding-left: 20px; margin-top: -40px; margin-bottom: 30px; width: 300px; text-align: left; font-size: 18px; color: #990000; font-weight: bold;">
    New Expense Entry Here
</div>

<div style="width: 99%; min-height: 350px; border: 0px solid red; margin: 0 auto;">
    <div class="BlueBox">

        <div class="GrayBox" style="">

            <table border="0" width="280px">
                <tr style="text-align: right;">
                    <td style="width: 150px;">Shipment :</td>
                    <td style="padding: 5px;">
                        <input 
                            onkeyup="fetchSuggestions(this.value)" 
                            style="width: 160px; text-align: left;" 
                            placeholder="Shipment" 
                            type="text" 
                            id="TxTShipment" 
                            value="" 
                        />
                    </td>
                </tr>
                <tr style="text-align: right;">
                    <td>Expense Type :</td>
                    <td>
                        <input 
                            style="width: 160px; text-align: right;" 
                            type="text" 
                            id="TxTType" 
                        />
                    </td>
                </tr>
                <tr style="text-align: right;">
                    <td>Amount :</td>
                    <td>
                        <input 
                            style="width: 160px; text-align: right;" 
                            type="text" 
                            id="TxTAmount" 
                            value="0" 
                            onkeypress="return (event.charCode != 8 && event.charCode == 0 || (event.charCode >= 48 && event.charCode <= 57))" 
                            onkeydown="if (event.keyCode === 13) { SubmitExp(); }" 
                        />
                    </td>
                    
                </tr>
                <tr style="text-align: right;">
                    
                    <td colspan="2" >

                        <button 
                        
                            class="button-5" 
                            onclick="ShowSummary();" 
                            title="Shipment Summary" 
                            style="cursor: pointer;">
                            Summary
                        </button>
                        
                        <button 
                            class="button-5" 
                            onclick="SubmitExp();" 
                            title="Save" 
                            style="cursor: pointer; margin-left:5px;">
                            Save
                        </button>
                    </td> 

                </tr>
            </table>
        </div>
        <div class="GrayBox" style="font-weight: bold;padding-top: 0px; ">
            Recent <br>
            <table border="0" style="margin-top: 10px ;">
                <tr>
                    <?php
                    // Define the function if not already defined
                    function highlightMatchedKeyword($inputValue, $clientName) {
                        if (empty($inputValue)) {
                            return htmlspecialchars($clientName); // Safely escape name
                        }
                        return preg_replace("/($inputValue)/i", "<span style='color: green;'>$1</span>", htmlspecialchars($clientName));
                    }

                    // SQL query
                    $sql = "SELECT `SHIPMENT` FROM `ShipmentLog` ORDER BY `SN` DESC LIMIT 12";

                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->get_result(); // if no mysqlnd, bind_result/fetch instead
                    $stmt->close();

                    $n = 0;

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $n++;
                            $clientName = $row['SHIPMENT'];
                            $clientNameUpper = strtoupper($clientName);
                            $highlightedName = highlightMatchedKeyword($inputValue ?? '', $clientName);
                    ?>
                            <td style="padding-left:20px;">
                                <a 
                                    class="suggestion-link" 
                                    href="javascript:void(0);" 
                                    onclick="selectClient('<?php echo $clientNameUpper; ?>')">
                                    <?php echo $highlightedName; ?>
                                </a>
                            </td>
                    <?php
                            if ($n % 2 == 0) {
                                echo "</tr><tr>";
                            }
                        }
                    } else {
                        echo "<td>No records found.</td>";
                    }
                    ?>
                </tr>
            </table>
        </div>
    </div>
<style type="text/css">

    .GrayBox{
        margin: 0px auto; 
        padding-top: 0px; 
        border: 0px solid gray; 
        border-radius: 5px;
    }

    .BlueBox{
        width: 300px;
        float: left; 
        min-height: 100px; 
        padding-top: 15px;
        margin-left: 20px;
    }
    
    .BlackBox{

        min-width: 350px; 
        height: 300px;
        float: left;
        overflow-y: auto; 
        overflow-x: hidden;
        border-left: 1px solid silver;
        border-top: 1px solid silver;
        border-bottom: 1px solid silver;
        padding:20px;
        padding-top: 3px;


        border-radius: 8px; /* Optional: smooth corners */
    }
</style>

    
        <div id="suggestionList" class="BlackBox" style="margin-left: 30px;">

            <!-- <div style="width: 100px; height: auto; border: 0px solid seagreen; margin:0 auto ;"> -->
                <!-- Hello World! -->
            <!-- </div> -->
            

        </div>
    
</div>

<style>
  .suggestion-link {
    color: blue;
    text-decoration: none;

  }
  
  .suggestion-link:hover {
    color: red;
  }
</style>



<script type="text/javascript">

function FocusTxt() {
    document.getElementById('TxTShipment').focus();
    }
    FocusTxt();
 function ShowSummary() {
    var ShipID = document.getElementById('TxTShipment').value;
    var link = "./ShipmentSum.php";

    $.ajax({
        url: link,
        type: "POST",
        data: { ShipID: ShipID },
        cache: false,
        success: function (data) {
            // Open a new popup window
            var popupWindow = window.open("", "PopupWindow", "width=600,height=500,scrollbars=yes,resizable=yes");

            if (popupWindow) {
                // Write content inside the new window
                popupWindow.document.write(`
                    <html>
                    <head>
                        <title>Shipment Summary</title>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                padding: 20px;
                                background-color: #f9f9f9;
                                text-align: center;
                            }
                            .content {
                                background: #fff;
                                padding: 15px;
                                border-radius: 8px;
                                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
                                text-align: left;
                            }
                            .close-btn {
                                display: block;
                                width: 100px;
                                margin: 20px auto;
                                padding: 10px;
                                background: red;
                                color: white;
                                border: none;
                                font-size: 16px;
                                cursor: pointer;
                                border-radius: 5px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="content">
                            ${data}
                        </div>
                        <button class="close-btn" onclick="window.close();">Close</button>
                    </body>
                    </html>
                `);

                popupWindow.document.close(); // Ensure the document is fully written
            } else {
                alert("Popup blocked! Please allow popups for this site.");
            }
        },
        error: function (xhr, status, error) {
            alert('Error: ' + error);
        }
    });
}



</script>


<style type="text/css">

/* CSS */

.button-5 {
  
  min-width: 30px;
  height: 25px;
  align-items: center;
  background-color: #FFFFFF;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: .25rem;
  box-shadow: rgba(0, 0, 0, 0.02) 0 1px 3px 0;
  box-sizing: border-box;
  color: rgba(0, 0, 0, 0.85);
  cursor: pointer;
  display: inline-flex;
  font-family: system-ui,-apple-system,system-ui,"Helvetica Neue",Helvetica,Arial,sans-serif;


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

.button-5:hover,
.button-5:focus {
  border-color: rgba(0, 0, 0, 0.15);
  box-shadow: rgba(0, 0, 0, 0.1) 0 4px 12px;
  color: rgba(0, 0, 0, 0.65);
}

.button-5:hover {
  transform: translateY(-1px);
}

.button-5:active {
  background-color: #F0F0F1;
  border-color: rgba(0, 0, 0, 0.15);
  box-shadow: rgba(0, 0, 0, 0.06) 0 2px 4px;
  color: rgba(0, 0, 0, 0.65);
  transform: translateY(0);
}


</style>