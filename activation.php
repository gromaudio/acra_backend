<?php

include "mysql.php";

if (isset($_GET['email']) && preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $_GET['email'])) {
	$email = $_GET['email'];
} else {
	exit;
}

if (isset($_GET['key']) && (strlen($_GET['key']) == 32)) {
	$key = $_GET['key'];
} else {
	exit;
}

if (isset($email) && isset($key)) {
	$sql = "SELECT `username` FROM `user` WHERE (`email` = '$email' AND `activationkey` = '$key') LIMIT 1";
	$res = mysqli_query($mysql,$sql);
	mysqli_data_seek($res,  0); $username_temp = mysqli_fetch_array($res)[0];

	$sql = "SELECT `activationkey` FROM `user` WHERE (`email` = '$email' AND `username` = '" . $username_temp . "') LIMIT 1";
	$res = mysqli_query($mysql,$sql);
	mysqli_data_seek($res,  0); $activationkey_temp = mysqli_fetch_array($res)[0];


	if ($activationkey_temp == $key) {
		$sql = "UPDATE `user` SET `activated` = 1, `activationkey` = 1337 WHERE (`email` = '$email' AND `activationkey` = '$key') LIMIT 1";
		$res = mysqli_query($mysql,$sql);

		if ($res == true) {
			echo '<div>Your account is now active. You may now <a href="login.php">Log in</a></div>';

		} else {
			echo '<div>Oops !Your account could not be activated. Please recheck the link or contact the system administrator.</div>';
		}

	} else {
		echo '<div>An error occured!</div>';
	}

} else {
	echo '<div>An error Occured!</div>';
}


?>