<?php
include ("./sessionchk2.php");
include ("./refchk.php");
include ("./conn.php");
?>
<link rel="stylesheet" type="text/css" href="stylev2.css">                        <!-- style sheet here -->

<!-- calender item here -->
<link href="./js/jquery.datepicker2.css" rel="stylesheet">
<script src="./js/jquery-3.6.3.min.js" ></script>
<script src="./js/jquery.datepicker2.js"></script>

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


<style>
    .datepicker {
        position: relative;
        display: inline-block;
    }
    .datepicker input {
        padding: 10px;
    }
    .datepicker .calendar {
        position: absolute;
        top: 100%;
        left: 0;
        background: #fff;
        border: 0px solid #ccc;
        border-top: none;
        display: none;
    }
    .datepicker .calendar table {
        border-collapse: collapse;
    }
    .datepicker .calendar table td {
        padding: 5px;
        text-align: left;
        border: 0px solid #ccc;
    }
    .datepicker .calendar table td:hover {
        background: #f0f0f0;
    }
</style>

<script>

function AddCNFF() {
  Swal.fire({
    title: 'Enter New CNF Name',
    input: 'text',
    inputPlaceholder: 'Type the new CNF name here...',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Submit',
    cancelButtonText: 'Cancel',
    inputValidator: (value) => {
      if (!value) {
        return 'Please enter a valid CNF name!';
      }
    }
  }).then((result) => {
    if (result.isConfirmed) {
      var userInput = result.value;

      // Show confirmation dialog
      Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to add ${userInput}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, add it!',
        cancelButtonText: 'No, cancel'
      }).then((confirmResult) => {
        if (confirmResult.isConfirmed) {
          var CNFName = userInput;
          var link = "./AddCNF.php";
          var arr = { CNFName: CNFName };

          $.ajax({
            url: link,
            type: "POST",
            data: arr,
            cache: false,
            success: function (data) {
              Swal.fire({
                icon: 'success', // Fixed typo here
                title: 'Success',
                text: data
              });

              GetPage2('CNFEntry');
            },
            error: function (xhr, status, error) {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to add CNF. Please try again.'
              });
            }
          });
        }
      });
    }
  });
}


function SubmitCNF() {
  // Get form values
  const CNFName = document.getElementById('CNFName').value;
  const TxTSHIPMENT = document.getElementById('TxTSHIPMENT').value;
  const date = document.getElementById('date').value;
  const TxTAWBNO = document.getElementById('TxTAWBNO').value;
  const TxTTotalBill = document.getElementById('TxTTotalBill').value;
  const TxTPayment = document.getElementById('TxTPayment').value;
  const TxTDiscount = document.getElementById('TxTDiscount').value;
  const TxTRemarks = document.getElementById('TxtArea').value;

  // Helper function to format money (assuming MoneyFormat exists)
  const formatMoney = (value) => MoneyFormat(value + ".00");

  // Validation
  if (CNFName === "CNFName") {
    Swal.fire({
      icon: 'warning',
      title: 'Validation Error',
      text: 'Please select a valid CNF Name'
    });
    return;
  }

  if (TxTSHIPMENT === '') {
    Swal.fire({
      icon: 'warning',
      title: 'Validation Error',
      text: 'Shipment Name cannot be blank'
    }).then(() => {
      document.getElementById('TxTSHIPMENT').focus();
    });
    return;
  }

  if (TxTAWBNO === '') {
    Swal.fire({
      icon: 'warning',
      title: 'Validation Error',
      text: 'AWB No cannot be blank'
    }).then(() => {
      document.getElementById('TxTAWBNO').focus();
    });
    return;
  }

  if (parseFloat(TxTTotalBill) === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Validation Error',
      text: 'Total Bill cannot be zero'
    }).then(() => {
      document.getElementById('TxTTotalBill').focus();
    });
    return;
  }

  // Format values for display
  const TxTTotalBillcmm = formatMoney(TxTTotalBill);
  const TxTPaymentcmm = formatMoney(TxTPayment);
  const TxTDiscountcmm = formatMoney(TxTDiscount);

  // Show confirmation dialog
  Swal.fire({
    title: 'Confirm New Entry',
    html: `
      <strong>CNF NAME:</strong> ${CNFName}<br>
      <strong>SHIPMENT:</strong> ${TxTSHIPMENT}<br>
      <strong>AWB No:</strong> ${TxTAWBNO}<br>
      <strong>Total Bill:</strong> ${TxTTotalBillcmm}<br>
      <strong>Payment:</strong> ${TxTPaymentcmm}<br>
      <strong>Discount:</strong> ${TxTDiscountcmm}
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, submit!',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      const link = "./SubmitCNF.php";
      const arr = {
        CNFName: CNFName,
        date: date,
        TxTSHIPMENT: TxTSHIPMENT,
        TxTAWBNO: TxTAWBNO,
        TxTTotalBill: TxTTotalBill,
        TxTPayment: TxTPayment,
        TxTRemarks: TxTRemarks,
        TxTDiscount: TxTDiscount
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
            GetPage('AdminLedger');
          });
        },
        error: function (xhr, status, error) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to submit CNF. Please try again.'
          });
        }
      });
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


<div style="width: 99%; height: auto; border:0px solid red; margin: 0px auto; padding:10px 40px 0px 40px; border-radius:10px; margin-left: 20px;
box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);
-webkit-box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);
-moz-box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);">

    <div style="width:100%; height: 350px; margin-top: 20px; border: 0px solid blue;">

        <div style="width:99%; height: 50px; border: 0px solid green; float: left;">

            <div style="width: 200px; min-height:30px; border:0px solid red; float: left;
                text-align:left; font-size: 26; color: 990000; letter-spacing: 2px; font-weight:bold ;">CNF ENTRY</div>

            <div style="min-width: 20px; min-height:30px; border:0px solid red; float: right;">

                <img class="BoxButton2" title="Add CNF" id="SaveIco" onclick="AddCNFF();" src='./images/addU.png' style='cursor: hand; width: 30px;  float:right;
                    height: 30px;  border: 0px solid green; margin-top:3px;'>

            </div>

            <div style="min-width: 200px; min-height:30px; border:0px solid blue; float: right; margin-right: 20px;">

                <select class="classic" id="CNFName" style="">
                    <option style="text-align: left; font-weight: bolder; " value="CNFName">CNF</option>
                    <?php 
                        $sql_item = "SELECT `NAME` FROM `CNF_AGENT` ORDER BY `NAME` ASC";
                        // $result = $conn->query($sql_item);

                        $stmt_item = $conn->prepare($sql_item);
                        $stmt_item->execute();
                        $result = $stmt_item->get_result(); // if no mysqlnd, use bind_result/fetch
                        $stmt_item->close();

                        
                        if ($result->num_rows > 0) {
                            // output data of each row
                            while($row = $result->fetch_assoc()) {
                                $CNF = $row['NAME'];
                    ?>
                                <option value="<?php echo $CNF ; ?>"><?php echo $CNF ; ?></option>
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
                        Date
                    </div>
                    <div style="float: left; margin-top: 3px;"> 
                            <?php $xtoday = date("m-d-Y"); ?>
                            <input class="form-control" style="width: 160px; text-align:left; float: right; border: 1px solid gray; border-radius: 3px;" 
                                                type="text" id="date" data-select="datepicker" value="<?php echo $xtoday;?>">
                    </div>
                </div>

                <div style="width:170px; min-height: 50px; border: 0px solid green; float: left; margin: 0 auto; margin-left: 20px; margin-top: 10px;">

                    <div style="width:150px; float: left; text-align: left; font-size: 13px;">
                        Shipment
                    </div>
                    <div style="float: left; margin-top: 3px;"> 
                            <input style="width: 160px; text-align:left; float: left; border: 1px solid gray; border-radius: 3px;" 
                                            type="text" id="TxTSHIPMENT" value="" placeholder="Shipment">
                    </div>
                  
                </div>

                <div style="width:170px; min-height: 50px; border: 0px solid green; float: left; margin: 0 auto; margin-left: 20px; margin-top: 10px;">

                    <div style="width:150px; float: left; text-align: left; font-size: 13px;">
                        AWB No
                    </div>
                    <div style="float: left; margin-top: 3px;"> 
                            <input style="width: 160px; text-align:left; float: left; border: 1px solid gray; border-radius: 3px;" 
                                    type="text" id="TxTAWBNO" value="" placeholder="AWB No">
                    </div>
                  
                </div>
              
            </div>

          </div>






          <div style="width:99%; min-height: 50px; border: 0px solid red; float: left; margin: 0 auto;">

            <div style="min-width:30%; min-height: 50px; border: 0px solid blue; float: left;">

                <div style="width:170px; min-height: 50px; border: 0px solid green; float: left; margin: 0 auto; margin-left: 20px; margin-top: 10px;">
                    <div style="width:150px; float: left; text-align: left; font-size: 13px;">
                        Total Bill
                    </div>
                    <div style="float: left; margin-top: 3px;"> 
                            <input style="width: 160px; text-align:right; float: left; border: 1px solid gray; border-radius: 3px;" type="text" id="TxTTotalBill" value="0" placeholder="Total Bill" 
                                onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))">
                    </div>
                </div>

                <div style="width:170px; min-height: 50px; border: 0px solid green; float: left; margin: 0 auto; margin-left: 20px; margin-top: 10px;">

                    <div style="width:150px; float: left; text-align: left; font-size: 13px;">
                        Payment
                    </div>
                    <div style="float: left; margin-top: 3px;"> 
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
                                onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))">
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
                      <img class="BoxButton2" title="Save Data" id="SaveIco" onclick="SubmitCNF();" src='./images/save1.png' style='cursor: hand; width: 50px; float:left;
                            height: 50px; border: 0px solid green; margin-top:3px; margin-left: 30px;'>
                    </div>

                  </div>

          </div>
  

</div>