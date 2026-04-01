<?php
include __DIR__ . "/sessionchk2.php"; include __DIR__ . "/refchk.php"; 

// include ("./refchk.php");

?>


</script>

	<link rel="stylesheet" type="text/css" href="./js/jquery.dataTables.min.css">						<!-- style sheet here -->
	<link rel="stylesheet" type="text/css" href="./js/buttons.dataTables.min.css">						<!-- style sheet here -->


	<script src="./js/jquery-3.6.3.min.js"></script>
	<script src="./js/jquery.dataTables.min.js"></script>

	<script src="./js/dataTables.buttons.min.js"></script>
	<script src="./js/jszip.min.js"></script>
	<script src="./js/buttons.print.min.js"></script>
	<script src="./js/pdfmake.min.js"></script>
	<script src="./js/vfs_fonts.js"></script>
	<script src="./js/buttons.html5.min.js"></script>



<style type="text/css">

	.dtred{
		color: red;
	}

	.dtblue{
		color: blue;
	}

	.dtgreen{
		color: green;
	}
	
	.dtRight{
	    text-align: right !important;
	}


	table {
    font-size: 13px;

    width: 100%;
    table-layout: fixed;
    }

    .scroll {
    border: 1;
    border-collapse: collapse;
    }    
    .scroll tr {
    display: flex;
    }    
    .scroll td {
    flex: 1;
    }    
    .scroll thead tr:after {
    overflow-y: scroll;
    visibility: hidden;
    height: 0;
    }    
    .scroll thead th {
    flex: 1;
    display: block;
    
    }    
    .scroll tbody {
    display: block;

    overflow-y: auto;
    height: calc(100vh - 300px );
    }

table#TableLedger.dataTable tbody tr:hover {
  background-color: #c0d5ff;
  color: black;
  cursor: pointer;
  font-weight: bold;
}
 
table#TableLedger.dataTable tbody tr:hover > .sorting_1 {
  background-color: #c0d5ff;
}

table#Amnt.dataTable tbody{
    text-align:right;
}

</style>



<script type="text/javascript">

// function deleteRow(sn, shipment, client, amount) {
function deleteRow(sn, shipment, client, amount, type) {

        const response = confirm("Are you sure you want to delete selected record?");



    if (response) {


        var link       = "./SubmitDelReq.php";
        var UID        = document.getElementById('UID').value;

        var arr = {};
            arr["sn"]            = sn;
            arr["shipment"]      = shipment;
            arr["client"]        = client;
            arr["type"]          = type;
            arr["amount"]        = amount;
            arr["UID"]           = UID;

          $.ajax({ url:link,type:"POST", data: arr, cache: false, 
                    success:function(data){ 
                        
                        alert(data);                        
                        
                    } 
                });

          return 0;

    // alert('sn: ' + sn + ', shipment: ' + shipment + ', client: ' + client + ', amount: ' + amount);

    }

}

$(document).ready(function () {

function MoneyFormat(v) {
    const num = parseFloat(v);
    if (isNaN(num)) return '0.00';
    const formatted = num.toFixed(2);
    let [integerPart, decimalPart] = formatted.split(".");
    let negative = '';
    if (integerPart.startsWith('-')) {
        negative = '-';
        integerPart = integerPart.slice(1);
    }
    let lastThree = integerPart.slice(-3);
    let rest = integerPart.slice(0, -3);
    if (rest !== '') {
        rest = rest.replace(/\B(?=(\d{2})+(?!\d))/g, ",");
        integerPart = rest + "," + lastThree;
    }
    return negative + integerPart + "." + decimalPart;
}

    // datatable
    $dt = $('#TableLedger').dataTable({
        ajax: {
            contentType: "application/json",
            dataType: "json",
            url: './GetLedgerData.php',
            dataSrc: '',
            data: function(d){
                d.ClientName = $('#ClientName').val();
            }
        },
        "columnDefs": [
            {
                className: "dtRight", targets: [ 5, 8 ]  // Adjusted for new column
            },
            {
                targets: 0, // Target the first column (ACTION)
                width: "10px" // Set fixed width
            }
        ],



        "columns": [
            {
				"title": "-",
				"data": "sn",
				"render": function (data, type, row, meta) {
					return '<span class="delete-icon" style="cursor: pointer; display: none;" onclick="deleteRow(\'' + data + '\', \'' + row.SHIPMENT + '\', \'' + row.CLIENT + '\', \'' + row.AMOUNT + '\')">&#10060;</span>';
				}
			}

            // {
            //     "title": "-",
            //     "data": "sn",
            //     "render": function (data, type, row, meta) {
            //         return '<span class="delete-icon" style="cursor: pointer; display: none;" onclick="deleteRow(\'' + data + '\', \'' + row.SHIPMENT + '\', \'' + row.CLIENT + '\', \'' + row.AMOUNT + '\', \'' + row.TYPE + '\')">&#10060;</span>';
            //     }
            // }
,
            {"title": "DATE", "data": "DATE"},
            {"title": "SHIPMENT", "data": "SHIPMENT"},
            {"title": "CLIENT", "data": "CLIENT"},
            {"title": "C.WT", "data": "CHARG_WEIGHT"},
            {"title": "AMOUNT", "data": "AMOUNT", "render": function (data, type, row, meta) {
                return MoneyFormat(data);
            }},
            {"title": "TYPE", "data": "TYPE"},
            {"title": "REMARKS", "data": "REMARKS"},
            {"title": "BALANCE", "data": "OUTSTANDING", "render": function (data, type, row, meta) {
                return MoneyFormat(data);
            }},
        ],
        "bSort": false,
        "serverSide": false,
        "processing": true,
        "bPaginate": true,
        "bInfo": true,
        "bFilter": true,
        "bSortable": false,
        "autoWidth": true,
        "language": {
            emptyTable: "No Data Found."
        },
        "dom": "<flB><t><i><p>",
        "buttons": [
            'excel', 'pdf'
        ],
        "order": [[2, 'desc']],  // Adjusted for new column
        "initComplete": function (settings, json) {  
            $("#dt_AD").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");            
        },
        createdRow: function (row, data, index) {
            var type1 = data['TYPE'];
            var ISHIP = data['SHIPMENT'];
            var ICLIENT = data['CLIENT'];

            if(type1 == 'SHIPMENT-BILL'){
                $(row).addClass('dtred');
                // $(row).on('click', function () {
                //     ShowInvoice(ISHIP, ICLIENT);
                // });
            } else if(type1 == 'PAYMENT'){
                $(row).addClass('dtblue');
            } else if(type1 == 'DISCOUNT'){
                $(row).addClass('dtgreen');
            }

            $('td', row).eq(5).css('text-align', 'right');  // Adjusted for new column
            $('td', row).eq(8).css('text-align', 'right');  // Adjusted for new column

            $('td', row).eq(2).on('click', function () {ShowInvoice(ISHIP, ICLIENT);});         //call show invoice function on click
            $('td', row).eq(3).on('click', function () {ShowInvoice(ISHIP, ICLIENT);});
            $('td', row).eq(4).on('click', function () {ShowInvoice(ISHIP, ICLIENT);});
            $('td', row).eq(5).on('click', function () {ShowInvoice(ISHIP, ICLIENT);});
            $('td', row).eq(6).on('click', function () {ShowInvoice(ISHIP, ICLIENT);});
            $('td', row).eq(7).on('click', function () {ShowInvoice(ISHIP, ICLIENT);});
            $('td', row).eq(8).on('click', function () {ShowInvoice(ISHIP, ICLIENT);});


            // Add hover event
            $(row).hover(
                function() { // mouse in
                    $(this).find('.delete-icon').show();
                }, function() { // mouse out
                    $(this).find('.delete-icon').hide();
                }
            );
        }
    });

    $dt.api().buttons().container().appendTo($('.my-buttons'));

    $(document).on("keyup, click", "#ad_searchText", function() {
        $dt.api().ajax.reload();
    });

    $(document).on("change", "#ClientName", function() {
        $dt.api().ajax.reload();
    });

    $('#TableLedger').on('click', 'td', function () {
        var table = $('#TableLedger').DataTable();
        var cell = table.cell(this).data();
        var row = table.row(this).data();
    });

})

	// ---------------------
function ShowInvoice(ISHIP, ICLIENT){

var link       = "./Guid.php";

var guid = generateGUID();

if(ISHIP == ''){
    exit();
}

var arr = {};
                        arr["SID"]  = ISHIP;
                        arr["CID"]  = ICLIENT;
                        arr["guid"] = guid;
                        arr["TYPE"] = 'INV-L';

						$.ajax({ 

						url:link,type:"POST",
						data: arr,
						cache: false, 
						success:function(data){                              
							// $(tframe).html(data);
							
							var url = "./i.php?iid=" + guid;
							
							var myWindow = window.open(url, "", "");

							$(tframe).html("Invoice");
							
							
							
						} 
						});
// alert(guid);

}

function generateGUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16 | 0,
            v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}


</script>


<div style="height: 35px; width: 100%; border: 0px solid red;">

			<div style="min-width: 100%; border:0px solid black; float: right; margin-bottom: 10px;">


						<span id="LedTitle" style="font-size: 26; color: 990000; letter-spacing: 5px; float: left; font-weight:bold ;">LEDGER</span>

						        

						        <div style="height:100%; float:right; border: 0px solid red;">
						        
						        
								
						        <img title="Export Summary"  id="ExportIco" onclick="exportToExcelUpdated('EXPORT1', 'LEDGER SUMMARY (<?php echo date("d-m-Y"); ?>)')" src='./images/export.png' 
                                style='cursor: hand; width: 33px; display:none;
                                height: 30px;  border: 0px solid green; margin-right:30px;'>
                                
								
								
								<img title="Ledger Summary" id="SumIco" onclick="ShowSummaryL();" src='./images/summary.png' style='cursor: hand; width: 33px; 
                                height: 30px;  border: 0px solid green; margin-right:20px;'>
                                
                                <img onclick="GetPage('Ledger');" src='./images/reload.png' style='cursor: hand; width: 33px; float: right;
								height: 30px;  border: 0px solid green;'>
                                
						</div>

						<div id="LedgerClientList" style="min-width:300px; ; border:0px solid red; float:right; margin: 0 auto;">
						    
						    
	
<!-- -------------------------------------------------------------------------------------------- -->
<select class="customSelect classic" id="ClientName" onchange="GetTotal();" style="width:200px; float:right; margin-right:30px;">
    <option style="text-align: center; font-weight: bolder; float:right; " value="CLIENT">CLIENT</option>
    <?php 
        include ("./conn.php");
    
        $sql_item = "SELECT distinct(`CLIENT`) 'CLIENT'
        FROM `last_shipment` order by `CLIENT` asc";
        $result = $conn->query($sql_item);
    
        if ($result->num_rows > 0) {
        // output data of each row
            while($row = $result->fetch_assoc()) {
    
            $CLIENT                = $row['CLIENT'];
    ?>
            <option value="<?php echo $CLIENT ; ?>"><?php echo $CLIENT ; ?></option>
    <?php  }
        }
    ?>
</select>
<!-- -------------------------------------------------------------------------------------------- -->
<div id="InfoBox" style="float: right; border: 0px solid blue; min-width: 100px; padding-top:0px;
						                 margin-right: 20px; text-align:right; color:red;"></div>

						</div>


			</div>
</div>
<div id="LedgerBody" style="min-height: 400px; width: 100%; border: 0px solid blue;">

	<table id="TableLedger" class="display" style="width:100%"></table>
	
	
    <div class="my-buttons" style="float:left;"></div> 



</div>


<script type="text/javascript">
	  function GetTotal() {

      	var ClientName = document.getElementById('ClientName').value;		

	    var link = "./GetTotal.php?FClient=" + ClientName;

    	var tframe = "#InfoBox";

	    $(tframe).html("Loading...");

	    $.ajax({
	      url: link,
	      type: "GET",
	      dataType: "html",
	      success: function(data) {
	        $(tframe).html("");
	        $(tframe).html(data);
	      }
	    });



      // console.log(link);

  }

function ShowSummaryL(){
    
    var link       = "./LedgerSum.php";
    var tframe      = "#LedgerBody";

    // alert(link);
    // exit();
    
    var ExportIco = document.getElementById('ExportIco');
        ExportIco.style.display = 'inline';
        
    var SumIco = document.getElementById('SumIco');
        SumIco.style.display = 'none';
    
    // var LedgerClientList = document.getElementById('LedgerClientList');
    //     LedgerClientList.style.display = 'none';  
        
    // var PlusIco = document.getElementById('PlusIco');
    //     PlusIco.style.display = 'none'; 
    
    var xframe      = "#LedTitle";
    
    $(xframe).html("LEDGER SUMMARY");
    
    
    $(tframe).html("<div style='border:0px solid red; height:100px; width:100px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");
    
    var arr = {};
    // arr["ShipID"]  = ShipID;
                        
                        
                        
          $.ajax({ url:link,type:"POST", data: arr, cache: false, 
                    success:function(data){                              
                        $(tframe).html(data);
                    } 
                });
}

</script>

