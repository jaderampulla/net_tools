<?php
$title="Default Syslog";
include("include/functions.php");
require("include/mysql.php");
$time_start=microtime_float();
$mysql=new mysql;
$returnar=$mysql->connect(1);
$thedb=$returnar[0];
$dbconn=$returnar[1];
$logstable=$mysql->getLogsTable();
require("include/header.php");
echo "<h3>$title</h3>\n";
echo "<font style=\"color: #8B0000;\">Note: This is the default logging location</font><br />\n";
$getlogs="SELECT timestamp,facility,level,host,msg FROM $logstable ORDER BY seq DESC LIMIT 100;";
$getlognum="SELECT count(seq) as thecount FROM $logstable;";
$getlogs=mysql_query($getlogs);
$getlognum=number_format(mysql_fetch_object(mysql_query($getlognum))->thecount,0,".",",");
$time=end_time($time_start);
echo "$getlognum total syslog messages. 100 messages displayed. SQL query completed in {$time}seconds.<br /><br />\n";
echo "<div id=\"contenttable\">\n";
echo "<table border='1'>\n";
echo "<tr><th>Timestamp</th><th>Facility</th><th>Level</th><th>Host</th><th>Message</th></tr>\n";
while($row=mysql_fetch_array($getlogs)){
	echo "<tr>";
	echo "<td class=\"timestamp\">" . $row['timestamp'] . "</td>";
	echo "<td>" . $row['facility'] . "</td>";
	echo "<td>" . $row['level'] . "</td>";
	echo "<td>" . $row['host'] . "</td>";
	echo "<td>" . $row['msg'] . "</td>";
	echo "</tr>\n";
}
echo "</table>\n";
echo "</div>\n";
$mysql->disconnect($dbconn);
require("include/end.php");
?>