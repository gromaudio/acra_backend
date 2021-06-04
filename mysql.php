<?php

@include "config.php";

$mysql = mysqli_connect($mysql_server, $mysql_user, $mysql_password);
if (!$mysql) {
	log_to_file("Unable to connect to mysql server: ".mysqli_error($mysql));
	die("Server down");
}

if (!mysqli_select_db($mysql, $mysql_db)) {
	log_to_file("Unable to select db: ".mysqli_error($mysql));
	die("Server down");
}


function create_mysql_insert($object) {
	$cols = "INSERT INTO crashes (".implode(", ", array_keys($object)).") ";
	$vals = "VALUES ('".implode("', '", $object)."')";
	return $cols.$vals;
}

function create_mysql_select($columns = NULL, $selection = NULL, $selectionArgs = NULL, $order = NULL, $group = NULL) {
	global $mysql;

	// Columns
	if ($columns != NULL) {
		$cols = implode(", ", $columns);
	} else {
		$cols = "*";
	}

	// Selection
	if ($selection == NULL) {
		$condition = "1";
	} else {
		$sel = str_replace(array("%", "?"), array("%%", "%s"), $selection);
		$selA = array();
		foreach($selectionArgs as $s) {
			$selA[] = mysqli_real_escape_string($mysql, $s);
		}
		$condition = vsprintf($sel, $selA);
	}

	// Order
	if ($order == NULL) {
		$order = "id DESC";
	}

	// Group
	if ($group != null) {
		$grp = "GROUP BY " . mysqli_real_escape_string($mysql, $group);
	} else {
		$grp = "";
	}

	return "SELECT $cols FROM crashes WHERE $condition $grp ORDER BY $order";
}

function create_mysql_update($object, $selection, $selectionArgs) {
	global $mysql;
	$sel = str_replace("?", "%s", $selection);
	$selA = array();
	foreach($selectionArgs as $s) {
		$selA[] = mysqli_real_escape_string($mysql,$s);
	}
	$condition = vsprintf($sel, $selA);

	$sql = "";
	foreach ($object as $k => $v) {
		if ($sql == "") {
			$sql = "UPDATE crashes SET ";
		} else {
			$sql .= ", ";
		}

		$sql .= "$k = '$v'";
	}

	return $sql . " WHERE " . $condition;
}


/************************
Fields: (* int)
id *
report_id
app_version_code
app_version_name
package_name
file_path
phone_model
android_version
build
brand
product
total_mem_size *
available_mem_size *
custom_data
custom_data
stack_trace
initial_configuration
crash_configuration
display
user_comment
user_app_start_date *
user_crash_date *
dumpsys_meminfo
dropbox
logcat
eventslog
radiolog
is_silent
device_id
installation_id
user_email
device_features
environment
settings_system
settings_secure
shared_preferences
***********************/

// Values
$values = array(	//"id", // auto_increment
	"report_id",
	"app_version_code",
	"app_version_name",
	"package_name",
	"file_path",
	"phone_model",
	"android_version",
	"build",
	"brand",
	"product",
	"total_mem_size",
	"available_mem_size",
	"custom_data",
	"stack_trace",
	"initial_configuration",
	"crash_configuration",
	"display",
	"user_comment",
	"user_app_start_date",
	"user_crash_date",
	"dumpsys_meminfo",
	"dropbox",
	"logcat",
	"eventslog",
	"radiolog",
	"is_silent",
	"device_id",
	"installation_id",
	"user_email",
	"device_features",
	"environment",
	"settings_system",
	"settings_secure",
	"shared_preferences",
	"application_log",
	"user_ip");

?>
