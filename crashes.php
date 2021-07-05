<?php

error_reporting(E_ALL & ~E_NOTICE);

define('STATE_NEW', 0);
define('STATE_FIXED', 1);
define('STATE_INVALID', 2);

function log_to_file($msg) {
	$file = fopen("err_", "a+");
	fputs($file, date("d/M/Y G:i:s\t") . $msg . "\n");
	fclose($file);
}

function status_name($status) {
	if (intval($status) == STATE_NEW) {
		return "new";
	} else if (intval($status) == STATE_FIXED) {
		return "fixed";
	} else {
		return "invalid";
	}
}

// Finds in array
function array_find($needle, $haystack) {
	foreach($haystack as $k => $v) {
		if (strstr($v, $needle) !== FALSE) {
			return $k;
		}
	}
	return FALSE;
}

function issue_id($stack_trace, $package) {
	$lines = explode("\n", $stack_trace);
       //$idx = array_find('Caused by:', $lines);
       //$v = $lines[$idx];
       if (array_find(": ", $lines) === FALSE && array_find($package, $lines) === FALSE) {
	   $value = $lines[0];
       } else {
	   $value = "";
	   foreach ($lines as $id => $line) {
	       if (strpos($line, ": ") !== FALSE || strpos($line, $package) !== FALSE
			 || strpos($line, "Error") !== FALSE || strpos($line, "Exception") !== FALSE) {
		  $value .= $line . "<br />";
	       }
	   }
       }
	return md5($value);
}

function display_versions($appid) {
	global $_GET;
	global $mysql;

	$columns = array('id', 'max(added_date) as last_seen',
					'count(issue_id) as nb_errors',
					'app_version_code', 'app_version_name', 'android_version');

	// if(!empty($_GET[package])) {
	// 	$sel = "package_name LIKE '?'";
	// 	$selA[] = array(mysqli_real_escape_string("%",str_replace("*");
	// } else {
	// 	$sel = null;
	// 	$selA[] = null;
	// }

	$sel = "`appid` = '?'";
	$selA[] = $appid;

	$order = "app_version_code ASC";
	$group = "app_version_code";
	$sql = create_mysql_select($columns, $sel, $selA, $order, $group);
	$res = mysqli_query($mysql, $sql);



	if (!$res || mysqli_num_rows($res) ==0) {
		echo "<p>unable to compute versions<br /></p>\n";
		return;
	}

	$versions = array();
	$names = array();
	$nb_errors = array();

	while ($tab = mysqli_fetch_assoc($res)) {
		$versions[] = $tab['app_version_code'];
		$names[] = $tab['app_version_name'];
		$nb_errors[] = $tab['nb_errors'];
	}

	// echo "<h1>Application versions</h1>\n";
	echo "<table class=\"crashes\" style=\"width: 600px;\">\n<thead>\n<tr>\n";
	foreach ($versions as $id => $version) {
		echo "<th>$version<br />(".$names[$id].")</th>\n";
	}
	echo "</tr>\n</thead>\n<tbody>\n<tr>\n";
	foreach ($nb_errors as $id => $nb) {
		echo '<td style="text-align: center; ';
		if ($_GET[v] == $versions[$id]) {
			echo " background: rgb(50,200,50);";
		}
		echo "\"><a class=\"versions\" href=\"?app=".$appid."&v=".$versions[$id]."\">$nb</a></td>\n";
	}

	echo "</tbody>\n</table>\n";
	echo '<div id="chart1" style="height:300px;width:300px; "></div>';
	echo "<script>$(document).ready(function(){
  var data = [";

	foreach ($versions as $id => $version) {
		echo "['V$version', ".$nb_errors[$id]."] ";
	}
	echo "  ];
  var plot1 = jQuery.jqplot ('chart1', [data],
    {
      seriesDefaults: {
	renderer: jQuery.jqplot.PieRenderer,
	rendererOptions: {
	  showDataLabels: true
	}
      },
      legend: { show:true, location: 'e' }
    }
  );
});</script>";

}

function get_nb_crashes_per_package($package) {
	$columns = array("date_format(from_unixtime(added_date), '%Y-%c-%d') as date", 'added_date', 'count(*) as nb_crashes');
	
	$sel = "added_date > '?'";
	$selA = array(time() - 86400*30);
	
	$sel .= " AND package_name = '?'";
	$selA[] = $package;
	
	$order = "date ASC";
	$group = "date";
	
	$sql = create_mysql_select($columns, $sel, $selA, $order, $group);
	$res = mysqli_query($mysql,$sql);
	
	if (!$res || !mysqli_num_rows($res)) {
		echo "<p>$sql</p>";
		echo "<p>Server error.</p>";
		return;
	}
	
	$results = array();
	while ($tab = mysqli_fetch_assoc($res)) {
		$results[] = $tab;
	}
	return $results;
}

function display_crashes_vs_date() {
	global $_GET;
	
	$columns = array('package_name');
	
	
	
	$sql = create_mysql_select(array('package_name'), null, null, 'package_name asc', 'package_name');
	
	$res = mysqli_query($mysql,$sql);
	
	if (!$res || !mysqli_num_rows($res)) {
		echo "<p>$sql</p>";
		echo "<p>Server error.</p>";
		return;
		
	}
	
	echo '<div id="crashes_vs_date" style="height:400px;width:600px;"></div>';
	echo "<script>jQuery(document).ready(function(){\n";
	$series = array();
	$seriesNames = array();
	$data = array();
	while ($tab = mysqli_fetch_assoc($res)) {
		if (!strlen($tab[package_name])) {
			continue;
		}
		$varname = str_replace(".", "", $tab[package_name]);
		$series[] = $varname;
		$seriesNames[] = $tab[package_name];
		$data[$varname] = array();
		
		$crashes = get_nb_crashes_per_package($tab[package_name]);
		foreach ($crashes as $crash_data) {
			$data[$varname][] = "['".$crash_data[date]."', ".$crash_data[nb_crashes]."]";
		}
		echo "var $varname=[". implode(", ", $data[$varname]) ."];\n";
		
	}

	

	echo "
	var line1 = [6.5, 9.2, 14, 19.65, 26.4, 35, 51];

	var plot4 = $.jqplot('crashes_vs_date', [".implode(", ", $series)."], {
		axes:{
			yaxis:{
				min:0,
				tickOptions:{
					formatString:'%.0f'
	    		}
			},
			xaxis:{
			 	renderer: $.jqplot.DateAxisRenderer,
			 	tickOptions:{
	    			formatString:'%b %#d'
	  			}
			}
		},

		highlighter: {
			show: true,
			sizeAdjust: 7.5
		},

		cursor:{ 
			show: true,
			zoom:true, 
			showTooltip:false
		},

		legend: {
			show:true,
			location: 'e',
			placement: 'outsideGrid',
			predraw: true,
			labels:['".implode("', '", $seriesNames)."']
		},
		captureRightClick: true,
		series:[";

			foreach ($seriesNames as $name) {
				echo "\n\t\t{lineWidth:4, label:'$name'},";
			}

	echo "]";
	echo "});});</script>";
	
	// echo "
 //  var plot1 = $.jqplot('crashes_vs_date', [". implode(", ", $series)."], {
 //    title:'Crashes: last 30 days',
 //    axes:{
	// 	yaxis:{
	// 		min: 0,
	// 		tickOptions:{
	// 			formatString:'%.0f'
	//     }
	// 	},
	// xaxis:{
	//   renderer: jQuery..jqplot.DateAxisRenderer,
	//   tickOptions:{
	//     formatString:'%b %#d'
	//   } 
	// }
	// }
	// ,
	// highlighter: {
	// 	show: true,
	// 	sizeAdjust: 7.5
	// },
	// cursor:{ 
	// 	show: true,
	// 	zoom:true, 
	// 	showTooltip:false
	// },
	// legend: {
	// 	show:true,
	// 	location: 'e',
	// 	placement: 'outsideGrid',
	// 	predraw: true,
	// 	labels:['".implode("', '", $seriesNames). "']
	// },
	// captureRightClick: true,
	// series:["
	// ;
	
// 	foreach ($seriesNames as $name) {
// 		echo "\n\t\t{lineWidth:4, label:$name},";
// 	}
// 	echo "
// 	]
//   });
// });</script>";
	// echo "});</script>";
}

function affectedVersionsAndUsers($issue_id) {
	global $mysql;
	global $_GET, $package;

	$columns = array('custom_data');

	$sel = "issue_id = ?";
	$selA = array($issue_id);
	$sql = create_mysql_select($columns, $sel, $selA, "", "issue_id");
	$res = mysqli_query($mysql, "select custom_data from crashes where issue_id='" . $issue_id . "'");

	$result = array();
	while ($row = mysqli_fetch_assoc($res)) {
		$lines = explode("\n", $row['custom_data']);
		if (count($lines) >= 2) {
			$v = explode("=", $lines[0])[1];
			$result['users'][$v] = 1;
			$v = explode("=", $lines[1])[1];
			$result['versions'][$v] = 1;
		}
	}

	return $result;
}

function display_crashes($status) {
	global $mysql;
	global $_GET, $package;

	$columns = array('id', /* 'status', */ 'MAX(added_date) as last_seen', 'COUNT(issue_id) as nb_errors', 
		'COUNT(DISTINCT (installation_id)) as affected_users',
		'issue_id',
		'MAX(app_version_code) as version_code', /*'MAX(app_version_name) as version_name', 'package_name', */
		// 'phone_model', 'android_version', 'brand', 'product',
		'custom_data',
		'stack_trace');

	$sel = "status = ?";
	$selA = array($status);

	// Filter by appid
	if(!empty($_GET[app])) {
		$sel .= " AND appid = '?'";
		$selA[] = mysqli_real_escape_string($mysql,$_GET['app']);
	}

	// // Filter by package
	// if (!empty($_GET[package])) {
	// 	$sel .= " AND package_name LIKE '?'";
	// 	$pkg = str_replace("*", "%", $_GET[package]);
	// 	$selA[] = mysqli_real_escape_string($mysql,$pkg);
	// }

	// Filter by app version code
	if (!empty($_GET[v])) {
		$sel .= " AND app_version_code = ?";
		$selA[] = mysqli_real_escape_string($mysql,$_GET[v]);
	}

	// Search
	if ($_GET[q] != '') {
		$args = explode(" ", $_GET[q]);
		foreach($args as $arg) {
			if ($arg[0] == "-") {
				$sel .= " AND custom_data NOT LIKE '%?%'";
				$selA[] = substr($arg, 1);
			} else {
				$sel .= " AND custom_data LIKE '%?%'";
				$selA[] = $arg;
			}
		}
	}

	$order = "";
	if ($_GET[v]) {
		$order .= "nb_errors DESC, ";
	}
	$order .= "version_code DESC, last_seen DESC";
	$sql = create_mysql_select($columns, $sel, $selA, $order, "issue_id");
	$res = mysqli_query($mysql,$sql);

	if (!$res) {
		log_to_file("Unable to query: $sql");
		echo "<p>Server error.</p>\n";
		echo "<p>SQL: $sql</p>";
		return;
	} else if (mysqli_num_rows($res) == 0) {
		echo "<p>No result for this query.</p>\n";
		return;
	}

	echo "<h1 style='display: inline; margin-top: 40px'>".status_name($status)." reports (".mysqli_num_rows($res).")</h1>";

	echo "<button style='display: inline; float: right; height: 40px; padding: 10px;' onclick=\"location.href='group.php?appid=$_GET[app]'\" type='button'>
         PROCESS</button>";

	if ($_GET[q] != '') {
		echo "<p>Filtered with phone_model matching '$_GET[q]'</p>\n";
	}
	$first = 1;
	echo "<table class=\"crashes\">\n";
	while ($tab = mysqli_fetch_assoc($res)) {
		if ($first == 1) {
			echo "<thead>\n<tr>\n";
			foreach ($tab as $k => $v) {
				if ($k == "stack_trace") {
					$k = "exception";
				}

				if ($k == "custom_data") {
					//echo "<th>serial</th>\n";
					echo "<th>build</th>\n";
				}

				if ($k == "version_code" || $k == "issue_id" || $k == "custom_data" || $k == "id" || $k == "affected_users")
					continue;

				echo "<th>$k</th>\n";
			}
			$first = 0;
			echo "</tr>\n</thead>\n<tbody>\n";
		}

		echo '<tr id="id_'.$tab['id'].'" onclick="javascript:document.location=\'./report.php?issue_id='.$tab['issue_id'].'\';">'."\n";
		foreach ($tab as $k => $v) {
			if ($k == "version_code" || $k == "issue_id" || $k == "id" || $k == "affected_users")
				continue;
			if ($k == "stack_trace") {
				$lines = explode("\n", $v);
				//$idx = array_find('Caused by:', $lines);
				//$v = $lines[$idx];
				$value = "<div style='height:250px; overflow:hidden'>";
				/*if (array_find(": ", $lines) === FALSE && array_find(PACKAGE, $lines) === FALSE) {
					$value .= $lines[0];
				} else {*/
					$value .= "";
					foreach ($lines as $id => $line) {
						/*if (strpos($line, ": ") !== FALSE || strpos($line, PACKAGE) !== FALSE
							|| strpos($line, "Error") !== FALSE || strpos($line, "Exception") !== FALSE) {*/
							$value .= $line . "<br />";
						//}
					}
				//}

				if ($tab['issue_id'] == "") {
					mysql_query(create_mysql_update(array('issue_id' => md5($value)), "id = ?", array($tab['id'])));
				}

				$value .= "</div>";
			} else if ($k == "last_seen") {
				$value = date("d/M/Y G:i:s", $v);
			} else if ($k == "status") {
				$value = status_name($tab['status']);
			} else if ($k == "custom_data") {
				$lines = explode("\n", $v);
				if (count($lines) == 0) {
					echo "<td$style>none</td>\n";
					//echo "<td$style>none</td>\n";
				}  else if(count($lines) >= 2) {
					//$v = explode("=", $lines[0])[1];
					//echo "<td$style>$v</td>\n";
					$v = explode("=", $lines[1])[1];
					echo "<td$style>$v</td>\n";
				}
				
				continue;
			} elseif ($k == "nb_errors") {
				$value = $v . " (" . $tab["affected_users"] . " users)";
				# code...
			}
				
			 /*else if ($k == "version_code") {
				$c = array('app_version_code', 'count(app_version_code) as nb');
				$sl = "issue_id = '?'";
				$slA = array($tab[issue_id]);
				if ($_GET[app]) {
					$sl .= " AND `appid` = ?";
					$slA[] = $_GET['app'];
				}
				if ($_GET[v]) {
					$sl .= " AND app_version_code = ?";
					$slA[] = $_GET[v];
				}
				$s = create_mysql_select($c, $sl, $slA, 'nb DESC', 'app_version_code');
				$r = mysqli_query($mysql, $s);
				$js = "$(document).ready(function(){\n"."\tvar data = [\t";
				$value = "";
				if ($r) {
				    while ($t = mysqli_fetch_assoc($r)) {
				      	if (strlen($value)) {
				    		$js .= ", ";
				    	}
				    	$js .= "['V: ".$t[app_version_code]."', ".$t[nb]."]";
				    	$value .= '<b title="'.$t[nb].' occurrences">'.$t[app_version_code]."</b> (".sprintf("%.1f%%", 100.0*$t[nb]/$tab[nb_errors]).")<br />";
        				}
                                }
*/
				// $js .= "\t ];\n"
				// 	."	var plot_".$tab[issue_id]." = jQuery.jqplot ('chartdiv_".$tab[issue_id]."', [data], \n"
				// 	."	      { \n"
				// 	."		seriesDefaults: {\n"
				// 	."		      renderer: jQuery.jqplot.PieRenderer, \n"
				// 	."		      rendererOptions: {\n"
				// 	."			showDataLabels: true\n"
				// 	."		      }\n"
				// 	."		}, \n"
				// 	."	      }\n"
				// 	."	);\n"
				// 	."      });\n";

				// $value .= '<div id="chartdiv_'.$tab[issue_id].'" style="height:200px;width:200px; "></div>';
				//$value .= '<script>'.$js.'</script>';
			/*}*/ else if ($k == "TODO") {

			} else {
				$value = $v;
			}

			$style = $k != "stack_trace" ? ' style="text-align: center;"' : "";

			// Display the row
			if (0 && strstr($value, "\n") !== FALSE) {
				$value = "<textarea>$value</textarea>";
			}

			echo "<td$style>$value</td>\n";
		}
		echo "</tr>\n";
	}
	echo "</tbody></table>\n";
}

?>
