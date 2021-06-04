<?php

// initiate session!
if(!isset($_SESSION)) { 
    session_start(); 
} 

if (isset($_SESSION["username"])) {
	if (isset($_COOKIE["username"]) && isset($_COOKIE["password"])) {
		if(_check_db($_COOKIE["username"], $_COOKIE["password"])) {
			$_SESSION["username"] = $_COOKIE["username"];
        } else {
        	notLoggedIn();
        }
	}

} else {
	if (isset($_COOKIE["username"]) && isset($_COOKIE["password"])) {
		if(_check_db($_COOKIE["username"], $_COOKIE["password"])) {
			$_SESSION["username"] = $_COOKIE["username"];
        } else {
        	notLoggedIn();
        }
	} else {
		notLoggedIn();
	}
}



function notLoggedIn() {
	echo "Not logged in, please login first! Click here to <a href=\"login.php\">Login</a>";
	exit;
}

function _check_db($username, $password) {
	include "config.php";

    $con = mysqli_connect($mysql_server, $mysql_user , $mysql_password) or die("Server can't be reached or is down!"); 
    mysqli_select_db($con,$mysql_db) or die ("Can't select database!");
    $sql = "SELECT `username`, `password`, `activated` FROM `user` WHERE `username` = '" . strtolower($username) . "' LIMIT 1";
    $result = mysqli_query($con,$sql);
    $row = mysqli_fetch_object($result);
 
    //general return
    if(is_object($row) && $row->password == $password && $row->activated == 1)
        return true;
    else
        return false;
}

?>