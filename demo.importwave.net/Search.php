<?php
include ("./sessionchk2.php");
include ("./refchk.php");
?>

<div style="min-height: 250px; width:100%; border: 0px solid black;">
    
    <div style="height: 30px; width:80%; border: 0px solid red; margin: 0 auto;">
        <td><input style="width: 100%; text-align:left; float: left; border-radius: 4px; text-align: center;" type="text" id="TxTSearch" 
            value="" placeholder="Type Shipping Mark or CTN NO (Then press enter)" onchange="ExeSearch();"></td>
    </div>
    
    <div id="SearchBody" style="min-height: 100px; width:100%; border: 0px solid black; padding-left:30px;">
        
    </div>
    
</div>

<script>
    
    function ExeSearch(){
        var SKEY = document.getElementById('TxTSearch').value;
        
        if(SKEY == ""){return;}

         if (SKEY.length < 3) {
            alert("Please enter at least 3 characters to search.");
            return;
        }
        
        console.log(SKEY);
        
        var link       = "./SearchExe.php";
        var tframe = "#SearchBody";
            
            
            $(tframe).html("Searching...Please wait.");
            
            var arr = {};
                        arr["SKEY"]      = SKEY;

                          $.ajax({ url:link,type:"POST", data: arr, cache: false, 
                                    success:function(data){
                                        // alert("New Entry Complete.")
                                        // GetPage('ExpenseLedger');
                                        $(tframe).html(data);
                                    } 
                                });
    }
</script>