<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$title="Server Stats";
include("include/functions.php");
require("include/mysql.php");
$time_start=microtime_float();
$mysql=new mysql;
$returnar=$mysql->connect(1);
$thedb=$returnar[0];
$dbconn=$returnar[1];
$logstable=$mysql->getLogsTable();
$sum=0;
$tablesize=0;
$indexsize=0;
$totalhourly=0;
$totalminute=0;
$totalsecond=0;
require("include/header.php");
echo "<h3>$title</h3>\n";
//Default Logs
$getlognum="SELECT count(seq) AS thecount FROM $logstable;";
$getlognum=mysql_fetch_object(mysql_query($getlognum))->thecount;
$gethourly="SELECT count(seq) as thecount FROM $logstable WHERE timestamp>=DATE_SUB(NOW(),INTERVAL 1 HOUR);";
$gethourly=mysql_fetch_object(mysql_query($gethourly))->thecount;
$getminute="SELECT count(seq) as thecount FROM $logstable WHERE timestamp>=DATE_SUB(NOW(),INTERVAL 1 MINUTE);";
$getminute=mysql_fetch_object(mysql_query($getminute))->thecount;
$getsecond="SELECT count(seq) as thecount FROM $logstable WHERE timestamp>=DATE_SUB(NOW(),INTERVAL 1 SECOND);";
$getsecond=mysql_fetch_object(mysql_query($getsecond))->thecount;
$gettablestats="SELECT data_length,index_length FROM information_schema.TABLES WHERE table_schema='$thedb' and table_name='$logstable';";
$gettablestats=mysql_fetch_object(mysql_query($gettablestats));
$sum=$sum+$getlognum;
$tablesize=$tablesize+$gettablestats->data_length;
$indexsize=$indexsize+$gettablestats->index_length;
$totalhourly=$totalhourly+$gethourly;
$totalminute=$totalminute+$getminute;
$totalsecond=$totalsecond+$getsecond;
$time=end_time($time_start);
echo "SQL stat queries completed in {$time}seconds.<br />\n";
echo "<div id=\"contenttablestats\" style=\"min-width: 800px;\">\n";
//Outer table formatting
echo "<table>\n";
echo "<tr><td style=\"padding-right: 10px;\">\n";
//Default Logs Table
echo "<h4>Default Syslog Table</h4>\n";
echo "<table border=1>\n";
echo "<tr><th>Stat Type</th><th>Value</th></tr>\n";
echo "<tr>";
echo "<td>Total Messages</td>";
echo "<td>" . number_format($getlognum,0,".",",") . "</td>";
echo "</tr>\n";
echo "<tr>";
echo "<td>Table Size</td>";
echo "<td>" . formatBytes($gettablestats->data_length) . "</td>";
echo "</tr>\n";
echo "<tr>";
echo "<td>Index Size</td>";
echo "<td>" . formatBytes($gettablestats->index_length) . "</td>";
echo "</tr>\n";
echo "<tr>";
echo "<td>Messages in Last Hour</td>";
echo "<td>" . number_format($gethourly,0,".",",") . "</td>";
echo "</tr>\n";
echo "<tr>";
echo "<td>Messages in Last Minute</td>";
echo "<td>" . number_format($getminute,0,".",",") . "</td>";
echo "</tr>\n";
echo "<tr>";
echo "<td>Messages in Last Second</td>";
echo "<td>" . number_format($getsecond,0,".",",") . "</td>";
echo "</tr>\n";
echo "</table><br />\n";
//Outer Table formatting
echo "</td><td style=\"vertical-align: top;\">\n";
//MySQL Server Info/Stats
echo "<h4>MySQL Server Info/Stats</h4>\n";
$drivesizear=split("\n",`df -B1 -t ext4 | grep -v sys | awk '{print $3, $4}'`);
echo "<table border=1>\n";
foreach($drivesizear as $ar){
	if($ar){
		list($used,$free)=explode(' ',$ar);
		echo "<tr><td>Used Drive Space by MySQL Tables</td><td>" . formatBytes($tablesize+$indexsize) . "</td></tr>\n";
		echo "<tr><td>Free Drive Space</td><td>" . formatBytes($free) . "</td></tr>\n";
	}
}
$estimatedtotal=($sum*($tablesize+$indexsize+$free))/($tablesize+$indexsize);
$estimatedremain=($sum*$free)/($tablesize+$indexsize);
echo "<tr><td>Estimated Total Message Capacity</td><td>" . number_format($estimatedtotal,0,".",",") . "</td></tr>\n";
echo "<tr><td>Estimated Messages Remaining</td><td>" . number_format($estimatedremain,0,".",",") . "</td></tr>\n";
//Syslog-ng Service
$mysqlrootlogincheckar=split("\n",`ps aux | grep syslog-ng | grep run | awk '{print $11}'`);
$count=0;
foreach($mysqlrootlogincheckar as $ar){
	if($ar!='' && $ar!='sh'){
		echo "<tr><td>syslog-ng Service Check</td><td style=\"text-align: center;\">";
		if(preg_match('/syslog-ng/',$ar)){
			echo "<img src=\"green.png\" />";
		} else {
			echo "<img src=\"red.png\" />";
		}
		echo "</td></tr>\n";
		$count+=1;
	}
}
if($count==0) echo "<tr><td>syslog-ng Service Check</td><td style=\"text-align: center;\"><img src=\"red.png\" /></td></tr>\n";
echo "</table>\n";
//Outer Table formatting
echo "</td></tr></table>\n";
echo "</div>\n";
$mysql->disconnect($dbconn);
require("include/end.php");
?>