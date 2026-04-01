
<style>
  .icon-button {
    cursor: pointer;
    width: 25px;
    height: 25px;
    float: right;
    margin-right: 10px;
    border-radius: 5px;
  }

  .icon-button:hover {
    border: 1px solid gray; /* Adds a 1px black border on hover */
  }
</style>

<div style="width:99%; min-height:300px;; border:0px solid gray; margin:0px auto; margin-left:15px; border-radius:10px; margin-bottom: 30px;">
    <div name="TopBox" id="TopBox" style="height:50px;width:90%; margin: 5px auto; border:1px dotted white;">
        <div name="MenuBar" id="MenuBar" style="height:40px;width:auto; margin: 0px auto; border:0px solid blue; padding-top: 10px;">
              <img
                title="User Control"
                id="BtnAddUser"
                onclick="GetPage3('Users', 'BotBox');"
                src="./images/men.png"
                class="icon-button"
              />

            <!--   <img
                title="Add User"
                id="BtnAddUser"
                onclick="GetPage3('AddUser', 'BotBox');"
                src="./images/adduser.png"
                class="icon-button"
              /> -->

             <!--  <img
                title="Reset User"
                id="BtnReset"
                onclick="GetPage3('ResetUser', 'BotBox');"
                src="./images/reset.png"
                class="icon-button"
              />
 -->
             <!--  <img
                title="Remove User"
                id="BtnRemUser"
                onclick="GetPage3('RemUser', 'BotBox');"
                src="./images/remuser.png"
                class="icon-button"
              /> -->

              <img
                title="Approval Management"
                id="BtnChangeRec"
                onclick="GetPage3('DelRecord', 'BotBox');"
                src="./images/approved.png"
                class="icon-button"
              />

              <img
                title="Accounting"
                id="BtnProfit"
                onclick="GetPage3('ExpenseEntry', 'BotBox');"
                src="./images/accounting.png"
                class="icon-button"
              />
        </div>
    </div>

    <div name="BotBox" id="BotBox" style="width:100%; margin: 0px auto; border:0px solid red;">


    </div>
</div>


<script type="text/javascript">

function DeleteRecExp1184(sn, type) {
  // Show confirmation dialog
  Swal.fire({
    title: 'Confirm Delete',
    text: `Are you sure you want to delete: ${type}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      const link = "./DelRecordBusExpense.php";
      const arr = {
        sn: sn,
        type: type
      };

      $.ajax({
        url: link,
        type: "POST",
        data: arr,
        cache: false,
        success: function (data) {
          Swal.fire({
            icon: 'success',
            title: 'Deleted',
            text: data
          }).then(() => {
            PullExp();
            document.getElementById('TxTType').focus();
          });
        },
        error: function (xhr, status, error) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to delete record. Please try again.'
          });
        }
      });
    }
  });
}



GetPage3('ExpenseEntry', 'BotBox');


function ShowBalance() {
    var ShipID = document.getElementById('TxTShipment').value;

    var link = "./ExpDataPull.php";

    var arr = {};
    arr["s"] = ShipID;

    $.ajax({
        url: link,
        type: "POST",
        data: arr,
        cache: false,
        success: function (data) {
            // Create a popup container if it doesn't exist
            if (!document.getElementById('popupContainer')) {
                var popupDiv = document.createElement('div');
                popupDiv.id = 'popupContainer';
                popupDiv.style.position = 'fixed';
                popupDiv.style.top = '50%';
                popupDiv.style.left = '50%';
                popupDiv.style.transform = 'translate(-50%, -50%)';
                popupDiv.style.backgroundColor = '#fff8e5';
                popupDiv.style.border = '5px solid #ccc';
                popupDiv.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';
                popupDiv.style.padding = '50px';
                popupDiv.style.zIndex = '1000';
                popupDiv.style.maxWidth = '80%';
                popupDiv.style.maxHeight = '80%';
                popupDiv.style.overflowY = 'auto';
                popupDiv.innerHTML = `
                    <button id="closePopup" style="float:right; margin-bottom:10px; cursor: pointer; ">X</button>
                    <div id="popupContent"></div>
                `;
                document.body.appendChild(popupDiv);

                // Add close functionality
                document.getElementById('closePopup').addEventListener('click', function () {
                    document.body.removeChild(popupDiv);
                });
            }

            // Update the popup content
            document.getElementById('popupContent').innerHTML = data;
        },
        error: function (xhr, status, error) {
            alert('Error: ' + error);
        }
    });
}
</script>

