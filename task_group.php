<?php

// group.php?appid=f5ar7wfpkdmda852krjpwmt8iunu4d9f&offset=0&count=10&lengthDiffPercent=85&group=1&length=15000

$_GET['offset'] = 0;
$_GET['count'] = 4000;
$_GET['lengthDiffPercent'] = 85;
$_GET['length'] = 15000;
$_GET['group'] = 1;
$_GET['extra'] = isset($_GET['extra']) ? $_GET['extra'] : true;
$keepConnection = true;



$_GET['appid'] = '72gym8mf5juqjwxk43y8m47ygq3nnab8';
include('group.php');
$_GET['appid'] = '32gym8mf5juqjwxk43y8m47ygq3nnab8';
include('group.php');
$_GET['appid'] = '95wjw673hkkiw37rcumqarrwiczcqpk3';
include('group.php');
$_GET['appid'] = '77wjw673hkkiw37rcumqarrwiczcqpk3';
include('group.php');
$_GET['appid'] = '5ztxh97ax4uc3n7yjvd76jy4ekd9cgx7';
include('group.php');
$_GET['appid'] = 'f5ar7wfpkdmda852krjpwmt8iunu4d9f';
include('group.php');
$_GET['appid'] = 'n7yjvztxh97d76jy4ek5ax4uc3d9cgx7';
include('group.php');

mysqli_close($mysql);

?>