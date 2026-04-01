<?php
include ("./sessionchk2.php");
include ("./refchk.php");
require "./conn.php"; // Assumes this file establishes a $conn mysqli connection

/**
 * Formats a number into Indian currency style (lakhs/crores) with commas 
 * and exactly two decimal places.
 * @param mixed $num The number to format.
 * @return string The formatted currency string.
 */
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
        $restunits = substr($integerPart, 0, -3); 
        $restunits = (strlen($restunits) % 2 == 1) ? "0" . $restunits : $restunits;
        $expunit = str_split($restunits, 2);
        for ($i = 0; $i < sizeof($expunit); $i++) {
            if ($i == 0) {
                $explrestunits .= (int)$expunit[$i] . ","; 
            } else {
                $explrestunits .= $expunit[$i] . ",";
            }
        }
        $thecash = $explrestunits . $lastthree . $decimalPart;
    } else {
        $thecash = $integerPart . $decimalPart;
    }

    return $isNegative ? '-' . $thecash : $thecash;
}

// 2. Securely get the client name from the GET request
// CRITICAL: Use real_escape_string to prevent SQL Injection
if (!isset($conn)) {
    // Fallback error if connection is not available
    http_response_code(500);
    die("Database connection not established.");
}
$FClient = $conn->real_escape_string($_GET['FClient']);


// If the client is the default 'CLIENT' value, exit
if($FClient == 'CLIENT'){
    exit();
}

// 3. Construct and execute the secured query
$sql = "SELECT SUM(`AMOUNT`) AS TOTAL FROM `billledger` WHERE `CLIENT` = '$FClient'";
$result = $conn->query($sql);

$Total = 0.00; // Initialize total as float

if ($result) {
    $row = $result->fetch_assoc();
    // Use the fetched value, defaulting to 0.00 if the row or column is null
    $Total = (float)($row['TOTAL'] ?? 0.00); 
}

// 4. Output the result based on the Total
// Use htmlspecialchars() for client name to prevent XSS
echo "<span style='color:blue;'>" . htmlspecialchars($FClient) . "</span> </br> ";

if ($Total > 0) {
    // Receivable (Dues) - Display in Red
    echo "<span style='color:red;'>Total Receivable: &nbsp; " . moneyFormat(strval($Total)) . "</span>";
}
else if ($Total == 0) {
    // No Dues - Display in Green
    echo "<span style='color:green;'>No Dues</span>";
}
else {
    // Advance/Credit (Negative Total) - Display in Blue
    $Advance = abs($Total);
    echo "<span style='color:blue;'>Advance: &nbsp; " . moneyFormat(strval(number_format($Advance, 2, ".", ""))) . "</span>";
}

// 5. Close the connection
$conn->close();
?>