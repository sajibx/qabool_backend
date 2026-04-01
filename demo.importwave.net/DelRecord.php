<?php
include ("./sessionchk2.php");
include ("./refchk.php");
include ("./conn.php");

?>



<style type="text/css">

/* CSS */

.button-4 {
  
  width: 40px;
  height: 35px;
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
  font-size: 16px;
  font-weight: 600;
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

.button-4:hover,
.button-4:focus {
  border-color: rgba(0, 0, 0, 0.15);
  box-shadow: rgba(0, 0, 0, 0.1) 0 4px 12px;
  color: rgba(0, 0, 0, 0.65);
}

.button-4:hover {
  transform: translateY(-1px);
}

.button-4:active {
  background-color: #F0F0F1;
  border-color: rgba(0, 0, 0, 0.15);
  box-shadow: rgba(0, 0, 0, 0.06) 0 2px 4px;
  color: rgba(0, 0, 0, 0.65);
  transform: translateY(0);
}


</style>



<div style="margin: 20px;">
    
    Delete Request :

<?php

$sql = "
    SELECT `sn`, `date`, `type`, `client`, `amount`, `user`, `approve` FROM `delete_aprv` WHERE `approve` = 'N' ORDER BY `date` DESC;
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result(); // if no mysqlnd, use bind_result/fetch
$stmt->close();

// $result = $conn->query($sql);



$dataView = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $dataView[] = $row;
    }
}




?>

<table style="padding: 0px; width:100%;text-align: center; font-size: 13px; font-family: Calibri, sans-serif; " id="">
    <tr>
        <th style="background-color: #c0d5ff;">TYPE</th>
        <th style="background-color: #c0d5ff;">CLIENT</th>
        <th style="background-color: #c0d5ff;">AMOUNT</th>
        <th style="background-color: #c0d5ff;">REQ BY</th>
        <th style="background-color: #c0d5ff;">APPROVE (Y/N)</th>

    </tr>


    <?php


        foreach ($dataView as $row) {
            
            // `sn`, `date`, `type`, `shipment`, `client`, `amount`, `user`, `approve`

            $SN         = $row['sn'];
            $date       = $row['date'];
            $type       = $row['type'];
            $client     = $row['client'];
            $amount     = $row['amount'];
            $user       = $row['user'];
    ?>

        <tr>
            <td><?php echo $type; ?></td>
            <td><?php echo $client; ?></td>
            <td><?php echo $amount; ?></td>
            <td><?php echo $user; ?></td>
            <td>
                <button onclick="DeleteR('<?php echo $SN; ?>','Y');" class="button-4" id="">Yes</button>
                <button onclick="DeleteR('<?php echo $SN; ?>','N');" class="button-4" id="">No</button>


            </td>

        </tr>

       <?php } ?>

</table>


</div>

<script type="text/javascript">


    function DeleteR(sn,type) {


            var link    = "./DelRecordLedger.php";
            
            var arr = {};
                        arr["sn"]       = sn;
                        arr["type"]     = type;
                       
                          
                          $.ajax({ url:link,
                                  type:"POST",
                                  data: arr,
                                  cache: false,
                                  success:function(data){
                                    //   $(tframe).html("Ledger Update complete.");
                                    GetPage3('DelRecord', 'BotBox');

                                  } });

    }


</script>