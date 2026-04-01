<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; 

// include ("./refchk.php");
include ("./conn.php");



$EVENT      = $_POST["EVENT"]; 

$REMARKS    = "XXX".$_POST["REMARKS"]; 

$UID        = $_POST["UID"]; 





// Function to create a log entry in the database

function createLog($EVENT, $UID, $REMARKS) {

    // Connect to the MySQL database



    // Check for connection errors

    // if ($conn->connect_error) {

    //     die("Connection failed: " . $conn->connect_error);

    // }

    

    // Get the current time in Asia/Dhaka timezone

    date_default_timezone_set('Asia/Dhaka');

    $timestamp = date('Y-m-d H:i:s');

    

    // Convert the timestamp to 24-hour format

    $timestamp = date("Y-m-d H:i:s", strtotime($timestamp));

    

    // Prepare the SQL statement

    $stmt = $conn->prepare("INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`)

                                                VALUES (?, ?, ?, ?, ?)");

                                        

    ("INSERT INTO logs (event_type, $userName, timestamp) VALUES (?, ?, ?, ?, ?)");                                   

                                        

                                        

    $stmt->bind_param(NULL,$timestamp,$UID, $EVENT, $REMARKS );

    

    // Execute the SQL statement

    $stmt->execute();

    

    // Close the statement and connection

    $stmt->close();

    $conn->close();

}



// Create a log entry

createLog($eventType, $userName);

?>









<!------------------------------------------------------------------------------------------------------------->

