<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta name="author" content="Jade Rampulla" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="../include/style.css" media="screen" />
	<link rel="shortcut icon" href="/favicon.png" type="image/png">
	<link rel="shortcut icon" type="image/png" href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/favicon.png" />
	<?php
	//Live log ajax
	if($title=="Live Log"){
		echo "<script type=\"text/javascript\" src=\"ajax.js\"></script>\n";
		echo "<script type=\"text/javascript\" src=\"../logtail.js\"></script>\n";
	}
	//Codemirror
	if($title=="SQL Syslog Query"){
		
		echo "<link rel=\"stylesheet\" href=\"../include/codemirror/codemirror.css\" />\n";
		echo "<script src=\"../include/codemirror/codemirror.js\"></script>\n";
		echo "<script src=\"../include/codemirror/sql.js\"></script>\n";
		echo "<style>\n";
		echo "
		.CodeMirror {
			border: 1px solid black;
			height: auto;
			width: 80%;
		}
		.CodeMirror-scroll {
			overflow-y: hidden;
			overflow-x: auto;
		}
		";
		echo "</style>\n";
		echo "<link rel=\"stylesheet\" href=\"../include/codemirror/docs.css\">\n";
		echo "<script>\n";
		echo "var init = function() {\n";
		echo "var mime = 'text/x-mariadb';";

		echo "
		if (window.location.href.indexOf('mime=') > -1) {
			mime = window.location.href.substr(window.location.href.indexOf('mime=') + 5);
		}
		\n";
		echo"
		window.editor = CodeMirror.fromTextArea(document.getElementById('thesql'), {
		mode: mime,
		indentWithTabs: true,
		smartIndent: true,
		lineNumbers: false,
		lineWrapping: true,
		matchBrackets : true,
		autofocus: true
		});
		\n";
		echo "};\n";
		echo "</script>\n";
		
	}
	if($title=="Router/Switch Info" || $title=="NMAP Scan" || $title=="Unused Switch Ports"){
		echo "<script type=\"text/javascript\" src=\"/include/js/jquery-1.5.1.min.js\"></script>\n";
		echo "\t<script type=\"text/javascript\" src=\"/include/js/jquery.thfloat-0.7.2.js\"></script>\n";
	}
	?>
	<title><?php echo "NetTools - $title"; ?></title>
</head>
<?php
if(!$widthmodset){
	$widthmod="";
}
if($title=="SQL Syslog Query"){
	echo "<body {$widthmod}onload=\"init();\">\n";
} else {
	echo "<body{$widthmod}>\n";
}
require("options/paths.php");
/*
$basepath="/var/www";
$apachebasepath="/etc/apache2";
*/
?>
<nav>
	<ul>
		<li><a href="https://<?php echo $_SERVER['SERVER_NAME']; ?>">Home</a></li>
		<?php
		include("options/extra_links_before.php");
		//Build Syslog Menu
		if(file_exists("$basepath/default.php") || file_exists("$basepath/livelog.php") || file_exists("$basepath/sql/sql.php")){
			echo "<li><a href=\"#\">Syslog</a><ul>\n";
		}
		if(file_exists("$basepath/default.php")){
			echo "<li><a href=\"https://" . $_SERVER['SERVER_NAME'] . "/default.php\">Default</a></li>\n";
		}
		if(file_exists("$basepath/livelog.php")){
			echo "<li><a href=\"https://" . $_SERVER['SERVER_NAME'] . "/livelog.php\">Live log</a></li>\n";
		}
		if(file_exists("$basepath/sql/sql.php")){
			echo "<li><a href=\"https://" . $_SERVER['SERVER_NAME'] . "/sql/sql.php\">SQL</a></li>\n";
		}
		include("options/extra_links_syslog.php");
		//Close Syslog Menu
		if(file_exists("$basepath/default.php") || file_exists("$basepath/livelog.php") || file_exists("$basepath/sql/sql.php")){
			echo "</ul></li>\n";
		}
		$netflowservicecommand="service netflowanalyzer status 2>/dev/null";
		$netflowstatus=strstr(shell_exec($netflowservicecommand),'is running');
		//Build Tools Menu
		if(file_exists("$basepath/sql/netinfo.php") || file_exists("$basepath/sql/nmapscan.php") || file_exists("$basepath/sql/unusedports.php") || file_exists("$apachebasepath/conf.d/cacti.conf") || file_exists("$apachebasepath/conf-enabled/cacti.conf") || $netflowstatus==true){
			echo "<li><a href=\"#\">Tools</a><ul>\n";
		}
		if(file_exists("$basepath/sql/netinfo.php")){
			echo "<li><a href=\"https://" . $_SERVER['SERVER_NAME'] . "/sql/netinfo.php\">Router/Switch Info</a></li>\n";
		}
		if(file_exists("$basepath/sql/nmapscan.php")){
			echo "<li><a href=\"https://" . $_SERVER['SERVER_NAME'] . "/sql/nmapscan.php\">NMAP Scan</a></li>\n";
		}
		if(file_exists("$basepath/sql/unusedports.php")){
			echo "<li><a href=\"https://" . $_SERVER['SERVER_NAME'] . "/sql/unusedports.php\">Unused Network Ports</a></li>\n";
		}
		if(file_exists("$apachebasepath/conf.d/cacti.conf") || file_exists("$apachebasepath/conf-enabled/cacti.conf")){
			echo "<li><a href=\"https://" . $_SERVER['SERVER_NAME'] . "/cacti\">Cacti</a></li>\n";
		}
		if($netflowstatus==true){
			echo "<li><a href=\"http://" . $_SERVER['SERVER_NAME'] . ":8080\">NetFlow</a></li>\n";
		}
		include("options/extra_links_tools.php");
		//Close Tools Menu
		if(file_exists("$basepath/sql/netinfo.php") || file_exists("$basepath/sql/nmapscan.php") || file_exists("$basepath/sql/unusedports.php") || file_exists("$apachebasepath/conf.d/cacti.conf") || $netflowstatus==true){
			echo "</ul></li>\n";
		}
		//Syslog Stats Menu
		if(file_exists("$basepath/syslogstats.php")){
			echo "<li><a href=\"https://" . $_SERVER['SERVER_NAME'] . "/syslogstats.php\">Server Stats</a></li>\n";
		}
		include("options/extra_links_after.php");
		?>
	</ul>
</nav>