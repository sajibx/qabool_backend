<?php 


// // money format latest
// function moneyFormat($num) {                                                                   
//     // Convert input to float and format to exactly 2 decimal places
//     $formattedNum = number_format((float)$num, 2, '.', '');
    
//     // Split into integer and decimal parts
//     $parts = explode('.', $formattedNum);
//     $integerPart = $parts[0];
//     $decimalPart = '.' . $parts[1];

//     // Handle negative numbers
//     $isNegative = $integerPart[0] === '-';
//     if ($isNegative) {
//         $integerPart = substr($integerPart, 1); // Remove the negative sign
//     }

//     $explrestunits = "";
//     if (strlen($integerPart) > 3) {
//         $lastthree = substr($integerPart, -3);
//         $restunits = substr($integerPart, 0, -3); // Extracts all but the last three digits
//         $restunits = (strlen($restunits) % 2 == 1) ? "0" . $restunits : $restunits; // Add leading zero if needed
//         $expunit = str_split($restunits, 2);
//         for ($i = 0; $i < sizeof($expunit); $i++) {
//             // Creates each of the 2's group and adds a comma
//             if ($i == 0) {
//                 $explrestunits .= (int)$expunit[$i] . ","; // Remove leading zeros for first group
//             } else {
//                 $explrestunits .= $expunit[$i] . ",";
//             }
//         }
//         $thecash = $explrestunits . $lastthree . $decimalPart;
//     } else {
//         $thecash = $integerPart . $decimalPart;
//     }

//     return $isNegative ? '-' . $thecash : $thecash; // Add negative sign if needed
// }

?>

<script>
    
    
    function showTime(){
            var date = new Date();
            var h = date.getHours(); // 0 - 23
            var m = date.getMinutes(); // 0 - 59
            var s = date.getSeconds(); // 0 - 59
            var session = "AM";
            
            if(h == 0){
                h = 12;
            }
            
            if(h > 12){
                h = h - 12;
                session = "PM";
            }
            
            h = (h < 10) ? "0" + h : h;
            m = (m < 10) ? "0" + m : m;
            s = (s < 10) ? "0" + s : s;
            
            var time = h + ":" + m + ":" + s + " " + session;
        
            // console.log(time);
        
            var tframe = "#MyClockDisplay";
            var tframe2 = "#MyClockDisplay2";
            
            $(tframe).html(time);
            $(tframe2).html(time);
            
            // document.getElementById("MyClockDisplay").html(time);
            // document.getElementById("MyClockDisplay").html = time;
            
            setTimeout(showTime, 1000);
    
}

showTime();



</script>




<script type="text/javascript">

function handleKeyPress(event) {
    
    var CLOC = document.getElementById('CLOC').value;
    var x = event.keyCode;
    var y = x-48;
    
    var element = event.target;
      if (element && (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA')) {return;}
  
      if (x === 39 && CLOC == 'HOME') {NextTen('old');}
      if (x === 37 && CLOC == 'HOME') {NextTen('new');}
      
      if (x > 48 && x <= 57  && CLOC == 'HOME') {getShipment(y);}
      if ( x == 189  && CLOC == 'HOME') {getShipment(11);}
      if ( x == 187  && CLOC == 'HOME') {getShipment(12);}

      // console.log(x);

      if (x === 48 && CLOC == 'HOME') {getShipment(10);}
      if (x === 69 && CLOC == 'HOME') {GetPage('ExpenseLedger');}
      if (x === 67 && CLOC == 'HOME') {GetPage2('Correction');}
      if (x === 80 && CLOC == 'HOME') {GetPage2('NewEntry');}
      if (x === 85 && CLOC == 'HOME') {window.open('/upload/');}
      
      if (x === 73 && CLOC == 'SHIPMENT') {ShowBill();}
      if (x === 83 && CLOC == 'SHIPMENT') {ShowSummary();}
      if (x === 82 && CLOC == 'SHIPMENT') {Reload();}
      if (x === 65 && CLOC == 'SHIPMENT') {FAddEntry();}
      
      if (x === 39 && CLOC == 'ExpenseLedger') {GetExp('next');}
      if (x === 37 && CLOC == 'ExpenseLedger') {GetExp('prev');}
      if (x === 68 && CLOC == 'ExpenseLedger') {BtnDWM('D');}
      if (x === 87 && CLOC == 'ExpenseLedger') {BtnDWM('W');}
      if (x === 77 && CLOC == 'ExpenseLedger') {BtnDWM('M');}
      if (x === 82 && CLOC == 'ExpenseLedger') {GetPage('ExpenseLedger');}
      
      if (x === 76 && ((CLOC == 'HOME') || (CLOC == 'SHIPMENT'))) {GetPage('Ledger');}
      
      
      if (x === 8) {GoHome();}
      if (x === 192) {GoHome();}
      
      
      
     
      
}




  var tableToExcel = (function() {
  var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns=""><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
    , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
    , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
  return function(table, name) {
    if (!table.nodeType) table = document.getElementById(table)
    var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
    window.location.href = uri + base64(format(template, ctx))
  }
})()


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


function generateGUIDx() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

function GetPage(pageName) {
  var link = pageName + ".php";
  var tframe = "#main_body";
  
  var LBOX = document.getElementById('CLOC');
        LBOX.value = pageName;

  var GUIDx =  generateGUIDx();

  // alert(GUIDx);
   
  $(tframe).html("<div style='border:0px solid red; height:80px; width:80px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");
  
  // asdf
//   $.ajax({
//   url: link,
//   type: "POST", 
//   dataType: "html",
//   headers: {
//     "GUIDx": GUIDx
//   },
//   success: function(data) {
//     $(tframe).html("");
//     $(tframe).html(data);
//   }
// });
  $.ajax({
    url: link,
    type: "POST", // Changed the request type to POST
    dataType: "html",
      headers: {
        "GUIDx": GUIDx
    },
    success: function(data) {
      $(tframe).html("");
      $(tframe).html(data);
    }
  });
}

function GetPage2(pageName) {
  var link = pageName + ".php";
  var tframe = "#SideCon";
  
  var LBOX = document.getElementById('CLOC');
        LBOX.value = pageName;

  var box11 = document.getElementById('DelToggle');
  box11.style.display = 'none';

  $(tframe).html("<div style='border:0px solid red; height:80px; width:80px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");

  $.ajax({
    url: link,
    type: "POST", // Changed the request type to POST
    dataType: "html",
    success: function(data) {
      $(tframe).html("");
      $(tframe).html(data);
    }
  });
}

function GetPage3(pageName,frame) {
  var link = pageName + ".php";
  var tframe = "#"+frame;
  
  var LBOX = document.getElementById('CLOC');
        LBOX.value = pageName;

  var box11 = document.getElementById('DelToggle');
  box11.style.display = 'none';

  $(tframe).html("<div style='border:0px solid red; height:80px; width:80px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");

  $.ajax({
    url: link,
    type: "POST", // Changed the request type to POST
    dataType: "html",
    success: function(data) {
      $(tframe).html("");
      $(tframe).html(data);
    }
  });
}


function Log(EVENT,REMARKS){
    var UID     = document.getElementById('UID').value;
    var link    = "./log.php";
    
    var arr = {};
                arr["EVENT"]    = EVENT;
                arr["REMARKS"]  = REMARKS;
                arr["UID"]      = UID;
                  
                  $.ajax({ url:link,
                          type:"POST",
                          data: arr,
                          cache: false,
                          success:function(data){
                            //   $(tframe).html("Ledger Update complete.");
                          } });
}



</script>

<script>
    function Logout(){
        
        var tframe = "#NewFilter";
    
         $(tframe).html("<div style='border:0px solid red; height:80px; width:80px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");
         
        location.replace("./logout.php")
    }
  
    function GoHome(){
        
         var tframe = "#main_body";
    
         $(tframe).html("<div style='border:0px solid red; height:80px; width:80px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");
         
         location.replace("./index1.php")
    }
  




    function ChangePass(){
        
            var OldPass        = document.getElementById('OldPass').value;            
            var NewPass        = document.getElementById('NewPass').value;
            var NewPass2       = document.getElementById('NewPass2').value;
            var UID            = document.getElementById('UID').value;
            
            var tframe = "#StatP";
            
            // alert(NewPass.length);
            
            // if(NewPass.length==0){
            //     alert("Password is blank");
            //     return;
            // }
            // else 
            // if(NewPass.length<6){
            //     alert("New Password should be atleast 6 characters.");
            //     return;
            // }
            
            if(NewPass!=NewPass2){
                alert('Please check New Pass again');
                var box11 = document.getElementById('NewPass2');
                    box11.focus();
                return;
            }
            
            var link       = "./PassChangeProcess.php";
            
            var arr = {};
                        arr["OldPass"]  = OldPass;
                        arr["NewPass"]  = NewPass;
                        arr["UID"]      = UID;
                        
                        
                        $.ajax({ url:link, type:"POST", data: arr, cache: false, success:function(data){
                              
                                    $(tframe).html(data);
                              
                        }});
}



function Logout(){
        var tframe = "#main_body";
        
        $(tframe).html("<div style='border:0px solid red; height:80px; width:80px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");
        
        location.replace("./logout.php")


  }
  
  
  
  
// Convert a string to an ArrayBuffer
function stringToArrayBuffer(str) {
  var encoder = new TextEncoder();
  return encoder.encode(str);
}

// Convert an ArrayBuffer to a string
function arrayBufferToString(buffer) {
  var decoder = new TextDecoder();
  return decoder.decode(buffer);
}

// Generate a random encryption key
async function generateKey() {
  return await window.crypto.subtle.generateKey(
    {
      name: "AES-CBC",
      length: 256,
    },
    true,
    ["encrypt", "decrypt"]
  );
}

// Encrypt data using AES-CBC algorithm
async function encryptAES(data, key) {
  var iv = window.crypto.getRandomValues(new Uint8Array(16));
  var encodedData = stringToArrayBuffer(data);

  var encryptedData = await window.crypto.subtle.encrypt(
    {
      name: "AES-CBC",
      iv: iv,
    },
    key,
    encodedData
  );

  var encryptedBytes = new Uint8Array(encryptedData);
  var ciphertext = new Uint8Array(iv.length + encryptedBytes.length);
  ciphertext.set(iv);
  ciphertext.set(encryptedBytes, iv.length);

  return Array.from(ciphertext).map(byte => String.fromCharCode(byte)).join('');
}

// Decrypt data using AES-CBC algorithm
async function decryptAES(ciphertext, key) {
  var ciphertextBytes = new Uint8Array(ciphertext.length);
  for (var i = 0; i < ciphertext.length; i++) {
    ciphertextBytes[i] = ciphertext.charCodeAt(i);
  }

  var iv = ciphertextBytes.slice(0, 16);
  var encryptedBytes = ciphertextBytes.slice(16);

  var decryptedData = await window.crypto.subtle.decrypt(
    {
      name: "AES-CBC",
      iv: iv,
    },
    key,
    encryptedBytes
  );

  var decryptedBytes = new Uint8Array(decryptedData);
  var decryptedString = arrayBufferToString(decryptedBytes);

  return decryptedString;
}


function NextDate(dateString, days) {
  var parts = dateString.split('-');
  var year = parts[0];
  var month = parts[1];
  var day = parts[2];
  
  var date = new Date(year, month - 1, day);
  date.setDate(date.getDate() + days);
  
  var resultYear = date.getFullYear().toString();
  var resultMonth = (date.getMonth() + 1).toString().padStart(2, '0');
  var resultDay = date.getDate().toString().padStart(2, '0');
  
  return `${resultYear}-${resultMonth}-${resultDay}`;
}

function CurDate(){
    var currentDate = new Date();
    var year = currentDate.getFullYear().toString();
    var month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
    var day = currentDate.getDate().toString().padStart(2, '0');
    
    var formattedDate = `${year}-${month}-${day}`;
    // console.log(formattedDate);
    
    return formattedDate;
}



</script>








