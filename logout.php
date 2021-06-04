<?php

// initiate session!
if(!isset($_SESSION)) { 
    session_start(); 
} 


// check if sesstion exists
if(isset($_SESSION["username"])) {
	unset($_SESSION["username"]);
   	unset($_SESSION);

   	setcookie('username', '', time() - 30*24*60*60);
    setcookie('password', '', time() - 30*24*60*60);

    session_destroy();

   	echo "Successfuly logged out!";
   	exit;

} else {
	echo "Not logged in.";
	exit;
}

?>