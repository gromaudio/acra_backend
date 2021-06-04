<?php
include "checklogin.php";
include "html.php";
?>
<a href="index.php"><<< Back to Dashboard</a>
<br /><br />
<form action="#" method="post" />
Application Name:<br />
<input type="name" name="app_name" /><br />
<input type="submit" value="Register Application" />
</form>

<?php

include "mysql.php";

if(!isset($_SESSION)) { 
    session_start(); 
} 


if (isset($_POST["app_name"]) && isset($_SESSION["username"])) {
	$appname = $_POST["app_name"];
	// $appid = alphaID(uniqid(), true);
	$appid = generateRndString(32);

	$sql = "SELECT `id` FROM `user` WHERE `username` = '" . strtolower($_SESSION["username"]) . "'";
	$res = mysqli_query($mysql,$sql);
	mysqli_data_seek($res,  0); $userid = mysqli_fetch_array($res)[0];

	$sql = "INSERT INTO `app` (`appname`, `appid`, `userid`) VALUES ('$appname', '$appid', $userid)";
	$res = mysqli_query($mysql,$sql);

	if ($res == true) {
		echo "Application successfully registered!";
		echo "Your unique App ID: ".$appid;

	} else {
		echo "An error occurred!";
	}

}

function generateRndString($length) {
	$characters = 'bcdfghjklmnpqrstvwxyzBCDFGHJLKMNPQRSTVWXYZ';

	if (!isset($length)) {
		$random_string_length = 32;
	}
	
	$string = '';
 	for ($i = 0; $i < $random_string_length; $i++) {
      $string .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $string;
}

?>