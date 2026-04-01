<?php
include ("./sessionchk2.php");
include ("./refchk.php");
?>

<script src="./js/crypto-js.min.js"></script>

<?php 
$shipID = $_REQUEST["shipID"];
?>

<?php 
$sn = 0;

include ("./conn.php");

// Fetch detailed data for clients, shipping marks, and item lists
$sql = "SELECT `CLIENT`, `SHIPPING_MARK`, `ITEM_LIST`, COUNT(`SN`) AS CNT
        FROM `packinglist`
        WHERE `SHIPMENT` = '$shipID'
        GROUP BY `CLIENT`, `SHIPPING_MARK`, `ITEM_LIST`
        ORDER BY `CLIENT`, `SHIPPING_MARK`, `ITEM_LIST`";

$result = $conn->query($sql);

$clients = []; // client => total_cnt
$all_shipping_marks = []; // shipping_mark => count
$all_item_lists = []; // item_list => count
$marks_per_client = []; // client => [shipping_mark => cnt]
$items_per_client = []; // client => [item_list => cnt]

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $client = $row['CLIENT'];
        $mark = trim($row['SHIPPING_MARK']);
        $item = $row['ITEM_LIST'];
        $cnt = $row['CNT'];

        // Aggregate clients
        if (!isset($clients[$client])) {
            $clients[$client] = 0;
        }
        $clients[$client] += $cnt;

        // Aggregate shipping marks
        if (!isset($all_shipping_marks[$mark])) {
            $all_shipping_marks[$mark] = 0;
        }
        $all_shipping_marks[$mark] += $cnt;

        // Aggregate item lists
        if (!isset($all_item_lists[$item])) {
            $all_item_lists[$item] = 0;
        }
        $all_item_lists[$item] += $cnt;

        // Map shipping marks to clients
        if (!isset($marks_per_client[$client])) {
            $marks_per_client[$client] = [];
        }
        $marks_per_client[$client][$mark] = isset($marks_per_client[$client][$mark]) ? $marks_per_client[$client][$mark] + $cnt : $cnt;

        // Map item lists to clients
        if (!isset($items_per_client[$client])) {
            $items_per_client[$client] = [];
        }
        $items_per_client[$client][$item] = isset($items_per_client[$client][$item]) ? $items_per_client[$client][$item] + $cnt : $cnt;
    }
}

// Sort arrays for consistent dropdown order
ksort($clients);
ksort($all_shipping_marks);
ksort($all_item_lists);

// Fetch shipment date
$uSQL = "SELECT `DATE` FROM `ShipmentLog` WHERE `SHIPMENT` LIKE '$shipID' ORDER BY `SN` DESC LIMIT 1";
$Uresult = $conn->query($uSQL);
$Urow = $Uresult->fetch_assoc();
$Udate = ($Urow['DATE']);
$dateObj = new DateTime($Udate);
$formattedDate = $dateObj->format('d M Y');

// Fetch warehouse name
$sql_wh = "SELECT `WAREHOUSE` FROM `ShipmentLog` WHERE `SHIPMENT` LIKE '$shipID'";
$result_wh = $conn->query($sql_wh);
$WHNAMEROW = $result_wh->fetch_assoc();
$WHNAME = ($WHNAMEROW['WAREHOUSE']);



    // generate log ----------------------------------------------------

    date_default_timezone_set('Asia/Dhaka');

    $EVENT = "Shipment Open";

    $REMARKS = "SHIPMENET : " . $shipID;

    $UID = $_SESSION['user'];

    $date = date("y-m-d H:i:s");

    $sql_log = "INSERT INTO `log` (`sn`, `date`, `user`, `event`, `comment`)

                VALUES (NULL, '$date', '$UID', '$EVENT', '$REMARKS');";

    $result_log = $conn->query($sql_log);

    // generate log ----------------------------------------------------


?>

<style type="text/css">
table, th, td {
  border: 0.5px solid gray;
  border-collapse: collapse;
}

.EdTxt {
    border: 0px;
    text-align: right;
    width: 70px;
    margin: 0 auto;
}

.HdnTxT {
    visibility: none;
}
</style>




<div style="width: 100%; height: 20px; border: 0px solid blue; margin-bottom: 10px;">
    <div style="min-height:30px; border:0px solid red; float:left;">
        <input id="ShipID" type="text" name="" style="display:none;" value="<?php echo $shipID; ?>">
        <span style="float: left; margin-top: -5px; color: darkred; margin-right:15px; font-weight: bold; font-size: 20px; display: none;">
            <?php echo "".$shipID."</span></br>"; ?>
        <span class="StanTxt" style="float:left; display: none;"><?php echo "&#8593; ".$formattedDate; ?></span>
        <input id="UpDate" type="text" name="" style="display:none;" value="<?php echo $formattedDate; ?>">

    </div>
    <div style="border: 0px solid red; float: left;">
        <div style="float:left; margin-left: 5px; margin-right: 5px; text-align: left; border: 0px solid red;">
            <select class="classic" id="ClientName1" onchange="updateFilters(); FilterUpdate(this.value, 'CLIENT');">
                <option style="text-align: left; font-weight: bolder;" value="CLIENT">CLIENT</option>
                <?php 
                foreach ($clients as $CLIENT => $CNT) {
                    $CLIENT_D = $CLIENT . ' (' . $CNT . ')';
                ?>
                    <option value="<?php echo htmlspecialchars($CLIENT); ?>"><?php echo htmlspecialchars($CLIENT_D); ?></option>
                <?php } ?>
            </select>
        </div>
        <div style="float:left; margin-left: 5px; margin-right: 5px; text-align: left; border: 0px solid red;">
            <select class="classic" id="ShipMark1" onchange="FilterUpdate(this.value, 'MARK');">
                <option style="text-align: left; font-weight: bolder;" value="MARK">SHIPPING MARK</option>
                <?php 
                foreach ($all_shipping_marks as $SHIPPING_MARK => $CNT) {
                ?>
                    <option value="<?php echo htmlspecialchars($SHIPPING_MARK); ?>"><?php echo htmlspecialchars($SHIPPING_MARK); ?></option>
                <?php } ?>
            </select>
        </div>
        <div style="float:left; margin-left: 5px; text-align: left; border: 0px solid red;">
            <select class="classic" id="ItemList1" onchange="FilterUpdate(this.value, 'ITEM');">
                <option style="text-align: left; font-weight: bolder;" value="ITEM">ITEM LIST</option>
                <?php 
                foreach ($all_item_lists as $ITEM_LIST => $CNT) {
                ?>
                    <option value="<?php echo htmlspecialchars($ITEM_LIST); ?>"><?php echo htmlspecialchars($ITEM_LIST); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div style="float:right;">
        <div style="float:right; margin-left: 1px; text-align: left; border: 0px solid red;">
            <img title="Export Summary" id="ExportIco" onclick="exportToExcelUpdated('EXPORT1', 'Summary-<?php echo $shipID; ?>')" src='./images/export.png' 
                style='cursor: hand; width: 33px; display:none; height: 30px; border: 0px solid green; margin-right:20px;'>
            <img title="Shipment Summary" id="SumIco" onclick="ShowSummary();" src='./images/summary.png' style='cursor: hand; width: 33px; 
                height: 30px; border: 0px solid green; margin-right:20px; display:inline'>
            <img title="Save Data" id="SaveIco" onclick="SaveEntry();" src='./images/save1.png' style='cursor: hand; width: 33px; display:none;
                height: 30px; border: 0px solid green; margin-right:20px;'>
            <img title="New Entry" id="PlusIco" onclick="FAddEntry();" src='./images/plus1.png' style='cursor: hand; width: 33px; display:inline;
                height: 30px; border: 0px solid green; margin-right:20px;'>
            <img title="Reload" onclick="Reload();" src='./images/reload.png' style='cursor: hand; width: 33px; 
                height: 30px; border: 0px solid green;'>
        </div>
    </div>
    <div style="float:right; display:none">
        <input id="ClientName" type="text" name="" value="CLIENT">
        <input id="ShippingMark" type="text" name="" value="MARK">
        <input id="ItemName" type="text" name="" value="ITEM">
    </div>
</div>

<div id="NewFilter"></div>

<script type="text/javascript">
    var marksPerClient = <?php echo json_encode($marks_per_client); ?>;
    var itemsPerClient = <?php echo json_encode($items_per_client); ?>;
    var allShippingMarks = <?php echo json_encode($all_shipping_marks); ?>;
    var allItemLists = <?php echo json_encode($all_item_lists); ?>;



function ShipBanner_F() {

    var UpDate = document.getElementById('UpDate').value;
    var ShipID = document.getElementById('ShipID').value;
    var tframe = "#ShipBanner";
    
    $(tframe).html(`
        <span style='color: darkred; font-weight: bold; font-size: 20px;'>${ShipID}</span>
        <br>
        <span class="StanTxt" style="float:left; margin-left:1px;">&#8593;  ${UpDate}</span>
    `);
}

ShipBanner_F();

    function updateFilters() {
        var client = $('#ClientName1').val();
        var markSelect = $('#ShipMark1');
        var itemSelect = $('#ItemList1');

        FMark = document.getElementById('ShippingMark');
        FMark.value = 'MARK';

        Fitem = document.getElementById('ItemName');
        Fitem.value = 'ITEM';

        // Update Shipping Marks dropdown
        markSelect.empty();
        markSelect.append($('<option>', {
            value: 'MARK',
            text: 'SHIPPING MARK',
            css: {'text-align': 'left', 'font-weight': 'bolder'}
        }));

        var marks = client === 'CLIENT' ? allShippingMarks : (marksPerClient[client] || {});
        var sortedMarks = Object.keys(marks).sort();
        for (var i = 0; i < sortedMarks.length; i++) {
            var mark = sortedMarks[i];
            markSelect.append($('<option>', {
                value: mark,
                text: mark
            }));
        }
        markSelect.val('MARK');

        // Update Item Lists dropdown
        itemSelect.empty();
        itemSelect.append($('<option>', {
            value: 'ITEM',
            text: 'ITEM LIST',
            css: {'text-align': 'left', 'font-weight': 'bolder'}
        }));

        var items = client === 'CLIENT' ? allItemLists : (itemsPerClient[client] || {});
        var sortedItems = Object.keys(items).sort();
        for (var i = 0; i < sortedItems.length; i++) {
            var item = sortedItems[i];
            itemSelect.append($('<option>', {
                value: item,
                text: item
            }));
        }
        itemSelect.val('ITEM');
    }

    function printDiv(divId, printStylesheet) {
        var div = document.getElementById(divId);
        var stylesheet = document.styleSheets[0];
        if (printStylesheet) {
            stylesheet.disabled = false;
        }
        div.style.display = "block";
        window.print();
        if (printStylesheet) {
            stylesheet.disabled = true;
        }
    }

    function ShowBill() {
        var SID = document.getElementById('ShipID').value;
        var CID = document.getElementById('ClientName').value;
        var guid = document.getElementById('guid').value;
        var Bind = "|"+SID+"|!|"+CID+"|";
        var r = Bind.replace(/[-\/\s]/g, '');
        var tframe = "#BtnShowBill";
        $(tframe).html("Preparing...");
        var link = "./Guid.php";
        var arr = {
            "SID": SID,
            "CID": CID,
            "guid": guid,
            "TYPE": 'INV'
        };
        $.ajax({
            url: link,
            type: "POST",
            data: arr,
            cache: false,
            success: function(data) {
                var url = "./i.php?iid=" + guid;
                var myWindow = window.open(url, "", "");
                $(tframe).html("Invoice");
            }
        });
    }

    function SaveEntry() {
        const ShipID = document.getElementById('ShipID').value;
        const nCTN = document.getElementById('nCTN').value;
        const nCLIENT = document.getElementById('nCLIENT').value;
        const nSHIP = document.getElementById('nSHIP').value;
        const nITEM = document.getElementById('nITEM').value;
        const nWEIGHT = document.getElementById('nWEIGHT').value;
        const nPWT = document.getElementById('nPWT').value;
        const nUNIT_PRICE = document.getElementById('nUNIT_PRICE').value;
        const nTOTAL = document.getElementById('nTOTAL').value;

        if (nCLIENT === "CLIENT" || nCLIENT.trim() === "") {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select a valid client name.'
            });
            return;
        }

        if (nCTN === null || nCTN === undefined || nCTN === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please enter a valid CTN number.'
            }).then(() => {
                document.getElementById('nCTN').focus();
            });
            return;
        }

        if (!nUNIT_PRICE || isNaN(nUNIT_PRICE) || parseFloat(nUNIT_PRICE) <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please enter a valid unit price.'
            }).then(() => {
                document.getElementById('nUNIT_PRICE').focus();
            });
            return;
        }

        if (!nTOTAL || isNaN(nTOTAL) || parseFloat(nTOTAL) <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please enter a valid total amount.'
            }).then(() => {
                document.getElementById('nTOTAL').focus();
            });
            return;
        }

        Swal.fire({
            title: 'Confirm Entry',
            html: `
                <strong>Client Name:</strong> ${nCLIENT}<br>
                <strong>CTN No:</strong> ${nCTN}<br>
                <strong>Shipment:</strong> ${nSHIP || 'N/A'}<br>
                <strong>Item:</strong> ${nITEM || 'N/A'}<br>
                <strong>Weight:</strong> ${nWEIGHT || 'N/A'}<br>
                <strong>Per Weight:</strong> ${nPWT || 'N/A'}<br>
                <strong>Unit Price:</strong> ${nUNIT_PRICE}<br>
                <strong>Total Amount:</strong> ${nTOTAL}
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, save it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('SaveIco').style.display = 'none';
                document.getElementById('PlusIco').style.display = 'inline';
                const link = "./SubmitEntry.php";
                const tframe = "#EntryBox";
                $(tframe).html(`
                    <div style="display: flex; justify-content: center; align-items: center; margin: 20px auto; font-size: 16px;">
                        <img style="width: 30px; height: 30px; margin-right: 10px;" src="./images/loading/loading1.gif" alt="Loading...">
                        <span>Saving Data...</span>
                    </div>
                `);
                $.ajax({
                    url: link,
                    type: "POST",
                    data: {
                        ShipID: ShipID,
                        nCTN: nCTN,
                        nCLIENT: nCLIENT,
                        nSHIP: nSHIP,
                        nITEM: nITEM,
                        nWEIGHT: nWEIGHT,
                        nPWT: nPWT,
                        nUNIT_PRICE: nUNIT_PRICE,
                        nTOTAL: nTOTAL
                    },
                    dataType: "html",
                    success: function(data) {
                        $(tframe).html(data);
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Entry saved successfully!'
                        }).then(() => {
                            Reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        $(tframe).html('');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to save entry. Please try again.'
                        });
                    }
                });
            }
        });
    }

    function FAddEntry() {
        var ShipID = document.getElementById('ShipID').value;
        var FClient = document.getElementById('ClientName').value;
        var SaveIco = document.getElementById('SaveIco');
        SaveIco.style.display = 'inline';
        var PlusIco = document.getElementById('PlusIco');
        PlusIco.style.display = 'none';
        var link = "./AddEntry.php";
        var tframe = "#EntryBox";
        $(tframe).html("<span style='margin:0 auto;'>...</span>");
        $.ajax({
            url: link,
            type: "POST",
            data: {
                FClient: FClient,
                ShipID: ShipID
            },
            dataType: "html",
            success: function(data) {
                $(tframe).html("");
                $(tframe).html(data);
            }
        });
    }

    function LedgerUpdate() {
        const ShipID = document.getElementById('ShipID').value;
        const FClient = document.getElementById('CLINTNN').value;
        const UID = document.getElementById('UID').value;
        if (!FClient || FClient === "CLIENT" || FClient.trim() === "") {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select a valid client name.'
            });
            return;
        }
        Swal.fire({
            title: 'Confirm Ledger Update',
            text: `Are you sure you want to update the ledger for client: ${FClient}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, update it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const link = "./UpdateLedger.php";
                const tframe = "#BtnLedger";
                const arr = {
                    ShipID: ShipID,
                    FClient: FClient,
                    UID: UID
                };
                $(tframe).html(`
                    <div style="display: flex; justify-content: center; align-items: center; margin: 20px auto; font-size: 16px;">
                        Updating...
                        <span>Updating Ledger...</span>
                    </div>
                `);
                $.ajax({
                    url: link,
                    type: "POST",
                    data: arr,
                    cache: false,
                    success: function(data) {
                        $(tframe).html(data);
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Ledger updated successfully!'
                        });
                    },
                    error: function(xhr, status, error) {
                        $(tframe).html('');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to update ledger. Please try again.'
                        });
                    }
                });
            }
        });
    }

    $(document).ready(function($) {
        var ShipID = document.getElementById('ShipID').value;
        var link = "./FilterData.php";
        var tframe = "#NewFilter";
        $(tframe).html("<div style='border:0px solid red; height:100px; width:100px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");
        $.ajax({
            url: link,
            type: "POST",
            dataType: "html",
            data: {
                Fmark: "MARK",
                Fitem: "ITEM",
                FClient: "CLIENT",
                ShipID: ShipID
            },
            success: function(data) {
                $(tframe).html("");
                $(tframe).html(data);
            }
        });
    });

    function Reload() {
        var ShipID = document.getElementById('ShipID').value;
        var FItem = document.getElementById('ItemName').value;
        var FClient = document.getElementById('ClientName').value;
        var Fmark = document.getElementById('ShippingMark').value;
        var SaveIco = document.getElementById('SaveIco');
        SaveIco.style.display = 'none';
        var ExportIco = document.getElementById('ExportIco');
        ExportIco.style.display = 'none';
        var PlusIco = document.getElementById('PlusIco');
        PlusIco.style.display = 'inline';
        var SumIco = document.getElementById('SumIco');
        SumIco.style.display = 'inline';
        var link = "./FilterData.php";
        var tframe = "#NewFilter";
        $(tframe).html("<div style='border:0px solid red; height:100px; width:100px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");
        $.ajax({
            url: link,
            type: "POST",
            dataType: "html",
            data: {
                Fmark: Fmark,
                Fitem: FItem,
                FClient: FClient,
                ShipID: ShipID
            },
            success: function(data) {
                $(tframe).html("");
                $(tframe).html(data);
            }
        });
    }

    function ShowSummary() {
        var ShipID = document.getElementById('ShipID').value;
        var link = "./ShipmentSum.php";
        var tframe = "#NewFilter";
        var PlusIco = document.getElementById('PlusIco');
        PlusIco.style.display = 'none';
        var SumIco = document.getElementById('SumIco');
        SumIco.style.display = 'none';
        var SaveIco = document.getElementById('SaveIco');
        SaveIco.style.display = 'none';
        var ExportIco = document.getElementById('ExportIco');
        ExportIco.style.display = 'inline';
        $(tframe).html("<div style='border:0px solid red; height:100px; width:100px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");
        var arr = {
            "ShipID": ShipID
        };
        $.ajax({
            url: link,
            type: "POST",
            data: arr,
            cache: false,
            success: function(data) {
                $(tframe).html(data);
            }
        });
    }

    function CopyTxt(sn) {
        if (sn == 1) {
            return;
        }
        var snx = sn - 1;
        var PID = "PID" + snx;
        var PIDx = document.getElementById(PID).value;
        var SrcBox = PIDx + "PT";
        var SrcVal = document.getElementById(SrcBox).value;
        var TrgtPID = "PID" + sn;
        var TPIDx = document.getElementById(TrgtPID).value;
        var TrgtBox = TPIDx + "PT";
        TrgtBox = document.getElementById(TrgtBox);
        TrgtBox.value = SrcVal;
        UpdateVal(TPIDx, "PT", sn);
    }

    function UpdateVal(PID, Target, PIDSN) {
        var NewUnit = document.getElementById(PID + Target).value;
        var ShipID = document.getElementById('ShipID').value;
        if (Target == "NM") {
            // Changing name not recommended
        } else {
            var LastRow = document.getElementById('LastRow').value;
            var SELROW = PIDSN;
            PIDSN = PIDSN + 1;
            try {
                var NextItem = document.getElementById("PID" + PIDSN).value;
            } catch (err) {}
            if (Target == "PT") {
                var box1 = document.getElementById(PID + Target);
                box1.style.color = 'red';
                if (LastRow != "PID" + SELROW) {
                    var box11 = document.getElementById(NextItem + 'PT');
                    box11.focus();
                }
            } else {
                var box2 = document.getElementById(PID + Target);
                box2.style.color = 'red';
                if (LastRow != "PID" + SELROW) {
                    var box22 = document.getElementById(NextItem + 'WT');
                    box22.focus();
                }
            }
            var val_wt = parseFloat(document.getElementById(PID + "WT").value) || 0;
            var val_pt = parseFloat(document.getElementById(PID + "PT").value) || 0;
            var val_total = (val_wt * val_pt).toFixed(2);
            var TotalBox = document.getElementById(PID + "TT");
            TotalBox.value = val_total;
        }
        var link = "ValUpdate.php";
        var today = new Date();
        var tframe = "#InfoBox";
        $(tframe).html("Updating...");
        var arr = {
            "PID": PID,
            "TARGET": Target,
            "VAL": NewUnit,
            "ShipID": ShipID
        };
        $.ajax({
            url: link,
            type: "POST",
            data: arr,
            cache: false,
            success: function(data) {
                var timeN = today.getHours() + ":" + today.getMinutes();
                $(tframe).html("Last updated : " + timeN);
            }
        });
    }

    function FocusPayment() {
        var box11 = document.getElementById('TxTPayment');
        box11.focus();
    }

    function WHUpdate(ItemVal, TYP) {
        var ShipID = document.getElementById('ShipID').value;
        var link = "./WHUpdate.php";
        $.ajax({
            url: link,
            type: "POST",
            data: {
                ItemVal: ItemVal,
                ShipID: ShipID
            },
            success: function(data) {
                Swal.fire({
                    icon: 'success',
                    title: '',
                    text: 'Warehouse Name Updated.'
                });
                return 0;
            }
        });
    }

    function FilterUpdate(ItemVal, TYP) {
        var ItemBox, Fmark, FItem, FClient;

        if (TYP == "MARK") {
            ItemBox = document.getElementById('ShippingMark');
            ItemBox.value = ItemVal;

            Fmark = ItemVal;
            FItem = document.getElementById('ItemName').value;
            FClient = document.getElementById('ClientName').value;


        } else if (TYP == "ITEM") {
            ItemBox = document.getElementById('ItemName');
            ItemBox.value = ItemVal;
            Fmark = document.getElementById('ShippingMark').value;
            FItem = ItemVal;
            FClient = document.getElementById('ClientName').value;
        } else if (TYP == "CLIENT") {
            ItemBox = document.getElementById('ClientName');
            ItemBox.value = ItemVal;
            Fmark = document.getElementById('ShippingMark').value;
            FItem = document.getElementById('ItemName').value;
            FClient = ItemVal;
        } else if (TYP == "CLIENTN") {
            Fmark = document.getElementById('ShippingMark').value;
            FItem = document.getElementById('ItemName').value;
            FClient = ItemVal;
        }
        var ShipID = document.getElementById('ShipID').value;
        var SaveIco = document.getElementById('SaveIco');
        SaveIco.style.display = 'none';
        var ExportIco = document.getElementById('ExportIco');
        ExportIco.style.display = 'none';
        var PlusIco = document.getElementById('PlusIco');
        PlusIco.style.display = 'inline';
        var SumIco = document.getElementById('SumIco');
        SumIco.style.display = 'inline';
        var link = "./FilterData.php";
        var tframe = "#NewFilter";
        $(tframe).html("<div style='border:0px solid red; height:100px; width:100px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");
        $.ajax({
            url: link,
            type: "POST",
            data: {
                Fmark: Fmark,
                Fitem: FItem,
                FClient: FClient,
                ShipID: ShipID
            },
            success: function(data) {
                $(tframe).html("");
                $(tframe).html(data);
            }
        });
    }

    function FilterUpdate2(client) {
        var ShipID = document.getElementById('ShipID').value;
        var FItem = document.getElementById('ItemName').value;
        var FClient = client;
        var Fmark = document.getElementById('ShippingMark').value;
        var SaveIco = document.getElementById('SaveIco');
        SaveIco.style.display = 'none';
        var link = "./FilterData.php?Fmark="+Fmark+"&Fitem="+FItem+"&FClient="+FClient+"&ShipID="+ShipID;
        var tframe = "#NewFilter";
        $(tframe).html("<div style='border:0px solid red; height:100px; width:100px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");
        $.ajax({
            url: link,
            type: "GET",
            dataType: "html",
            success: function(data) {
                $(tframe).html("");
                $(tframe).html(data);
            }
        });
    }
</script>