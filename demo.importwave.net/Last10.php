<?php
include ("./sessionchk2.php"); 
    // include ("./refchk.php");
    
    //original
    $StP = 0;

    if($UL==0){
        echo "Inactive Account";
        // die;
    }
    
    $ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/App_Data/app.ini', true);
    $LIMIT10   = $ini["GENERAL"]["LAST10LIMIT"];

    include ("./Functions.php"); 
?>


<style>

    table {

        /*border: 1px solid red;*/
        border-collapse: collapse;

    }

    td img {

        float: none; /* Remove float */

        display: block;

        margin: 0 auto; /* Center images */

    }
</style>



<div style="width:100%; height:60px; border:0px solid gray; padding-top:0px;">
    <div style="float: left; min-width:40px; margin-left:27px; margin-top:20px; font-weight: bold; font-size:20px;">
        <?php 
            
        ?>
    </div>
    <div style="float: right; width:40px; margin-left:20px;">
        <?php if($UL>=5){ ?>
            <input type="checkbox" id="DelToggle" style="float: right;">
        <?php } ?>
    </div>
</div>

<!--<span>Recents Shipments : </br></span>-->

<div style="width:50px; min-height:100px; float:left; border:0px solid red; margin-top:10px; margin-left:30px;">
    <?php if($UL>=3){ ?>
        <img title="Search by Shipping Mark/CTN No" class="BoxButton2" id="BtnShipments" onclick="GetPage2('Search');" src='./images/search.png' style='cursor: hand; width: 50px; float:left; height: 50px;'>
    <?php } 
        if($UL>=7){
    ?>
        <img title="Admin Pannel" id="BtnAdmin" onclick="GetPage2('Pannel3');" src='./images/admin.png' class="BoxButton2" style='cursor: hand; width: 50px; float:left; height: 50px; margin-top:20px;'>
    <?php } ?>
 
    <?php if($UL>=1){ ?>
        <img title="Change Password" class="BoxButton2" id="BtnCPass" onclick="GetPage2('ChangeP');" src='./images/cpass.png' style='cursor: hand; width: 50px; float:left; height: 50px; margin-top:20px;'>
    <?php } ?>
    <img title="Logout" class="BoxButton2" id="BtnLogout" onclick="Logout();" src='./images/logout.png' style='cursor: hand; width: 50px; float:left; height: 50px; margin-top:20px;'>
</div>

<?php
    if($UL==0){ 
        exit();
    }    
?>

<div style="width:50px; min-height:100px; float:right; border:0px solid green;">
    <table>
        <tr>
            <?php if($UL>=6){ ?>
                <td>
                    <img title="CNF Ledger" class="BoxButton2" id="BtnLedgerAdmin" onclick="GetPage('AdminLedger');" src='./images/ledger1.png' style='cursor: hand; width: 50px; float:right; height: 50px; margin-right:20px;'>
                </td>
        </tr>
        <tr>
                <td>
                    <img title="CNF Bill Entry" class="BoxButton2" id="CNFBill" onclick="GetPage2('CNFEntry');" src='./images/bill1.png' style='cursor: hand; width: 50px; float:right; height: 50px; margin-top:20px; margin-right:20px;'>
                </td>
            <?php } ?>
        </tr>
        <tr>
            <?php if($UL>=4){ ?>
                <td>
                    <img title="Payment" class="BoxButton2" id="NewEntry" onclick="GetPage2('NewEntry');" src='./images/payment1.png' style='cursor: hand; width: 50px; float:right; height: 50px; margin-top:20px; margin-right:20px;'>
                </td>
        </tr>
        <tr>
                <td>
                    <img title="Ledger" class="BoxButton2" id="BtnLedger" onclick="GetPage('Ledger');" src='./images/ledger11.png' style='cursor: hand; width: 50px; float:right; height: 50px; margin-top:20px; margin-right:20px;'>
                </td>
            <?php } ?>
        </tr>
    </table>
</div>

<div id="SideCon" style="width:80%; margin:0 auto; min-height:300px; border:0px solid blue;">
    <input style="display:none;" type="text" id="CSTP" value="<?php echo $StP; ?>">
    <input style="display:none;" type="text" id="LIMIT" value="<?php echo $LIMIT10; ?>">
    <div id="FrameLast101" style="max-width:700px; min-height:300px; border:0px solid green; margin:0 auto; padding-top:1px;">
        <div style="border:0px solid red; width: 200px; height: 30px; float:left;">
            
            <label for="search">Search</label>
            <input style="border:0px solid gray; margin: 0 auto; border-radius: 5px; height:35px;" onchange="SearchIni();" id="TxTsearch" type="search" placeholder="Search Shipment" />  
        </div>
        <div style="float: right; min-width:100px; border:0px solid blue;">
            <div style="float: right;">
                <img id="Btnold" onclick="NextTen('old');" src='./images/right-chevron.png' style='cursor: hand; width: 30px; height: 30px; float:right; margin-top:0; margin-right:0px;'>
            </div>
            <div id="PGnum" style="float: right; border:0px solid red; width:20px; height:20px; margin-right:15px;margin-left:15px;margin-top:5px; text-align:center; color:#23311e; font-size: 15;">
                1
            </div>
            <div style="float: right;">
                <img id="Btnnew" onclick="NextTen('new');" src='./images/left-chevron.png' style='cursor: auto; width: 30px; height: 30px; float:right; margin-top:0; margin-right:0px; filter:grayscale(100%);'>
            </div>
        </div>
        <div id="FrameLast10" style="margin: 50px auto; width: 90%; border: 0px solid red; text-align: center;">
            <?php 
                include ("./conn.php");
                // $sql = "SELECT `SHIPMENT` FROM `ShipmentLog` ORDER BY `DATE` DESC , `SN` DESC LIMIT $LIMIT10 OFFSET $StP;";
                // $result = $conn->query($sql);

                $stmt = $conn->prepare("SELECT SHIPMENT FROM ShipmentLog ORDER BY DATE DESC, SN DESC LIMIT ? OFFSET ?");
                $stmt->bind_param("ii", $LIMIT10, $StP);
                $stmt->execute();
                $result = $stmt->get_result();


            $stmt1 = $conn->prepare("SELECT Count(SHIPMENT) AS COUNT FROM ShipmentLog;");
            $stmt1->execute();
            $result1 = $stmt1->get_result();
            $row1 = $result1->fetch_assoc();
            $cnt = $row1['COUNT'];
            $Ncnt = $cnt-11;
            if($Ncnt==0){$Ncnt=0;}


                $i = 0;
                $sn = 0;
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $value1 = $row['SHIPMENT'];
                        $sn = $sn+1;
                        if (isset($BnO[$sn])) {
                            $BnOx = " " . $BnO[$sn];
                        } else {
                            $BnOx = " ";
                        }
                        $BtnFace = $row['SHIPMENT'];
            ?>  
                <button id="<?php echo $sn; ?>" value = "<?php echo $value1; ?>" class="button-6" style="margin-left: 10px; margin-top: 10px; border: 1px solid gray;" onclick="getShipment(<?php echo $sn; ?>);">  <?php echo $BtnFace ?> </button>
            <?php 
                    }
                } else {
                    echo "No Shipment Found.";
                }
                $conn->close();
            ?>
            <div style="border: 0px solid red; width:100%; margin-top:20px; float:left; text-align:right; bottom:0; font-size: 15;">Showing : <?php echo $cnt."-".$Ncnt; ?></div>
        </div>
        <div id="DelInfo" style="width:30px; height:100px; border:0px solid green; float:right;"></div>

        
    </div>
</div>

<div style="border: 0px solid darkred; height:50px; margin-left: 30%; margin-top: 20px;">
            
            <?php if($UL>4){ ?>
                
                <table border=0 width="auto" style="text-align:right;">
                    <tr>
                        <?php if($UL>=6){ ?>
                            <td>
                                <img title="China Expense" class="BoxButton2" id="BtnChina" onclick="GetPage('ExpenseLedger_cn');" src='./images/ExpLedger_cn1.png' style='cursor: hand; width: 50px; float:right; height: 50px; margin-top:0; margin-right:20px;'>
                            </td>
                        <?php } if($UL>=5){ ?>
                            <td>
                                <img title="Expense Ledger" class="BoxButton2" id="BtnExpense" onclick="GetPage('ExpenseLedger');" src='./images/ExpLedger1.png' style='cursor: hand; width: 50px; float:right; height: 50px; margin-top:0; margin-right:20px;'>
                            </td>
                        <?php } if($UL>=6){ ?>
                            <td>
                                <img title="Correction" class="BoxButton2" id="Correction" onclick="GetPage2('Correction');" src='./images/correction1.png' style='cursor: hand; width: 50px; float:right; height: 50px; margin-top:0px;margin-right:20px;'>
                            </td>
                        <?php } if($UL>=4){ ?>
                        <?php } if($UL>=3){ ?>
                            <td>
                                <img title="Warehouse" class="BoxButton2" id="BtnWrhouse" onclick="GetPage('Whouse');" src='./images/warehouse1.png' style='cursor: hand; width: 50px; float:right; height: 50px; margin-top:0; margin-right:20px;'>
                            </td>
                        <?php } if($UL>=6){ ?>
                            <td>
                                <img title="Upload" class="BoxButton2" id="Upload" onclick="OpenUploader();" src="./images/upload1.png" style="cursor: hand; width: 50px; float:right; height: 50px; border: margin-top:0; margin-right:20px;">
                            </td>
                        <?php } if($UL>=6){ ?>
                            <td>
                                <!-- <img title="File Storage" class="BoxButton2" id="" onclick="window.open('https://files.promatradeint.com/');" src='./images/folder1.png' style='cursor: hand; width: 50px; float:right; height: 50px; margin-top:0px;margin-right:0px;'> -->
                            </td>
                        <?php } if($UL>=6){ ?>
                        <?php } ?>
                    </tr>
                </table>
            <?php } ?>
</div>

<!-- ------------------------------------------------------------------------------------ -->

<style type="text/css">
    /* CSS */
    .button-6 {
        min-width: 170px;
        height: 35px;
        align-items: center;
        background-color: #FFFFFF;
        border: 0px solid rgba(0, 0, 0, 0.1);
        border-radius: .25rem;
        box-shadow: rgba(0, 0, 0, 0.02) 0 1px 3px 0;
        box-sizing: border-box;
        color: rgba(0, 0, 0, 0.85);
        cursor: pointer;
        display: inline-flex;
        font-family: system-ui,-apple-system,system-ui,"Helvetica Neue",Helvetica,Arial,sans-serif;
        font-size: 16px;
        font-weight: 600;
        justify-content: center;
        line-height: 1.25;
        margin: 0;
        padding: calc(.875rem - 1px) calc(1.5rem - 1px);
        position: relative;
        text-decoration: none;
        transition: all 250ms;
        user-select: none;
        -webkit-user-select: none;
        touch-action: manipulation;
        vertical-align: baseline;
    }
    .button-6:hover,
    .button-6:focus {
        border-color: rgba(0, 0, 0, 0.15);
        box-shadow: rgba(0, 0, 0, 0.1) 0 4px 12px;
        color: rgba(0, 0, 0, 0.65);
    }
    .button-6:hover {
        transform: translateY(-1px);
    }
    .button-6:active {
        background-color: #F0F0F1;
        border-color: rgba(0, 0, 0, 0.15);
        box-shadow: rgba(0, 0, 0, 0.06) 0 2px 4px;
        color: rgba(0, 0, 0, 0.65);
        transform: translateY(0);
    }
</style>


   
    <style type="text/css">
        //Vars 
        :root {
            --rad: .7rem;
            --dur: .3s;
            --color-dark: #2f2f2f;
            --color-light: #fff;
            --color-brand: #57bd84;
            --font-fam: 'Lato', sans-serif;
            --height: 5rem;
            --btn-width: 6rem;
            --bez: cubic-bezier(0, 0, 0.43, 1.49);
        }

        // Setup
        body {background: var(--color-dark); display: flex; align-items: center; justify-content: center; min-height: 100vh }
        html { box-sizing: border-box; height: 100%; font-size: 10px; } *, *::before, *::after { box-sizing: inherit; }

        // Main styles
        form {
            position: relative;
            width: 30rem;
            background: var(--color-brand);
            border-radius: var(--rad);
        }
        input, button {
            height: var(--height);
            font-family: var(--font-fam);
            border: 0;
            color: var(--color-dark);
            font-size: 1.8rem;
        }
        input[type="search"] {
            outline: 0; // <-- shold probably remove this for better accessibility, adding for demo aesthetics for now.
            width: 100%;
            background: var(--color-light);
            padding: 0 1.6rem;
            border-radius: var(--rad);
            appearance: none; //for iOS input[type="search"] roundedness issue. border-radius alone doesn't work
            transition: all var(--dur) var(--bez);
            transition-property: width, border-radius;
            z-index: 1;
            position: relative;
        }
        button {
            display: none; // prevent being able to tab to it
            position: absolute;
            top: 0;
            right: 0;
            width: var(--btn-width);
            font-weight: bold;
            background: var(--color-brand);
            border-radius: 0 var(--rad) var(--rad) 0;
        }
        input:not(:placeholder-shown) {
            border-radius: var(--rad) 0 0 var(--rad);
            width: calc(100% - var(--btn-width));
            + button {
                display: block;
            }
        }
        label {
            position: absolute;
            clip: rect(1px, 1px, 1px, 1px);
            padding: 0;
            border: 0;
            height: 1px;
            width: 1px;
            overflow: hidden;
        }
    </style>





<script type="text/javascript">
                function SearchIni(){
                    var SKey = document.getElementById("TxTsearch").value;
                    let Slen = SKey.length;
                    if(Slen <= 2){
                        return 0;
                    }
                    var link = "./Search10.php";
                    var tframe = "#FrameLast10";
                    var arr = {};
                    arr["SKEY"] = SKey;
                    $.ajax({
                        url: link,
                        type: "POST",
                        data: arr,
                        cache: false,
                        success: function (data) {
                            $(tframe).html(data);
                        }
                    });
                }
            </script>

<script>
                function openUploadPage() {
                    window.open('/upload/');
                }
                function generateGUID() {
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                        var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
                        return v.toString(16);
                    });
                }
                function OpenUploader() {
                    var SID = '-';
                    var CID = '-';
                    var guid = generateGUID();
                    var Bind = "|"+SID+"|!|"+CID+"|";
                    let r = Bind.replace(/[-\/\s]/g, '');
                    var link = "./Guid.php";
                    var arr = {};
                    arr["SID"] = SID;
                    arr["CID"] = CID;
                    arr["guid"] = guid;
                    arr["TYPE"] = 'UPLOAD';
                    $.ajax({ 
                        url: link,
                        type: "POST", 
                        data: arr, 
                        cache: false, 
                        success: function(data) {
                            var url = "./upload/index.php?uid=" + guid;
                            var myWindow = window.open(url, "", "");
                        } 
                    });
                }
            </script>

<script type="text/javascript">
                    function getShipment(shipx) {
                        console.log(shipx);
                        var shipment = document.getElementById(shipx).value;
                        var DelVal = document.getElementById("DelToggle").checked;
                        var LBOX = document.getElementById('CLOC');
                        LBOX.value = "SHIPMENT";
                        if (DelVal) {
                            const response = confirm("Are you sure you want to delete shipment: " + shipment + "?");
                            if (response) {
                                var link = "./DelShipment.php";
                                var link1 = "./RefreshLastShip.php";
                                var tframe = "#main_body";
                                $(tframe).html("<div style='border:0px solid red; height:100px; width:100px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");
                                $(tframe).append("<p>Deleting Shipment is a complex process. It requires time. Please do not close or stop this...</p>");
                                var arr = {
                                    "ShipID": shipment
                                };
                                $.ajax({
                                    url: link,
                                    type: "POST",
                                    data: arr,
                                    cache: false,
                                    success: function (data) {
                                        $(tframe).empty().append(data);
                                    }
                                });
                                return;
                            }
                        }
                        var link = "./shipments.php?shipID=" + shipment;
                        var tframe = "#main_body";
                        $(tframe).html("<div style='border:0px solid red; height:100px; width:100px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");
                        $.ajax({
                            url: link,
                            type: "POST",
                            dataType: "html",
                            success: function (data) {
                                $(tframe).empty().append(data);
                            }
                        });
                    }


                    
                    function NextTen(TYPE){
                        var CSTP = document.getElementById('CSTP').value;
                        var LIMIT = document.getElementById('LIMIT').value;
                        var colorImage = document.getElementById("Btnnew");
                        var SKEY = document.getElementById('TxTsearch').value;
                        TFramex = "#PGnum";
                        if (TYPE == 'old') {
                            var NSTP = parseInt(CSTP) + parseInt(LIMIT);
                        } else {
                            var NSTP = parseInt(CSTP) - parseInt(LIMIT);
                            if (NSTP < 0) {
                                return 0;
                            }
                        }
                        if (NSTP == 0) {
                            colorImage.style.filter = "grayscale(100%)";
                            colorImage.style.cursor = "auto";
                        } else {
                            colorImage.style.filter = "grayscale(0%)";
                            colorImage.style.cursor = "pointer";
                        }
                        var TrgtBox = document.getElementById('CSTP');
                        TrgtBox.value = NSTP;
                        var link = "./NextTen.php";
                        var tframe = "#FrameLast10";
                        var tframe1 = "#DelInfo";
                        var PageInfo = Math.floor(NSTP / LIMIT) + 1;
                        // $(tframe).html("...");
                        $(tframe).html("<div style='border:0px solid red; height:100px; width:100px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");
                        $(TFramex).html(PageInfo);
                        var arr = {};
                        arr["NSTP"] = NSTP;
                        arr["SKEY"] = SKEY;
                        $.ajax({
                            url: link,
                            type: "POST",
                            data: arr,
                            cache: false,
                            success: function (data) {
                                $(tframe).html(data);
                            }
                        });
                    }
                </script>