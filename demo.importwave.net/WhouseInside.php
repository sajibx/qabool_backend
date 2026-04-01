<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; include __DIR__ . "/conn.php"; 
?>
<?php
// include ("./sessionchk.php");
// include("./refchk.php");

// include ("./conn.php");


$WNAME = $_POST['WNAME'];

// Fetch detailed data
$sql_detailed = "SELECT A.`SHIPMENT`, A.`CLIENT`, COUNT(A.`SN`) AS CNT
                 FROM `packinglist` A
                 LEFT JOIN `ShipmentLog` B 
                 ON A.`SHIPMENT` = B.`SHIPMENT`
                 WHERE A.`DELIVERY` = 'N' 
                 AND B.`WAREHOUSE` = '$WNAME'
                 GROUP BY A.`SHIPMENT`, A.`CLIENT`
                 ORDER BY A.`SHIPMENT`, A.`CLIENT`";

$result_detailed = $conn->query($sql_detailed);

$shipments = []; // shipment => total_cnt
$all_clients = []; // client => total_cnt
$clients_per_shipment = []; // shipment => [client => cnt]

if ($result_detailed->num_rows > 0) {
    while($row = $result_detailed->fetch_assoc()) {
        $ship = $row['SHIPMENT'];
        $cli = $row['CLIENT'];
        $cnt = $row['CNT'];

        if (!isset($shipments[$ship])) {
            $shipments[$ship] = 0;
        }
        $shipments[$ship] += $cnt;

        if (!isset($all_clients[$cli])) {
            $all_clients[$cli] = 0;
        }
        $all_clients[$cli] += $cnt;

        if (!isset($clients_per_shipment[$ship])) {
            $clients_per_shipment[$ship] = [];
        }
        $clients_per_shipment[$ship][$cli] = $cnt;
    }
}

// Sort shipments and all_clients by key
ksort($shipments);
ksort($all_clients);

?>


<style type="text/css">

/* CSS */

.button-5 {
  
  min-width: 30px;
  height: 20px;
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
  font-weight: bolder;


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


input[type=checkbox] {
    height: 0;
    width: 0;
    visibility: hidden;
}

label {
    cursor: pointer;
    text-indent: -9999px;
    width: 40px; /* Reduced further */
    height: 20px; /* Reduced further */
    background: grey;
    display: block;
    border-radius: 20px; /* Reduced further */
    position: relative;
}

label:after {
    content: '';
    position: absolute;
    top: 2px; /* Reduced further */
    left: 2px; /* Reduced further */
    width: 16px; /* Reduced further */
    height: 16px; /* Reduced further */
    background: #fff;
    border-radius: 16px; /* Reduced further */
    transition: 0.3s;
}

input:checked + label {
    background: #bada55;
}

input:checked + label:after {
    left: calc(100% - 2px); /* Reduced further */
    transform: translateX(-100%);
}

label:active:after {
    width: 24px; /* Reduced further */
}

</style>


<input id="WNAME" type="text" name="" style="display:none;" value="<?php echo $WNAME; ?>">

<div style="width: 100%; border: 0px solid red; padding-left: 0px; margin-top: 0px; height: 40px; margin-bottom: 10px; padding-top: 5px;">



    <div style="float: left;">
        <img title="Go Back" id="NewEntry" onclick="GetPage('Whouse');" src='./images/back.png' style='cursor: hand;
            width: 30px;  float:right  ; height: 30px;  border:  margin-top:0;'>
    </div>
    
    <div style="font-weight:bolder; font-size:24px; color: darkred;margin-left:20px; float: left;"><?php echo $WNAME; ?></div>

    <div style="float:right; margin-right: 0px;">

        <button class="button-5" onclick="SaveDelivary();" title="Save" style="cursor: pointer; margin-left:5px;">
                Save
        </button>
        
    </div>



<!-- FILTER STARTS HERE -->
        <div style="float:left;  margin-left: 20px; margin-right: 5px; text-align: left; border: 0px solid red; padding-bottom: 5px;">
                <select class="classic" id="WSHIPMENT" onchange="updateClients(); FilterUpdate();">
                        <option style="text-align: left; font-weight: bolder; " value="WSHIPMENT">ALL SHIPMENT</option>

                            <?php 
                            foreach ($shipments as $SHIPMENT => $CNT) {
                                $SHIPMENT_D = $SHIPMENT . " ( " . $CNT . " ) ";
                            ?>
                                <option value="<?php echo htmlspecialchars($SHIPMENT); ?>"><?php echo htmlspecialchars($SHIPMENT_D); ?></option>
                            <?php } ?>
                </select>
    </div>

    <div style="float:left;  margin-left: 20px; margin-right: 5px; text-align: left; border: 0px solid red; padding-bottom: 5px;">
                <select class="classic" id="WCLIENT" onchange="FilterUpdate()">
                        <option style="text-align: left; font-weight: bolder; " value="WCLIENT">CLIENT</option>

                            <?php 
                            foreach ($all_clients as $CLIENT => $CNT) {
                                $CLIENT_D = $CLIENT . " ( " . $CNT . " ) ";
                            ?>
                                <option value="<?php echo htmlspecialchars($CLIENT); ?>"><?php echo htmlspecialchars($CLIENT_D); ?></option>
                            <?php } ?>
                </select>
    </div>

</div>


<div id="FilterBody">
    

</div>


<!--     </div>

        


 </div> -->





<script type="text/javascript">

    var clientsPerShipment = <?php echo json_encode($clients_per_shipment); ?>;
    var allClients = <?php echo json_encode($all_clients); ?>;

    $(document).ready(function($) {

  var WNAME = document.getElementById('WNAME').value;

  var link = "./WFilter.php";
  var tframe = "#FilterBody";

  $(tframe).html("<div style='border:0px solid red; height:100px; width:100px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");

  $.ajax({
    url: link,
    type: "POST", // Change the request type to POST
    dataType: "html",
    data: {
      WSHIPMENT: "WSHIPMENT", // Update with your desired value
      WCLIENT: "WCLIENT", // Update with your desired value
      WNAME: WNAME
    },
    success: function(data) {
      $(tframe).html("");
      $(tframe).html(data);
    }
  });
});


    function FilterUpdate(){

        var WNAME = document.getElementById('WNAME').value;
        var SHIPMENT = document.getElementById('WSHIPMENT').value;
        var CLIENT = document.getElementById('WCLIENT').value;


        // alert(WNAME);

        // alert(WSHIPMENT);

        // alert(WCLIENT);

        // exit();


        var link = "./WFilter.php";
        var tframe = "#FilterBody";

      $(tframe).html("<div style='border:0px solid red; height:100px; width:100px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");

      $.ajax({
        url: link,
        type: "POST", // Change the request type to POST
        dataType: "html",
        data: {
          WSHIPMENT: SHIPMENT, // Update with your desired value
          WCLIENT: CLIENT, // Update with your desired value
          WNAME: WNAME
        },
        success: function(data) {
          $(tframe).html("");
          $(tframe).html(data);
        }
      });




    }

    function updateClients() {
        var ship = $('#WSHIPMENT').val();
        var clientSelect = $('#WCLIENT');
        clientSelect.empty();
        clientSelect.append($('<option>', {
            value: 'WCLIENT',
            text: 'CLIENT',
            css: {'text-align': 'left', 'font-weight': 'bolder'}
        }));

        var clients = {};
        if (ship === 'WSHIPMENT') {
            clients = allClients;
        } else {
            clients = clientsPerShipment[ship] || {};
        }

        var sortedClients = Object.keys(clients).sort();
        for (var i = 0; i < sortedClients.length; i++) {
            var cli = sortedClients[i];
            var cnt = clients[cli];
            var d = cli + ' ( ' + cnt + ' ) ';
            clientSelect.append($('<option>', {
                value: cli,
                text: d
            }));
        }

        clientSelect.val('WCLIENT');
    }

function SaveDelivary(){

    var target = document.getElementById('SPID').value;

    var link = "./WDeliver.php";

    // console.log(target);

    $.ajax({
        url: link,
        type: "POST", // Change the request type to POST
        dataType: "html",
        data: {
          TARGET: target, // Update with your desired value
        },
        success: function(data) {

            // alert(data);
            FilterUpdate();
        }
      });

}

    
</script>