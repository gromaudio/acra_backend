<?php

function show_output() {
	$html = ob_get_clean();
	
@include "config.php";
$mysql = mysqli_connect($mysql_server, $mysql_user, $mysql_password);

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en" >
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Crash Reports</title>
		<!-- <script type="text/javascript" language="javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script> -->
		<!-- <script type="text/javascript" language="javascript" src="googlejqueryapi.js"></script> -->
		<!-- <script type="text/javascript" language="javascript" src="http://code.jquery.com/jquery-1.8.2.js"></script> -->
		
    	<!-- <script src="http://code.jquery.com/ui/1.9.0/jquery-ui.js"></script> -->
    	
		<script language="javascript" type="text/javascript" src="dist/jquery.min.js"></script>
		<script type="text/javascript" src="dist/examples/jquery-ui/js/jquery-ui.min.js"></script>

		<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="excanvas.js"></script><![endif]-->
		<script type="text/javascript" language="javascript" src="dist/jquery.jqplot.min.js"></script>
		<script type="text/javascript" src="dist/plugins/jqplot.pieRenderer.min.js"></script>
		<script type="text/javascript" src="dist/plugins/jqplot.dateAxisRenderer.min.js"></script>
		<script type="text/javascript" src="dist/plugins/jqplot.highlighter.min.js"></script>
		<script type="text/javascript" src="dist/plugins/jqplot.cursor.min.js"></script>

		<!-- Highchart -->
		<script src="/js/highcharts.js" type="text/javascript"></script>
		





		
		<script type="text/javascript" language="javascript">

		function setStatusAndGo(iid, stat, url) {
		$.get("ajax.php", { action: "update_status", status: stat, issue_id: iid }, function() {
			document.location=url;
		});
	}

		</script>
		
		<link rel="stylesheet" type="text/css" href="dist/examples/jquery-ui/css/smoothness/jquery-ui.min.css" />
		<link rel="stylesheet" type="text/css" href="dist/examples/examples.min.css" />

		<!-- <script class="include" type="text/javascript" src="dist/jquery.jqplot.min.js"></script> -->
		<link class="include" type="text/css" href="dist/examples/jquery-ui/css/smoothness/jquery-ui.min.css" rel="Stylesheet" /> 
  		<script class="include" type="text/javascript" src="dist/examples/jquery-ui/js/jquery-ui.min.js"></script>

		<!-- <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.0/themes/base/jquery-ui.css" /> -->

		<!-- JQPlot CSS -->
		<link rel="stylesheet" type="text/css" href="dist/jquery.jqplot.css" />
		<link rel="stylesheet" type="text/css" href="style.css"></link>

		<style type="text/css">

		    /*.ui-tabs, .ui-accordion {
		      width: 690px;
		      margin: 2em auto;
		    }*/

		    .ui-tabs-nav, .ui-accordion-header {
		      font-size: 12px;
		    }
		    
		    .ui-tabs-panel, .ui-accordion-content {
		      font-size: 14px;
		    }
		    
		    .jqplot-target {
		      font-size: 18px;
		    }
		    
		    ol.description {
		      list-style-position: inside;
		      font-size:15px;
		      margin:1.5em auto;
		      padding:0 15px;
		      width:600px;
		    }
  		</style>

	</head>

	<body>
			<script type="text/javascript">

				console.log($('#logo'));


			</script>

	<div id="wrapper">
		<a id="logo"href=""></a>
		<div id="droid"></div>
<!-- User Menu Start -->
			<ul id="nav">
			<a href="index.php"> Dashboard </a> 
			<a class="appx"> My Apps </a>

		 
			<a href="settings.php"> Settings </a>
			
			<a class="nav_user">	
				logged in as:
				<?php
					if(!isset($_SESSION)) { 
					    session_start(); 
					} 

					if (isset($_SESSION["username"])) {
					echo $_SESSION["username"];
				}
		?>
				</a>
			

			<a onclick="javascript:window.location.href='logout.php'"> Logout </a>

			</ul>

	

<?php

$sql = "SELECT `id` FROM `user` WHERE `username` = '" . strtolower($_SESSION["username"]) . "'";
$res = mysqli_query($mysql, $sql, MYSQLI_USE_RESULT);
$userid;
if($res) {
    mysqli_data_seek($res,  0); 
    $userid = mysqli_fetch_array($res)[0];
} else
    $userid = false;

$rows = 0;
$sql = "SELECT `appname`, `appid` FROM `app` WHERE `userid` = $userid";
$res = mysqli_query($mysql, $sql);
if($res)
    $rows = mysqli_num_rows($res);
?>
<ul class="dropdown" style="width:302px;height:<?echo ($rows*34);?>px;">

<?	

// if ($rows == 0) {
// } else {
	while ($tab = mysqli_fetch_assoc($res)) {
		echo "<a href=\"reports.php?app=" . $tab[appid] . "\">" . $tab[appname] . "</a>";
	}
//}

?>
	</ul>	

	<div id="content">

<!-- User Menu End -->


	<?php
//		session_start();
//		echo "<div id=\"headerUserMenu\">";
//		if (isset($_SESSION["username"])) {
//			echo $_SESSION["username"];
//		}
//		echo " (<a href=\"settings.php\">Settings</a>)";
//		echo "</div>";
	?>

<!--
	<div id="logoutButtonDiv">
	<input type="button" onclick="javascript:window.location.href='logout.php'" value="logout" />
	</div>
-->


	<?php echo $html; ?>
	</div>
</div>
	</body>
</html>
<?php
}

ob_start();
register_shutdown_function("show_output");

?>