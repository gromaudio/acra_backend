<?php

include "crashes.php";
include "html.php";

if (file_exists($_SERVER[DOCUMENT_ROOT]."/.htaccess") && file_exists($_SERVER[DOCUMENT_ROOT]."/config.php")) {
	echo '<div class="ok">Installation is already done.</div>';
	exit;
}

$host = isset($_POST[host]) ? $_POST[host] : "localhost";
$username = isset($_POST[username]) ? $_POST[username] : "username";
$password = isset($_POST[password]) ? $_POST[password] : "password";
$database = isset($_POST[database]) ? $_POST[database] : "database";
$table = isset($_POST[table]) ? $_POST[table] : "crashes";

if (!isset($_POST[submit])) {
	$show_form = true;
} else {
	$show_form = false;
	
	$mysql = mysqli_connect($_POST[host], $_POST[username], $_POST[password]);
	if (!$mysql) {
		$show_form = true;
		echo '<div class="error">Unable to connect to mysql server. Check host, user name and password</div>';
	} else {
		echo '<div class="ok">Connected to mysql server, logged in using '.$_POST[username].'</div>';
		
		if (!mysqli_select_db($mysql, $_POST[database])) {
			$show_form = true;
			echo '<div class="error">Unable to select database. Check that you have created the database `'.$_POST[database].'`.</div>';
		} else {
			echo '<div class="ok">Selected the  database `'.$_POST[database].'`.</div>';
			
			// Write config.php
			$file = fopen("config.php", "w");
			if (!$file) {
				echo '<div class="error">Unable to create `config.php`. Check file/folder permissions.</div>';
			} else {
				fprintf($file, "<?php

\$mysql_server = '$host';
\$mysql_user = '$username';
\$mysql_password = '$password';
\$mysql_db = '$database';

?>");
				fclose($file);
				echo '<div class="ok">Wrote `config.php`.</div>';

				// Create table
				$sql = <<<SQL_CREATE
CREATE TABLE IF NOT EXISTS `crashes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appid` varchar(255) NOT NULL,
  `added_date` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `issue_id` varchar(32) NOT NULL,
  `report_id` text NOT NULL,
  `app_version_code` text NOT NULL,
  `app_version_name` text NOT NULL,
  `package_name` text NOT NULL,
  `file_path` text NOT NULL,
  `phone_model` text NOT NULL,
  `android_version` text NOT NULL,
  `build` text NOT NULL,
  `brand` text NOT NULL,
  `product` text NOT NULL,
  `total_mem_size` bigint(14) NOT NULL,
  `available_mem_size` bigint(14) NOT NULL,
  `custom_data` text NOT NULL,
  `stack_trace` text NOT NULL,
  `initial_configuration` text NOT NULL,
  `crash_configuration` text NOT NULL,
  `display` text NOT NULL,
  `user_comment` text NOT NULL,
  `user_app_start_date` text NOT NULL,
  `user_crash_date` text NOT NULL,
  `dumpsys_meminfo` text NOT NULL,
  `dropbox` text,
  `logcat` mediumtext NOT NULL,
  `eventslog` text,
  `radiolog` text,
  `is_silent` text NOT NULL,
  `device_id` text,
  `installation_id` text NOT NULL,
  `user_email` text NOT NULL,
  `device_features` text NOT NULL,
  `environment` text NOT NULL,
  `settings_system` text,
  `settings_secure` text,
  `shared_preferences` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


SQL_CREATE;

			$sql_user = <<<SQL_CREATE
CREATE TABLE IF NOT EXISTS `user` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`username` varchar(100) NOT NULL,
	`password` varchar(128) NOT NULL,
	`email` varchar(100) NOT NULL,
	`activated` int(1) UNSIGNED NOT NULL,
	`activationkey` varchar(32),
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8

SQL_CREATE;

			$sql_app = <<<SQL_CREATE
CREATE TABLE IF NOT EXISTS `app` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`appname` varchar(255) NOT NULL,
`appid` varchar(255) NOT NULL,
`userid` int(11) UNSIGNED NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8

SQL_CREATE;


				// Insert Tables
				$res = mysqli_query($mysql, $sql);
				$res_user = mysqli_query($mysql, $sql_user);
				$res_app = mysqli_query($mysql, $sql_app);

				if (!$res || !$res_user || !$res_app) {
					echo '<div class="error">Create tables failed.</p>';
				} else {
					echo '<div class="ok">Created the tables</div>';
				}
			}
		}
	}
}

if ($show_form) {
?>
		<form method="post" action="install.php" class="form">

			<h1>MySQL server connection</h1>
			<p>Server host:<br />
				<input type="text" name="host" value="<?php echo $host; ?>" /></p>
			<p>Server username:<br />
				<input type="text" name="username" value="<?php echo $username; ?>" /></p>
			<p>Server password:<br />
				<input type="text" name="password" value="<?php echo $password; ?>" /></p>
			<p>Server database name:<br />
				<input type="text" name="database" value="<?php echo $database; ?>" /></p>
			<?php /*<p>Table name:<br />
				<input type="text" name="table" value="<?php echo $table; ?>" /></p>*/ ?>

			<h1>Go!</h1>
			<p><input type="submit" name="submit" value="Process installation" /></p>
		</form>
<?php
}

?>
