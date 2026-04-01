<?php
include ("./sessionchk2.php");
include ("./refchk.php");
require "./conn.php";

    error_reporting(0);


function highlightMatchedKeyword($inputValue, $clientName) {
  return preg_replace("/$inputValue/i", "<span style='color: red;'>$0</span>", $clientName);
}

	$SKEY = $_POST['SKEY'];

	$sql = "SELECT `SHIPMENT`, `CLIENT`, `SHIPPING_MARK` FROM `packinglist`
                WHERE `SHIPPING_MARK` LIKE '%$SKEY%' OR `CTN_NO` LIKE '%$SKEY%'
                GROUP BY `SHIPMENT`, `CLIENT`, `SHIPPING_MARK`
                ORDER BY STR_TO_DATE(LEFT(`SHIPMENT`, 4), '%m%y') DESC;";

    // $sql = "SELECT DISTINCT `SHIPMENT`, DISTINCT `CLIENT`, DISTINCT `SHIPPING_MARK`
    //             FROM `packinglist`
    //             WHERE `SHIPPING_MARK` LIKE '%$SKEY%' 
    //                OR `CTN_NO` LIKE '%$SKEY%';";

    

    $result = $conn->query($sql);
    
    ?>
<style>
  table {
    border-collapse: collapse;
    text-align:left;
  }
</style>
 
    <table width="100%">
        <tr>
            <th>Shipment</th>
            <th>Client</th>
            <th>Shipping Mark</th>
        </tr>

    
    <?php
        
    if ($result->num_rows > 0) {
        
        while($row = $result->fetch_assoc()) {
            
            $SHIPMENTS       = $row["SHIPMENT"];
            $CLIENT          = $row["CLIENT"];
            $SHIPPING_MARK   = highlightMatchedKeyword($SKEY, $row["SHIPPING_MARK"]);
            
            ?>
            
            <tr>
                <!-- <td><?php echo $SHIPMENTS; ?></td> -->
                <td>
                    <a href="javascript:void(0);" 
                       onclick="getShipment_S('<?php echo $SHIPMENTS; ?>')"
                       >
                        <?php echo $SHIPMENTS; ?>
                    </a>
                </td>
                <td><?php echo $CLIENT; ?></td>
                <td><?php echo $SHIPPING_MARK; ?></td>
            </tr>
            
            <?php
            
        }
        
        
    }
	
?>

</table>


<script>
    

function getShipment_S(shipment) {

  var link = "./shipments.php?shipID=" + shipment;

  var tframe = "#main_body";

  // Display a loading message while fetching shipment data

  $(tframe).html("<div style='border:0px solid red; height:100px; width:100px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");


  $.ajax({

    url: link,

    type: "GET",

    dataType: "html",

    success: function (data) {

      // Display the fetched shipment data

      $(tframe).empty().append(data);

    }

  });

}

</script>