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

// display services
echo "<div id=\"services\">";
echo "<h5><a href=\"services.php?id=group\">" . "Group " . "</a>
– group all reports into issues (takes some time, DO NOT refresh before the finish)</h5>";

echo "<h5><a href=\"services.php?id=clean\">" . "Clean " . "</a>
– clean all reports from the old versions</h5>";

echo "<h5><a href=\"services.php?id=clean_plus\">" . "Clean+ " . "</a>
– removes all issues that have only 1 occurrence (use for ANRs)</h5>";

echo "</div>";


// Display Your Apps
$sql = "SELECT `id` FROM `user` WHERE `username` = '" . strtolower($_SESSION["username"]) . "'";
$res = mysqli_query($mysql, $sql);
mysqli_data_seek($res,  0); 
$userid = mysqli_fetch_array($res)[0];

$sql = "SELECT `appname`, `appid` FROM `app` WHERE `userid` = $userid";
$res = mysqli_query($mysql, $sql);
$rows = mysqli_num_rows($res);

echo "<br/><div id=\"listApps\">";
if ($rows == 0) {
	echo "No Applications registered!";
} else {
	echo "<h3>My Applications:</h3>";
	while ($tab = mysqli_fetch_assoc($res)) {
		//echo "<a href=\"reports.php?app=" . $tab[appid] . "\">" . $tab[appname] . "</a><br />";
		
		$status = 0;
		$columns = array('id', 'MAX(added_date) as last_seen', 'COUNT(issue_id) as nb_errors', 'issue_id');
		$sel = "status = ?";
		$selA = array($status);
		$order = "id DESC";

		// Filter by appid
		if (!empty($tab['appid'])) {
			$sel .= " AND appid = '?'";
			$selA[] = mysqli_real_escape_string($mysql, $tab['appid']);
		}

		$sql = create_mysql_select($columns, $sel, $selA, $order, "issue_id");
		$result = mysqli_query($mysql, $sql);

		$issues = 0;
		if (!$result) {
			log_to_file("Unable to query: $sql");
			echo "<p>Server error.</p>\n";
			echo "<p>SQL: $sql</p>";
			//return;
		} else if (mysqli_num_rows($result) == 0) {
			$issues = 0;
			//return;
		} else
			$issues = mysqli_num_rows($result);

		echo "<h3><a href=\"reports.php?app=" . $tab['appid'] . "\">" . $tab['appname'] . " (".$issues.")" . "</a><br /></h3>";
		//echo "<h1>".status_name($status)." reports (".$issues.")</h1>\n";
	}
}
echo "</div>";

// TODO
// display_crashes_vs_date();

echo "<br /><br /><br /><br />";

?>