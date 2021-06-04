<?php

include "checklogin.php";
include "crashes.php";
include "html.php";
include "check_install.php";
include "mysql.php";
include "alphaID.php";

// echo "<h4 style=\"text-align: center;\"><a href=\"reports.php\">Reports</a></h4>";



if(!isset($_SESSION)) { 
    session_start(); 
} 


// Display Your Apps
$sql = "SELECT `id` FROM `user` WHERE `username` = '" . strtolower($_SESSION["username"]) . "'";
$res = mysqli_query($mysql, $sql);
mysqli_data_seek($res,  0); 
$userid = mysqli_fetch_array($res)[0];

$sql = "SELECT `appname`, `appid` FROM `app` WHERE `userid` = $userid";
$res = mysqli_query($mysql, $sql);
$rows = mysqli_num_rows($res);

echo "<br /> <br /> <br />";
echo "<div id=\"listApps\">";
if ($rows == 0) {
	echo "No Applications registered!";
} else {
	echo "My Applications: <br />";
	while ($tab = mysqli_fetch_assoc($res)) {
		echo "<a href=\"reports.php?app=" . $tab[appid] . "\">" . $tab[appname] . "</a><br />";
	}
}
echo "</div>";

// TODO
// display_crashes_vs_date();

echo "<br /><br /><br /><br />";
echo "<a href=\"register_app.php\">Register Application</a>";

?>
