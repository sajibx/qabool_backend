<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; 

// include ("./refchk.php");
include ("./conn.php");

// Function to safely highlight the matched keyword.
// We must escape any potential regex characters in $inputValue before using it in preg_replace.
function highlightMatchedKeyword($inputValue, $clientName) {
    // Escape special regular expression characters in the user input
    $escapedInput = preg_quote($inputValue, '/');
    
    // Use the escaped input in the regex pattern
    return preg_replace("/($escapedInput)/i", "<span style='color: red;'>$0</span>", $clientName);
}


if (isset($_GET['q'])) {
    // 1. Input Processing: Use the input as-is for the LIKE value
    $inputValue = $_GET['q'];
    
    // We need to wrap the input with % for the LIKE clause in the prepared statement
    $searchParam = "%" . $inputValue . "%";

    // 2. Prepare the SQL query
    // SECURITY FIX: Use prepared statement to prevent SQL Injection
    $sql = "SELECT `CLIENT` FROM `last_shipment` WHERE `CLIENT` LIKE ? ORDER BY `CLIENT` ASC LIMIT 20";
    
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameter: 's' for string ($searchParam)
        $stmt->bind_param("s", $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Output the suggestions
            while ($row = $result->fetch_assoc()) {
                $clientName = $row['CLIENT'];
                
                // Use htmlspecialchars to ensure the output is safe, especially in the onclick attribute
                $safeClientNameUpper = htmlspecialchars(strtoupper($clientName), ENT_QUOTES, 'UTF-8');
                
                // Highlight the name for display
                $highlightedName = highlightMatchedKeyword($inputValue, $clientName);

                // Output the hyperlinked suggestion
                // Use the safely escaped $safeClientNameUpper in the JavaScript function call
                echo "<a class='suggestion-link' href='javascript:void(0);' onclick='selectClient(\"{$safeClientNameUpper}\")'>" . $highlightedName . "</a> <span style='margin-left: 10px;'></span>";
            }
        } else {
            echo "No suggestions found";
        }
        
        $stmt->close();
    } else {
        // Handle database preparation error if necessary (e.g., echo "Error preparing statement")
    }
}

// Ensure connection is closed after all operations are complete
$conn->close();

?>

<style>
    .suggestion-link {
        color: blue;
        text-decoration: none;
    }
    
    .suggestion-link:hover {
        color: red;
    }
</style>