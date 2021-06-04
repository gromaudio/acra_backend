<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Login</title>
</head>
<body>
    <form action="#" method="post">
        
        Username:<br />
        <input type="text" size="24" maxlength="50" name="username" /><br /><br />

        Password:<br />
        <input type="password" size="24" maxlength="50" name="password" /><br /><br />

        <input type="checkbox" name="remember" id="remember" value="1" /> Remember me <br />

        <input type="submit" value="Login" /><input type="reset" value="Reset" />
    </form>
</body>
</html>


<?php 
    if(!isset($_SESSION)) { 
    session_start(); 
} 

?> 

<?php

if (!isset($_SESSION["username"])) {
    if (!isset($_COOKIE["username"]) && !isset($_COOKIE["password"])) {
        if(_check_db($_COOKIE["username"], $_COOKIE["password"])) {
            loggedIn();
        }
    }

} else {
    loggedIn();
}

function loggedIn() {
    header("location: index.php");
    exit;
}

// $con = mysqli_connect($mysql_server, $mysql_user , $mysql_password) or die("Server can't be reached or is down!"); 
// mysqli_select_db($con,$mysql_db) or die ("Can't select database!"); 

if (isset($_POST["username"]) || $_POST["username"] != '' || isset($_POST["password"]) || $_POST["password"] != '') {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $password_hashed = hash("sha512", $password);


    if (isset($username) && isset($password_hashed)) {
        if (_check_db($username, $password_hashed)) {
            $_SESSION["username"] = $username;

            if(isset($_POST["remember"])) {
                setcookie('username', $username, time() + 30*24*60*60);
                setcookie('password', $password_hashed, time() + 30*24*60*60);

            } else {
                setcookie('username', '', time() - 30*24*60*60);
                setcookie('password', '', time() - 30*24*60*60);
            }

            echo "Login successful! You will be redirected, if not click here: <a href=\"index.php\">Dashboard</a>";
            header("refresh: 1; index.php");

        } else { 
            echo "Wrong username and/or password or account not activated!"; 
        }
    } else {

    }
}

function _check_db($username, $password) {
    include "config.php";

    $con = mysqli_connect($mysql_server, $mysql_user , $mysql_password) or die("Server can't be reached or is down!"); 
    mysqli_select_db($con,$mysql_db) or die ("Can't select database!");
    $sql = "SELECT `username`, `password`, `activated` FROM `user` WHERE `username` = '" . strtolower($username) . "' LIMIT 1";
    $result = mysqli_query($con,$sql);
    $row = mysqli_fetch_object($result);
 
    //general return
    if(is_object($row) && $row->password == $password && $row->activated == 1) {
        return true;
    } else {
        return false;
    }
}

?>