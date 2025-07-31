<?php
include_once "mysql.php";
include "alphaID.php";
include "version.php";

$appid = $_GET['id'];
if ($appid === "group") { 
  include('task_group.php?extra=false');
} else if ($appid === "clean") {
  $q = "DELETE FROM crashes WHERE (appid='n7yjvztxh97d76jy4ek5ax4uc3d9cgx7' or appid='f5ar7wfpkdmda852krjpwmt8iunu4d9f') AND (application_log NOT LIKE '%$VERSION_FILTERED.%' AND application_log NOT LIKE '%$VERSION_FILTERED_A12.%' AND application_log NOT LIKE '%$VERSION_FILTERED_A7.%')";
  mysqli_query($mysql, $q);
  echo $q . "<br/>";

  $q = "DELETE FROM crashes WHERE (appid='95wjw673hkkiw37rcumqarrwiczcqpk3' or appid='72gym8mf5juqjwxk43y8m47ygq3nnab8' or appid='88wjw673hkkiw37rcumqarrwiczcqpk3' or appid='5ztxh97ax4uc3n7yjvd76jy4ekd9cgx7' or appid='77wjw673hkkiw37rcumqarrwiczcqpk3' or appid='32gym8mf5juqjwxk43y8m47ygq3nnab8') and (custom_data NOT LIKE '%$VERSION_FILTERED.%' AND custom_data NOT LIKE '%$VERSION_FILTERED_A12.%' AND custom_data NOT LIKE '%$VERSION_FILTERED_A7.%')";
  mysqli_query($mysql, $q);
  echo $q . "<br/>";

} else if ($appid === "clean_plus") {
  $q = "DELETE FROM crashes WHERE id IN ( SELECT * FROM ( SELECT id from crashes WHERE (appid='n7yjvztxh97d76jy4ek5ax4uc3d9cgx7' or appid='f5ar7wfpkdmda852krjpwmt8iunu4d9f') and (status = 0 or status = 1) and CHAR_LENGTH(stack_trace) < 15000 group by issue_id HAVING count(id)=1 ) AS p )";
  mysqli_query($mysql, $q);
  echo $q . "<br/>";
}

mysqli_close($mysql);

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>