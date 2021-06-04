<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Register</title>
</head>
<body>
    <form action="#" method="post">

    Username:<br />
    <input type="text" size="24" maxlength="50" name="username" /><br /><br />

    E-Mail:<br />
    <input type="text" size="24" maxlength="255" name="email" /><br /><br />

    Password:<br />
    <input type="password" size="24" maxlength="50" name="password" /><br />

    Repeat password:<br />
    <input type="password" size="24" maxlength="50" name="password_2" /><br />

    <input type="submit" value="Register" /><input type="reset" value="Reset" />
    </form>
</body>
</html>


<?php

if (!isset($_POST["username"]) && !isset($_POST["password"]) && !isset($_POST["password_2"]) && !isset($_POST["email"])) {
    if(!isset($_SESSION)) { 
        session_start(); 
    } 


    if (!isset($_SESSION["username"])) {
        if (!isset($_COOKIE["username"]) && !isset($_COOKIE["password"])) {
            if(_check_db($_COOKIE["username"], $_COOKIE["password"])) {
                loggedIn();
            }
        }

    } else {
        loggedIn();
    }

}


function loggedIn() {
    header("location: index.php");
    exit;
}


include "config.php";

$con = mysqli_connect($mysql_server, $mysql_user , $mysql_password) or die("Server can't be reached or is down!");
mysqli_select_db($con,$mysql_db) or die ("Can't select database!");

if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["password_2"]) && isset($_POST["email"])) {

    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $password_2 = $_POST["password_2"];

    if($password != $password_2) {
        echo "Passwords do not match!";
        exit;
    }

    if (!isValidEmail($email)) {
        echo "E-Mail is not valid!";
        exit;
    }

    $password_hashed = hash("sha512", $password);
    $activationkey = md5($email);


    $res_name = mysqli_query($con,"SELECT `id` FROM `user` WHERE `username` = '" . strtolower($username) . "'");
    $res_mail = mysqli_query($con,"SELECT `id` FROM `user` WHERE `email` = '$email'");
    $rows_name = mysqli_num_rows($res_name);
    $rows_mail = mysqli_num_rows($res_mail);

    if($rows_name == 0 && $rows_mail == 0) {
        $sql = "INSERT INTO `user` (`username`, `password`, `email`, `activated`, `activationkey`) VALUES ('" . strtolower($username) . "', '$password_hashed', '$email', 0, '$activationkey')";
        $res = mysqli_query($con,$sql);

        if($res == true) {
            $link = "http://crashreport.arvid-g.de/activation.php?email=" . $email . "&key=" . $activationkey . "";
            $emailadress = "$username <$email>";
            $subject = "Activate your Crash Report Account";
            $message = "Hello $username, <br /><br />
                        please click on the following link to activate your account: <a href=\"$link\">$link</a> <br /><br />
                        King regards, <br />
                        the Crash Report Team";
            $message = wordwrap($message, 70);
            $header  = "MIME-Version: 1.0" . "\r\n";
            $header .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
            $header .= "From: Crash Report Team <noreply@arvid-g.de>" . "\r\n";
            mail($emailadress, $subject, $message, $header);
            echo "Account successfuly created! Please click on the link in the E-Mail we just send you. Login here: <a href=\"login.php\">Login</a>";

        } else {
        	echo "Error!";
        }

    } else {
        echo "Username or E-Mail already taken!";
    }

} else {

}

function isValidEmail($email){
    return preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email);
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