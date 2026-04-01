<?php
include ("./sessionchk2.php");
include ("./refchk.php");
include ("./conn.php");

$FClient            = $_POST["FClient"];
$TxTPayment         = $_POST["TxTPayment"];
$TxTDiscount        = $_POST["TxTDiscount"];
$TxTcommission      = $_POST["TxTcommission"];
$RMVAL              = substr($_POST['RMVAL'],0,40);

$UID                = $_SESSION['user'];

$RMVAL              = $RMVAL." | ".$UID;

$sql3 = "SELECT  SUM(`AMOUNT`) TOTAL FROM `billledger` WHERE `CLIENT` = '$FClient'";

    $result3 = $conn->query($sql3);

    $row3 = $result3->fetch_assoc();
    
    $Total3 = ($row3['TOTAL']);
    
    // $Total3 = $Total3 - $TxTPayment - $TxTDiscount + $TxTcommission;
    

date_default_timezone_set('Asia/Dhaka');

$date = date("y-m-d");

if($TxTPayment > 0){

            $Total3 = $Total3 - $TxTPayment;
    
            $value = $TxTPayment * (-1);
    
            $sql = "INSERT INTO `billledger` (`sn`, `SHIPMENT`, `CLIENT`, `CHARG_WEIGHT`, `DATE`,  `AMOUNT`, `TYPE`, `REMARKS`, `OUTSTANDING`) VALUES 
                                (NULL, '', '$FClient', '0', '$date', '$value', 'PAYMENT', '$RMVAL', '$Total3');";
            $result = $conn->query($sql);
            
}

if($TxTDiscount > 0){


    $Total3 = $Total3 - $TxTDiscount;

                $value = $TxTDiscount * (-1);

                $sql1 = "INSERT INTO `billledger` (`sn`, `SHIPMENT`, `CLIENT`, `CHARG_WEIGHT`, `DATE`,  `AMOUNT`, `TYPE`, `REMARKS`, `OUTSTANDING`) VALUES 
                                        (NULL, '', '$FClient', '0', '$date', '$value', 'DISCOUNT', '$RMVAL', '$Total3');";
                    $result = $conn->query($sql1);
            }

if($TxTcommission > 0){

    $Total3 = $Total3 + $TxTcommission;

                $value = $TxTcommission;

                $sql1 = "INSERT INTO `billledger` (`sn`, `SHIPMENT`, `CLIENT`, `CHARG_WEIGHT`, `DATE`,  `AMOUNT`, `TYPE`, `REMARKS`, `OUTSTANDING`) VALUES 
                                        (NULL, '', '$FClient', '0', '$date', '$value', 'Commission', '$RMVAL', '$Total3');";
                    $result = $conn->query($sql1);
            }


            // generate log ----------------------------------------------------
            
            $EVENT      = "Payment Entry"; 
            $REMARKS    = "Client :".$FClient." | Payment : ".$TxTPayment." | Discount : ".$TxTDiscount." | Commission : ".$TxTcommission; 
            $UID        = $_SESSION['user']; 
            $date = date("y-m-d H:i:s");
            
            $sql_log    = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`)
                                VALUES (NULL, '$date', '$UID', '$EVENT', '$REMARKS');";
            
            $result_log = $conn->query($sql_log);
            
            // generate log ----------------------------------------------------
            
echo "New Entry complete.";


 ?>

