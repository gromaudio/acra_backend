<?php

include "checklogin.php";
include "crashes.php";
include "html.php";
include "check_install.php";
include "mysql.php";
include "alphaID.php";
include "version.php";

// echo "<h4 style=\"text-align: center;\"><a href=\"reports.php\">Reports</a></h4>";

global $VERSION_RELEASE, 
       $VERSION_RELEASE_FULL, 
       $VERSION_FILTERED, 
       $VERSION_RELEASE_A12, 
       $VERSION_RELEASE_FULL_A12, 
       $VERSION_FILTERED_A12,
       $VERSION_RELEASE_A7, 
       $VERSION_RELEASE_FULL_A7, 
       $VERSION_FILTERED_A7;

if(!isset($_SESSION)) { 
    session_start(); 
} 

$q= $_GET['q'];
$username= strtolower($_SESSION["username"]);

// display services
echo "<div id=\"services\">
     <h5><a href=\"services.php?id=group\"> Group </a>
    – group all reports into issues (takes some time, DO NOT refresh before the finish)</h5>
      <h5><a href=\"services.php?id=clean\"> Clean </a>
      – clean all reports from the old versions</h5>
      <h5><a href=\"services.php?id=clean_plus\"> Clean+ </a>
      – removes all issues that have only 1 occurrence (ANRs and Tombstones only)</h5>
      </div>
      <form method=\"get\" action=\"reports.php\">
        Filter by serial: <input type=\"text\" name=\"q\" value=\"$q\"/> 
        <input type=\"submit\" value=\"Search\"/>
        <input type=\"hidden\" name=\"d\" value=\"1\" />
      </form><hr/>";

// Display Your Apps
$sql = "SELECT a.appname, a.appid, COUNT(*) issues 
        FROM crashes as c JOIN app a ON a.appid= c.appid 
                          JOIN user u ON u.id= a.userid 
        WHERE u.username= '$username'
        GROUP BY appid";
$res = mysqli_query($mysql, $sql);
$rows = mysqli_num_rows($res);

echo "<br/><div id=\"listApps\">";
if ($rows == 0) {
	echo "No Applications registered!";
} else {
	echo "<h3>My Applications:</h3>";
	while ($tab = mysqli_fetch_assoc($res)) {
		//echo "<a href=\"reports.php?app=" . $tab[appid] . "\">" . $tab[appname] . "</a><br />";
		echo "<h3><a href=\"reports.php?app=" . $tab['appid'] . "\">" . $tab['appname'] . " (".$tab['issues'].")" . "</a><br/></h3>";
		//echo "<h1>".status_name($status)." reports (".$issues.")</h1>\n";
	}
}
echo "</div>  
      <hr/>
      <div id=\"stats\">
        <h4>Statistics:</h4>
        <table>
          <tr>
            <th width='80px' align='left'><h5>Version</h5></th>
            <th width='80px' align='left'><h5>Reports</h5></th>
            <th><h5>Users</h5></th>
          </tr>";

$sql = "SELECT
		    COUNT(DISTINCT custom_data) as u,
		    android_version as b,
		    COUNT(*) as c
		FROM
		    crashes
		GROUP BY
		    android_version
		ORDER BY
		    c DESC";
$res = mysqli_query($mysql, $sql);

while ($build = mysqli_fetch_assoc($res)) {
		//echo "<a href=\"reports.php?app=" . $build[appid] . "\">" . $build[appname] . "</a><br />";
		$ver = $build['b'];
		$count = $build['c'];
    	$users = $build['u'];
    

		if (strpos($ver, "V2OVL") !== false)
			$ver = substr($ver, 5);
		if (strpos($ver, 'V2SC') !== false)
			$ver = substr($ver, 4);
		if (strpos($ver, 'LITE_N_VL') !== false)
			$ver = substr($ver, 9);

		$lowMemoryCount = 0;

		if ($VERSION_RELEASE_A12 === $ver) {
			$sql = "SELECT COUNT(`id`) as u FROM `crashes` WHERE `appid` = '77wjw673hkkiw37rcumqarrwiczcqpk3' and stack_trace='Low memory' and android_version='$VERSION_RELEASE_A12'";
			$res2 = mysqli_query($mysql, $sql);
			$lowMemoryCount = mysqli_fetch_assoc($res2)['u'];

			$count = $count - $lowMemoryCount;
			$count = $count . " (+" . $lowMemoryCount. " low memory)";
		} else if ($VERSION_RELEASE_A7 === $ver) {
			$sql = "SELECT COUNT(`id`) as u FROM `crashes` WHERE `appid` = '32gym8mf5juqjwxk43y8m47ygq3nnab8' and stack_trace='Low memory' and android_version='$VERSION_RELEASE_A7'";
			$res2 = mysqli_query($mysql, $sql);
			$lowMemoryCount = mysqli_fetch_assoc($res2)['u'];

			$count = $count - $lowMemoryCount;
			$count = $count . " (+" . $lowMemoryCount. " low memory)";
		}

		echo "  <tr>";
		echo "<td><a href='https://g-auth.net/mycar/acra/reports.php?q=" . urlencode($ver) . "&ver=1'>" . htmlspecialchars($ver) . "</a></td>";
		echo "<td  width='240'>" . $count . "</td>";
		echo "<td>" . $users . "</td>";
		echo "</tr>";
	}

echo "</table>
      </div>
      <br />";

?>
