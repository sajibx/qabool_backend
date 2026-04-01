<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; include __DIR__ . "/conn.php"; 
?>
<?php
// include ("./sessionchk.php");
// include("./refchk.php");
// include("./conn.php");

$WNAME = $_POST["WNAME"];
$WSHIPMENT = $_POST["WSHIPMENT"];
$WCLIENT = $_POST["WCLIENT"];
?>

<script>
    function handleCheckboxClick(pid, checkbox) {
        var spidInput = document.getElementById('SPID');
        var spidValue = spidInput.value;
        var pidString = pid.toString(); // Ensure pid is a string

    //     let checkbox = document.getElementById(`delivery_${PID}`);
    // checkbox.checked = !checkbox.checked;  // Toggle the checked state

        if (checkbox.checked) {
            // Checkbox is now checked (on)
            if (spidValue === "00") {
                spidInput.value = pidString;
            } else {
                spidInput.value = spidValue + "," + pidString;
            }
        } else {
            // Checkbox is now unchecked (off)
            if (spidValue !== "00") {
                var pidArray = spidValue.split(",");
                var index = pidArray.indexOf(pidString);

                if (index !== -1) {
                    pidArray.splice(index, 1);
                    spidInput.value = pidArray.length > 0 ? pidArray.join(",") : "00";
                }
            }
        }
    }

    function handleCheckboxAuto(pid) {
        
        var TMP = document.getElementById('SPIDFULL').value;

        var TMP1 = TMP+","+pid;

        document.getElementById('SPIDFULL').value = TMP1;
    }

    function handleCheckboxAll() {
        
        var TMP = document.getElementById('SPIDFULL').value;

        let ids = TMP.split(',').slice(1);

         let checkbox = document.getElementById(`delivery_all`);

         if (checkbox.checked) {
            ids.forEach(id => {
            let checkbox = document.getElementById(`delivery_${id}`);
            if (checkbox) {
                checkbox.checked = true; // Toggle on the checkbox
                }
            });

            document.getElementById('SPID').value = TMP;
         }
         else{

            ids.forEach(id => {
            let checkbox = document.getElementById(`delivery_${id}`);
            if (checkbox) {
                checkbox.checked = false; // Toggle on the checkbox
                }
            });

            document.getElementById('SPID').value = '00';

         }

        

        // alert(TMP);
    }


</script>


<style>
    input[type=checkbox] {
        height: 0;
        width: 0;
        visibility: hidden;
    }

    label {
        cursor: pointer;
        text-indent: -9999px;
        width: 40px;
        height: 20px;
        background: grey;
        display: block;
        border-radius: 20px;
        position: relative;
    }

    label:after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 16px;
        height: 16px;
        background: #fff;
        border-radius: 16px;
        transition: 0.3s;
    }

    input:checked + label {
        background: #bada55;
    }

    input:checked + label:after {
        left: calc(100% - 2px);
        transform: translateX(-100%);
    }
</style>

    <input id="SPID" type="text" name="" style="display:none;" value="00">
    <input id="SPIDFULL" type="text" name="" style="display:none;" value="00">


<table border="1" class="StanTxt" style="padding: 0px; width:100%;text-align: center; border-collapse: collapse;">
    <tr>
        <th style="background-color: #c0d5ff;">SHIPMENT</th>
        <th style="background-color: #c0d5ff;">CLIENT</th>
        <th style="background-color: #c0d5ff;">CTN NO</th>
        <th style="background-color: #c0d5ff;">SHIPPING_MARK</th>
        <th style="background-color: #c0d5ff;">PWT</th>
        <th class="tddelivery" style="background-color: #c0d5ff;">Delivered<br>

            <div style="display: flex; justify-content: center; align-items: center;">
                <div>
                    <input type="checkbox" name="delivery_all" id="delivery_all" wfd-id="id_all" 
                        onclick="handleCheckboxAll()">
                    <label for="delivery_all"></label>
                </div>
            </div>

        </th>
    </tr>

    <?php
    $sql = "SELECT A.`SN`, A.`SHIPMENT`, A.`CTN_NO`, A.`CLIENT`, A.`SHIPPING_MARK`, A.`DELIVERY`, A.`P.WT`, B.`WAREHOUSE`
                FROM `packinglist` A
                LEFT JOIN `ShipmentLog` B
                ON A.`SHIPMENT` = B.`SHIPMENT`
                WHERE A.`DELIVERY` = 'N' AND B.`WAREHOUSE` = '$WNAME'";

    if ($WCLIENT != "WCLIENT") {
        $sql .= " AND A.`CLIENT` = '$WCLIENT'";
    }

    if ($WSHIPMENT != "WSHIPMENT") {
        $sql .= " AND A.`SHIPMENT` = '$WSHIPMENT'";
    }

    $sql .= " ORDER BY `SHIPMENT`, `CLIENT`, `CTN_NO`";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $PID = $row['SN'];
            $SHIPMENT = $row['SHIPMENT'];
            $CLIENT = $row['CLIENT'];
            $CTN_NO = $row['CTN_NO'];
            $SHIPPING_MARK = $row['SHIPPING_MARK'];
            $PWT = $row['P.WT'];
            $DELIVERY = $row['DELIVERY'];
            ?>

            <tr>
                <td><?php echo $SHIPMENT; ?></td>
                <td><?php echo $CLIENT; ?></td>
                <td><?php echo $CTN_NO; ?></td>
                <td><?php echo $SHIPPING_MARK; ?></td>
                <td><?php echo $PWT; ?></td>


                <script type="text/javascript">
                    handleCheckboxAuto(<?php echo $PID; ?>);
                </script>
                
<td>


    <div style="display: flex; justify-content: center; align-items: center;">
        <div>
            <input type="checkbox" name="delivery_<?php echo $PID; ?>" id="delivery_<?php echo $PID; ?>" wfd-id="id_<?php echo $PID; ?>" 
                    onclick="handleCheckboxClick(<?php echo $PID; ?>, this)">
            <label for="delivery_<?php echo $PID; ?>"></label>
        </div>
    </div>
</td>





                </td>
            </tr>

            <?php
        }
    }
    ?>
</table>