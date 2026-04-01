<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; include __DIR__ . "/conn.php"; 
?>
<?php
// include("./sessionchk2.php");
// include("./refchk.php");
// include("./conn.php");

date_default_timezone_set('Asia/Dhaka');

set_time_limit(0);

// Check session
session_start();
if (!isset($_SESSION['user'])) {
    echo "User session not found.<br>";
    exit;
}
$UID = $_SESSION['user'];
$ShipID = isset($_POST["ShipID"]) ? $_POST["ShipID"] : '';

// Function to log events
function logEvent($conn, $date, $user, $event, $comment) {
    $stmt = $conn->prepare("INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`) VALUES (NULL, ?, ?, ?, ?)");
    $stmt->bind_param("ssss", $date, $user, $event, $comment);
    if (!$stmt->execute()) {
        throw new Exception("Logging failed: " . $conn->error);
    }
    $stmt->close();
}

// Start transaction
$conn->begin_transaction();
try {
    // Get minimum SN
    $sqlm = "SELECT MIN(SN) MIN FROM `packinglist` WHERE `SHIPMENT` = ?";
    $stmt = $conn->prepare($sqlm);
    $stmt->bind_param("s", $ShipID);
    $stmt->execute();
    $resultm = $stmt->get_result();
    $row = $resultm->fetch_assoc();
    $STARTSN = $row['MIN'] ?? null;
    $stmt->close();
    // echo "Retrieved minimum SN.<br>";

    // Delete from packinglist_trash
    $sql1 = "DELETE FROM `packinglist_trash` WHERE `SHIPMENT` = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("s", $ShipID);
    if (!$stmt1->execute()) {
        throw new Exception("Error deleting trash: " . $conn->error);
    }
    $stmt1->close();
    echo "Trash deleted.<br>";

    // Delete from packinglist
    $sql = "DELETE FROM `packinglist` WHERE `SHIPMENT` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ShipID);
    if (!$stmt->execute()) {
        throw new Exception("Error deleting packinglist: " . $conn->error);
    }
    $stmt->close();
    echo "Packinglist deleted.<br>";

    // Delete from billledger
    $sql0 = "DELETE FROM `billledger` WHERE `SHIPMENT` = ?";
    $stmt0 = $conn->prepare($sql0);
    $stmt0->bind_param("s", $ShipID);
    if (!$stmt0->execute()) {
        throw new Exception("Error deleting bill ledger: " . $conn->error);
    }
    $stmt0->close();
    echo "Bill Ledger deleted.<br>";

    // Delete from last_shipment
    $sql2 = "DELETE FROM `last_shipment` WHERE `LAST_SHIPMENT` = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("s", $ShipID);
    if (!$stmt2->execute()) {
        throw new Exception("Error deleting last shipment: " . $conn->error);
    }
    $stmt2->close();
    echo "Last shipment deleted.<br>";

    // Delete from ShipmentLog
    $sql3 = "DELETE FROM `ShipmentLog` WHERE `SHIPMENT` = ?";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("s", $ShipID);
    if (!$stmt3->execute()) {
        throw new Exception("Error deleting shipment log: " . $conn->error);
    }
    $stmt3->close();
    echo "Shipment log deleted.<br>";

    // Log deletion event
    $date = date("Y-m-d H:i:s");
    $REMARKS = "Shipment ID: $ShipID | Start SN: $STARTSN";
    logEvent($conn, $date, $UID, 'Delete Shipment', $REMARKS);
    // echo "Log generated.<br>";

    // Clean up billledger entries with no matching packinglist
    $sql_clean = "DELETE b FROM billledger b LEFT JOIN packinglist p ON b.CLIENT = p.CLIENT WHERE p.CLIENT IS NULL";
    if (!$conn->query($sql_clean)) {
        throw new Exception("billledger cleanup failed: " . $conn->error);
    }
    logEvent($conn, $date, $UID, 'Cleanup', 'billledger cleanup completed.');
    echo "Billledger cleanup completed.<br>";

    // Clean up last_shipment entries with no matching packinglist
    $sql_clean1 = "DELETE FROM last_shipment WHERE NOT EXISTS (SELECT 1 FROM packinglist pl WHERE pl.CLIENT = last_shipment.CLIENT)";
    if (!$conn->query($sql_clean1)) {
        throw new Exception("last_shipment cleanup failed: " . $conn->error);
    }
    logEvent($conn, $date, $UID, 'Cleanup', 'last_shipment cleanup completed.');
    echo "Last shipment cleanup completed.<br>";

    // Create temporary table
    $sql_create = "CREATE TEMPORARY TABLE tmp_latest_shipment (
        CLIENT VARCHAR(255),
        SHIPMENT VARCHAR(255),
        PRIMARY KEY (CLIENT)
    )";
    if (!$conn->query($sql_create)) {
        throw new Exception("Failed to create temporary table: " . $conn->error);
    }

    // Insert into temporary table
    $sql_insert = "INSERT INTO tmp_latest_shipment (CLIENT, SHIPMENT)
                   SELECT A.CLIENT, A.SHIPMENT
                   FROM packinglist A
                   INNER JOIN (
                       SELECT CLIENT, MAX(SN) AS MAX_SN
                       FROM packinglist
                       WHERE STATUS <> 'Added'
                       GROUP BY CLIENT
                   ) B ON A.CLIENT = B.CLIENT AND A.SN = B.MAX_SN";
    if (!$conn->query($sql_insert)) {
        throw new Exception("Failed to insert into temporary table: " . $conn->error);
    }

    // Delete from last_shipment using temporary table
    $sql_delete = "DELETE ls
                   FROM last_shipment ls
                   LEFT JOIN tmp_latest_shipment lp ON ls.CLIENT = lp.CLIENT
                   WHERE lp.SHIPMENT IS NULL OR ls.LAST_SHIPMENT != lp.SHIPMENT";
    if (!$conn->query($sql_delete)) {
        throw new Exception("last_shipment deletion failed: " . $conn->error);
    }
    logEvent($conn, $date, $UID, 'Cleanup', 'last_shipment deletion completed.');
    echo "Last shipment deletion completed.<br>";

    // Insert into last_shipment using temporary table
    $sqlLS = "INSERT INTO `last_shipment` (`CLIENT`, `LAST_SHIPMENT`) 
              SELECT lp.CLIENT, lp.SHIPMENT
              FROM tmp_latest_shipment lp
              WHERE NOT EXISTS (
                  SELECT 1
                  FROM last_shipment ls
                  WHERE ls.CLIENT = lp.CLIENT
              )";
    if (!$conn->query($sqlLS)) {
        throw new Exception("last_shipment insertion failed: " . $conn->error);
    }
    logEvent($conn, $date, $UID, 'Cleanup', 'last_shipment insertion completed.');
    echo "Last shipment inserted.<br>";

    // Commit transaction
    $conn->commit();
    echo "All operations completed successfully.<br>";
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage() . "<br>";
    error_log("Transaction failed: " . $e->getMessage(), 3, "/var/log/php_errors.log");
} finally {
    $conn->query("DROP TEMPORARY TABLE IF EXISTS tmp_latest_shipment");
    $conn->close();
}
?>