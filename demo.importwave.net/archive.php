<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; 
?>


<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; include __DIR__ . "/conn.php"; 
?>


<?php


$sql = "INSERT INTO guid (guid,SID,CID,TIME,`user`,TYPE)
        VALUES (?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE guid = guid";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $guid, $SID, $CID, $date, $UID, $TYPE);
$stmt->execute();
$stmt->close();



$term = $_GET['q'] ?? '';
$like = "%{$term}%";
$stmt = $conn->prepare("SELECT id, name FROM ledger WHERE name LIKE ? LIMIT 50");
$stmt->bind_param("s", $like);
$stmt->execute();



$sql_val = "SELECT SUM(`P.WT`*`UNIT_PRICE`) AS TOTAL_BILL FROM `packinglist`
            WHERE `SHIPMENT` = ? AND `CLIENT` = ? AND `CTN` = 1";

// $sql_val = "SELECT SUM(`P.WT`*`UNIT_PRICE`) TOTAL_BILL FROM `packinglist`
//             WHERE `SHIPMENT` = '$ShipID' AND `CLIENT` = '$FClient' AND `CTN` = 1;";

// $result0 = $conn->query($sql_val);

$stmt_sql_val2 = $conn->prepare($sql_val);
$stmt_sql_val2->bind_param('ss', $ShipID, $FClient);
$stmt_sql_val2->execute();
$result0 = $stmt_sql_val2->get_result(); // next lines ($row=..., $BILLING_AMOUNT=...) stay the same
$stmt_sql_val2->close();


$row = $result0->fetch_assoc();

$BILLING_AMOUNT     = $row['TOTAL_BILL'];
?>





include __DIR__ . "/refchk.php"; 



                        "dom": "<flB><t><i><p>",




                        // "buttons": [
                //     'excel', 'pdf'
                // ],

"buttons": [
    'excel',
    {
        extend: 'pdfHtml5',
        text: 'PDF',
        orientation: 'landscape',   // more horizontal space
        pageSize: 'A4',             // or 'A3' if you want even wider
        exportOptions: {
            // explicitly export all 8 columns
            columns: [0, 1, 2, 3, 4, 5, 6, 7]
        },
        customize: function (doc) {
            // Smaller font so more fits in width
            doc.defaultStyle.fontSize = 8;
            doc.styles.tableHeader.fontSize = 9;

            // Fix column widths so the last column stays on the page
            // Adjust percentages to fit your data
            doc.content<a href="" class="citation-link" target="_blank" style="vertical-align: super; font-size: 0.8em; margin-left: 3px;">[1]</a>.table.widths = [
                '8%',   // DATE
                '10%',  // SHIPMENT
                '18%',  // CNF NAME
                '12%',  // AWB NO
                '10%',  // AMOUNT
                '8%',   // TYPE
                '24%',  // REMARKS
                '10%'   // OS DUES
            ];

            // Optional: tighten cell padding to gain more space
            var objLayout = {};
            objLayout['hLineWidth'] = function() { return 0.5; };
            objLayout['vLineWidth'] = function() { return 0.5; };
            objLayout['paddingLeft']  = function() { return 3; };
            objLayout['paddingRight'] = function() { return 3; };
            doc.content<a href="" class="citation-link" target="_blank" style="vertical-align: super; font-size: 0.8em; margin-left: 3px;">[1]</a>.layout = objLayout;
        }
    }
],
