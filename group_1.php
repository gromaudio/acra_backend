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

$sql = "SELECT `id`, `issue_id`, `stack_trace` FROM `crashes` WHERE `appid` = '" . $appid ."' and (status = 0 or status = 1) group by issue_id LIMIT 0, 1000";
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
      //$res = mysqli_query($mysql, $sql);
      echo "$sql<br><br>";
    }
  }
}

//var_dump($arr);



// Close MySQL
mysqli_close($mysql);

header('Location: ' . $_SERVER['HTTP_REFERER']);


?>