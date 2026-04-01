<?php
include ("./sessionchk2.php");
include ("./refchk.php");
?>

<div style="width:90%; height:333px;; border:1px solid gray;margin:30px auto; margin-left:45px; border-radius:10px;

box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);

-webkit-box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);

-moz-box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);

">

    

<div style="width: 300px; height:250px; border:0px solid darkblue; margin: 0px auto; padding:20px 40px 40px 40px; border-radius:10px;">



<div style="min-width: 100px; min-height:20px; margin: 0 auto; border:0px solid red; margin-bottom:20px; 

text-align:center; font-size: 26; color: 990000; letter-spacing: 2px; font-weight:bold ;">CHANGE PASSWORD



</div>



	<table width="100%" border="0">

		

		<tr>

			<td >Current Password</td>

			<td>

                <input style="width: 160px; text-align:left; float: right;" type="password" id="OldPass" value="">

            </td>

		</tr>



		<tr>			<td>				<div style="height: 10px;"></div>			</td>		</tr>



		<tr>

			<td>New Password</td>

			<td>

                <input style="width: 160px; text-align:left; float: right;" type="password" id="NewPass" name="NewPass2" value=""

                

                onkeydown="ChkP();"

                onchange="ChkP();"

               

                >

            </td>

		</tr>

	    	    <tr>

	    	        <td></td>

	    	        <td>

	    	            <div style="height:10px;">

	    	                <div id="StatP1" style="height:15px; width:15px; border:0px solid red;  margin:0 auto; display:none;

	    	                                    background-image: url('./images/cross.png'); width:15px; height:15px;

	    	                                    margin:0 auto; padding-top:1px;

                                                background-repeat: no-repeat;  background-size: 15px 15px;">

	    	                    

	    	                </div>

                        </div>

                    </td>

	    	    </tr>

		<tr>

			<td>Confirm it</td>

			<td>

                <input style="width: 160px; text-align:left; float: right;" type="password" name="NewPass2" id="NewPass2" value=""

                onkeydown="ChkP1();"

                onchange="ChkP1();"

                

                >

                

            </td>

            <td><div style="width:20px; height:20px; border:0px solid red; float:right;">

                <img id="imgcross" src='./images/redcross.png' style='cursor: hand; width: 20px; height: 20px; display:none;'>

                <img id="imgtick" src='./images/tick.png' style='cursor: hand; width: 20px; height: 20px; display:none'>

            </div></td>

		</tr>



	</table>



<div id="StatP" style="border:0px solid red; width:100%; float:left; text-align:left; margin-top:10px; margin-bottom:25px; height:20px;"></div>









<div style="padding-top:50px;">
    <div style="height:60px; width:60px; float:left; border:0px solid red;">
        <img title="Logout" class="BoxButton" id="BtnLogout" onclick="Logout();" src='./images/logout.png' 
                    style='cursor: hand; width: 50px;  float:right; height: 50px;'>
    </div>


    <div style="height:60px; width:60px; float:right; border:0px solid red;">
        <img class="BoxButton" title="Change Password" id="cpass" onclick="ChangePass();" src='./images/cpass.png' style='cursor: hand; width: 50px;  float:right;

                        height: 50px;'>
    </div>
</div>







</div>

</div>







<script type="text/javascript">

    function ChangePass(){
        
            var OldPass        = document.getElementById('OldPass').value;            
            var NewPass        = document.getElementById('NewPass').value;
            var NewPass2       = document.getElementById('NewPass2').value;
            var UID            = document.getElementById('UID').value;
            
            var tframe = "#StatP";
            
            // alert(NewPass.length);
            
            // if(NewPass.length==0){
            //     alert("Password is blank");
            //     return;
            // }
            // else 
            // if(NewPass.length<6){
            //     alert("New Password should be atleast 6 characters.");
            //     return;
            // }
            
            if(NewPass!=NewPass2){
                alert('Please check New Pass again');
                var box11 = document.getElementById('NewPass2');
                    box11.focus();
                return;
            }
            
            var link       = "./PassChangeProcess.php";
            
            var arr = {};
                        arr["OldPass"]  = OldPass;
                        arr["NewPass"]  = NewPass;
                        arr["UID"]      = UID;
                        
                        
                        $.ajax({ url:link, type:"POST", data: arr, cache: false, success:function(data){
                              
                                    $(tframe).html(data);
                              
                        }});
}


function ChkP1(){

    

    var NewPass         = document.getElementById('NewPass').value;

    var NewPass2        = document.getElementById('NewPass2').value;

    

    var box22           = document.getElementById('NewPass2');

    

    var box33           = document.getElementById('StatP1');

    

    var imgtick         = document.getElementById('imgtick');

    var imgcross        = document.getElementById('imgcross');

    

    var Pass2Len        = NewPass2.length;

    

    box22.style.borderColor = "";

    

    box33.style.display = "inline";

    

    imgcross.style.display = 'inline';

    imgtick.style.display  = 'none';


    var tframe = "#StatP";

    

        $(tframe).html("");

        

    var LeftP1 = NewPass.substring(0, Pass2Len);

        

    // $(tframe).html(LeftP1+"|"+NewPass2);

    

    if(LeftP1!=NewPass2){

        // $(tframe).html("Not Matched.");

        box22.style.borderColor = "red";

        box22.style.border      = "2px solid red";

        

        imgcross.style.display = 'inline';

        imgtick.style.display  = 'none';

        

        box33.style.display = 'inline';

        

        $(tframe).html("Passwords don’t match.");

    }

    else{

        // $(tframe).html("Matched.");

        box22.style.borderColor = "green";

        $(tframe).html("");

        

        imgcross.style.display = 'none';

        imgtick.style.display  = 'inline';

        

        box33.style.display = 'none';

        

        ChkP();

    }





 

}



function ChkP(){

    

    var NewPass         = document.getElementById('NewPass').value;

        NewPass         = NewPass.trim();

    

    var box11           = document.getElementById('StatP');

    

        box11.style.color = "black";



    var number = /([0-9])/;

	var alphabets = /([a-zA-Z])/;

	var special_characters = /([~,!,@,#,$,%,^,&,*,-,_,+,=,?,>,<])/;

       

    var tframe = "#StatP";

    

    $(tframe).html("");

    

    if (NewPass.length < 6) {

        box11.style.color = "red";

        $(tframe).html("Please choose a stronger password.</br>Try a mix of letters, numbers, and symbols.");

        

	}else {

		if (NewPass.match(number) && NewPass.match(alphabets) && NewPass.match(special_characters)) {

		     box11.style.color = "blue";

		     

		    $(tframe).html("Strong Password");

		}

		else {

	        box11.style.color = "orange";

			$(tframe).html("Medium Password");

		}

	}

    

}



$(document).ready(function(){

    $('#NewPass2').keypress(function(e){

    

        ChkP();

        

        if(e.keyCode==13){ $('#cpass').click();}

      

    });

    

    $('#NewPass').keypress(function(e){

      if(e.keyCode==13){

          var   box11 = document.getElementById('NewPass2');

                box11.focus();

      }

    });

    

    $('#OldPass').keypress(function(e){

      if(e.keyCode==13){

          var   box11 = document.getElementById('NewPass');

                box11.focus();

      }

    });

    



});



    

</script>





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

</style>