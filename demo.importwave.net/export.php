<?php
include ("./sessionchk2.php");

    // include ("./refchk.php");

?>



<?php







// include ("./conn.php");



$FClient           = $_REQUEST["FClient"];

$Fitem 		       = $_REQUEST["Fitem"];

$Fmark 		       = $_REQUEST["Fmark"];

$ShipID            = $_REQUEST["ShipID"];









$sql = "SELECT `SHIPMENT`, `CTN_NO`, `CTN`,`WEIGHT`,`P.WT`, `CLIENT`, `SHIPPING_MARK`, `ITEM_LIST`, `UNIT_PRICE`,`TOTAL_AMOUNT` FROM `packinglist` 

	WHERE `SHIPMENT` = '$ShipID' AND `WEIGHT` <> 0";



// $sql1 = $sql;



if ($FClient<>"CLIENT") {

		

	$temp = " AND `CLIENT` = '$FClient'";



	$sql = $sql.$temp;

}



if ($Fitem<>"ITEM") {



	$temp = " AND `ITEM_LIST` = '$Fitem'";



	$sql = $sql.$temp;

}



if ($Fmark<>"MARK") {



	$temp = " AND `SHIPPING_MARK` = '$Fmark'";



	$sql = $sql.$temp;

}



echo $sql = $sql." ORDER BY `CLIENT` ASC, `CTN_NO` ASC";



// date_default_timezone_set('Australia/Melbourne');echo $date = date('m/d/Y h:i:s a', time());



// exit();

// -------------------------------------------------------------------------------------------------------------------------------------------



$DB_Server      = "localhost";

$DB_Username    = "promatra_rep0rtA";

$DB_Password    = "[F+ay1A8BwHZ";

$DB_DBName      = "promatra_pr0matrad3";



$result = mysql_query($sql) or die('Query failed!');



$filename = "Packing_List_"; 



header("Content-Type: application/xls");    

header("Content-Disposition: attachment; filename=filename.xls");  

header("Pragma: no-cache"); 

header("Expires: 0");





echo '<table border="1">';

//make the column headers what you want in whatever order you want

echo '<tr><th>SHIPMENT</th><th>CTN_NO</th><th>CLIENT</th></tr>';

//loop the query data to the table in same order as the headers

while ($row = mysqli_fetch_assoc($result)){

    echo "<tr><td>".$row['SHIPMENT']."</td><td>".$row['CTN_NO']."</td><td>".$row['CLIENT']."</td></tr>";

}

echo '</table>';





// `SHIPMENT`, `CTN_NO`, `CTN`,`WEIGHT`,`P.WT`, `CLIENT`, `SHIPPING_MARK`, `ITEM_LIST`, `UNIT_PRICE`,`TOTAL_AMOUNT`



// -------------------------------------------------------------------------------------------------------------------------------------------



?>

