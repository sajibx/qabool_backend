<?php
ob_start();
$Name = "-";
header('P3P: CP="CAO PSA OUR"');
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index1.php');
    exit;
} else {
    $UID   = $_SESSION['user'];
    $Name  = $_SESSION['NAME'];
    $UTYPE = $_SESSION['TYPE'];
    $FCP   = $_SESSION['FCP'];
    $UL    = $_SESSION['UL'];
}

if (!isset($UL) || $UL === '') {
    include __DIR__ . "/denied.php";
    exit;
}

?>

<?php $x = 0; ?>

<script src="./js/jquery-3.6.3.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<script>
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

</script>

<!DOCTYPE html>
<html>
<head>
    <title>FR TRADERS</title>
    <link rel="shortcut icon" href="./images/logo/favicon2.png" type="image/x-icon"/>
    <link rel="stylesheet" type="text/css" href="stylev2.css"> <!-- style sheet -->
</head>
<body onkeydown="handleKeyPress(event);" style="background-image: url('./images/bg_blue_digital.jpg'); z-index: -1; 
                                                    background-repeat: no-repeat; background-size:100% 100%;">

    <!-- hidden user/session data -->
    <input type="hidden" id="UID"   value="<?php echo $UID ?>">
    <input type="hidden" id="UName" value="<?php echo $Name ?>">
    <input type="hidden" id="UTYPE" value="<?php echo $UTYPE ?>">
    <input type="hidden" id="FCP"   value="<?php echo $FCP ?>">
    <input type="hidden" id="UL"    value="<?php echo $UL ?>">
    <input type="hidden" id="CLOC"  value="HOME">

    <!-- Banner -->
    <?php 
                        include ("./Functions.php");

     ?>
 <!-- Banner -->
<div class="main_banner">
    <div style="background-color: whitesmoke; border: 0px solid red; opacity: 0.80; border-radius: 10px;">

        <div class="banner_glass">
            <div class="banner_body">

                <div class="title" style="border: 0px solid red; position: relative;">
                    <!-- Logo with high z-index and full opacity -->
                    <div onclick="GoHome();" style="border: 0px solid red; margin-top: 5px; cursor: pointer; z-index: 9999; position: relative; opacity: 1;">
                        <img src='./images/logo/logo_banner.png' 
                             style='width: 200px; height: 43px; margin-top: 9px; margin-left: 20px; position: relative; z-index: 10000; opacity: 1;'>
                    </div>
                </div>

                

                <!-- Clock display on right -->
                <div style="float: right; border: 0px solid blue; height: 70px; width: 300px;">
                    <div id="MyClockDisplay" class="clock"></div>
                    <div id="MyClockDisplay2" class="clock2"></div>
                </div>

                <div id="ShipBanner" style="border: 0px solid green; margin-right: 20px; margin-top: 21px; position: relative; min-width: 50px; float: right;">

                    <div style="float: left; min-width:40px; margin-left:0px; margin-top:0px; font-size:14px;">
                        <?php 
                            echo "Welcome " . htmlspecialchars($Name, ENT_QUOTES, 'UTF-8');
                            if($UTYPE=="SADMIN"){ echo " (Administrator)"; }
                            else if($UTYPE=="Admin"){ echo " (Admin)"; }
                        ?>
                    </div>
                        
                </div>

            </div> <!-- end banner_body -->
        </div> <!-- end banner_glass -->

    </div>
</div> <!-- end main_banner -->
    <!-- end main_banner -->

    <!-- Main container -->
    <div class="container">

        <div id="main_body" style="background-image:url('./images/wpaper2.jpg'); background-repeat: repeat; background-size:100% auto;
                                        ">

            <!-- inner content -->
            <div>
                <?php 
                    // echo "this is test";
                    // echo $FCP;
                    if ($FCP == 1) {
                        echo "<script type='text/javascript'>GetPage('ChangeP');</script>";
                    } else if ($UL == "cn") {
                        echo "<script type='text/javascript'>GetPage('ExpenseLedger_cn');</script>";
                    } else if ($UL == "wh") {
                        echo "<script type='text/javascript'>GetPage('Whouse');</script>";
                    } 
                    else if ($UL == "1") {
                        echo "Account Activated. Please contact with admin.<br><br>";

                        ?>
                            <div style="height:60px; width:60px; margin: 0 auto; border:0px solid red;">
                                <img title="Logout" class="BoxButton" id="BtnLogout" onclick="Logout();" src='./images/logout.png' 
                                        style='cursor: hand; width: 50px;  float:right; height: 50px;'>
                            </div>
                        <?php
                    } 
                    else {
                        include ("./Last10.php");
                    }



                ?>
            </div>

        </div> <!-- end main_body -->

        <!-- Footer info (currently commented out) -->
        <div style="color:#c1c1c1; font-size:10px; float:right; text-align:right; margin-right:10px; margin-bottom:10px; margin-top: 10px;">
            <img src='./images/iwlogofinal2.png' style='width:50px; height:50px;'><br/> 
            The system was designed and developed by Import Wave<br/> 
            +880 1736 00 1223
        </div>

    </div> <!-- end container -->

    <!-- <div class="footer"></div> -->

</body>
</html>
