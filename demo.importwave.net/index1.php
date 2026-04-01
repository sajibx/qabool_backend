<?php
header('P3P: CP="CAO PSA OUR"');
session_start();
if (isset($_SESSION['user'])){
    echo "<script>location.href='./index3.php'</script>";
    exit();
}

function create_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = base64_encode(openssl_random_pseudo_bytes(32));
    }
}

create_csrf_token();
?>
<!DOCTYPE html>
<html>
<head>
<title>FR TRADERS</title>
<link rel="shortcut icon" href="./images/logo/favicon2.png" type="image/x-icon"/>
<link rel="stylesheet" type="text/css" href="stylev2.css"> <!-- style sheet here -->
<script src="./js/jquery-3.6.3.min.js"></script>
<script>
function GoHome() {
    var tframe = "#main_body";
    $(tframe).html("<div style='border:0px solid red; height:100px; width:100px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");
    location.replace("./index1.php");
}
</script>
</head>
<body>
<div class="main_banner">
        <div class="banner_glass">
            <div class="banner_body">
                <div class="title" style="border: 0px solid red;">
                    <div onclick="GoHome();" style="border: 0px solid red; margin-top: 5px; cursor: pointer;">
                        <img src='./images/logo/logo_banner.png' style='width: 200px; height: 43px; margin-top: 9px; margin-left: 20px;'>
                    </div>
                    <div style="float: right; border:0px solid red;" id="MyClockDisplay" class="clock"></div>
                </div>
            </div>
        </div>
</div>
<div class="container">
    <div id="main_body" style="background-image: url('./images/bg_blue_digital.jpg'); border:0px solid red; margin:0 auto; padding-top:10px;
        background-repeat: no-repeat;  background-size: 100% 100%;">
        <?php 
        // include("./login.php"); 
            include __DIR__ . "/login.php"; 

        ?>
    </div>
</div>
<!-- <div style="color:#c1c1c1; font-size:10px; float:right; text-align:right; margin-right:10px; margin-bottom:10px;">
    <img src='./images/iwlogofinal2.png' style='width: 50px; height: 50px;'><br>
    The system was designed and developed by Import Wave<br> +880 1736 00 1223
</div> -->
</body>
</html>
