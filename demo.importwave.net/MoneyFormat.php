<?php 
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

 ?>