<?php
include ("./sessionchk2.php");
include ("./refchk.php");
include ("./conn.php");

$TxTName            = $_POST["TxTName"];
$TxTAmount 	        = $_POST["TxTAmount"];
$TxTMethod          = $_POST["TxTMethod"];
$RMVAL              = substr($_POST['TxtRem'],0,40);

$UID                = $_SESSION['user'];

$Type               = $_POST["TYPE"];

$RT                 = $_POST["RTYPE"];

date_default_timezone_set('Asia/Dhaka');
$date = date("y-m-d");

$sql1 = "SELECT `CashIH`
FROM `Daily3xp_cn`
WHERE `sn` = (SELECT MAX(`sn`) FROM `Daily3xp_cn`)";

	$result1 = $conn->query($sql1);

    $row1 = $result1->fetch_assoc();
    
    $CashIH = $row1['CashIH'];

if($Type == 'Rec'){	
    
    $BANK = 0;
    
	$CashIH = $CashIH+$TxTAmount;
	
    if($RT == "BANK"){
        
        $BANK = 1;
        
        $CashIH = $CashIH-$TxTAmount;
        
        // $TxTName = $TxTName." &#127974";
        
        $sql4 = "INSERT INTO `Daily3xp_cn` (`sn`, `date`, `type`, `name`, `pay_method`, `amount`, `CashIH`, `remarks`, `user`, `BANK`) 
        VALUES (NULL, '$date', 'Exp', '$TxTName', '$TxTMethod', '$TxTAmount', '$CashIH', '$RMVAL', '$UID', $BANK);";
        
        $result4 = $conn->query($sql4);
    }
    
    $sql3 = "INSERT INTO `Daily3xp_cn` (`sn`, `date`, `type`, `name`, `pay_method`, `amount`, `CashIH`, `remarks`, `user`, `BANK`) 
    VALUES (NULL, '$date', '$Type', '$TxTName', '$TxTMethod', '$TxTAmount', '$CashIH', '$RMVAL', '$UID', $BANK);";
    
    
    $result3 = $conn->query($sql3);
    
}
else{
    $CashIH = $CashIH-$TxTAmount;
    
    $sql3 = "INSERT INTO `Daily3xp_cn` (`sn`, `date`, `type`, `name`, `pay_method`, `amount`, `CashIH`, `remarks`, `user`, `BANK`) 
    VALUES (NULL, '$date', '$Type', '$TxTName', '$TxTMethod', '$TxTAmount', '$CashIH', '$RMVAL', '$UID', '0');";
    
    $result3 = $conn->query($sql3);
}


	
	// generate log ----------------------------------------------------

            $EVENT      = $Type." Entry_cn"; 
            $REMARKS    = "Type :".$Type." | Name :".$TxTName." | Method :".$TxTMethod." | Amount :".$TxTAmount; 
            $UID        = $_SESSION['user']; 
            $date = date("y-m-d H:i:s");
            
            $sql_log    = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`)
                                VALUES (NULL, '$date', '$UID', '$EVENT', '$REMARKS');";
            
            $result_log = $conn->query($sql_log);
            
    // generate log ----------------------------------------------------
	
	echo "Save Complete.";



 ?>

