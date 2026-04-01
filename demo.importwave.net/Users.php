<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; include __DIR__ . "/conn.php"; 
?>
<?php
// include("./sessionchk2.php");
// include("./refchk.php");
// include ("./conn.php");

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sql = "
    SELECT `id`, `name`, `type`, `FCP`, `LogError`, `LastLog`,  `UL` FROM `user_info` WHERE `UL` < 7;
";

// $result = $conn->query($sql);
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result(); // if no mysqlnd, use bind_result/fetch
$stmt->close();

$dataView = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $dataView[] = $row;
    }
}


// print_r($dataView);

?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div id="ResCon">
    <div style="width:70%; min-height: 30px; border:0px solid red; margin-top: -50px;">

        <div style="min-width:100px; height: 30px; float: left; border: 0px solid black;">
            <input style="width: 160px; text-align:left; float: left;" type="text" id="nUID" value="" placeholder="ID">  
        </div>

        <div style="min-width:100px; height: 30px; float: left; border: 0px solid black;">
            <input style="width: 160px; text-align:left; float: left; margin-left: 10px;" type="text" id="nUNAME" value="" placeholder="Name">
        </div>

        <div style="min-width:100px; height: 30px; float: left; border: 0px solid black;">
                <img title="Add New User"  onclick="NewUser();" src="./images/addU.png" style="cursor: hand; width: 20px; height: 20px;float:left; margin-left: 10px;">
        </div>
    </div>
</div>


<div style="width: 99%; min-height: 200px; border:0px solid red; margin: 0px auto; border-radius:10px;">
    <div style="width:99%; margin: 10 auto; border: 0px solid red;">
        <table  style="padding: 0px; width: 100%;text-align: left; font-size: 13px; font-family: Calibri, sans-serif; 
                            border-collapse: collapse; border: 0px solid gray; " >
            <tr>
                <th style="background-color: #c0d5ff;">User ID</th>
                <th style="background-color: #c0d5ff;">Name</th>
                <th style="background-color: #c0d5ff;">FCP</th>
                <th style="background-color: #c0d5ff;">Last Login</th>
                <th style="background-color: #c0d5ff;">Status</th>
                <th style="background-color: #c0d5ff;">User Role</th>

            </tr>

            <?php

                foreach ($dataView as $row) {
                    
                    // `sn`, `date`, `type`, `shipment`, `client`, `amount`, `user`, `approve`

                    $ID         = $row['id'];
                    $UName      = $row['name'];
                    $Utype      = $row['type'];
                    $UFCP       = $row['FCP'];
                    $LogError   = $row['LogError'];

                    if ($row['LastLog'] == '0000-00-00 00:00:00' || empty($row['LastLog'])) {
                        $LastLog = 'No log date'; // Or any default value you prefer
                    } else {
                        $LastLog = date('d M Y', strtotime($row['LastLog']));
                    }

                    $UUL        = $row['UL'];

            ?>

                <tr><td><span style="height:12px;">  </span></td>
                </tr>

                <tr style="padding-top: 20px; ">
                    <td><?php echo $ID; ?></td>
                    <td><?php echo $UName; ?></td>
                    <td><?php if ($UFCP==1) { echo "<span style='color:red;'>On</span>"; } else{ echo "---"; } ?></td>
                    <td><?php echo $LastLog; ?></td>

                    <td>
                        <?php if ($UUL == 0) {
                                $UUlevel = "Inactive";
                                ?> <span style='color:gray;'><?php echo $UUlevel ?></span> <?php                     
                        }
                        elseif ($UUL == 1) {
                                $UUlevel = "No Role";
                                ?> <span style='color:blue;'><?php echo $UUlevel ?></span> <?php                     
                        }
                        elseif ($UUL == 5) {
                                $UUlevel = "Back Office";
                                ?> <span style='color:blue;'><?php echo $UUlevel ?></span> <?php                     
                        }
                        elseif ($UUL == 6) {
                                $UUlevel = "Full Operation";
                                ?> <span style='color:blue;'><?php echo $UUlevel ?></span> <?php                     
                        }
                        elseif ($UUL == 'wh') {
                                $UUlevel = "Warehouse";
                                ?> <span style='color:blue;'><?php echo $UUlevel ?></span> <?php                     
                        }
                        elseif ($UUL == 'cn') {
                                $UUlevel = "China Office";
                                ?> <span style='color:blue;'><?php echo $UUlevel ?></span> <?php                     
                        } 


                        if ($LogError > 2) {
                                echo "<span style='color:red;'> (Locked)</span>";
                            }
                        ?>

                    </td>

                    <td >
                        <div class="select-container">
                            <select class="custom-select" id="RoleID_<?php echo $ID; ?>">

                                <option style="background-color:lightgreen;" value="<?php echo $UUL; ?>"><?php echo $UUlevel; ?></option>
                                        <option value="1">No Role</option>
                                        <option value="5">Back Office</option>
                                        <option value="6">Full Operations</option>
                                        <option value="wh">WareHouse</option>
                                        <option value="cn">China Expense</option>

                            </select>
 
                            </div>

                            <div style="float:left; margin-left: 5px; margin-top: 3px;">
                                <img title="Set User Level" id="BtnSet" onclick="SetUserLvl('<?php echo $ID; ?>');" src="./images/process.png" class="icon-button">
                                
                            </div>

                            <div style="float:right; margin-left: 5px; margin-top: 3px;">
                                <img title="Remove User" id="BtnReset" onclick="RemoveUser('<?php echo $ID; ?>');" src="./images/remuser.png" class="icon-button">

                            </div>

                            <div style="float:right; margin-left: 5px; margin-top: 3px;">
                                <img title="Reset User" id="BtnReset" onclick="ResetUser('<?php echo $ID; ?>');" src="./images/reset.png" class="icon-button">

                            </div>

                            

 
                    </td>

                   

                </tr>

            <?php

                }
            ?>



        </table>

    </div>

</div>



<script type="text/javascript">

function SetUserLvl(ClientName) { // ClientName is the User ID

    // 1. Get the specific dropdown for this user
    var dynamicSelectID = "RoleID_" + ClientName;
    var selectedRoleValue = document.getElementById(dynamicSelectID).value;

    // Show confirmation dialog
    Swal.fire({
        title: 'Confirm New Role',
        text: `Are you sure you want to set the role to "${selectedRoleValue}" for user: ${ClientName}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, set it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Point this to a NEW PHP file for updating roles, or modify ResetUserProc.php to handle it
            const link = "./SetUserRoleProc.php"; 
            const tframe = "#ResCon";

            // 2. Prepare the data to send
            const arr = {
                ClientName: ClientName, // The User ID
                NewRole: selectedRoleValue // The selected Value (1, 5, wh, etc)
            };

            // Show loading animation
            $(tframe).html(`Updating Role...`);

            $.ajax({
                url: link,
                type: "POST",
                data: arr,
                cache: false,
                success: function(data) {
                    $(tframe).html(data);
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'User role updated successfully!'
                    });
                },
                error: function(xhr, status, error) {
                    $(tframe).html('');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update role.'
                    });
                }
            });
        }
    });
}


function RemoveUser(ClientName) {

    // Show confirmation dialog (Red Color for warning)
    Swal.fire({
        title: 'DELETE USER?',
        text: `Are you sure you want to PERMANENTLY delete user: ${ClientName}? This cannot be undone!`,
        icon: 'error', 
        showCancelButton: true,
        confirmButtonColor: '#d33', // Red button for danger
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, DELETE it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            
            // Point to the NEW file we just created
            const link = "./RemoveUserProc.php";
            const tframe = "#ResCon";
            const arr = { ClientName: ClientName };

            // Show loading animation
            $(tframe).html(`Removing User...`);

            $.ajax({
                url: link,
                type: "POST",
                data: arr,
                cache: false,
                success: function(data) {
                    // Update container with server response
                    $(tframe).html(data);
                    
                    Swal.fire(
                        'Deleted!',
                        'The user has been removed.',
                        'success'
                    ).then(() => {
                        // Optional: Reload page to refresh the table list
                        // location.reload(); 
                    });
                },
                error: function(xhr, status, error) {
                    $(tframe).html('');
                    Swal.fire(
                        'Error!',
                        'Failed to delete user.',
                        'error'
                    );
                }
            });
        }
    });
}



function ResetUser(ClientName) {

  // Show confirmation dialog
  Swal.fire({
    title: 'Confirm Reset',
    text: `Are you sure you want to reset user: ${ClientName}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, reset it!',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      const link = "./ResetUserProc.php";
      const tframe = "#ResCon";
      const arr = { ClientName: ClientName };

      // Show loading animation
      $(tframe).html(`Reseting User...`);

      $.ajax({
        url: link,
        type: "POST",
        data: arr,
        cache: false,
        success: function (data) {
          // Update container with server response
          $(tframe).html(data);
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'User reset successfully!'
          });
        },
        error: function (xhr, status, error) {
          // Clear loading animation and show error
          $(tframe).html('');
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to reset user. Please try again.'
          });
        }
      });
    }
  });
}


function NewUser() {
    var nUID = document.getElementById('nUID').value;            
    var nUNAME = document.getElementById('nUNAME').value;
    var link = "./NewUser.php";
    var tframe = "#ResCon";

    if (nUID == "") {
        alert("User ID is blank.");
        return;
    }
    
    if (nUNAME == "") {
        alert("User NAME is blank.");
        return;
    }
    
    $(tframe).html("Adding new user...");
    
    var arr = {
        "nUNAME": nUNAME,
        "nUID": nUID
    };
    
    $.ajax({
        url: link,
        type: "POST",
        data: arr,
        cache: false,
        success: function(data) {
            $(tframe).html(data);
        },
        error: function(xhr, status, error) {
            $(tframe).html("Failed. Try again.");
        }
    });
}

</script>


<style>
/* Container to help positioning if needed, though not strictly required */
.select-container {
  /*max-width: 300px;*/
  margin: 5px;
  padding: 3px;
}

.custom-select {
  /* 1. Remove default browser styles */
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  
  /* 2. Box Styling */
  /*width: 100%;*/
  padding: 3px 3px;
  background-color: #ffffff;
  border: 1px solid #d1d1d1; /* Light grey border */
  border-radius: 0; /* Sharp corners like the image */
  
  /* 3. Text Styling */
  font-family: sans-serif;
  font-size: 12px;
  color: #00008B; /* Dark Blue text color matching "Inactive" */
  
  /* 4. Custom Arrow Icon */
  /* This encodes a thin grey chevron SVG directly into the CSS */
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23666666' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 10px center;
  background-size: 10px;
  
  /* 5. Cursor */
  cursor: pointer;
}

/* Remove the outline on focus/click for a cleaner look */
.custom-select:focus {
  outline: none;
  border-color: #999;
}

</style>