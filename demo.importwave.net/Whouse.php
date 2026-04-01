<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; 
?>

<script type="text/javascript">
    

function GetWhouse(WNAME) {

    // alert('Under Construction!!!');
    // exit();

        var link = "WhouseInside.php";
        var tframe = "#HouseBox";

        $(tframe).html("<div style='border:0px solid red; height:80px; width:80px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");

           var arr = {};
                        arr["WNAME"]  = WNAME;

                        
                        
                        $.ajax({ url:link, type:"POST", data: arr, cache: false, success:function(data){
                              
                                    $(tframe).html(data);
                              
                        }});

}


</script>

<?php 



        include ("./conn.php");
    
    $sql = "SELECT  
                x.`WHNAME`, 
                COALESCE(y.`SCOUNT`, 0) AS SCOUNT, 
                COALESCE(y.`PCOUNT`, 0) AS PCOUNT 
            FROM `WareHouse` x
            LEFT JOIN (
                SELECT 
                    B.`WAREHOUSE`, 
                    COUNT(DISTINCT A.`SHIPMENT`) AS SCOUNT, 
                    COUNT(A.`SN`) AS PCOUNT 
                FROM `packinglist` A 
                JOIN `ShipmentLog` B ON A.`SHIPMENT` = B.`SHIPMENT` 
                WHERE A.`DELIVERY` = 'N' 
                GROUP BY B.`WAREHOUSE`
            ) y ON x.`WHNAME` = y.`WAREHOUSE`;
";
    
    $result = $conn->query($sql);
    
    $sn = 0;
?>


<div id="HouseBox" style="border: 0px solid gray;  width: 90%; margin: 0 auto; border-radius: 7px; margin-bottom: 30px; margin-left: 20px; height: auto; padding: 15px;">
    
    <div style="font-weight:bolder; font-size:20px; color: darkred; margin-top:20px; margin-left:20px; float: left;">Manage Warehouse :


 

    </div>

    <div style="border:0px solid red; width:augo; margin: 100px auto; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">

<?php





    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            $WHNAME = $row['WHNAME'];
            $SCOUNT = $row['SCOUNT'];
            $PCOUNT = $row['PCOUNT'];

            $sn = $sn + 1;

            ?>

   <div class="button-3" onclick="GetWhouse('<?php echo $WHNAME; ?>');">
        <span style="font-weight: bolder; font-size: 16px;"><?php echo $WHNAME; ?></span><br>
        <span style="font-size: 12px; margin-top: 5px;">
            SHIPMENT : <?php echo $SCOUNT; ?> <br>
            CTN : <?php echo $PCOUNT; ?>
        </span>
    </div>

            <?php

        }
    } else {
        echo "No Warehouse found.";
    }
    $conn->close();

?>
    


 
<!-- 
    <div class="button-3">
        <span style="font-weight: bolder; font-size: 16px;">WH-R19-H15</span><br>
        <span style="font-size: 12px;">
            SHIPMENT : 4 <br>
            CTN : 415
        </span>
    </div> -->
</div>




    <div style="min-width:40px; height: 30px; border: 0px solid blue; float: right; margin-right: 100px;">
                    <img title="Change Password" onclick="GetPage('ChangeP');" src='./images/cpass.png'
                             style='cursor: hand; width: 30px; height: 30px; margin-right: 20px;'>

                    <img title="Logout"  onclick="Logout();" src='./images/logout.png' 
                            style='cursor: hand; width: 30px;  height: 30px;'>

        
    </div>
    

</div>



<!-- HTML !-->



<style type="text/css">

/* CSS */
.button-3 {
    text-align: center;
  align-items: center;
  background-color: #FFFFFF;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: .25rem;
  box-shadow: rgba(0, 0, 0, 0.02) 0 1px 3px 0;
  box-sizing: border-box;
  color: rgba(0, 0, 0, 0.85);
  cursor: pointer;
  font-family: system-ui,-apple-system,system-ui,"Helvetica Neue",Helvetica,Arial,sans-serif;  
  justify-content: center;
  margin: 0;
  min-height: 3rem;
  padding: calc(.875rem - 1px) calc(1.5rem - 1px);
  position: relative;
  text-decoration: none;
  transition: all 250ms;
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
  vertical-align: baseline;
  width: 200px;
  height: 90px;
}

.button-3:hover,
.button-3:focus {
  border-color: rgba(0, 0, 0, 0.15);
  box-shadow: rgba(0, 0, 0, 0.1) 0 4px 12px;
  color: rgba(0, 0, 0, 0.65);
}

.button-3:hover {
  transform: translateY(-1px);
}

.button-3:active {
  background-color: #F0F0F1;
  border-color: rgba(0, 0, 0, 0.15);
  box-shadow: rgba(0, 0, 0, 0.06) 0 2px 4px;
  color: rgba(0, 0, 0, 0.65);
  transform: translateY(0);
}


</style>