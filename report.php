<?php

include "checklogin.php";
include "html.php";
include "mysql.php";
include "crashes.php";

if (!isset($_GET['issue_id'])) {
	header("location: index.php");
	exit;
}

echo '
<head>
<script src="jquery-3.6.0.min.js"></script>
<script src="pagination.js"></script>
<link rel="stylesheet" href="pagination.css">
</head>
';

function showReport($tab) {
	echo "<h1>Report #".$tab['id']."</h1>\n";
	echo '<div style="margin: 45px;">'."\n";
	foreach ($tab as $k => $v) {
		if ($k == "id") {
			continue;
		} else if ($k == "added_date") {
			if (intval($v) > 0) {
				$v = date('d/M/Y G:i:s', intval($v));
			} else {
				$v = "Date unknown";
			}
		} else if ($k == "status") {
			if (intval($v) == STATE_FIXED) {
				$v = 'fixed';
			} else {
				$v = 'new';
			}
		}

		echo "<h2>$k</h2>\n<pre>$v</pre>\n";
	}
	echo "</div>\n";
}

function showStacktrace($tab) {
	echo "<pre>".$tab['stack_trace']."</pre>";
}

function showStatus($tab) {
	if ($tab['status'] == STATE_NEW) {
		echo "<b class=\"status\">Current Status:</b> New\n";
		echo "<br />";
	} else if ($tab['status'] == STATE_FIXED) {
		echo "<b class=\"status\">Current Status:</b> Fixed\n";
		echo "<br />";
	} else if ($tab['status'] == STATE_INVALID) {
		echo "<b class=\"status\">Current Status:</b> Invalid\n";
		echo "<br />";
	}
}

function showVersions($tab) {
	foreach ($tab as $k => $v) {
		if ($k == "app_version_code") {
			$versioncode = $v;
		} else if ($k == "app_version_name") {
			$versionname = $v;
		}
		
	}

	echo "<tr>
		<td>$versioncode ($versionname)</td>";
	echo "<br />";
}

function showLastOccurred($tab) {
	$value = date("d/M/Y G:i:s", $tab['added_date']);
	echo "<b class=\"lastoccurrence\">Last Occurred:</b> ".$value."\n";
	echo "<br />";
}

function getNumberOfCrashes() {
	global $mysql;
	$sql_overall_crashnr = create_mysql_select(array('id'), "issue_id = '?'", array($_GET['issue_id']));
	$res_overall_crashnr = mysqli_query($mysql,$sql_overall_crashnr);
	return mysqli_num_rows($res_overall_crashnr);
}

function showNumberOfCrashes() {
        global $mysql;	
	$sql_overall_crashnr = create_mysql_select(array('id'), "issue_id = '?'", array($_GET['issue_id']));
	$res_overall_crashnr = mysqli_query($mysql, $sql_overall_crashnr);
	$rows = mysqli_num_rows($res_overall_crashnr);
	if ($rows == 0) {
//		header("location: index.php");
		exit;
	}
	if ($rows != 1) {
		echo "This Crash Occurred <b class=\"crashcount\">$rows</b> times";
	} else {
		echo "This Crash Occurred <b class=\"crashcount\">$rows</b> time";
	}
}

function prepareVersionPieChart() {
	global $mysql;
	$c = array('app_version_code', 'count(app_version_code) as nb');
	$sl = "issue_id = '?'";
	$slA = array($_GET['issue_id']);

	$sql = create_mysql_select($c, $sl, $slA, 'nb DESC', 'app_version_code');
	$res = mysqli_query($mysql,$sql);

	$empty = "";
	$js = "\t";
	while ($t = mysqli_fetch_assoc($res)) {
		if (strlen($empty)) {
			$js .= ", ";
		}
		$percantage = 100.0*($t['nb']/getNumberOfCrashes());
		$js .= "['v".$t['app_version_code']."', " . $t['nb'] . ", ". $percantage ."], ";
		// $value .= '<b title="'.$t[nb].' occurrences">'.$t[app_version_code]."</b> (".sprintf("%.1f%%", 100.0*$t[nb]/$tab[nb_errors]).")<br />";
	}
	$js = substr_replace($js ,"\t \n", -2);
	echo $js;
}


function prepareModelPieChart() {
	global $mysql;
	$c = array('phone_model', 'count(phone_model) as nb');
	$sl = "issue_id = '?'";
	$slA = array($_GET['issue_id']);

	$sql = create_mysql_select($c, $sl, $slA, 'nb DESC', 'phone_model');
	$res = mysqli_query($mysql,$sql);

	$empty = "";
	$js = "\t";
	while ($t = mysqli_fetch_assoc($res)) {
		$percantage = 100.0*($t['nb']/getNumberOfCrashes());
		$js .= "['".$t['phone_model']."', " . $t['nb'] . ", ". $percantage ."], ";
		// $value .= '<b title="'.$t[nb].' occurrences">'.$t[app_version_code]."</b> (".sprintf("%.1f%%", 100.0*$t[nb]/$tab[nb_errors]).")<br />";
	}
	$js = substr_replace($js ,"\t \n", -2);
	echo $js;
}

function prepareAndroidVersionPieChart() {
	global $mysql;
	$c = array('android_version', 'count(android_version) as nb');
	$sl = "issue_id = '?'";
	$slA = array($_GET['issue_id']);

	$sql = create_mysql_select($c, $sl, $slA, 'nb DESC', 'android_version');
	$res = mysqli_query($mysql,$sql);

	$empty = "";
	$js = "\t";
	while ($t = mysqli_fetch_assoc($res)) {
		$percantage = 100.0*($t['nb']/getNumberOfCrashes());
		$js .= "['".$t['android_version']."', " . $t['nb'] . ", ". $percantage ."], ";
		// $value .= '<b title="'.$t[nb].' occurrences">'.$t[app_version_code]."</b> (".sprintf("%.1f%%", 100.0*$t[nb]/$tab[nb_errors]).")<br />";
	}
	$js = substr_replace($js ,"\t \n", -2);
	echo $js;
}

function prepareOrientationPieChart() {
	global $mysql;
	$c = array('crash_configuration', 'count(crash_configuration) as nb');
	$sl = "issue_id = '?'";
	$slA = array($_GET['issue_id']);

	$sql = create_mysql_select($c, $sl, $slA, 'nb DESC', 'crash_configuration');
	$res = mysqli_query($mysql,$sql);

	$empty = "";
	$js = "\t";
	$orientation_portrait = 0;
	$orientation_landscape = 0;
	while ($t = mysqli_fetch_assoc($res)) {
		$percantage = 100.0*($t['nb']/getNumberOfCrashes());

		if (stristr($t['crash_configuration'], "orientation=ORIENTATION_PORTRAIT")) { 
			$orientation_portrait++;

		} else if (stristr($t['crash_configuration'], "orientation=ORIENTATION_LANDSCAPE")) {
			$orientation_landscape++;
		}
	}
	$js .= "['Portrait', " . $orientation_portrait . ", ". $percantage ."], ";
	$js .= "['Landscape', " . $orientation_landscape . ", ". $percantage ."], ";

	$js = substr_replace($js ,"\t \n", -2);
	echo $js;
}

$sql = "SELECT `appid` FROM `crashes` WHERE `issue_id` = '" . $_GET['issue_id'] . "'";
$res = mysqli_query($mysql,$sql);
mysqli_data_seek($res,  0); $row = mysqli_fetch_array($res)[0];

// Show button
echo '<div id="reportbuttons" style="margin-right: 100px;">';
echo '<a class="button" href="javascript:setStatusAndGo(\''.$_GET['issue_id'].'\', \''.STATE_FIXED.'\', \'./reports.php?app=' . $row . '\');">mark as fixed</a> ';
echo '<a class="button" href="javascript:setStatusAndGo(\''.$_GET['issue_id'].'\', \''.STATE_INVALID.'\', \'./reports.php?app='. $row . '\');">mark as invalid</a> ';
echo '<a class="button" href="javascript:setStatusAndGo(\''.$_GET['issue_id'].'\', \''.STATE_NEW.'\', \'./reports.php?app=' . $row . '\');">mark as new</a> ';
echo "</div>\n";

// Display reports
$c = array('status', 'count(issue_id) as nb_errors', 'added_date', 'stack_trace');
$sl = "issue_id = '?'";
$slA = array($_GET['issue_id']);

$sql = create_mysql_select($c, $sl, $slA, "id desc", 'issue_id');

//$sql = create_mysql_select(null, "issue_id = '?'", array($_GET[issue_id]));
$sql .= " LIMIT 0, 5";
$res = mysqli_query($mysql, $sql);

if (!$res) {
	log_to_file("Unable to query: $sql");
	echo "<p>Server error.</p>\n";
	return;
}

$rows = mysqli_num_rows($res);

?>
<br />


<div class="leftreportcolumn">
	<?php
		$tab1 = mysqli_fetch_assoc($res);
	  	showStatus($tab1);
	  	showLastOccurred($tab1);
	  	showNumberOfCrashes();

		$array = affectedVersionsAndUsers($_GET['issue_id']);
		echo "<br/>";
		echo "<b>Affected users: </b>" . count($array['users']);
		echo "<br/>";
		echo "<div style='word-break:break-all;'>(" . implode(",", array_keys($array['users'])) . ")<br/></div>";
		echo "<b>Affected versions</b></br>";
		foreach($array['versions'] as $version => $v)
			echo "$version<br/>";
		//var_dump($array);
		
	?>
</div>
<div class="rightreportcolumn">
	<div id="tabs">
	<!-- 	<ul>
	        <li><a href="#tabs-1">Stacktrace</a></li>
	        <li><a href="#tabs-2">Affected Versions</a></li>
	        <li><a href="#tabs-3">Statistics</a></li>
	    </ul> -->

	    <div id="tabs-1">
		  	<p><?php
		  			if ($rows == 1) {
		  				mysqli_data_seek($res,  0); $tab2 = mysqli_fetch_array($res)[ 'stack_trace'];
		  				echo "<pre>$tab2</pre>";

		  			} else {
		  				$tab2 = mysqli_fetch_assoc($res);
		  				showStacktrace($tab2);
		  			}
		  		?>
		  	</p>
	    </div>


	    <div id="tabs-2">
		  	<!-- <table>
				<tr>
					<th>Versioncode (Version Name)</th>
					<th>Crashes</th>
				</tr> -->
	
			<?php
				/*$sql_versions = "SELECT DISTINCT `app_version_code`, `app_version_name` FROM `crashes` WHERE `issue_id` = '" . $_GET[issue_id] . "'";

				$res_versions = mysqli_query($mysql,$sql_versions);

			  	while ($tab3 = mysqli_fetch_assoc($res_versions)) {
			  		showVersions($tab3);
			  		$sql_crashes = "SELECT * FROM `crashes` WHERE `issue_id` = '".$_GET[issue_id]."' AND `app_version_code` = '".$tab3['app_version_code']."'";
			  		$res_crashes = mysqli_query($mysql,$sql_crashes);
			  		$crashes = mysqli_num_rows($res_crashes);
			  		echo "<td>$crashes</td>
					</tr>";
			  	}*/
		  	?>
		  	<!-- </table> -->
	    </div>


	   
<br/>
	    <div id="demo"></div>
<br/>
	    <div id="cont"></div>

	    <br/>

	</div>
</div>


<!--
	// <?php
	// 	prepareVersionPieChart();
	// ?>
         
 //    var plot1 = $.jqplot('chartdiv_versions', [data_version], {
 //        grid: {
 //            drawBorder: false, 
 //            drawGridlines: false,
 //            background: '#ffffff',
 //            shadow:false
 //        },
 //        axesDefaults: {
             
 //        },
 //        seriesDefaults:{
 //            renderer:$.jqplot.PieRenderer,
 //            rendererOptions: {
 //                showDataLabels: true
 //            }
 //        },
 //        legend: {
 //            show: true,
 //            rendererOptions: {
 //                numberRows: 2
 //            },
 //            location: 's'
 //        }
 //    });

// print_r($tab);
// print_r(mysqli_fetch_assoc($res));
// $tab = mysqli_fetch_assoc($res);
// print_r($tab['stacktrace']);
// while ($tab = mysqli_fetch_assoc($res)) {
// 	showReport($tab);
// } -->


 <?php
         
# print_r($tab);
# print_r(mysqli_fetch_assoc($res));
# $tab = mysqli_fetch_assoc($res);
 #print_r($tab['stacktrace']);
$res = mysqli_query($mysql, $sql);
 while ($tab = mysqli_fetch_assoc($res)) {
 	//showReport($tab);
 }
?>


<script>

$(document).ready(function(){
	
	$('#demo').pagination({
    dataSource: 
    <?php 
    	$sql = "SELECT `id` FROM `crashes` WHERE `issue_id` = '" . $_GET['issue_id'] . "' order by id DESC";
    	$res = mysqli_query($mysql, $sql);
    	$array = array();
    	while( $row = mysqli_fetch_array($res)){
    		$array[] = $row['id']; // Inside while loop
	}	

	$position = 1;
	if (isset($_GET['report_id'])) {
		$position = array_search($_GET['report_id'], $array);
		if ($position === false)
			$position = 1;
		else $position++;
	}

	

	echo "[ " . implode(",", $array) . "]";
    ?>
    ,
    pageSize: 1,
    pageRange: 5,
    showGoInput: true,
    showGoButton: true,
    pageNumber: <?php echo $position; ?>,
    position: 'top',
    formatNavigator: '<span style="color: #f00"><%= currentPage %></span> st/rd/th, <%= totalPage %> pages, <%= totalNumber %> entries',
    callback: function(data, pagination) {
   		console.log(data);
   		//window.location.href='report.php?page=3'

   		$.get("ajax.php", { action: "getreport", report_id: data[0] }, function(data, status){
   			$('#cont').html(data);
		    //alert("Data: " + data + "\nStatus: " + status);
		  });
    }
})

});
</script>

<?php
if (!isset($keepConnection))
  mysqli_close($mysql);
?>

</body>
</html>
