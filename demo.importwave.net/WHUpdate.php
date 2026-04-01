<?php
include ("./sessionchk2.php");
include ("./refchk.php");

// Note: Ensure conn.php is included before using $conn
include ("./conn.php");

// Set timezone
date_default_timezone_set('Asia/Dhaka');

// Check for database connection
if (!isset($conn)) {
    http_response_code(500);
    die("Database connection error.");
}

// --- 1. Secure and Sanitize Input ---

// Sanitize all POST inputs using real_escape_string
$ShipID  = $conn->real_escape_string($_POST["ShipID"] ?? '');
$ItemVal = $conn->real_escape_string($_POST["ItemVal"] ?? '');

// --- 2. Execute Secured UPDATE Query (Prepared Statement) ---

$sql_update = "UPDATE `ShipmentLog` SET `WAREHOUSE` = ? WHERE `SHIPMENT` = ?";

$stmt_update = $conn->prepare($sql_update);
if ($stmt_update === false) {
    http_response_code(500);
    die("Error preparing statement: " . $conn->error);
}

// Bind parameters: 'ss' for string, string
$stmt_update->bind_param("ss", 
    $ItemVal, // Value to set
    $ShipID   // Condition
);

$result0 = $stmt_update->execute();
$stmt_update->close();


// --- 3. Final Output ---
if ($result0) {
    // Check if any rows were affected
    if ($conn->affected_rows > 0) {
        echo "Shipment $ShipID updated successfully.";
    } else {
        echo "Update successful, but no rows were affected (Shipment ID might not exist or WAREHOUSE value was the same).";
    }
} else {
    http_response_code(500);
    echo "Database error during update: " . $conn->error;
}

$conn->close();
?>