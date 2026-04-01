<?php
include ("./sessionchk2.php");
include ("./refchk.php");
include ("./conn.php");
?>

<link rel="stylesheet" type="text/css" href="stylev2.css">                        <!-- style sheet here -->


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


<script type="text/javascript">


function RemarksSize() {
    var RMVAL = document.getElementById('TxtArea').value;
    var TxtLen = RMVAL.length;
    var RemLen = 39 - TxtLen;
    if (TxtLen > 39) {
        document.getElementById('TxtArea').value = RMVAL.substring(0, 39);
        RemLen = 0;
    }
    $("#RMLimit").html(RemLen);
}


function FocusPayment() {
    var paymentInput = document.getElementById('TxTPayment');
    paymentInput.value = ""; // Clear the input field
    paymentInput.focus(); // Set focus
}


function SubmitEntry() {
  // Get form values
  const FClient = document.getElementById('ClientName').value;
  let TxTPayment = document.getElementById('TxTPayment').value;
  let TxTDiscount = document.getElementById('TxTDiscount').value;
  let TxTcommission = document.getElementById('TxTcommission').value;
  const RMVAL = document.getElementById('TxtArea').value;

  // Set default values for empty inputs
  TxTPayment = TxTPayment === "" ? "0" : TxTPayment;
  TxTDiscount = TxTDiscount === "" ? "0" : TxTDiscount;
  TxTcommission = TxTcommission === "" ? "0" : TxTcommission;

  // Validation
  if (FClient === "CLIENT") {
    Swal.fire({
      icon: 'warning',
      title: 'Validation Error',
      text: 'Please select a valid client name.'
    });
    return;
  }

  if (isNaN(TxTPayment) || parseFloat(TxTPayment) < 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Validation Error',
      text: 'Please enter a valid payment amount.'
    }).then(() => {
      document.getElementById('TxTPayment').focus();
    });
    return;
  }

  if (parseFloat(TxTPayment) === 0 && parseFloat(TxTDiscount) === 0 && parseFloat(TxTcommission) === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Validation Error',
      text: 'No value found. Please check payment, discount, or commission amounts.'
    }).then(() => {
      document.getElementById('TxTPayment').focus();
    });
    return;
  }

  // Format values for display
  const AmntShow = MoneyFormat(TxTPayment + ".00");
  const AmntDisc = MoneyFormat(TxTDiscount + ".00");
  const AmntComm = MoneyFormat(TxTcommission + ".00");

  // Show confirmation dialog
  Swal.fire({
    title: 'Confirm New Entry',
    html: `
      <strong>CLIENT NAME:</strong> ${FClient}<br>
      <strong>Payment:</strong> ${AmntShow}<br>
      <strong>Discount:</strong> ${AmntDisc}<br>
      <strong>Commission:</strong> ${AmntComm}
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, submit!',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      const link = "./PaymentEntry.php";
      const arr = {
        FClient: FClient,
        TxTPayment: TxTPayment,
        TxTDiscount: TxTDiscount,
        TxTcommission: TxTcommission,
        RMVAL: RMVAL
      };

      $.ajax({
        url: link,
        type: "POST",
        data: arr,
        cache: false,
        success: function (data) {
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: data
          }).then(() => {
            // Reset form fields
            document.getElementById('TxTPayment').value = "0";
            document.getElementById('TxTDiscount').value = "0";
            document.getElementById('TxTcommission').value = "0";
            document.getElementById('TxtArea').value = "";
          });
        },
        error: function (xhr, status, error) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to submit entry. Please try again.'
          });
        }
      });
    }
  });
}

    
</script>


<div style="width: 99%; height: auto; border:0px solid red; margin: 0px auto; padding:10px 40px 0px 40px; border-radius:10px; margin-left: 20px;
box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);
-webkit-box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);
-moz-box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);">

    <div style="width:100%; height: 350px; margin-top: 20px; border: 0px solid blue;">

        <div style="width:99%; height: 50px; border: 0px solid green; float: left;">

            <div style="width: 200px; min-height:30px; border:0px solid red; float: left;
                text-align:left; font-size: 26; color: 990000; letter-spacing: 2px; font-weight:bold ;">LEDGER ENTRY</div>

            <div style="min-width: 200px; min-height:30px; border:0px solid blue; float: right; margin-right: 20px;">

                <select class="classic" id="ClientName" onchange="FocusPayment();" style="">
                    <option style="text-align: left; font-weight: bolder; " value="CLIENT">CLIENT</option>
                    <?php 
                        $sql_item = "SELECT distinct(`CLIENT`) 'CLIENT' FROM `packinglist` order by `CLIENT` asc";
                    $result = $conn->query($sql_item);
                    if ($result->num_rows > 0) {
                            // output data of each row
                            while($row = $result->fetch_assoc()) {
                                
                                $CLIENT                = $row['CLIENT'];
                     ?>
                                <option value="<?php echo $CLIENT ; ?>"><?php echo $CLIENT ; ?></option>
                     <?php  }
                    }
                    ?>
                </select>
        </div>
    </div>





        <div style="width:99%; min-height: 50px; border: 0px solid red; float: left; margin: 0 auto;">

            <div style="min-width:30%; min-height: 50px; border: 0px solid blue; float: left;">

                <div style="width:170px; min-height: 50px; border: 0px solid green; float: left; margin: 0 auto; margin-left: 20px; margin-top: 10px;">
                    <div style="width:150px; float: left; text-align: left; font-size: 13px;">
                        Payment
                    </div>
                    <div style="float: left; margin-top: 3px;"> 
                        <span id="DispAmnt" style="float:right; border: 0px solid red; height:0px; width: 100px;" ></span>
                        <input style="width: 160px; text-align:right; float: left; border: 1px solid gray; border-radius: 3px;" type="text" id="TxTPayment" value="0" placeholder="Payment" 
                            onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))">
                    </div>
                </div>

                <div style="width:170px; min-height: 50px; border: 0px solid green; float: left; margin: 0 auto; margin-left: 20px; margin-top: 10px;">

                    <div style="width:150px; float: left; text-align: left; font-size: 13px;">
                        Discount
                    </div>
                    <div style="float: left; margin-top: 3px;"> 
                            <input style="width: 160px; text-align:right; float: left; border: 1px solid gray; border-radius: 3px;" type="text" id="TxTDiscount" value="0" placeholder="Discount" 
                                onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))">
                    </div>
                  
                </div>

                <div style="width:170px; min-height: 50px; border: 0px solid green; float: left; margin: 0 auto; margin-left: 20px; margin-top: 10px;">

                    <div style="width:150px; float: left; text-align: left; font-size: 13px;">
                        Commission
                    </div>
                    <div style="float: left; margin-top: 3px;"> 
                            <input style="width: 160px; text-align:right; float: left; border: 1px solid gray; border-radius: 3px;" type="text" id="TxTcommission" value="0" placeholder="Commission" 
                                onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))">
                    </div>
                  
                </div>
              
            </div>

          </div>






          <div style="width:99%; min-height: 50px; border: 0px solid red; float: left; margin: 0 auto;">

                  <div style="width:99%; min-height: 50px; border: 0px solid blue; float: left;">

                    

                    <div style="width:100%; min-height: 50px; border: 0px solid green; float: left; margin: 0 auto; margin-left: 20px; margin-top: 10px;">

                        
                          <textarea id="TxtArea" placeholder="Remarks" rows="5" cols="45" style="resize: none; border: 1px solid gray; border-radius: 3px;" onkeypress="RemarksSize();"></textarea>
                          <span id="RMLimit" style="color:gray; font-size: 10px"></span>

                          
                          
                    </div>

                    <div style="float:right;">
                      <img class="BoxButton2" title="Save Data" id="SaveIco" onclick="SubmitEntry();" src='./images/save1.png' style='cursor: hand; width: 50px; float:left;
                            height: 50px; border: 0px solid green; margin-top:3px; margin-left: 30px;'>
                    </div>

                  </div>

          </div>
  

</div>