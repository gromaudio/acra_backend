<?php

include "mysql.php";
include "crashes.php";
include "alphaID.php";
include "version.php";

$f = fopen("last_access", "w");
fputs($f, "access on ".date("d/M/Y G:i:s")."\n");
fclose($f);

ob_start();

global $VERSION_RELEASE, $VERSION_RELEASE_FULL, $VERSION_FILTERED, $VERSION_RELEASE_A12, $VERSION_RELEASE_FULL_A12, $VERSION_FILTERED_A12;

$BUG4787_VER = 'V2OVL3.71.1.0'; //vk

if (!isset($_GET['key'])) {
  echo "no Key";
        log_to_file("No key");
  die();
}

// Check _POST
if (count($_POST) == 0) {
  echo "empty post";
    log_to_file("Empty _POST query");
    die();
}

foreach($_POST as $k => $v) {
    if (array_search(strtolower($k), $values) === FALSE) {
        continue;
    }

    $object[strtolower($k)] = mysqli_real_escape_string($mysql, $v);
}


// Add custom data
$object['appid'] = $_GET['key'];
$object['added_date'] = time();
$object['issue_id'] = issue_id($object['stack_trace'], $object['package_name']);

if (isset($object['user_ip'])) {
  if(!(trim($object['user_ip']) === '') && $object['package_name'] != "com.gromaudio.vlineservice")
    $object['appid'] = $object['user_ip'];
  unset($object['user_ip']); 
}

$appid = $object['appid'];
if ($appid === "n7yjvztxh97d76jy4ek5ax4uc3d9cgx7" || $appid === "f5ar7wfpkdmda852krjpwmt8iunu4d9f") {
  $applog = $object['application_log'];
  if (strpos($applog, "rk3288:8.1.0/" . $VERSION_FILTERED) === false && 
  		strpos($applog, 'VL2-8.1') === false 
  		/*&& strpos($applog, 'crash_dump failed') === false*/ && 
  		strpos($applog, 'V2SC0.1') === false && 
  		strpos($applog, $BUG4787_VER) === false &&
  		strpos($applog, "rk3288_Android12:12/" . $VERSION_FILTERED_A12) === false) {
      echo "Old version";
      return;
  }
} 
else if ($appid === "72gym8mf5juqjwxk43y8m47ygq3nnab8" || $appid === "95wjw673hkkiw37rcumqarrwiczcqpk3" || $appid === "5ztxh97ax4uc3n7yjvd76jy4ekd9cgx7") {
  $applog = $object['custom_data'];
    if (strpos($applog, $VERSION_FILTERED) === false && 
    	strpos($applog, 'VL2-8.1') === false && 
    	strpos($applog, 'V2SC0.1') === false && 
    	strpos($applog, 'LITE_N_VL') === false && 
    	strpos($applog, $BUG4787_VER) === false &&
    	strpos($applog, $VERSION_FILTERED_A12) === false) {
      echo "Old version";
      return;
  }
}

$sql = "SELECT `status` FROM `crashes` WHERE `issue_id` = '" . $object['issue_id'] . "'";
$res = mysqli_query($mysql,$sql);
mysqli_data_seek($res,  0); 
$status = mysqli_fetch_array($res)[0];

if ($status == 0) {
  $object['status'] = STATE_NEW;

} else {
  $object['status'] = $status;
}


// Save to DB
$sql = create_mysql_insert($object);
$success = mysqli_query($mysql,$sql);

if ($success != TRUE) {
    log_to_file("Unable to save record: ".mysqli_error($mysql));
    log_to_file("Query was: ".$sql);
}

// Close MySQL
mysqli_close($mysql);

$f = fopen("log", "a");
fputs($f, "Output of ".date("d/M/Y G:i:s").":\n".ob_get_clean());
fclose($f);

echo "What are you doing here?";

?>
