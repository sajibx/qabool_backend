<?php 
// error_reporting(false);

$title = "Uploading data...";

include ("../conn.php");

date_default_timezone_set('Asia/Dhaka');
//$result = $conn->query("TRUNCATE TABLE `packinglist_temp`");
// $result = $conn->query("TRUNCATE TABLE `pr0matrad3`.`packinglist`");

if (!$conn -> query("DELETE FROM `packinglist_temp` WHERE 1")) 
{
  echo("Error description: " . $conn -> error);
 }


?>
<!DOCTYPE html>
<html>
<title><?php echo $title ?></title>
<head>
<link rel="stylesheet" type="text/css" href="stylev2.css">
</head>
<div class="banner">
    <?php echo $title ?>
</div>

<div >

<div style="height:150px; border: 0px solid red; width: 200px;">
</div>

<div style="width:500px;background-color: white;opacity: 0.85; min-height:60px; border:1px solid #0099CC; margin: 0 auto; text-align:center;">

<?PHP
////////////////////////////////progress bar code //////////////////////////////////  
$guid           = $_POST["guid"];

$stmt = $conn->prepare("SELECT `user`, `TIME` FROM `guid` WHERE `guid` = ?");
$stmt->bind_param("s", $guid); // Bind the parameter

$stmt->execute(); // Execute the statement
$result = $stmt->get_result(); // Get the result

$row = $result->fetch_assoc();

if ($row === NULL) {
    
    echo "link is not valid.";
    exit();
}

date_default_timezone_set('Asia/Dhaka');
$TIMENOW       = date("Y-m-d H:i:s");
$TIME          = ($row['TIME']);


$date1 = new DateTime($TIMENOW);
$date2 = new DateTime($TIME);

$UIDX = $row['user'];



$min   = 20;

$interval = $date1->diff($date2);
$diffInMinutes = $interval->i + ($interval->h * 60) + ($interval->days * 24 * 60);

// echo "The difference is $diffInHours hours.";

if($diffInMinutes > $min){
    die("Link Expired.");
}


$datetime1 = new DateTime(); 
echo '<div id="progress" style="width:500px;border:0px solid #ccc; text-align: center;"></div>

<div id="information" style="border: 0px solid blue; color:#0099CC;"></div>';

////////////////////////////////progress bar code //////////////////////////////////

////////////////////////////////File uploading process ////////////////////////////////// 
$file = $_FILES['uploadedfile'];

$target_path_dir = "temp/";

$SHIPID = "";

$OldCTN = "AA";

$sql_1 = "";			   
$target_path     = $target_path_dir . basename($_FILES['uploadedfile']['name']);
if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
    chmod("$target_path_dir", 0777);
    $file_handle = fopen("$target_path", "r");
    $row         = 0;
    $count = count(file($target_path));
    $end_c = $count+1;
    
    
    // start while loop
    while (!feof($file_handle)) {
        $line_of_text = fgetcsv($file_handle);
        $row++;
 ///////////////////////////////data processing starts here//////////////////////// 
        if ($row < $end_c) {  


////////////////////////////////progress bar code starts here/////////////////////////  

                $percent = number_format((($row + 1) / $end_c * 100), 0) . "%";
                
                echo '
<script language="javascript">document.getElementById("progress").innerHTML=
"<div style=\"text-align:center; color:white;width:' . $percent . ';background-color:#0099CC;\">' . $percent . '</div>";
document.getElementById("information").innerHTML="' . ($row + 1) . ' / ' . $end_c . ' rows processed.";
</script>';

                ob_flush();
                flush();
                
                // Sleep one second so we can see the delay
                //usleep(1000000*1);             
                
 ///////////////////////////////progress bar code ends here////////////////////////       

                            $CTN                = 1;

                            if($row == 1){
                                    $SHIPID = $line_of_text[ 0 ];
                                    $SHIPMENT = $SHIPID;
                                }
                                else{
                                    $SHIPMENT = $SHIPID;
                                }
                                
                            // $SHIPID             = $line_of_text[ 0 ];
                            // $SHIPMENT           = $SHIPID;

                            $CTN_NO             = $line_of_text[ 1 ];  
							if($OldCTN == $CTN_NO){
                                // $CTN = 0;
                            }

                            $OldCTN = $CTN_NO;					   
                            
							$CLIENT             = $conn->escape_string($line_of_text[ 2 ]);
                            $SHIPPING_MARK      = $conn->escape_string($line_of_text[ 3 ]);
                            $ITEM_LIST          = $conn->escape_string($line_of_text[ 4 ]);

                            $WEIGHT             = $line_of_text[ 5];
                            
                            // $PWT                = $line_of_text[ 6];
                            // $UNIT_PRICE         = $line_of_text[ 7];
                            

                            
                            $PWT                = number_format((float)$line_of_text[ 6], 2, '.', '');
		                    $UNIT_PRICE         = number_format((float)$line_of_text[ 7], 2, '.', '');

		                  //  $TOTAL_AMOUNT       = number_format((float)$row['TOTAL_AMOUNT'], 2, '.', '');
		                    
                            $TOTAL_AMOUNT       = ($PWT*$UNIT_PRICE);
	                       
                            // $SHIPPING_MARK      = preg_replace('/\s+|[[:^print:]]/', '', trim($SHIPPING_MARK));

                            $CLIENT             = trim($CLIENT);

							$SHIPMENT           = preg_replace('/\s+|[[:^print:]]/', '', trim($SHIPMENT));
                            $SHIPMENT           = $conn->escape_string($SHIPMENT);
                            
//   echo "</br>".                           

$sql_1 = "INSERT INTO `packinglist_temp` 
(`SN`, `SHIPMENT`, `CTN_NO`, `CTN`, `P.WT`, `CLIENT`, `SHIPPING_MARK`, `ITEM_LIST`, `WEIGHT`, `UNIT_PRICE`, `TOTAL_AMOUNT`, `STATUS`) VALUES 
(NULL, '$SHIPMENT', '$CTN_NO', '$CTN', '$PWT', '$CLIENT', '$SHIPPING_MARK', '$ITEM_LIST', '$WEIGHT', '$UNIT_PRICE', $PWT*$UNIT_PRICE, 'N');";

// echo "</br>".$sql_1."</br>";

$result = $conn->query($sql_1);            

 ///////////////////////////////data processing ends here////////////////////////
        } //$row > 13 & $row < $end_c  | $row < $end_c        
    } //!feof( $file_handle ) end of while loop
    fclose($file_handle);
    unlink($target_path); //file closing
 $sql_chk = "SELECT COUNT(*) CNT FROM `packinglist_temp`";       
                
$result = $conn->query($sql_chk);   

$row = $result->fetch_assoc();

echo $value1 = $row['CNT'];

$delBlank = 'DELETE FROM `packinglist_temp` WHERE `CLIENT` = ""';

$DelResult = $conn->query($delBlank); 

// $delBlank = 'DELETE FROM `packinglist_temp` WHERE `SHIPMENT` = "SHIPMENT"';

// $DelResult = $conn->query($delBlank); 

$sql_chk = "SELECT COUNT(*) CNT FROM `packinglist` WHERE `SHIPMENT` = (
SELECT  DISTINCT(`SHIPMENT`) SHIPMENT FROM `packinglist_temp`)";       
                
$result = $conn->query($sql_chk);   

$row = $result->fetch_assoc();

$value1 = $row['CNT'];

if($value1 > 0){
    
        echo "<span style='color:red';></br>This File has Already been loaded. </br> Please delete old one before upload again.
        </br>File not uploaded.</span></br></br>";
    }
else{

    $sql_copy = "INSERT INTO `packinglist`
                    SELECT 'NULL', `SHIPMENT`, `CTN_NO`, `CTN`, `P.WT`, `CLIENT`, `SHIPPING_MARK`, `ITEM_LIST`, `WEIGHT`, `UNIT_PRICE`, `TOTAL_AMOUNT`, `STATUS` FROM `packinglist_temp`";

    $result = $conn->query($sql_copy);  


    $sql_lstShip     = "DELETE FROM`last_shipment`
                        WHERE `CLIENT` IN (SELECT `CLIENT` FROM `packinglist_temp` GROUP BY `CLIENT`);" ;

    $result = $conn->query($sql_lstShip);  


    $sql_lstShip     = "INSERT INTO `last_shipment`
                        SELECT `CLIENT`,`SHIPMENT` FROM `packinglist_temp`
                        GROUP BY `CLIENT`,`SHIPMENT`;" ;

    $result = $conn->query($sql_lstShip);  
    
    
    $datexx = date("y-m-d");
    
    
    $sql_s1     = "DELETE FROM `ShipmentLog` WHERE `SHIPMENT` = '$SHIPID';";

    $results1 = $conn->query($sql_s1);  
    
    $sql_s2     = "INSERT INTO `ShipmentLog` (`SN`, `SHIPMENT`, `DATE`) VALUES (NULL, '$SHIPID', '$datexx');";

    $results2 = $conn->query($sql_s2);  

    $sql_cg = "DELETE FROM guid WHERE `TIME` < NOW() - INTERVAL 4 DAY;";

    $resultg = $conn->query($sql_cg);

     // generate log ----------------------------------------------------
     date_default_timezone_set('Asia/Dhaka');

     $UID        = $UIDX; 
     $EVENT      = "Upload"; 
     $REMARKS    = $SHIPID; 
     
     $date       = date("y-m-d H:i:s");
     
     $sql_log    = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`)
                         VALUES (NULL, '$date', '$UID', '$EVENT', '$REMARKS');";
     
     $result_log = $conn->query($sql_log);


}






///////////////////////////////All processing complete////////////////////////
echo "</br><div style='color : green;'>processing complete</div>";
$datetime2 = new DateTime();
$interval = $datetime1->diff($datetime2);
//%y years %m months %a days 
$elapsed = $interval->format('%h hours %i minutes %S seconds');
$elapsed = str_replace("0 hours","",$elapsed);
// $elapsed = str_replace("0 minutes","",$elapsed);

echo "</br>Total processing time : ".$elapsed;    
    /*end middle */
} //move_uploaded_file( $_FILES[ 'uploadedfile' ][ 'tmp_name' ], $target_path )
else {
    echo "There was an error uploading the file, please try again!<br>";
    echo "Please hit backspace to go back.";
    exit;
}
?>

</div>
<style>
.footer {
    position: fixed; 
    bottom: 0;
    left: 0;
    right: 0;
    height: 30px;
    width: 100%;
    background-color: #0099CC;
    font: 'Calibri';
    color: white;
    text-align: center;
    padding-top: 10px;
    font-size: 16px;
    opacity: 0.80;
}
</style>
<div class="footer">&copy Analytics & Reporting - Resource Management</div>
</div>