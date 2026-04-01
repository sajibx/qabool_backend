<?php
include ("./sessionchk2.php");
include ("./refchk.php");
?>

<script>
       function CorrData(){
        
            var OldName       = document.getElementById('OldName').value;            
            var NewName       = document.getElementById('NewName').value;

            var link       = "./ChangeName.php?OldName="+OldName+"&NewName="+NewName;
            
            var tframe = "#InfoBox";
            
                  $.ajax({ url:link,
                  type:"GET",
                  dataType: "html", 
                  success:function(data){
                        $(tframe).html("");
                        $(tframe).html(data);
                  } });

}

</script>

<link rel="stylesheet" type="text/css" href="stylev2.css">                        <!-- style sheet here -->

<div style="width: 300px; min-height: 200px; border:0px solid darkblue; margin: 50px auto; padding:20px 40px 40px 40px; border-radius:10px; padding-bottom: 30px;
box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);
-webkit-box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);
-moz-box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);"
>

<div style="min-width: 100px; min-height:20px; margin: 0 auto; border:0px solid red; margin-bottom:30px; 
text-align:center; font-size: 26; color: 990000; letter-spacing: 2px; font-weight:bold ;">CORRECTION

</div>

	<table width="100%" border="0">

		
		<tr>
			<td >Old Name</td>
			<td>
                <input style="width: 120px; text-align:left; float: right;" type="text" id="OldName" value="">
            </td>
		</tr>

		<tr>			<td>				<div style="height: 10px;"></div>			</td>		</tr>

		<tr>
			<td>New Name</td>
			<td>
                <input style="width: 120px; text-align:left; float: right;" type="text" id="NewName" value="">
            </td>
		</tr>
		

    <tr>
      
      <td>
          
      </td>
    </tr>

   <tr>
      <td>
        
      </td>
      <td>

      </td>
    </tr>


		<tr style="text-align:center;">
			<td colspan="2" style="text-align:center;">
				
			</td>
		</tr>	

    <tr>
      <td></td>
      <td>
        <button 
                            class="button-5" 
                            onclick="CorrData();" 
                            title="Change All" 
                            style="cursor: pointer; margin-left:5px; float: right;">
                            Save
                        </button>

      </td>
    </tr>
	</table>

<!-- <img class="BoxButton2" title="Save Data" id="SaveIco" onclick="CorrData();" src='./images/correction.png' style='cursor: hand; width: 50px;  float:right;
                    height: 50px;  border: 0px solid green; margin-top:30px;'> -->
                        
                        

</div>

<div id="InfoBox">
    
</div>




<style type="text/css">

	select {

  /* styling */
  background-color: white;
  border: thin solid gray;
  border-radius: 4px;
  display: inline-block;
  font: inherit;
  line-height: 15px;
  padding: 0.5em 3.5em 0.5em 1em;
  width: 200px;

  /* reset */

  margin: 0;      
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-appearance: none;
  -moz-appearance: none;
  cursor: hand;
}


/* arrows */

select.classic {
width: 100%;
  background-image:
    linear-gradient(45deg, transparent 50%, blue 50%),
    linear-gradient(135deg, blue 50%, transparent 50%),
    linear-gradient(to right, skyblue, skyblue);
  background-position:
    calc(100% - 20px) calc(1em + 2px),
    calc(100% - 15px) calc(1em + 2px),
    100% 0;
  background-size:
    5px 5px,
    5px 5px,
    2.5em 2.5em;
  background-repeat: no-repeat;
}x

select.classic:focus {
  background-image:
    linear-gradient(45deg, white 50%, transparent 50%),
    linear-gradient(135deg, transparent 50%, white 50%),
    linear-gradient(to right, gray, gray);
  background-position:
    calc(100% - 15px) 1em,
    calc(100% - 20px) 1em,
    100% 0;
  background-size:
    5px 5px,
    5px 5px,
    2.5em 2.5em;
  background-repeat: no-repeat;
  border-color: grey;
  outline: 0;
}
</style>



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