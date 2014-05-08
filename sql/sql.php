<?php
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	session_start();
	$title="SQL Syslog Query";
	require("../include/header.php");
	require ("../include/functions.php");
	require("../include/mysql.php");
	$time_start=microtime_float();
	set_magic_quotes_runtime(0);
	$sql=${"sql"};
	$qt=chr(34);
	$sqt=chr(92).chr(34);
	
	$thesql=$_REQUEST[thesql];
	$thesql=str_replace("\'","'",$thesql);
	$thesql=str_replace($sqt,$qt,$thesql);
	
	echo "\n<br />";
	echo "<form style='display: inline;' action=\"sql.php\" method=post>";
	echo "Connect to another server:\n";
	echo "<input type='text' style='text-align: center; width: 12em;' name='otherdb' id='otherdb' value='";
	if($_POST['otherdb'] && !($_POST['otherdbconnkill'])){
		echo $_POST['otherdb'] . "' />\n";
		$DBHost=$_POST['otherdb'];
	} else {
		//Default Host
		require("../include/options/m_s.php");
		$DBHost=$mysqlserver;
		echo "$DBHost' />\n";
	}
	echo "&nbsp;Username:&nbsp;<input type='text' style='text-align: center; width: 7em;' name='otherdbname' id='otherdbname' value='";
	if($_POST['otherdbname'] && !($_POST['otherdbconnkill'])){
		echo $_POST['otherdbname'] . "' />\n";
		$UserName=$_POST['otherdbname'];
	} else {
		//Default Username
		require("../include/options/m_un.php");
		$UserName=$mysqlusername;
		echo "$UserName' />\n";
	}
	echo "&nbsp;Password:&nbsp;<input type='password' style='text-align: center; width: 10em;' name='otherdbpass' id='otherdbpass' value='";
	if($_POST['otherdbpass'] && !($_POST['otherdbconnkill'])){
		echo $_POST['otherdbpass'] . "' />\n";
		$Password=$_POST['otherdbpass'];
	} else {
		//Default Password for default host
		require("../include/options/m_p.php");
		$Password=$mysqlpassword;
		echo "$Password' />\n";
	}
	echo " <input type='submit' name='otherdbconn' value='Connect'>\n";
	echo " <input type='submit' name='otherdbconnkill' value='Disconnect'>\n";
	echo "</form><br />\n";
	function showerror(){
		echo mysql_error();
	}
	//Connect to database
	$db=mysql_connect($DBHost,$UserName,$Password) or die("<br /><b>Could not connect because of bad username, password, and or service not available</b>");
	//Default selected database
	$dbase=$_REQUEST[Database]; if(!$dbase) $dbase="syslog";
	echo "<form style='display: inline;' action=\"sql.php\" name=mymain method=post>";
	echo "Database: ";
	echo dropdown($db,"show databases","Database","Database",$dbase);
	echo " <input type=submit value=Execute>";
	echo "&nbsp;&nbsp;<a target='_BLANK' href='sql.php'>New SQL Tab</a>\n";
	
	echo "&nbsp;&nbsp;Example SQL:&nbsp;<select name='thesqlexample'>";
	if($dbase=="syslog"){
		echo "<option value=''></option>\n";
		include("../include/options/sql_queries.php");
		foreach($queriesarray as $name=>$query){
			echo "<option value=\"$query\">$name</option>\n";
		}
		echo "</select>\n";
	}
	if($_POST['thesqlexample']) $thesql=$_POST['thesqlexample'];
	
	echo "<br />";
	
	//Keep selected database during a query
	echo "<input type='hidden' name='otherdb' value='$DBHost' />";
	echo "<input type='hidden' name='otherdbname' value='$UserName' />";
	echo "<input type='hidden' name='otherdbpass' value='$Password' />";
	echo "SQL: <textarea id=\"thesql\" name=\"thesql\">";// rows=";
	//echo substr_count($thesql,"\n")+2;
	//echo " style=\"width:95%\">\n";
	echo $thesql;
	echo "</textarea>";
	echo "</form>\n";
	echo "<br />";
	if($thesql=="") $thesql="show tables";
	mysql_select_db($dbase,$db);
	//Run the query and return arrays with the header and data
	list($headerar,$dataar)=sql2table($thesql,$db,"");
	$time=end_time($time_start);
	echo "\n<br />SQL query completed in {$time}seconds.";
	//echo "<pre>";
	//print_r($headerar);
	//print_r($dataar);
	//Export XLSX Button
	$_SESSION['headerar']=$headerar;
	$_SESSION['dataar']=$dataar;
	if(sizeof($dataar)>0){
		echo "&nbsp;<form action='../excel/syslogtoxls.php' method='post' style='display: inline;'>\n";
		echo "<input type='submit' value='Export to XLSX' />\n";
		echo "</form>\n";
	}
	echo "<br />\n";
	require("../include/end.php");
?>
