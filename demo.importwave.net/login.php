<?php
session_start();

function decryptText($encryptedText, $key) {
    $encryptedText = base64_decode($encryptedText);
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($encryptedText, 0, $ivLength);
    $encrypted = substr($encryptedText, $ivLength);
    $decryptedText = openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return $decryptedText;
}

$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/App_Data/app.ini', true);
$salt = $ini["GENERAL"]["SALT"];

$decryptedPass = isset($_COOKIE["TxTPASS"]) ? decryptText($_COOKIE["TxTPASS"], $salt) : "";

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Proma Trade International</title>
    <link rel="shortcut icon" href="./images/logo_small.png" type="image/x-icon"/>
    <link rel="stylesheet" type="text/css" href="stylev2.css">
    <script src="./js/jquery-3.6.3.min.js"></script>
    <script>
        function Login() {
            var TxTID = document.getElementById('TxTID').value.toLowerCase().trim();
            var TxTPASS = document.getElementById('TxTPASS').value;
            var checkBox = document.getElementById('remember');
            var remember = checkBox.checked ? 1 : 0;
            var csrf_token = document.getElementById('csrf_token').value;

            if (TxTID === "") {
                alert("Please enter ID");
                return;
            } else if (TxTPASS === "") {
                alert("Password is blank.");
                return;
            }

            document.getElementById("main_body").style.backgroundImage = "url()";

            var link = "./LoginProcess.php";
            var arr = {
                "TxTID": TxTID,
                "TxTPASS": TxTPASS,
                "remember": remember,
                "csrf_token": csrf_token
            };

            $("#main_body").html("<div style='border:0px solid red; height:100px; width:100px; margin:100px auto;'><img style='width:100px; height:100px;' src='./images/loading/loading1.gif'></div>");

            $.ajax({
                url: link,
                type: "POST",
                data: arr,
                cache: false,
                success: function(data) {
                    $("#main_body").html(data);
                }
            });
        }

        $(document).ready(function() {
            $('#TxTID').focus();
            $('#TxTID').keypress(function(e) {
                if (e.keyCode === 13) $('#TxTPASS').focus();
            });
            $('#TxTPASS').keypress(function(e) {
                if (e.keyCode === 13) $('#BtnLogin').click();
            });
        });
    </script>
    <style type="text/css">
        select {
            background-color: white;
            border: thin solid gray;
            border-radius: 4px;
            display: inline-block;
            font: inherit;
            line-height: 15px;
            padding: 0.5em 3.5em 0.5em 1em;
            width: 200px;
            margin: 0;      
            box-sizing: border-box;
            appearance: none;
            cursor: pointer;
        }

        select.classic {
            width: 100%;
            background-image: linear-gradient(45deg, transparent 50%, blue 50%), linear-gradient(135deg, blue 50%, transparent 50%), linear-gradient(to right, skyblue, skyblue);
            background-position: calc(100% - 20px) calc(1em + 2px), calc(100% - 15px) calc(1em + 2px), 100% 0;
            background-size: 5px 5px, 5px 5px, 2.5em 2.5em;
            background-repeat: no-repeat;
        }

        select.classic:focus {
            background-image: linear-gradient(45deg, white 50%, transparent 50%), linear-gradient(135deg, transparent 50%, white 50%), linear-gradient(to right, gray, gray);
            background-position: calc(100% - 15px) 1em, calc(100% - 20px) 1em, 100% 0;
            background-size: 5px 5px, 5px 5px, 2.5em 2.5em;
            background-repeat: no-repeat;
            border-color: grey;
            outline: 0;
        }

        .login-container {
            width: 300px;
            min-height: 50px;
            border: 1px solid black;
            margin: 100px auto;
            padding: 20px 40px 40px 40px;
            background-color: white;
            opacity: .85;
            border-radius: 10px;
            box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);
            -webkit-box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);
            -moz-box-shadow: -1px 1px 11px 1px rgba(0,0,0,0.75);
        }

        .login-title {
            min-width: 100px;
            min-height: 20px;
            margin: 0 auto;
            margin-bottom: 30px;
            text-align: center;
            font-size: 26px;
            color: #18262c;
            letter-spacing: 2px;
            font-weight: bold;
        }

        .login-input {
            width: 160px;
            text-align: left;
            margin-bottom: 30px;
        }

        .login-remember {
            min-width: 100px;
            min-height: 20px;
            margin: 0 auto;
            margin-bottom: 30px;
            text-align: center;
            padding-top: 10px;
        }

        .glow-on-hover {
            float: right;
            min-width: 100px;
        }
    </style>
</head>
<body>
        <div class="login-container">
            <input id="csrf_token" type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="login-title">Please Login</div>
                    <div class="login-title">
                        <input class="login-input" type="text" id="TxTID" name="TxTID" value="<?php echo isset($_COOKIE["TxTID"]) ? $_COOKIE["TxTID"] : ''; ?>" 
                        placeholder="Login ID">
                    <!-- </div>
                    <div class="login-title"> -->
                        <input class="login-input" type="password" name="TxTPASS" id="TxTPASS" value="<?php echo $decryptedPass; ?>" placeholder="Password">
                    </div>
                <!-- <div class="login-remember"> -->
                    <input id="remember" type="checkbox" name="remember"> Remember me
                    <button onclick="Login();" class="glow-on-hover" id="BtnLogin">Login</button>
                </div>
        </div>
</body>
</html>
