<?php 

$guid           = ($_REQUEST["uid"]);




include ("../conn.php");
$title = "UPLOAD";

$stmt = $conn->prepare("SELECT `user`, `TIME` FROM `guid` WHERE `guid` = ?");
$stmt->bind_param("s", $guid); // Bind the parameter

$stmt->execute(); // Execute the statement
$result = $stmt->get_result(); // Get the result

$row = $result->fetch_assoc();

if ($row === NULL) {
    
    echo "link is not valid.";
    exit();
}

date_default_timezone_set('Asia/Dhaka');
$TIMENOW       = date("Y-m-d H:i:s");
$TIME          = ($row['TIME']);


$date1 = new DateTime($TIMENOW);
$date2 = new DateTime($TIME);



$min   = 10;

$interval = $date1->diff($date2);
$diffInMinutes = $interval->i + ($interval->h * 60) + ($interval->days * 24 * 60);

// echo "The difference is $diffInHours hours.";

if($diffInMinutes > $min){
    die("Link Expired.");
}



?>
<!DOCTYPE html>
<html>
<title><?php echo $title ?></title>
<head>
  <link rel="shortcut icon" href="../images/logo_small.png" type="image/x-icon"/>
<link rel="stylesheet" type="text/css" href="./stylev2.css">
</head>

<body>
<div class="container">
<div class="banner">
    <?php echo $title ?>
  </div>
<div class="main_body">
<?php
                            


?>

<hr>
<B><h3></h3></B>

<form enctype="multipart/form-data" action="output.php" method="POST" name="upload"">
  <table width="553" border="0" align="center">
    <tr>
      <td width="10">&nbsp;</td>
      <td width="201">Choose a <B></B> file to upload:</td>
           
      <td width="324">
        <input name="uploadedfile" type="file"  accept=".csv"/>

        <input type="hidden" name="guid" id="guid" value="<?php echo $guid; ?>" required>
    </td>
    </tr>
    

    <tr height="25px">

      <div id="check_result"></div>
<?php //echo "<div style='color: red; border: 0px solid red; text-align:center;'>Last uploaded file : ".$max_date[0]."</div></br>"; ?>
     

      </div></td>


    </tr>
    <tr>
      <td>
        


      </td>
      <td>&nbsp;</td>
      <td colspan="2" class="submit_row"><input type="submit" value="Upload File" /></td>
    </tr>
  </table>
</form>
</BR>
<hr>
</div>
</div>
<div class="footer"> <?php echo $title; ?> </div>
</body>
</html>
