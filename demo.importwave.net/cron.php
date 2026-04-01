<?php
// Set timezone to Asia/Dhaka
date_default_timezone_set("Asia/Dhaka");
$date = date("y-m-d H:i:s");

// Include database connection
include ("./conn.php");

// Set reasonable time limit for cron job
set_time_limit(300);

// Check server timezone for debugging
if (date_default_timezone_get() !== 'Asia/Dhaka') {
    error_log("Server timezone is " . date_default_timezone_get() . ", expected Asia/Dhaka", 1, "");
}

// Function to log events
function logEvent($conn, $date, $user, $event, $comment) {
    $stmt = $conn->prepare("INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`) VALUES (NULL, ?, ?, ?, ?)");
    $stmt->bind_param("ssss", $date, $user, $event, $comment);
    $result = $stmt->execute();
    if (!$result) {
        error_log("Logging failed: " . $conn->error, 1, "");
    }
    $stmt->close();
    return $result;
}

// Start transaction for database operations (MySQL 5.7.23-23, packinglist table verified)
$conn->begin_transaction();
try {
    // Clean up billledger entries with no matching packinglist
    $sql_clean = "
        DELETE b
        FROM billledger b
        LEFT JOIN packinglist p
        ON b.CLIENT = p.CLIENT
        WHERE p.CLIENT IS NULL;
    ";
    $result_clean = $conn->query($sql_clean);
    if (!$result_clean) {
        throw new Exception("billledger cleanup failed: " . $conn->error);

        logEvent($conn, $date, 'cron', 'cron', 'billledger cleanup failed.');

    }


    $sql_clean1 = "
            DELETE FROM last_shipment
            WHERE NOT EXISTS (
                SELECT 1
                FROM packinglist pl
                WHERE pl.CLIENT = last_shipment.CLIENT);
    ";
    $result_clean1 = $conn->query($sql_clean1);
    if (!$result_clean1) {
        throw new Exception("LASTSHIP cleanup failed: " . $conn->error);
        logEvent($conn, $date, 'cron', 'cron', 'LASTSHIP cleanup failed.');

    }


        $sqlLD = "
        CREATE TEMPORARY TABLE tmp_latest_shipment (
            CLIENT VARCHAR(255),
            SHIPMENT VARCHAR(255),
            PRIMARY KEY (CLIENT)
        );

        INSERT INTO tmp_latest_shipment (CLIENT, SHIPMENT)
        SELECT A.CLIENT, A.SHIPMENT
        FROM packinglist A
        INNER JOIN (
            SELECT CLIENT, MAX(SN) AS MAX_SN
            FROM packinglist
            WHERE STATUS <> 'Added'
            GROUP BY CLIENT
        ) B ON A.CLIENT = B.CLIENT AND A.SN = B.MAX_SN;

        DELETE ls
        FROM last_shipment ls
        LEFT JOIN tmp_latest_shipment lp ON ls.CLIENT = lp.CLIENT
        WHERE lp.SHIPMENT IS NULL OR ls.LAST_SHIPMENT != lp.SHIPMENT;

        DROP TEMPORARY TABLE tmp_latest_shipment;
    ";


    $resultLD = $conn->multi_query($sqlLD);
    if (!$resultLD) {
        throw new Exception("last_shipment deletion failed: " . $conn->error);

        logEvent($conn, $date, 'cron', 'cron', 'last_shipment deletion failed.');

    }
    do {
        $conn->next_result(); // Clear results from multi_query
    } while ($conn->more_results());
    // logEvent($conn, $date, 'cron', 'cron', 'last_shipment deletion completed.');


    $sqlLS = "
        INSERT INTO `last_shipment` (`CLIENT`, `LAST_SHIPMENT`) 
        SELECT A.CLIENT, A.`SHIPMENT`
        FROM packinglist A
        LEFT JOIN (
            SELECT CLIENT, MAX(SN) AS MAX_SN
            FROM packinglist
            WHERE STATUS <> 'Added'
            GROUP BY CLIENT
        ) B ON A.CLIENT = B.CLIENT
        WHERE A.SN = B.MAX_SN
        AND NOT EXISTS (
            SELECT 1
            FROM last_shipment C
            WHERE C.CLIENT = A.CLIENT
        );
    ";
    $resultLS = $conn->query($sqlLS);
    if (!$resultLS) {
        throw new Exception("last_shipment insertion failed: " . $conn->error);
        logEvent($conn, $date, 'cron', 'cron', 'last_shipment insertion failed.');
    }


    // Commit transaction
    $conn->commit();

    // Log operations using original $REMARKS and $EVENT
    $REMARKS = "cron job : Last Shipment Refresh Complete.";
    $EVENT = "cron";
    logEvent($conn, $date, 'cron', $EVENT, $REMARKS);

    $REMARKS = "cron job : Cron Job Complete.";
    $EVENT = "cron";
    logEvent($conn, $date, 'cron', $EVENT, $REMARKS);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    // Log error using original $EVENT
    $EVENT = "cron";
    logEvent($conn, $date, 'cron', $EVENT, 'Cron job failed: ' . $e->getMessage());
    // Send email notification
    error_log("Cron job failed: " . $e->getMessage(), 1, "");
    echo "cron job complete.".getMessage();
    exit(1);
}

// Close database connection
$conn->close();

// echo "cron job complete.";
?>