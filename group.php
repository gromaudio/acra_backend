<?php

include "mysql.php";
include "crashes.php";
include "alphaID.php";

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

ob_start();


if(!isset($_GET['appid']))
 die("no appid");

$appid = $_GET['appid'];

$skip = isset($_GET['limitOffset']) && isset($_GET['limitCount']);

if ($appid === "n7yjvztxh97d76jy4ek5ax4uc3d9cgx7" && !$skip) { // tombstones
  // todo: delete for tombstones
  mysqli_query($mysql, 'DELETE from crashes WHERE stack_trace LIKE "%crash_dump failed to dump process%"');

  mysqli_query($mysql, "DELETE FROM crashes WHERE appid='n7yjvztxh97d76jy4ek5ax4uc3d9cgx7'
    and id NOT IN (SELECT * 
                    FROM (SELECT MIN(n.id)
                            FROM crashes n
                        GROUP BY n.application_log) x) ORDER BY `id`  DESC");


  $sql = "SELECT `id`, `custom_data`, SUBSTRING(application_log, LOCATE('Build finger', application_log), 100) as build FROM `crashes` WHERE `appid` = 'n7yjvztxh97d76jy4ek5ax4uc3d9cgx7' and app_version_name NOT LIKE '%V2OV%' and app_version_name NOT LIKE '%LITE_N_VL%'";
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

} elseif ($appid === "f5ar7wfpkdmda852krjpwmt8iunu4d9f" && !$skip) { // ANR
 mysqli_query($mysql, "update `crashes` set status=2 WHERE `appid` = 'f5ar7wfpkdmda852krjpwmt8iunu4d9f' and stack_trace NOT LIKE '%grom%' and stack_trace NOT LIKE '%vline%' and stack_trace NOT LIKE '%vbase%' and stack_trace NOT LIKE '%exo%' and stack_trace NOT LIKE '%bluetooth%' and stack_trace NOT LIKE '%dashlinq%' and stack_trace NOT LIKE '%aalinq%' and stack_trace NOT LIKE '%com.android%'");

  mysqli_query($mysql, "DELETE FROM crashes WHERE appid='f5ar7wfpkdmda852krjpwmt8iunu4d9f'
    and id NOT IN (SELECT * 
                    FROM (SELECT MIN(n.id)
                            FROM crashes n
                        GROUP BY n.application_log) x) ORDER BY `id`  DESC");
}



$sql = "SELECT `id`, `issue_id`, `stack_trace` FROM `crashes` WHERE `appid` = '" . $appid ."' and (status = 0 or status = 1) group by issue_id";
if ($skip) {
    $sql .= " LIMIT " . $_GET['limitOffset'] . ", " . $_GET['limitCount'];
}
$res = mysqli_query($mysql, $sql);
mysqli_data_seek($res,  0); 

$arr = array();

while ($row = mysqli_fetch_assoc($res)) {
  //$row['id'];
  //$row['issue_id']
  //$row['stack_trace']
  $found = false;
  echo $row['issue_id'] . " " . $row['id'] . "<br/>";
 $s = preg_replace("/[^a-zA-Z\s]/","",$row['stack_trace']); 
//echo $s . "<br/><br/><br/>";
  foreach ($arr as $key => $value) {
    if(isset($arr[$key])) {
      if ($arr[$key][0]->issue_id === $row['issue_id'])
        continue;
    }
   
    
   
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


echo count($arr);

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
mysqli_close($mysql);

header('Location: ' . $_SERVER['HTTP_REFERER']);


?>