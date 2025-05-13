<?php

include_once "mysql.php";
include_once "crashes.php";
include_once "alphaID.php";



ob_start();


if(!isset($_GET['str']))
 die("no str");

$str = $_GET['str'];


$sql = "SELECT issue_id, id, application_log from crashes WHERE application_log LIKE '%" . $str ."%'";
//echo $sql;
$res = mysqli_query($mysql, $sql);
//Build fingerprint: 'rockchip/rk3288/rk3288:8.1.0/V2OVL3.40.0.0/024400:user/release-keys'
// V2OVL3.34.1.0
// LITE_N_VL1.5.4.0
// LITE_N_VL1.5.4.0
echo "<table style = 'border: 1px solid black; border-collapse: collapse;'>
  <tr style = 'border: 1px solid black; border-collapse: collapse;'>
    <th style = 'border: 1px solid black; border-collapse: collapse;'>link</th>
    <th style = 'border: 1px solid black; border-collapse: collapse;'>result</th>
  </tr>
  ";
while ($tab = mysqli_fetch_assoc($res)) {
  //echo $tab['issue_id'] . " " . . " " . . "<br/>";

  $html = $tab['application_log'];
  $needle = $str;
  $pos = 0;

  while (($pos = strpos($html, $needle, $pos))!== false) {
      $s = substr($html, $pos, ( strpos($html, "\n", $pos) ) - $pos);
      $pos = $pos + strlen($needle);
      $td1 = "<a style='text-decoration:underline;' href='https://g-auth.net/mycar/acra/report.php?issue_id=" . $tab['issue_id'] . "&report_id=" . $tab['id'] . "'>Report #". $tab['id'] . "</a>";
      //$td1 = '<a href="https://g-auth.net/mycar/acra/report.php?issue_id=">report</a>';
      //$td2 = "<td>" .  $s . "</td>";
      echo "<tr><td style = 'border: 1px solid black; border-collapse: collapse;'>" . $td1 . "</td><td style = 'border: 1px solid black; border-collapse: collapse;'>" . $s . "</td></tr>";
      //echo "<tr><td>" . $tab['issue_id'] . "</td><td></td></tr>";
      /*echo "<tr><td><a style='text-decoration:underline;' href='https://g-auth.net/mycar/acra/report.php?issue_id=" . $tab['issue_id'] . "&report_id=" . $tab['id'] . "'>Report #". $tab['id'] . "</a></td><td>" .  $s . "</td></tr>";*/
  }
}

echo "</table>";

// Close MySQL
if (!isset($keepConnection))
  mysqli_close($mysql);

//header('Location: ' . $_SERVER['HTTP_REFERER']);


?>