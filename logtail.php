<?php
require("include/mysql.php");
require("include/functions.php");
session_start();
$mysql=new mysql;
$returnar=$mysql->connect(1);
//$thedb=$returnar[0];
//$thedb="JadeHomeLab";
$dbconn=$returnar[1];
$thedb=mysql_real_escape_string($_SESSION['thedatabase']);
$logstable=mysql_real_escape_string($_SESSION['thetables']);
$numevents=mysql_real_escape_string($_SESSION['numevents']);
$hostfilter=mysql_real_escape_string($_SESSION['hostfilter']);
$levelselect=mysql_real_escape_string($_SESSION['levelselect']);
$program=mysql_real_escape_string($_SESSION['programfilter']);
$notprogram=mysql_real_escape_string($_SESSION['notprogram']);
$showsql=mysql_real_escape_string($_SESSION['showsql']);
//echo "Logs table: $logstable<br />\n";
//echo "Events: $numevents<br />\n";
//Create the host filter
if($hostfilter){
	$hostar=NmapFindIP($hostfilter);
	$sizeof=sizeof($hostar);
	if($sizeof>0){
		$hostfilter="WHERE (";
		$count=0;
		foreach($hostar as $ip){
			if($count==0){
				$hostfilter=$hostfilter . "host='$ip'";
			} else if($count>0){
				$hostfilter=$hostfilter . " or host='$ip'";
			}
			$count+=1;
		}
		$hostfilter=$hostfilter . ")";
	}
}
//echo "HOST: $hostfilter<br />";
//Create the level filter
if($levelselect && $levelselect!='select'){
	if($hostfilter){
		$levelfilter="AND ";
	} else {
		$levelfilter="WHERE ";
	}
	if($levelselect=="alert"){
		$levelfilter=$levelfilter . "(level='alert' OR level='emerg')";
	} else if($levelselect=="crit"){
		$levelfilter=$levelfilter . "(level='crit' OR level='alert' OR level='emerg')";
	} else if($levelselect=="err"){
		$levelfilter=$levelfilter . "(level='err' OR level='crit' OR level='alert' OR level='emerg')";
	} else if($levelselect=="warning"){
		$levelfilter=$levelfilter . "(level='warning' OR level='err' OR level='crit' OR level='alert' OR level='emerg')";
	} else if($levelselect=="noticenowarnnoxlate"){
		$levelfilter=$levelfilter . "(level='notice' OR level='err' OR level='crit' OR level='alert' OR level='emerg') AND msg not like '%No xlate%'";
	} else if($levelselect=="noticenowarn"){
		$levelfilter=$levelfilter . "(level='notice' OR level='err' OR level='crit' OR level='alert' OR level='emerg')";
	} else if($levelselect=="notice"){
		$levelfilter=$levelfilter . "(level='notice' OR level='warning' OR level='err' OR level='crit' OR level='alert' OR level='emerg')";
	} else if($levelselect=="info"){
		$levelfilter=$levelfilter . "(level='info' OR level='notice' OR level='warning' OR level='err' OR level='crit' OR level='alert' OR level='emerg')";
	} else if($levelselect=="emergonly"){
		$levelfilter=$levelfilter . "level='emerg'";
	} else if($levelselect=="alertonly"){
		$levelfilter=$levelfilter . "level='alert'";
	} else if($levelselect=="critonly"){
		$levelfilter=$levelfilter . "level='crit'";
	} else if($levelselect=="erronly"){
		$levelfilter=$levelfilter . "level='err'";
	} else if($levelselect=="warningonly"){
		$levelfilter=$levelfilter . "level='warning'";
	} else if($levelselect=="noticeonly"){
		$levelfilter=$levelfilter . "level='notice'";
	} else if($levelselect=="infoonly"){
		$levelfilter=$levelfilter . "level='info'";
	} else if($levelselect=="debugonly"){
		$levelfilter=$levelfilter . "level='debug'";
	}
}
//Create the program filter
if($program){
	if($hostfilter || $levelfilter){
		if($notprogram=="on"){
			$programfilter="AND program!='$program'";
		} else {
			$programfilter="AND program='$program'";
		}
	} else {
		if($notprogram=="on"){
			$programfilter="WHERE program!='$program'";
		} else {
			$programfilter="WHERE program='$program'";
		}
	}
}
//Set a default table
if($logstable=="") $logstable="defaultlogs";
$getlogs="SELECT timestamp,facility,level,host,program,msg FROM $thedb.$logstable $hostfilter $levelfilter $programfilter ORDER BY seq DESC LIMIT $numevents;";
if($showsql){
	echo "<b>SQL:</b> $getlogs<br />\n";
}
$getlogs=mysql_query($getlogs);
echo "<div id=\"contenttable\">\n";
echo "<table border='1'>\n";
echo "<tr>";
echo "<th>Timestamp</th>";
echo "<th>Level</th>";
echo "<th>Host</th>";
echo "<th>Program</th>";
echo "<th>Message</th>";
echo "</tr>\n";
while($row=mysql_fetch_array($getlogs)){
	echo "<tr>";
	echo "<td class=\"timestamp\">" . $row['timestamp'] . "</td>";
	echo "<td style=\"min-width: 80px;\">" . $row['level'] . "</td>";
	echo "<td style=\"min-width: 110px;\">" . $row['host'] . "</td>";
	echo "<td>" . $row['program'] . "</td>";
	$msg=$row['msg'];
	if($logstable=="audiocodeslogs"){
		$msg=preg_replace('/\//','&#47;',$row['msg']);
		$msg=preg_replace('/</','&#60;',$msg);
		$msg=preg_replace('/>/','&#62;',$msg);
		$msg=preg_replace('/\n/','<br>',$msg);
	}
	echo "<td>$msg</td>";
	echo "</tr>\n";
}
echo "</table>\n";
echo "</div>\n";
?>