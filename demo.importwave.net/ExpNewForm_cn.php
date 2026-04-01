<?php
include ("./refchk.php");

$TYPE = $_POST['TYPE'];

if($TYPE=="Rec"){

?>

<script>
    function PMColor(){
        var a = document.getElementById("RTYPE").value;
        
        var e = document.getElementById("RTYPE");
        
        // console.log(a);
        
        if(a == 'BANK'){
            e.style.backgroundColor = "yellow";
            
        }
        if(a == 'CASH'){
            e.style.backgroundColor = "#13fb03";
            
        }
    }
    
</script>


<div style="float:left; margin-left:20px;">
    <table width:100%; style="">
        <tr>           
            
            
<td style="width:60px; margin-left:10px;">         
    <div class="onoffswitch">
        <input id="myonoffswitch" type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="myonoffswitch" tabindex="0">
        <label class="onoffswitch-label" for="myonoffswitch">
            <span class="onoffswitch-inner"></span>
            <span class="onoffswitch-switch"></span>
        </label>
    </div>            
            
            
            
            
            
            
            
            <!--------------------------------------------------------------------------------------->
            <td style="padding-left:5px;"><input
                                                  onkeyup="fetchSuggestions(this.value)"
                                                  style="min-width: 140px; text-align: left; float: left;"
                                                  placeholder="Client Name"
                                                  type="text"
                                                  id="TxTName"
                                                  value=""
                                                /></td>
            
			
			<td style="padding-left:10px;">
                <input style="width: 140px; text-align:left; float: right;" type="text" id="TxTMethod" Placeholder="Payment Method" value="">
            </td>
                        
            <td style="padding-left:10px;">
                <input style="width: 140px; text-align:right; float: right;" type="text" id="TxTAmount" value="" Placeholder="Amount"
                    onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"       >
            </td style="padding-left:20px;">
            
            <td style="padding-left:10px;">
                <textarea id="TxtRem"  rows="2" cols="20" style="resize: none;" onkeypress="" placeholder="Remarks"></textarea>
            </td>
            
             <td style="padding-left:10px; text-align:right;" colspan=5 >
                <!-- <div id="BtnSave" onclick="SaveExpense('Rec');" class="glow-on-hover" style="float:right; margin-left:20px; border-radius: 3px;">Add Receive</div> -->
                <button id="BtnSave" class="glow-on-hover" onclick="SaveExpense('Rec');" style="min-width:110px;">Add Receive</button>
            </td>
            
        </tr>

        <tr>
            <td colspan=5>
                <div id="suggestionList" style="width:600px; min-height:50px; border:0px solid gray; float:left; margin-left:80px;"></div>
            </td>
            
            
        </tr>
    </table>
    
</div>

                

<?php

}
else{ ?>
    
<div style="float:right; ">
    <table width:100%; style="">
        <tr>
            
            <td style="padding-left:5px;"><input
                                                  
                                                  style="min-width: 140px; text-align: left; float: left;"
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
            
             <td style="padding-left:10px; text-align:right;" colspan=5 >
                <!-- <div id="BtnSave" onclick="SaveExpense('Exp');" class="glow-on-hover" style="float:right; margin-left:20px; border-radius: 3px;">Add Expensexx</div> -->
                <button id="BtnSave" class="glow-on-hover" onclick="SaveExpense('Exp');" style="min-width:110px;">Add Expense</button>
            </td>
            
        </tr>

    </table>
    
</div>

<?php }

?>

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


<style>

.onoffswitch {
    position: relative; width: 90px;
    -webkit-user-select:none; -moz-user-select:none; -ms-user-select: none;
}
.onoffswitch-checkbox {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.onoffswitch-label {
    display: block; overflow: hidden; cursor: pointer;
    border: 2px solid #999999; border-radius: 20px;
}
.onoffswitch-inner {
    display: block; width: 200%; margin-left: -100%;
    transition: margin 0.3s ease-in 0s;
}
.onoffswitch-inner:before, .onoffswitch-inner:after {
    display: block; float: left; width: 50%; height: 30px; padding: 0; line-height: 30px;
    font-size: 14px; color: white; font-family: Trebuchet, Arial, sans-serif; font-weight: bold;
    box-sizing: border-box;
}
.onoffswitch-inner:before {
    content: "BANK";
    padding-left: 10px;
    background-color: #FFFF00; color: #000000;
}
.onoffswitch-inner:after {
    content: "CASH";
    padding-right: 10px;
    background-color: #13FB03; color: #000000;
    text-align: right;
}
.onoffswitch-switch {
    display: block; width: 12px; margin: 9px;
    background: #FFFFFF;
    position: absolute; top: 0; bottom: 0;
    right: 56px;
    border: 2px solid #999999; border-radius: 20px;
    transition: all 0.3s ease-in 0s; 
}
.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
    margin-left: 0;
}
.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
    right: 0px; 
}

</style>





















