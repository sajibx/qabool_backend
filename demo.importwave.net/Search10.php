<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; 

// include ("./refchk.php");

require "./conn.php";

    $SKEY = $_POST['SKEY'];

    // $sql = "SELECT `SHIPMENT` FROM `ShipmentLog`

    //  WHERE `SHIPMENT` LIKE '%$SKEY%'

    //  ORDER BY `DATE` DESC , `SN` DESC

    // LIMIT 12 OFFSET 0;";


$sql = "SELECT `SHIPMENT` FROM `ShipmentLog`
        WHERE `SHIPMENT` LIKE CONCAT('%', ?, '%')
        ORDER BY `DATE` DESC , `SN` DESC
        LIMIT 12 OFFSET 0";


// exit();

    

    // $result = $conn->query($sql);
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $SKEY);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();


// $stmt1 = $conn->prepare("SELECT Count(SHIPMENT) AS COUNT FROM ShipmentLog WHERE `SHIPMENT` LIKE '%$SKEY%';");
//             $stmt1->execute();
$stmt1 = $conn->prepare("SELECT Count(SHIPMENT) AS COUNT
                         FROM ShipmentLog
                         WHERE `SHIPMENT` LIKE CONCAT('%', ?, '%')");
$stmt1->bind_param('s', $SKEY);
$stmt1->execute();

            $result1 = $stmt1->get_result();
            $row1 = $result1->fetch_assoc();
            $cnt = $row1['COUNT'];
            $Ncnt = $cnt-11;
            if($Ncnt<=0){$Ncnt=0;}




    $i = 0;
    $sn = 0;
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $value1 = $row['SHIPMENT'];
            $sn = $sn+1;
            $BtnFace = $row['SHIPMENT'];
            //   
            ?>  
                <button id="<?php echo $sn; ?>" value = "<?php echo $value1; ?>" class="button-6" style="margin-left: 10px; margin-top: 5px;" 
                                                                            onclick="getShipment(<?php echo $sn; ?>);">  <?php echo $BtnFace ?> </button>
                <?php 
        }

    } 
    else {
            echo "No Shipment Found.";

    }
    $conn->close();
    ?>
        <div style="border: 0px solid red; width:100%; margin-top:20px; float:left; text-align:right; bottom:0; font-size: 15;">Showing : <?php echo $cnt."-".$Ncnt; ?></div>