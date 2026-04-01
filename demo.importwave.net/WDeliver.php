<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; include __DIR__ . "/conn.php"; 

date_default_timezone_set('Asia/Dhaka');

$TARGET = $_POST["TARGET"];

// Clean the target string of leading/trailing commas/zeros if they are causing issues
$TARGET = trim($TARGET, ',0'); 

$items = explode(',', $TARGET);
$items = array_map('intval', $items);
$total_count = count($items);

$batch_size = 150; 

$UID = $_SESSION['user'];

$del_date = date("Y-m-d");

// Process in batches
for ($i = 0; $i < $total_count; $i += $batch_size) {
    $batch = array_slice($items, $i, $batch_size); 
    $batch_count = count($batch); // CORRECT: This will be 1 for the last batch

    $batch_str = implode(',', $batch);

    // Reconstructing the specific log format you provided: '0,IDs,0'
    $log_batch_str = "0," . $batch_str . ",0"; 

    // $sql_val = "UPDATE `packinglist` SET `DELIVERY` = 'Y' AND  `DEL_USER` = '$UID' WHERE `SN` IN ($batch_str)";

    $sql_val = "UPDATE `packinglist` 
            SET `DELIVERY` = 'Y', `DEL_USER` = '$UID' , `DEL_DATE` = '$del_date'
            WHERE `SN` IN ($batch_str)";

    $conn->query($sql_val);

    $EVENT = "Delivery";
    // Using the CORRECT $batch_count (1 for the last batch)
    $REMARKS = "Item IDs: " .$batch_count.":".$log_batch_str; 
    $date = date("y-m-d H:i:s");

    $sql_log = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`)
                VALUES (NULL, '$date', '$UID', '$EVENT', '$REMARKS')";
    $conn->query($sql_log);
}
?>


