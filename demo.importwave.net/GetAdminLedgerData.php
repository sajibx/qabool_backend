<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; 

// include ("./refchk.php");

?>
<?php



session_start();

if(!isset($_SESSION['user'])) {

    

	$array = array();

	

	echo $json_string = json_encode($array, JSON_INVALID_UTF8_IGNORE, JSON_PRETTY_PRINT);

    exit;

}


	require "./conn.php";



	function fnSqlExec($con, $sql, $parameter)

	{   

		$push = array();

		$result = $con->query($sql);

		if ($result->num_rows > 0) {

			while($row = $result->fetch_assoc()) {

      

	            $push[] = $row;

        	}

		}



		return $push;

	}




// money format latest
function moneyFormat($num) {                                                                   
    // Convert input to float and format to exactly 2 decimal places
    $formattedNum = number_format((float)$num, 2, '.', '');
    
    // Split into integer and decimal parts
    $parts = explode('.', $formattedNum);
    $integerPart = $parts[0];
    $decimalPart = '.' . $parts[1];

    // Handle negative numbers
    $isNegative = $integerPart[0] === '-';
    if ($isNegative) {
        $integerPart = substr($integerPart, 1); // Remove the negative sign
    }

    $explrestunits = "";
    if (strlen($integerPart) > 3) {
        $lastthree = substr($integerPart, -3);
        $restunits = substr($integerPart, 0, -3); // Extracts all but the last three digits
        $restunits = (strlen($restunits) % 2 == 1) ? "0" . $restunits : $restunits; // Add leading zero if needed
        $expunit = str_split($restunits, 2);
        for ($i = 0; $i < sizeof($expunit); $i++) {
            // Creates each of the 2's group and adds a comma
            if ($i == 0) {
                $explrestunits .= (int)$expunit[$i] . ","; // Remove leading zeros for first group
            } else {
                $explrestunits .= $expunit[$i] . ",";
            }
        }
        $thecash = $explrestunits . $lastthree . $decimalPart;
    } else {
        $thecash = $integerPart . $decimalPart;
    }

    return $isNegative ? '-' . $thecash : $thecash; // Add negative sign if needed
}






	if(isset($_GET["ClientName"]) && $_GET['ClientName'] != ''){

		$ClientName = $_GET['ClientName'];



	} else {



		exit();



	};



$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/App_Data/app.ini', true);

$LEDGERLIMIT   = $ini["GENERAL"]["LEDGERLIMIT"];


if($ClientName=='CNF'){

	$sql = "SELECT `SHIPMENT`, `CNFNAME`, `DATE`, `AWBNO`, `AMOUNT`, `TYPE`, `REMARKS`, `OUTSTANDING` FROM `AdminLedger` 
                 ORDER BY `sn` DESC LIMIT $LEDGERLIMIT";
}else{
	$sql = "SELECT `SHIPMENT`, `CNFNAME`, `DATE`, `AWBNO`, `AMOUNT`, `TYPE`, `REMARKS`, `OUTSTANDING` FROM `AdminLedger` 
		WHERE `CNFNAME` = '$ClientName'
		ORDER BY `sn` DESC LIMIT $LEDGERLIMIT";
}

	



	$params = array();

	$array = fnSqlExec($conn, $sql, $params);

	

	echo $json_string = json_encode($array, JSON_INVALID_UTF8_IGNORE, JSON_PRETTY_PRINT);





?>







