<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$title="Syslog DB Stats";
$widthmodset=true;
$widthmod=" style=\"min-width: 1280px;\" ";
require("include/header.php");
include("include/functions.php");
require("include/mysql.php");
include("include/options/custom_stats.php");
$time_start=microtime_float();
$debugtime=false;
$showsql=false;
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display: inline;">
<br /><input name="debugtime" type="checkbox" <?php if($_POST['debugtime']) echo "checked"; ?> />&nbsp;<font style="color: purple;">Debug SQL time</font>
<input name="showsql" type="checkbox" <?php if($_POST['showsql']) echo "checked"; ?> />&nbsp;<font style="color: red;">Show SQL</font>
<br />Database: <select name="thedatabase">
<?php
$mysql=new mysql;
$returnar=$mysql->connect(1);
$thedb=$returnar[0];
$dbconn=$returnar[1];
$getdb="show databases;";
$getdb=mysql_query($getdb);
while($row=mysql_fetch_array($getdb)){
	if($row[0]!="information_schema" && $row[0]!="cacti" && $row[0]!="mysql" && $row[0]!="performance_schema"){
		if($row[0]==$_REQUEST['thedatabase']){
			echo "\n<option value=\"{$row[0]}\" selected>{$row[0]}</option>";
			$thedb=$row[0];
		//If page loading for first time
		} else if($row[0]==$thedb){
			echo "\n<option value=\"{$row[0]}\" selected>{$row[0]}</option>";
		//Catch anything else
		} else {
			echo "\n<option value=\"{$row[0]}\">{$row[0]}</option>";
		}
	}
}
?>
</select>
<input type="submit" value="See Stats" name="StatsDB" />
</form><br />
<?php
if($_REQUEST['debugtime']=="on") $debugtime=true;
if($_REQUEST['showsql']=="on") $showsql=true;
if($_REQUEST['StatsDB']){
	$thedb=mysql_real_escape_string($_REQUEST['thedatabase']);
	$gettables="show tables in $thedb;";
	$gettables=mysql_query($gettables);
	//Number of tables used when printing results
	$numtables=mysql_num_rows($gettables);
	//Outer table
	if($numtables>0){
		echo "<table>\n";
		echo "<tr><td style=\"padding-right: 10px;\">\n";
	}
	//Table counter for number of tables in a row
	$tblcnt=0;
	$totaltbl=0;
	//Totals table variables
	$logsum=$logsum+$getlognum;
	$tablesizesum=0;
	$indexsizesum=0;
	$hourlysum=0;
	$minutesum=0;
	$secondsum=0;
	//Loop through each table in the selected database
	while($row=mysql_fetch_array($gettables)){
		//echo "ROW: {$row[0]}<br />\n";
		$logstable=$row[0];
		$gethourly="SELECT count(seq) as thecount FROM $thedb.$logstable WHERE timestamp>=DATE_SUB(NOW(),INTERVAL 1 HOUR);";
		if($showsql==true) $hourlysql=$gethourly;
		$gethourly=mysql_fetch_object(mysql_query($gethourly))->thecount;
		$getminute="SELECT count(seq) as thecount FROM $thedb.$logstable WHERE timestamp>=DATE_SUB(NOW(),INTERVAL 1 MINUTE);";
		if($showsql==true) $minutesql=$getminute;
		$getminute=mysql_fetch_object(mysql_query($getminute))->thecount;
		$getsecond="SELECT count(seq) as thecount FROM $thedb.$logstable WHERE timestamp>=DATE_SUB(NOW(),INTERVAL 1 SECOND);";
		if($showsql==true) $secondsql=$getsecond;
		$getsecond=mysql_fetch_object(mysql_query($getsecond))->thecount;
		$gettablestats="SELECT data_length,index_length FROM information_schema.TABLES WHERE table_schema='$thedb' and table_name='$logstable';";
		if($showsql==true) $sizesql=$gettablestats;
		$gettablestats=mysql_fetch_object(mysql_query($gettablestats));
		
		//If the size of the table is 700MB or larger, don't count all the entries just estimate it
		//megabits to bits calculator http://www.matisse.net/bitcalc/
		$estimatewhen=734003200;
		if($gettablestats->data_length>=$estimatewhen){
			$getlognum="SELECT table_rows FROM INFORMATION_SCHEMA.TABLES WHERE table_schema='$thedb' and table_name='$logstable';";
			$totalmessagessql=$getlognum;
			$getlognum=mysql_fetch_object(mysql_query($getlognum))->table_rows;
		} else {
			$getlognum="SELECT count(seq) AS thecount FROM $thedb.$logstable;";
			$totalmessagessql=$getlognum;
			$getlognum=mysql_fetch_object(mysql_query($getlognum))->thecount;
		}
		//Print out results for each table if there's at least 1 table
		if($numtables>0){
			$tblcnt+=1;
			$totaltbl+=1;
			echo "<h4>$logstable Table </h4>\n";
			echo "<table border=1>\n";
			echo "<tr><th>Stat Type</th><th>Value</th></tr>\n";
			echo "<tr>";
			if($gettablestats->data_length>=$estimatewhen){
				echo "<td><i>Estimated</i> Total Messages</td>";
			} else {
				echo "<td>Total Messages</td>";
			}
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
			echo "</table>\n";
			if($debugtime==true){
				$time=end_time($time_start);
				echo "<font style=\"color: purple;\">".$time."seconds</font><br />";
			}
			if($showsql==true){
				echo "<font style=\"color: red;\">";
				echo "<b>$logstable Total Messages SQL</b>: $totalmessagessql<br />\n";
				echo "<b>$logstable Size SQL</b>: $sizesql<br />\n";
				echo "<b>$logstable Last Hour SQL</b>: $hourlysql<br />\n";
				echo "<b>$logstable Last Minute SQL</b>: $minutesql<br />\n";
				echo "<b>$logstable Last Second SQL</b>: $secondsql<br />\n";
				echo "</font>";
			}
			$logsum=$logsum+$getlognum;
			$tablesizesum=$tablesizesum+$gettablestats->data_length;
			$indexsizesum=$indexsizesum+$gettablestats->index_length;
			$hourlysum=$hourlysum+$gethourly;
			$minutesum=$minutesum+$getminute;
			$secondsum=$secondsum+$getsecond;
		}
		//Start new row (Outer table) and allow 4 tables per row
		if($tblcnt==4 && $totaltbl<$numtables){
			$tblcnt=0;
			echo "</td></tr><tr><td style=\"padding-right: 10px;\">";
		//Start new td in row
		} else if($totaltbl<$numtables){
			echo "</td><td style=\"padding-right: 10px;\">";
		}
	}
	if($numtables>0){
		echo "</td></tr>\n";
		echo "</table>\n";
	}
	
	//Outer formatting
	echo "<table><tr><td style=\"padding-right: 10px;\">\n";
	//Totals table
	echo "<h4 style=\"color: #8B0000;\">Totals Table</h4>\n";
	echo "<table border=1>\n";
	echo "<tr><th>Stat Type</th><th>Value</th></tr>\n";
	echo "<tr>";
	echo "<td>Total Messages</td>";
	echo "<td>" . number_format($logsum,0,".",",") . "</td>";
	echo "</tr>\n";
	echo "<tr>";
	echo "<td>Table Size</td>";
	echo "<td>" . formatBytes($tablesizesum) . "</td>";
	echo "</tr>\n";
	echo "<tr>";
	echo "<td>Index Size</td>";
	echo "<td>" . formatBytes($indexsizesum) . "</td>";
	echo "</tr>\n";
	echo "<tr>";
	echo "<td>Messages in Last Hour</td>";
	echo "<td>" . number_format($hourlysum,0,".",",") . "</td>";
	echo "</tr>\n";
	echo "<tr>";
	echo "<td>Messages in Last Minute</td>";
	echo "<td>" . number_format($minutesum,0,".",",") . "</td>";
	echo "</tr>\n";
	echo "<tr>";
	echo "<td>Messages in Last Second</td>";
	echo "<td>" . number_format($secondsum,0,".",",") . "</td>";
	echo "</tr>\n";
	echo "</table>\n";
	if($debugtime==true){
		$time=end_time($time_start);
		echo "<font style=\"color: purple;\">".$time."seconds</font>";
	}
	//Outer formatting
	echo "</td><td style=\"vertical-align: top;\">\n";
	//MySQL Server Info/Stats
	echo "<h4 style=\"color: green;\">MySQL Server Info/Stats</h4>\n";
	$drivesizear=split("\n",`df -B1 -t ext4 / | grep -v sys | awk '{print $3, $4}'`);
	echo "<table border=1>\n";
	foreach($drivesizear as $ar){
		if($ar){
			list($used,$free)=explode(' ',$ar);
			echo "<tr><td>Used Drive Space by MySQL Tables</td><td>" . formatBytes($tablesizesum+$indexsizesum) . "</td></tr>\n";
			echo "<tr><td>Free Drive Space</td><td>" . formatBytes($free) . "</td></tr>\n";
		}
	}
	$estimatedtotal=($logsum*($tablesizesum+$indexsizesum+$free))/($tablesizesum+$indexsizesum);
	$estimatedremain=($logsum*$free)/($tablesizesum+$indexsizesum);
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
	//Outer formatting
	echo "</td></tr></table>\n";
	
	//Custom watchlist
	if(sizeof($watchlistar>0)){
		//$sqlstring="SELECT";
		//Loop through all watchlists
		foreach($watchlistar as $ar){
			echo "<h4>{$ar['Description']}</h4>\n";
			echo "<table border=1>\n";
			echo "<tr><th>Name</th><th>IP</th><th>Messages Last Hour</th><th>Status</th></tr>";
			$sqlstring="SELECT";
			//Loop through all values for a watchlist
			foreach($ar as $elem=>$val){
				//echo "ELEM: $elem, VAL: $val<br />\n";
				//Loop through all the hosts in a watchlist
				if($elem=="hosts"){
					foreach($val as $host){
						//Build SQL string
						$sqlstring=$sqlstring."(SELECT count(seq) FROM {$ar['database']}.{$ar['table']} where host='$host' and timestamp>=DATE_SUB(NOW(),INTERVAL {$ar['amounttime']})) as '$host',";
					}
				}
			}
			$sqlstring=substr($sqlstring,0,-1).";"; 
			//echo "SQL String: $sqlstring<br />\n";
			//Query could take a long while to run...be careful
			$watchlistval=mysql_fetch_object(mysql_query($sqlstring));
			//Loop through results and print status
			foreach($watchlistval as $watchhost=>$watchlistcnt){
				echo "<tr>";
				echo "<td>" . array_search($watchhost,$ar['hosts']) . "</td>";
				echo "<td>$watchhost</td>";
				echo "<td>$watchlistcnt</td>";
				if($watchlistcnt>0){
					echo "<td style=\"text-align: center;\"><img src=\"green.png\" /></td>";
				} else {
					echo "<td style=\"text-align: center;\"><img src=\"red.png\" /></td>";
				}
				echo "</tr>";
			}
			echo "</table>\n";
			if($debugtime==true){
				$time=end_time($time_start);
				echo "<font style=\"color: purple;\">".$time."seconds</font><br />";
			}
			if($showsql==true) echo "<font style=\"color: red;\">$sqlstring</font><br />\n";
		}
		echo "<br />\n";
	}
	$time=end_time($time_start);
	echo "<br />SQL stats completed in {$time}seconds.<br />\n";
}
require("include/end.php");
?>