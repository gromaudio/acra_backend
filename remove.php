<?php

include "mysql.php";
include "crashes.php";
include "alphaID.php";

class Foo {
    public $id;
    public $issue_id;
   
   
    public function __construct($id, $issue_id) {
        $this->id = $id;
        $this->issue_id = $issue_id;
    }
}

ob_start();


if(!isset($_GET['appid']))
 die("no appid");

$appid = $_GET['appid'];

if ($appid != "f5ar7wfpkdmda852krjpwmt8iunu4d9f" && $appid != "n7yjvztxh97d76jy4ek5ax4uc3d9cgx7")
 die("wrong appid");

$sql = "SELECT id, issue_id, custom_data, COUNT(issue_id) as nb_errors, COUNT(DISTINCT (installation_id)) as affected_users from crashes WHERE status=0 and application_log NOT LIKE '%V2OVL3.42.0.0%' and appid='" . $appid . "' GROUP by issue_id HAVING affected_users=1 and nb_errors < 5";

$res = mysqli_query($mysql,$sql);
echo(mysqli_num_rows($res)) . "<br/>";
while ($tab = mysqli_fetch_assoc($res)) {
	$sql = "delete from crashes where id = " . $tab['id'];
	echo $sql . "<br/>";
	mysqli_query($mysql, $sql);
}



// Close MySQL
mysqli_close($mysql);

?>
