<?php

include "checklogin.php";
include "html.php";
include "mysql.php";
include "crashes.php";

if (!isset($_GET[issue_id])) {
	header("location: index.php");
	exit;
}

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
	$sql_overall_crashnr = create_mysql_select(null, "issue_id = '?'", array($_GET[issue_id]));
	$res_overall_crashnr = mysqli_query($mysql,$sql_overall_crashnr);
	return mysqli_num_rows($res_overall_crashnr);
}

function showNumberOfCrashes() {
        global $mysql;	
	$sql_overall_crashnr = create_mysql_select(null, "issue_id = '?'", array($_GET[issue_id]));
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
	$slA = array($_GET[issue_id]);

	$sql = create_mysql_select($c, $sl, $slA, 'nb DESC', 'app_version_code');
	$res = mysqli_query($mysql,$sql);

	$empty = "";
	$js = "\t";
	while ($t = mysqli_fetch_assoc($res)) {
		if (strlen($empty)) {
			$js .= ", ";
		}
		$percantage = 100.0*($t[nb]/getNumberOfCrashes());
		$js .= "['v".$t[app_version_code]."', " . $t[nb] . ", ". $percantage ."], ";
		// $value .= '<b title="'.$t[nb].' occurrences">'.$t[app_version_code]."</b> (".sprintf("%.1f%%", 100.0*$t[nb]/$tab[nb_errors]).")<br />";
	}
	$js = substr_replace($js ,"\t \n", -2);
	echo $js;
}


function prepareModelPieChart() {
	global $mysql;
	$c = array('phone_model', 'count(phone_model) as nb');
	$sl = "issue_id = '?'";
	$slA = array($_GET[issue_id]);

	$sql = create_mysql_select($c, $sl, $slA, 'nb DESC', 'phone_model');
	$res = mysqli_query($mysql,$sql);

	$empty = "";
	$js = "\t";
	while ($t = mysqli_fetch_assoc($res)) {
		$percantage = 100.0*($t[nb]/getNumberOfCrashes());
		$js .= "['".$t[phone_model]."', " . $t[nb] . ", ". $percantage ."], ";
		// $value .= '<b title="'.$t[nb].' occurrences">'.$t[app_version_code]."</b> (".sprintf("%.1f%%", 100.0*$t[nb]/$tab[nb_errors]).")<br />";
	}
	$js = substr_replace($js ,"\t \n", -2);
	echo $js;
}

function prepareAndroidVersionPieChart() {
	global $mysql;
	$c = array('android_version', 'count(android_version) as nb');
	$sl = "issue_id = '?'";
	$slA = array($_GET[issue_id]);

	$sql = create_mysql_select($c, $sl, $slA, 'nb DESC', 'android_version');
	$res = mysqli_query($mysql,$sql);

	$empty = "";
	$js = "\t";
	while ($t = mysqli_fetch_assoc($res)) {
		$percantage = 100.0*($t[nb]/getNumberOfCrashes());
		$js .= "['".$t[android_version]."', " . $t[nb] . ", ". $percantage ."], ";
		// $value .= '<b title="'.$t[nb].' occurrences">'.$t[app_version_code]."</b> (".sprintf("%.1f%%", 100.0*$t[nb]/$tab[nb_errors]).")<br />";
	}
	$js = substr_replace($js ,"\t \n", -2);
	echo $js;
}

function prepareOrientationPieChart() {
	global $mysql;
	$c = array('crash_configuration', 'count(crash_configuration) as nb');
	$sl = "issue_id = '?'";
	$slA = array($_GET[issue_id]);

	$sql = create_mysql_select($c, $sl, $slA, 'nb DESC', 'crash_configuration');
	$res = mysqli_query($mysql,$sql);

	$empty = "";
	$js = "\t";
	$orientation_portrait = 0;
	$orientation_landscape = 0;
	while ($t = mysqli_fetch_assoc($res)) {
		$percantage = 100.0*($t[nb]/getNumberOfCrashes());

		if (stristr($t[crash_configuration], "orientation=ORIENTATION_PORTRAIT")) { 
			$orientation_portrait++;

		} else if (stristr($t[crash_configuration], "orientation=ORIENTATION_LANDSCAPE")) {
			$orientation_landscape++;
		}
	}
	$js .= "['Portrait', " . $orientation_portrait . ", ". $percantage ."], ";
	$js .= "['Landscape', " . $orientation_landscape . ", ". $percantage ."], ";

	$js = substr_replace($js ,"\t \n", -2);
	echo $js;
}

$sql = "SELECT `appid` FROM `crashes` WHERE `issue_id` = '" . $_GET[issue_id] . "'";
$res = mysqli_query($mysql,$sql);
mysqli_data_seek($res,  0); $row = mysqli_fetch_array($res)[0];

// Show button
echo '<div id="reportbuttons" style="margin-right: 100px;">';
echo '<a class="button" href="javascript:setStatusAndGo(\''.$_GET['issue_id'].'\', \''.STATE_FIXED.'\', \'./reports.php?app=' . $row . '\');">mark as fixed</a> ';
echo '<a class="button" href="javascript:setStatusAndGo(\''.$_GET['issue_id'].'\', \''.STATE_INVALID.'\', \'./reports.php?app='. $row . '\');">mark as invalid</a> ';
echo '<a class="button" href="javascript:setStatusAndGo(\''.$_GET['issue_id'].'\', \''.STATE_NEW.'\', \'./reports.php?app=' . $row . '\');">mark as new</a> ';
echo "</div>\n";

// Display reports
$sql = create_mysql_select(null, "issue_id = '?'", array($_GET[issue_id]));
$sql .= " LIMIT 0, 100";
$res = mysqli_query($mysql, $sql);
$rows = mysqli_num_rows($res);

if (!$res) {
	log_to_file("Unable to query: $sql");
	echo "<p>Server error.</p>\n";
	return;
}

?>
<br />
<script>
    $(function() {
        $( "#tabs" ).tabs();
    });
</script>

<div class="leftreportcolumn">
	<?php
		$tab1 = mysqli_fetch_assoc($res);
	  	showStatus($tab1);
	  	showLastOccurred($tab1);
	  	showNumberOfCrashes();
	?>
</div>
<div class="rightreportcolumn">
	<div id="tabs">
		<ul>
	        <li><a href="#tabs-1">Stacktrace</a></li>
	        <li><a href="#tabs-2">Affected Versions</a></li>
	        <li><a href="#tabs-3">Statistics</a></li>
	    </ul>

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
		  	<table>
				<tr>
					<th>Versioncode (Version Name)</th>
					<th>Crashes</th>
				</tr>
	
			<?php
				$sql_versions = "SELECT DISTINCT `app_version_code`, `app_version_name` FROM `crashes` WHERE `issue_id` = '" . $_GET[issue_id] . "'";
				$res_versions = mysqli_query($mysql,$sql_versions);

			  	while ($tab3 = mysqli_fetch_assoc($res_versions)) {
			  		showVersions($tab3);
			  		$sql_crashes = "SELECT * FROM `crashes` WHERE `issue_id` = '".$_GET[issue_id]."' AND `app_version_code` = '".$tab3['app_version_code']."'";
			  		$res_crashes = mysqli_query($mysql,$sql_crashes);
			  		$crashes = mysqli_num_rows($res_crashes);
			  		echo "<td>$crashes</td>
					</tr>";
			  	}
		  	?>
		  	</table>
	    </div>


	    <div id="tabs-3">
			<p>
			<div id="charts">
				<div id="chartdiv_versions" style="width: 450px; height: 300px; "></div>
				<div id="chartdiv_androidversions" style="width: 450px; height: 300px; "></div>
				<div id="chartdiv_models" style="width: 450px; height: 300px; "></div>
				<div id="chartdiv_orientations" style="width: 450px; height: 300px; "></div>
				<div id="clear_chartdivs"></div>
			</div>
			</p>
	    </div>

	</div>
</div>

<script>
$(document).ready(function(){
	// Radialize the colors
    Highcharts.getOptions().colors = $.map(Highcharts.getOptions().colors, function(color) {
        return {
            radialGradient: { cx: 0.5, cy: 0.3, r: 0.7 },
            stops: [
                [0, color],
                [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
            ]
        };
    });

	$.jqplot.config.enablePlugins = false;

	$("#tabs").tabs();

	// Build the chart
    var chart1 = new Highcharts.Chart({
        chart: {
            renderTo: 'chartdiv_versions',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        credits: {
            enabled: false
        },
        title: {
            text: 'App Versions'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage}%</b>',
            percentageDecimals: 1
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    formatter: function() {
                        return '<b>'+ this.point.name +'</b>: '+ this.y;
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Versions share',
            data: [
            <?php
            	prepareVersionPieChart();
           	?>
            ]
        }]
    });

	var chart2 = new Highcharts.Chart({
        chart: {
            renderTo: 'chartdiv_androidversions',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        credits: {
            enabled: false
        },
        title: {
            text: 'Android Versions'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage}% ({point.y})</b>',
            percentageDecimals: 1
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    formatter: function() {
                        return '<b>'+ this.point.name +'</b>: '+ this.y;
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'App Versions share',
            data: [
            <?php
            	prepareAndroidVersionPieChart();
           	?>
            ]
        }]
    });

var chart3 = new Highcharts.Chart({
    chart: {
        renderTo: 'chartdiv_models',
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false
    },
    credits: {
            enabled: false
        },
    title: {
        text: 'Android Phone Models'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage}%</b>',
        percentageDecimals: 1
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                color: '#000000',
                connectorColor: '#000000',
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.y;
                }
            }
        }
    },
    series: [{
        type: 'pie',
        name: 'Phone Models share',
        data: [
        <?php
        	prepareModelPieChart();
       	?>
        ]
    }]
});


var chart4 = new Highcharts.Chart({
    chart: {
        renderTo: 'chartdiv_orientations',
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false
    },
    credits: {
            enabled: false
        },
    title: {
        text: 'Phone Orientations'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage}%</b>',
        percentageDecimals: 1
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                color: '#000000',
                connectorColor: '#000000',
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.y;
                }
            }
        }
    },
    series: [{
        type: 'pie',
        name: 'Phone Orientation share',
        data: [
        <?php
        	prepareOrientationPieChart();
       	?>
        ]
    }]
});



	$('#tabs').bind('tabsshow', function(event, ui) {

        if (ui.index === 2 && chart1._drawCount === 0) {
        	chart1.replot();
    	} 
    	if (ui.index === 2 && chart2._drawCount === 0) {
    		chart2.replot();
    	}
    	if (ui.index === 2 && chart3._drawCount === 0) {
    		chart3.replot();
    	}
    	if (ui.index === 2 && chart4._drawCount === 0) {
    		chart4.replot();
    	}

    });

});
</script>
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
 	showReport($tab);
 }
?>

</body>
</html>
