<?php

include_once "mysql.php";
include_once "crashes.php";
include_once "alphaID.php";

if (!function_exists('microtime_float'))    {
    function microtime_float()
  {
      list($usec, $sec) = explode(" ", microtime());
      return ((float)$usec + (float)$sec);
  }
}


if (!class_exists('Foo')) {
    class Foo {
          public $id;
          public $issue_id;
          public $stack_trace;
          
          
          public function __construct($id, $issue_id, $stack_trace) {
              $this->id = $id;
              $this->issue_id = $issue_id;
              $this->stack_trace = $stack_trace;
          }
      }
}
 
ob_start();


if(!isset($_GET['appid']))
 die("no appid");

$appid = $_GET['appid'];

$extra = isset($_GET['extra']) ? $_GET['extra'] : true;

$skip = isset($_GET['offset']) && isset($_GET['count']);
$limitLength = isset($_GET['lengthDiffPercent']) ? $_GET['lengthDiffPercent'] : 85;
$length = isset($_GET['length']) ? $_GET['length'] : 15000;

echo "Doing extra: $extra <br/>";

if ($appid === "n7yjvztxh97d76jy4ek5ax4uc3d9cgx7" && !$skip) { // tombstones
  $time_start = microtime_float();
  // todo: delete for tombstones
  mysqli_query($mysql, 'DELETE from crashes WHERE stack_trace LIKE "%crash_dump failed to dump process%"');

  mysqli_query($mysql, "DELETE FROM crashes WHERE appid='n7yjvztxh97d76jy4ek5ax4uc3d9cgx7'
    and id NOT IN (SELECT * FROM (SELECT MIN(n.id)
                             FROM crashes n
                             INNER JOIN crash_detail cd ON n.id = cd.id
                           GROUP BY cd.application_log) x)");


  $sql = "SELECT c.id, c.custom_data, SUBSTRING(cd.application_log, LOCATE('Build finger', cd.application_log), 100) as build FROM `crashes` c INNER JOIN `crash_detail` cd ON c.id = cd.id WHERE c.`appid` = 'n7yjvztxh97d76jy4ek5ax4uc3d9cgx7' and c.app_version_name NOT LIKE '%V2OV%' and c.app_version_name NOT LIKE '%LITE_N_VL%'";
  $res = mysqli_query($mysql,$sql);
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
  $time_end = microtime_float();
  $time = $time_end - $time_start;
  echo "<br/><br/>Tombstones processing $time seconds for $appid<br/>";

} elseif ($appid === "f5ar7wfpkdmda852krjpwmt8iunu4d9f" && !$skip) { // ANR
  $time_start = microtime_float();
  /*mysqli_query($mysql, "update `crashes` set status=2 WHERE `appid` = 'f5ar7wfpkdmda852krjpwmt8iunu4d9f' and stack_trace NOT LIKE '%grom%' and stack_trace NOT LIKE '%vline%' and stack_trace NOT LIKE '%vbase%' and stack_trace NOT LIKE '%exo%' and stack_trace NOT LIKE '%bluetooth%' and stack_trace NOT LIKE '%dashlinq%' and stack_trace NOT LIKE '%aalinq%' and stack_trace NOT LIKE '%com.android%'");*/

  // remove duplicates
  if ($extra === true) {
    mysqli_query($mysql, "DELETE FROM crashes WHERE appid='f5ar7wfpkdmda852krjpwmt8iunu4d9f'
      and id NOT IN (SELECT * FROM (SELECT MIN(n.id)
                               FROM crashes n
                               INNER JOIN crash_detail cd ON n.id = cd.id
                             GROUP BY cd.application_log) x)");
  }
  $time_end = microtime_float();
  $time = $time_end - $time_start;
  echo "<br/><br/>ANR processing $time seconds for $appid<br/>";
} elseif ($appid === "77wjw673hkkiw37rcumqarrwiczcqpk3" || $appid === "32gym8mf5juqjwxk43y8m47ygq3nnab8") { // A12 low memory
  $sql = "SELECT `id`, `issue_id`, `stack_trace` FROM `crashes` WHERE `appid` = '" . $appid . "' and stack_trace LIKE '%Caused by: java.lang.Throwable: %' LIMIT 500";

  $res = mysqli_query($mysql, $sql);

  echo $sql . "<br/>";
  
  while ($row = mysqli_fetch_assoc($res)) {
      $stack_trace = $row['stack_trace'];

      // Define the two strings we need to find
      $needle_caused_by = "Caused by: java.lang.Throwable: ";
      $needle_on_trim = $appid === "77wjw673hkkiw37rcumqarrwiczcqpk3" ? "at com.gromaudio.panel.AndroidApplication.onTrimMemory" : "at com.gromaudio.vlite2.MyApplication.onTrimMemory";

      // Find the position of both strings independently
      $pos_caused_by = strpos($stack_trace, $needle_caused_by);
      $pos_on_trim = strpos($stack_trace, $needle_on_trim);

      //echo "<br>============================================================</br>";

      // Check if BOTH strings were found, regardless of their order
      if ($pos_caused_by !== false && $pos_on_trim !== false) {
          // Extract the text that comes AFTER the "Caused by" line
          $start_of_substring = $pos_caused_by + strlen($needle_caused_by);
          $s = substr($stack_trace, $start_of_substring);
          
          // Escape the string to be safe in the SQL query
          $s_escaped = mysqli_real_escape_string($mysql, trim($s));

          //echo "<pre>Extracted for logcat: " . htmlspecialchars($s_escaped) . "</pre>";

          // Update crash_detail with the extracted text
          $sql_update_detail = "UPDATE crash_detail SET logcat = CONCAT('" . $s_escaped . "', logcat) WHERE id = " . $row['id'];
          mysqli_query($mysql, $sql_update_detail);
          echo "Running Query: " . $sql_update_detail . "<br/>";

          // Update the main crash record
          $sql_update_crash = "UPDATE crashes SET stack_trace = 'Low memory' WHERE id = " . $row['id'];
          mysqli_query($mysql, $sql_update_crash);
          echo "Running Query: " . $sql_update_crash . "<br/>";

          echo "https://g-auth.net/mycar/acra/report.php?issue_id=" . $row['issue_id'] . "&report_id=" . $row['id'] . "<br/>";
      }
      //echo "<br>============================================================</br>";
  }
}



$sql = "SELECT `id`, COUNT(id) AS number, `issue_id`, `stack_trace` FROM `crashes` WHERE `appid` = '$appid' and (status = 0 or status = 1) and CHAR_LENGTH(`stack_trace`) < $length group by issue_id";

if (isset($_GET['group']))
  $sql .= " ORDER BY number DESC";
if ($skip)
    $sql .= " LIMIT " . $_GET['offset'] . ", " . $_GET['count'];

echo "$sql <br/>";

$res = mysqli_query($mysql, $sql);
mysqli_data_seek($res,  0); 

$arr = array();

$time_start = microtime_float();

$maxId = 0;
$read = file_get_contents($appid);
if ($read === false)
  $maxId = 0;
else
  $maxId = $read;

echo "Max id: $maxId <br/>";
$newMaxId = 0;

while ($row = mysqli_fetch_assoc($res)) {
  //$row['id'];
  //$row['issue_id']
  //$row['stack_trace']
  $found = false;
  echo $row['issue_id'] . " " . $row['id'] . "<br/>";
  $s = preg_replace("/[^a-zA-Z\s]/","",$row['stack_trace']);

  $id1 = $row['id'];
//echo $s . "<br/><br/><br/>";
  foreach ($arr as $key => $value) {
    if(isset($arr[$key])) {
      if ($arr[$key][0]->issue_id === $row['issue_id'])
        continue;
    }

    $id2 = $arr[$key][0]->id;

    if ($id1 < $maxId && $id2 < $maxId) {
      //echo "Don't compare: id1=$id1, id2=$id2, maxId=$maxId<br/>";
      continue;
    }

    if ($id1 > $newMaxId)
      $newMaxId = $id1;
    if ($id2 > $newMaxId)
      $newMaxId = $id2;

    $l1 = strlen($key);
    $l2 = strlen($s); 
    $diff = min($l1, $l2) * 100 / max($l1, $l2);
    //echo "diff: $l1/$l2 = $diff <br/>";
    if ($diff < $limitLength)
      continue;
    
    $perc = 0;
    similar_text($key, $s, $perc);

    if ($perc > 88) {
      echo "similarity: $perc % <br/>";
      $found = true;
      echo "push" . "<br>";
      array_push($arr[$key], new Foo($row['id'], $row['issue_id'], $row['stack_trace']));
      break;
    }
  }

  if (!$found) {
    //echo "NEW" . "<br>";
    $arr[$s] = array(new Foo($row['id'], $row['issue_id'], $row['stack_trace']));
  }
}

file_put_contents($appid, $newMaxId);
echo "new max id: $newMaxId <br/>";

$time_end = microtime_float();
$time = $time_end - $time_start;
echo "<br/><br/>Execution $time seconds for $appid<br/>";
echo count($arr);
echo "<br/><br/>";

foreach ($arr as $key => $value) {
  // todo: update
  if (count($value) > 1) {
    print("<pre>".print_r($value[0]->stack_trace,true)."</pre>");
    print("<pre>".print_r($value,true)."</pre>");
    echo "<br><br><br>";

    $issue_id = $value[0]->issue_id;
    foreach ($value as $foo) {
      $sql = "update crashes set `issue_id`='" . $issue_id . "' WHERE `issue_id` = '" . $foo->issue_id . "'";
      $res = mysqli_query($mysql, $sql);
      echo "$sql<br><br>";
    }
  }
}

//var_dump($arr);



// Close MySQL
if (!isset($keepConnection))
  mysqli_close($mysql);

//header('Location: ' . $_SERVER['HTTP_REFERER']);


?>
