<?php

include "mysql.php";
include "crashes.php";

ob_start();
$obj = array();


if ($_GET['action'] == "getreport") {
	$sql = "select id, added_date, custom_data, application_log, logcat from crashes where id=" . intval($_GET['report_id']);
	$res = mysqli_query($mysql, $sql);
	$tab = mysqli_fetch_assoc($res);

	/*
	added date
SERIAL
BUILD_SHORT
BUILD_FULL
stack_trace
application_log
logcat*/

	echo "<h1>Report #" . $tab['id'] ."</h1>\n";
	echo '<div style="margin: 45px;">'."\n";
	foreach ($tab as $k => $v) {
		if ($k == "id" || $v == null || $v == "none") {
			continue;
		} else if ($k == "added_date") {
			if (intval($v) > 0) {
				$v = date('d/M/Y G:i:s', intval($v));
			} else {
				$v = "Date unknown";
			}
		}

		echo "<h2>$k</h2>\n<pre>$v</pre>\n";
	}
	echo "</div>\n";
	return;
}

$sel = "issue_id = '?'";
$selA = array($_GET['issue_id']);

if ($_GET['action'] == "update_status") {
	$obj['status'] = intval($_GET['status']);
}

$sql = create_mysql_update($obj, $sel, $selA);
$res = mysqli_query($mysql, $sql);

if ($res) {
	ob_end_clean();
	echo "OK ($sql)";
} else {
	$file = fopen("last_ajax_fail", "w");
	fputs($file, "Unable to execute query: $sql\n");
	print_r($obj);
	echo "\n_GET: ";
	print_r($_GET);
	echo "\n_POST: ";
	print_r($_POST);
	fputs($file, "Object: ".ob_get_clean()."\n");
	fclose($file);

	echo "KO";
}

?>
