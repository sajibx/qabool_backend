<?php
include ("./refchk.php");

echo "Ledger admin";
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
}
 
table#TableLedger.dataTable tbody tr:hover > .sorting_1 {
  background-color: #c0d5ff;
}

table#Amnt.dataTable tbody{
    text-align:right;
}

</style>



<script type="text/javascript">

$(document).ready( function () {

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



// alert('xx')
	// datatable
		$dt = $('#TableLedger').dataTable({
			ajax: {
				contentType: "application/json",
				dataType : "json",
				url:'./GetLedgerData.php',
				dataSrc: '',



				data: function(d){
					d.ClientName = $('#ClientName').val();
				}
			},
			
"columnDefs": [
    {
      className: "dtRight", targets: [ 4,7]
    }
  ],
			"columns" : [
				
				{"title": "DATE", "data":"DATE"},
				{"title": "SHIPMENT", "data":"SHIPMENT"},
				{"title": "CLIENT", "data":"CLIENT"},
				{"title": "C.WT", "data":"CHARG_WEIGHT"},

				
				{"title": "AMOUNT", "data":"AMOUNT", "render": function ( data, type, row, meta ) {
						
						
				        return MoneyFormat(data);
				    }},
				    
				
				    
				{"title": "TYPE", "data":"TYPE"},
                
                {"title": "REMARKS", "data":"REMARKS"},
                
                {"title": "OS DUES", "data":"OUTSTANDING", "render": function ( data, type, row, meta ) {
						//console.log(data);
				        return MoneyFormat(data);
				    }},

			],

			"bSort" : false,
			"serverSide" : false,
	        "processing" : true,
			"bPaginate" : true,
			"bInfo" : true,
			"bFilter": true,
			"bSortable" : false,
			"autoWidth": true,
			"language" : {
	            emptyTable: "No data avaiable."
	        },
			//"pageLength": 10,
			//"lengthMenu": [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, 'All']],
			"dom": "<flB><t><i><p>",
			"buttons": [
	            'excel', 'pdf'
	        ],
			//"scrollX": true,          
			"initComplete": function (settings, json) {  
			$	("#dt_AD").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");            
			},
			//mark: true
			order: [[1, 'desc']],

			    createdRow: function (row, data, index) {
			    	var type1 = data['TYPE'];
			    	//console.log(type1);

			       if(type1 == 'SHIPMENT-BILL'){
			       			$(row).addClass('dtred');   //add class to row
			       }
			       else if(type1 == 'PAYMENT'){
			       			$(row).addClass('dtblue');   //add class to row

			       }
			       else if(type1 == 'DISCOUNT'){
			       			$(row).addClass('dtgreen');   //add class to row

			       }
			       
			        $('td', row).eq(4).css('text-align', 'right');  //add style to cell in third column
			        $('td', row).eq(7).css('text-align', 'right');  //add style to cell in third column
			     //   $('td', row).eq(3).css('text-align', 'center');  //add style to cell in third column
			    }
		});

$dt.api().buttons().container()
    .appendTo( $('.my-buttons' ) );

		$(document).on("keyup,click", "#ad_searchText", function(event) {  
			$dt.api().ajax.reload();
		});

		$(document).on("change", "#ClientName", function(event) {  
				// var FClient = this.value;

				// console.log(FClient);

				$dt.api().ajax.reload();

				
		});

		

		// $('#example').click(function(){

		// })

    $('#TableLedger').on( 'click', 'td', function (e) {
			var table = $('#TableLedger').DataTable();
			var cell = table.cell( this ).data();          // returns correct cell data
			//console.log(cell);
			var row = table.row(this).data();           // returns undefined
			//console.log(row);
    } );



})



</script>


<div style="height: 35px; width: 100%; border: 0px solid red;">

			<div style="min-width: 100%; border:0px solid black; float: right; margin-bottom: 10px;">


						<span id="LedTitle" style="font-size: 26; color: 990000; letter-spacing: 5px; float: left; font-weight:bold ;">LEDGER</span>

						        

						        <div style="height:100%; float:right; border: 0px solid red;">
						        
						        
								
						        <img title="Export Summary"  id="ExportIco" onclick="exportToExcelUpdated('EXPORT1', 'LEDGER SUMMARY (<?php echo date("d-m-Y"); ?>)')" src='./images/export.png' 
                                style='cursor: hand; width: 33px; display:none;
                                height: 30px;  border: 0px solid green; margin-right:30px;'>
                                
								
								
								This is ledger Admin
                                
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


</script>