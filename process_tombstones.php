<?php

include "mysql.php";
include "crashes.php";
include "alphaID.php";

ob_start();

$sql = "SELECT `id`, `application_log` FROM `crashes` WHERE `appid` = 'n7yjvztxh97d76jy4ek5ax4uc3d9cgx7'";
$res = mysqli_query($mysql,$sql);
mysqli_data_seek($res,  0); 

while ($tab = mysqli_fetch_assoc($res)) {
  echo $tab['id'] . "<br>";
  preg_match('/((backtrace)((?!stack)[\s\S])*+)/', $tab['application_log'], $matches, PREG_OFFSET_CAPTURE);
echo $matches[0][0];
$v = $matches[0][0];
$id = $tab['id'];

  if(count($matches) > 0) {
    $sql = "UPDATE crashes SET stack_trace='$v' WHERE id=$id";
    mysqli_query($mysql,$sql);
  }

  echo "<br><br><br>";
  //print_r($matches[0]);

    /*foreach ($tab as $k => $v) {
        echo $k . "=> " . $v . "\n";
    }*/
}



// Close MySQL
mysqli_close($mysql);


?>
