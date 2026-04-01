<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; 

// include ("./refchk.php");
include ("./conn.php");

// Function to safely highlight the matched keyword.
function highlightMatchedKeyword($inputValue, $clientName) {
    // Escape special regular expression characters in the user input
    // The second argument in preg_quote specifies the delimiter used in the regex, which is '/' here.
    $escapedInput = preg_quote($inputValue, '/');
    
    // Use the escaped input in the regex pattern for highlighting
    return preg_replace("/($escapedInput)/i", "<span style='color: green;'>$0</span>", $clientName);
}


if (isset($_GET['q'])) {
    $inputValue = $_GET['q'];
    
    // 1. Prepare the SQL query with a placeholder
    // SECURITY FIX: Use prepared statement to prevent SQL Injection
    $sql = "SELECT `SHIPMENT` FROM `ShipmentLog` WHERE `SHIPMENT` LIKE ? ORDER BY `SN` DESC LIMIT 99";
?>

<table border="0" style="margin-top: 10px ;">
    <tr>

<?php
    $n = 0;

    if ($stmt = $conn->prepare($sql)) {
        // We need to wrap the input with % for the LIKE clause
        $searchParam = "%" . $inputValue . "%";

        // Bind parameter: 's' for string ($searchParam)
        $stmt->bind_param("s", $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $n++;
                $clientName = $row['SHIPMENT'];
                $clientNameUpper = strtoupper($clientName);

                // SECURITY FIX: Escape the name for use in the JavaScript function call
                $safeClientNameUpper = htmlspecialchars($clientNameUpper, ENT_QUOTES, 'UTF-8');
                
                // Highlight the name for display
                $highlightedName = highlightMatchedKeyword($inputValue, $clientName);
                
                // Use the safely escaped $safeClientNameUpper in the JavaScript function call
                ?>
                <td style="padding-left:3px;">
                    <a 
                        class="suggestion-link" 
                        href="javascript:void(0);" 
                        onclick="selectClient('<?php echo $safeClientNameUpper; ?>')">
                        <?php echo $highlightedName; ?>
                    </a>
                </td>
                <?php
                if ($n % 3 == 0) {
                    echo "</tr><tr>";
                }
            }
        } else {
            echo "<td>No records found.</td>";
        }
        
        $stmt->close();
    } else {
        // Handle database preparation error (optional, for debugging)
        // echo "<td>Database error occurred.</td>";
    }
}

// -------------------------------------------------------------------------------------
$conn->close();

?>
    </tr>
</table>

<style>
    .suggestion-link {
        color: blue;
        text-decoration: none;
    }
    
    .suggestion-link:hover {
        color: red;
    }
</style>