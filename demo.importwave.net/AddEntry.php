<?php
include ("./refchk.php");

$FClient            = $_REQUEST["FClient"];
?>
<div>

<script>

        const pwtInput = document.getElementById('nPWT');
        const unitPriceInput = document.getElementById('nUNIT_PRICE');
        const totalInput = document.getElementById('nTOTAL');

    // Function to calculate and update total
    function updateTotal() {
            // Get references to the input fields


        pwt = parseFloat(pwtInput.value) || 0;
        unitPrice = parseFloat(unitPriceInput.value) || 0;
        total = pwt * unitPrice;
        totalInput.value = total.toFixed(2); // Format to 2 decimal places
    }

    // // Add event listeners for input changes
    pwtInput.addEventListener('input', updateTotal);
    unitPriceInput.addEventListener('input', updateTotal);
</script>

<style>
    .EdTxt {
        background-color: #fff8e5;
        width: calc(100% - 2px); /* Make input 2px shorter than td width */
        text-align: center;
        box-sizing: border-box; /* Include padding and borders in width */
        padding: 2px; /* Minimal padding for appearance */
        margin: 0; /* Remove default margins */
    }
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        border: 0;
    }
    #export {
        table-layout: fixed; /* Enforce fixed column widths based on <th> */
        width: 100%;
        padding: 0;
        font-size: 13px;
        font-family: Calibri, sans-serif;
        text-align: center;
    }
    #export td {
        padding: 0; /* Remove padding from cells */
        margin: 0;
    }
</style>

<table id="export">
    <tr style="background-color: #c0d5ff;">
        <th width="100px">CTN NO</th>
        <th width="150px">CLIENT</th>
        <th>SHIPPING MARK</th>
        <th>ITEM LIST</th>
        <th width="70px">WEIGHT</th>
        <th width="70px">P.WT</th>
        <th width="100px">UNIT PRICE</th>
        <th width="100px">TOTAL</th>
    </tr>
    <tr>
        <td>
            <label for="nCTN" class="sr-only">Carton Number</label>
            <input id="nCTN" class="EdTxt" type="text" value="1" style="width: 98px;" />
        </td>
        <td>
            <label for="nCLIENT" class="sr-only">Client</label>
            <input id="nCLIENT" class="EdTxt" type="text" style="width: 148px;" value="<?php echo htmlspecialchars($FClient); ?>" />
        </td>
        <td>
            <label for="nSHIP" class="sr-only">Shipping Mark</label>
            <input id="nSHIP" class="EdTxt" type="text" style="width: 148px;" placeholder="-" />
        </td>
        <td>
            <label for="nITEM" class="sr-only">Item List</label>
            <input id="nITEM" class="EdTxt" type="text" style="width: 148px;" placeholder="-" />
        </td>
        <td>
            <label for="nWEIGHT" class="sr-only">Weight</label>
            <input id="nWEIGHT" class="EdTxt" style="width: 68px;" type="number" value="1" />
        </td>
        <td>
            <label for="nPWT" class="sr-only">P.WT</label>
            <input id="nPWT" class="EdTxt" style="width: 68px;" type="number" value="1" />
        </td>
        <td>
            <label for="nUNIT_PRICE" class="sr-only">Unit Price</label>
            <input id="nUNIT_PRICE" class="EdTxt" style="width: 98px;" type="number" step="0.01" value="0" />
        </td>
        <td>
            <label for="nTOTAL" class="sr-only">Total</label>
            <input id="nTOTAL" class="EdTxt" style="width: 98px;" type="number" step="0.01" value="0" />
        </td>
    </tr>
</table>




</div>


