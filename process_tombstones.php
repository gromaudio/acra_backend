<?php

include_once ("mysql.php");
include_once("crashes.php");
include_once("alphaID.php");

ob_start();

$sql = "SELECT `id`, `custom_data`, SUBSTRING(application_log, LOCATE('Build finger', application_log), 100) as build FROM `crashes` WHERE `appid` = 'n7yjvztxh97d76jy4ek5ax4uc3d9cgx7' and app_version_name NOT LIKE '%V2OV%' and app_version_name NOT LIKE '%LITE_N_VL%'";
$res = mysqli_query($mysql,$sql);
//mysqli_data_seek($res,  0); 


// SELECT `id`, SUBSTRING(application_log, 0, LOCATE('\n', application_log)) FROM `crashes` WHERE `appid` = 'n7yjvztxh97d76jy4ek5ax4uc3d9cgx7'
//Build fingerprint: 'rockchip/rk3288/rk3288:8.1.0/V2OVL3.40.0.0/024400:user/release-keys'
// V2OVL3.34.1.0
// LITE_N_VL1.5.4.0
// LITE_N_VL1.5.4.0
while ($tab = mysqli_fetch_assoc($res)) {
  echo $tab['build'] . "<br/>";
  preg_match('/((V2OV)((?!\/)[\s\S])*+)/', $tab['build'], $matches, PREG_OFFSET_CAPTURE);
  //echo $matches[0][0];
  //$id = $tab['id'];

  if(count($matches) == 0) {
    preg_match('/((LITE_N_VL1)((?!\/)[\s\S])*+)/', $tab['build'], $matches, PREG_OFFSET_CAPTURE);
  } 

  if (count($matches) == 0) {
    continue;
  }

  $version = $matches[0][0];
  echo "MATCH: " . $version . "<br/>";

  $lines = explode("\n", $tab['custom_data']);
  if (count($lines) == 0) {
    continue;
  } else if(count($lines) == 2) {
      var_dump(lines);
    $v = explode("=", $lines[0])[1];
    echo "1. $v<br/>";
    $v = explode("=", $lines[1])[1];
    echo "2. $v<br/>";

    $newData = $lines[0] . "\n" . explode("=", $lines[1])[0] . "=" . $version;
    echo $newData . "<br/>";

    $sql = "update crashes set custom_data = '" . $newData . "' where id=" . $tab['id'];
    echo $sql;
    mysqli_query($mysql,$sql);

      echo "<br/>";

    $sql = "update crashes set app_version_name = '" . $version . "' where id=" . $tab['id'];
    echo $sql;
    mysqli_query($mysql,$sql);

  echo "<br><br><br>";
  //print_r($matches[0]);

    /*foreach ($tab as $k => $v) {
        echo $k . "=> " . $v . "\n";
    }*/
  }
}



// Close MySQL
mysqli_close($mysql);


?>
