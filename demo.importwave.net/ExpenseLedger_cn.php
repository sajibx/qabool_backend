<?php
include ("./sessionchk2.php");
include ("./refchk.php");
?> 



<script type="text/javascript">

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


</script>

<?php 

date_default_timezone_set('Asia/Shanghai');

$Date = date("Y-m-d");
$EDate = date("Y-m-d");

?>

<input type="hidden" id="CDATE" value="<?php echo $Date; ?>">
<input type="hidden" id="EDATE" value="<?php echo $EDate; ?>">
<input type="hidden" id="DWM" value="D">

<div style="height: 35px; width: 100%; border: 0px solid red;">
  <div style="min-width: 100%; border:0px solid black; float: right; margin-bottom: 10px;">
      
    <span id="LedTitle" style="font-size: 26; color: 990000; letter-spacing: 1px; float: left; font-weight:bold ;">Expense China</span>

<script type="text/javascript">
        function BtnDWM(TYPE){
        
            var BtnD = document.getElementById('BtnD');
            var BtnW = document.getElementById('BtnW');
            var BtnM = document.getElementById('BtnM');
            
            var CDATEx = document.getElementById('CDATE').value;
            
            if (TYPE == 'D') {
                BtnW.classList.remove('active');
                BtnD.classList.add('active');
                BtnM.classList.remove('active');
            
                var TBox2   = document.getElementById('EDATE');
                TBox2.value = CDATEx;
                
                var SDT     = CDATEx;
                var EDT     = CDATEx;
            }
            
            if (TYPE == 'W') {
                
                    BtnW.classList.add('active');
                    BtnD.classList.remove('active');
                    BtnM.classList.remove('active');
            
                var CDATE   = new Date(CDATEx);
                
                var TMPDATE = new Date(CDATE.setDate(CDATE.getDate() - (CDATE.getDay() + 1) % 7));
                
                var year    = TMPDATE.getFullYear();
                var month   = String(TMPDATE.getMonth() + 1).padStart(2, '0');
                var day     = String(TMPDATE.getDate()).padStart(2, '0');
                
                var FDATEW  = `${year}-${month}-${day}`;
                var LDATEW  = NextDate(FDATEW, 6);
                
                var TBox1   = document.getElementById('CDATE');
                var TBox2   = document.getElementById('EDATE');
                
                TBox1.value = FDATEW;
                TBox2.value = LDATEW;
                
                var SDT     = FDATEW;
                var EDT     = LDATEW;
            }
            
            if (TYPE == 'M') {
                    BtnW.classList.remove('active');
                    BtnD.classList.remove('active');
                    BtnM.classList.add('active');
                
                var CDATE   = new Date(CDATEx);
                
                var TMPDATE = new Date(CDATE.setDate(CDATE.getDate() - (CDATE.getDay() + 1) % 7));
                
                var year    = TMPDATE.getFullYear();
                var month   = String(TMPDATE.getMonth() + 1).padStart(2, '0');
                var day     = String(TMPDATE.getDate()).padStart(2, '0');
                
                var FDATEM  = `${year}-${month}-01`;
                var LDATEM  = `${year}-${month}-${new Date(year, TMPDATE.getMonth() + 1, 0).getDate()}`;
                
                var TBox1   = document.getElementById('CDATE');
                var TBox2   = document.getElementById('EDATE');
                
                TBox1.value = FDATEM;
                TBox2.value = LDATEM;
                
                var SDT     = FDATEM;
                var EDT     = LDATEM;
            }
            
            
            
            var TrgtBox = document.getElementById('DWM');
                TrgtBox.value = TYPE;
            
                LoadExp(SDT, EDT, TYPE);
}
    
    

function GetExp(TYPE){
        

        var CDATEx = document.getElementById('CDATE').value;
        var EDATE = document.getElementById('EDATE').value;
        var DWM = document.getElementById('DWM').value;
    
        // LoadExp(CDATE, EDATE);
        
        if(DWM == 'D'){
            
            if(TYPE == 'prev'){
                var NDATE1 = NextDate(CDATEx, -1 );
            }else{
                var NDATE1 = NextDate(CDATEx, 1 );
            }
        
            var NDATE2 = NDATE1;
            
        }
        
        if(DWM == 'W'){
            if(TYPE == 'prev'){
                var NDATE1 = NextDate(CDATEx, -7 );
            }else{
                var NDATE1 = NextDate(CDATEx, 7 );
            }
        
            var NDATE2 = NextDate(NDATE1, 6 );
            
        }
        
        if (DWM === 'M') {
        if (TYPE === 'prev') {
            let dateObj = new Date(CDATEx);
            dateObj.setDate(0); // Set the date to the last day of the previous month
            dateObj.setDate(1); // Set the date to the first day of the previous month
    
            let year = dateObj.getFullYear();
            let month = String(dateObj.getMonth() + 1).padStart(2, "0");
            let day = String(dateObj.getDate()).padStart(2, "0");
    
            let result = `${year}-${month}-${day}`;
    
            var NDATE1 = result;
    
            var NDATE2 = GetLastD(CDATEx);
        } else {
            
            let dateObj = new Date(CDATEx);
            dateObj.setMonth(dateObj.getMonth() + 1); // Set the date to the next month
            dateObj.setDate(1); // Set the date to the first day of the next month
    
            let year = dateObj.getFullYear();
            let month = String(dateObj.getMonth() + 1).padStart(2, "0");
            let day = String(dateObj.getDate()).padStart(2, "0");
    
            let result = `${year}-${month}-${day}`;
    
            var NDATE1 = result;
    
            var NDATE2 = GetLastDN(result);
            
            // console.log(NDATE1);
        }
    }
    
    function GetLastD(FDATE) {
        let dateObj     = new Date(FDATE);
        dateObj.setDate(0); // Set the date to the last day of the previous month
        
        let year        = dateObj.getFullYear();
        let month       = String(dateObj.getMonth() + 1).padStart(2, "0");
        let day         = String(dateObj.getDate()).padStart(2, "0");
        
        let result      = `${year}-${month}-${day}`;
        
        return result;
    }

    function GetLastDN(FDATE) {
        let dateObj = new Date(FDATE);
        dateObj.setMonth(dateObj.getMonth() + 1); // Set the date to the next month
        dateObj.setDate(0); // Set the date to the last day of the current month
        
        let year    = dateObj.getFullYear();
        let month   = String(dateObj.getMonth() + 1).padStart(2, "0");
        let day     = String(dateObj.getDate()).padStart(2, "0");
        
        let result  = `${year}-${month}-${day}`;
        
        return result;
    }
    
    
    // alert(NDATE2);
    
    var TrgtBox1    = document.getElementById('CDATE');
    var TrgtBox2    = document.getElementById('EDATE');
    
    TrgtBox1.value  = NDATE1;
    TrgtBox2.value  = NDATE2;
    
    var DWM         = document.getElementById('DWM').value;
    
    
    LoadExp(NDATE1, NDATE2, DWM);
        
}
    
function LoadExp(D1, D2, TYPEx){
    
    // alert("LoadExp");
    // return;
    
    
    var link   = "./ExpLedgerTable_cn.php";
    
    var tframe = "#ExpLedgerTable";
    
    // var TYPEx   = document.getElementById('DWM');
    
    $(tframe).html("Please wait...");
    
    // alert(TYPEx);
    
    var arr = {};
            
            arr["XDATE1"]    = D1;
            arr["XDATE2"]    = D2;
            arr["TYPE"]      = TYPEx;
                  
                  $.ajax({ url:link,
                          type:"POST",
                          data: arr,
                          cache: false,
                          success:function(data){
                              $(tframe).html(data);
                          } });
}

</script>
    
    <!-- <div id="BtnD" style="margin-left:40px;" onclick="BtnDWM('D');" class="flat_buttonX left_round active" >Daily</div>
    <div id="BtnW" onclick="BtnDWM('W');" class="flat_buttonX" >Weekly</div>
    <div id="BtnM" onclick="BtnDWM('M');" class="flat_buttonX right_round" >Monthly</div> -->
    <button id="BtnD" class="glow-on-hover active" onclick="BtnDWM('D');" style="min-width:110px; margin-left:30px; height:20px;">Daily</button>
    <button id="BtnW" class="glow-on-hover" onclick="BtnDWM('W');" style="min-width:110px; height:20px;">Weekly</button>
    <button id="BtnM" class="glow-on-hover" onclick="BtnDWM('M');" style="min-width:110px; height:20px;">Monthly</button>
    
<!--     <button id="Btn-D" class="button-6" onclick="BtnDWM('D');" style="min-width:110px; margin-left:30px; height:20px;">Daily</button>
    <button id="Btn-W" class="button-6" onclick="BtnDWM('W');" style="min-width:110px; height:20px;">Weekly</button>
    <button id="Btn-M" class="button-6" onclick="BtnDWM('M');" style="min-width:110px; height:20px;">Monthly</button>
    
 -->
    <div style="float:right; border: 0px solid red;">
 
        <img onclick="GetPage('ExpenseLedger_cn');" src='./images/reload.png' style='cursor: hand; width: 33px; float: right;
    								height: 30px;  border: 0px solid green;'>
    								
        <img   id="BtnNext" onclick="GetExp('next');" src='./images/right-chevron.png' 
                        style='cursor: hand; width: 30px; height: 30px;  float:right; margin-top:0; margin-right:40px;'>
                        
        <img  id="BtnPrev" onclick="GetExp('prev');" src='./images/left-chevron.png' 
                        style='cursor: hand; width: 30px; height: 30px;  float:right; margin-top:0; margin-right:30px;'>
               
    </div>

    <div style="min-width:40px; height: 30px; border: 0px solid blue; float: right; margin-right: 100px;">
                    <img title="Change Password" onclick="GetPage('ChangeP');" src='./images/cpass.png'
                             style='cursor: hand; width: 30px; height: 30px; margin-right: 20px;'>

                    <img title="Logout"  onclick="Logout();" src='./images/logout.png' 
                            style='cursor: hand; width: 30px;  height: 30px;'>

        
    </div>

    
    <div id="LedgerClientList" style="min-width:300px; ; border:0px solid red; float:right; margin: 0 auto;">
      <!-- -------------------------------------------------------------------------------------------- -->
      <div id="InfoBox" style="float: right; border: 0px solid blue; min-width: 100px; padding-top:0px; margin-right: 20px; text-align:right; color:red;"></div>
    </div>
  </div>
</div>
<div id="ExpLedgerTable" style="min-height: 250px; width: 100%; border: 0px solid blue;">


<?php include ("./ExpLedgerTable_cn.php"); ?>


</div>

<script>
    function Logout(){
         
        location.replace("./logout.php")
    }

</script>