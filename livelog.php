<?php
$title="Live Log";
require("include/header.php");
require("include/mysql.php");
require("include/functions.php");
session_start();
//Session variables to use in logtail.php
if($_REQUEST['thedatabase']){
	$_SESSION['thedatabase']=$_REQUEST['thedatabase'];
} else {
	$_SESSION['thedatabase']="syslog";
}
if($_REQUEST['thetables']){
	$_SESSION['thetables']=$_REQUEST['thetables'];
} else {
	$_SESSION['thetables']="defaultlogs";
}
if($_REQUEST['numevents']){
	$_SESSION['numevents']=$_REQUEST['numevents'];
} else {
	$_SESSION['numevents']=15;
}
if($_REQUEST['hostfilter']){
	$_SESSION['hostfilter']=$_REQUEST['hostfilter'];
} else {
	$_SESSION['hostfilter']="";
}
if($_REQUEST['levelselect']){
	$_SESSION['levelselect']=$_REQUEST['levelselect'];
} else {
	$_SESSION['levelselect']="";
}
if($_REQUEST['programfilter']){
	$_SESSION['programfilter']=$_REQUEST['programfilter'];
} else {
	$_SESSION['programfilter']="";
}
if($_REQUEST['notprogram']){
	$_SESSION['notprogram']=$_REQUEST['notprogram'];
} else {
	$_SESSION['notprogram']="";
}
if($_REQUEST['showsql']){
	$_SESSION['showsql']=$_REQUEST['showsql'];
} else {
	$_SESSION['showsql']="";
}
//Find out how many IP's are created by the hostfilter. If it's too many, don't allow the start and stop buttons to appear
$hostar=NmapFindIP($_REQUEST['hostfilter']);
$sizeof=sizeof($hostar);
echo "<br /><h3 style='display: inline;'>$title</h3> (<font style=\"text-align: right; color: #8B0000;\">Only 1 browser session of this page at a time</font>)<br /><br />\n";
?>
<b>Filtering options</b> (Submit required options in <font style="color: #8B0000;">red</font> then "Start Log")<br />
<div style="min-width: 750px;">
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display: inline;">
		<table style="border-style: none; display: inline;">
			<tr>
				<td>Database:
				<select name="thedatabase">
				<?php
				$mysql=new mysql;
				$returnar=$mysql->connect(1);
				$thedb=$returnar[0];
				$dbconn=$returnar[1];
				$getdb="show databases;";
				$getdb=mysqli_query($dbconn,$getdb);
				while($row=mysqli_fetch_array($getdb)){
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
				</td>
				<td><input type="submit" value="Change Database" name="ChangeDatabase" /></td>
			</tr>
		</table>
	</form>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display: inline;">
		<table style="border-style: solid; border-width: 1px; border-spacing: 10px;">
			<tr>
				<td style="text-align: right; color: #8B0000;"><b>Log Source</b></td>
				<td>
				<?php
				$gettables="show tables in $thedb;";
				$gettables=mysqli_query($dbconn,$gettables);
				echo "<select name=\"thetables\">\n";
				while($row=mysqli_fetch_array($gettables)){
					$thetable=$row[0];
					if($thetable=="logs" || $thetable=="defaultlogs"){
						$thetable="Default Logs";
					}
					if($row[0]==$_REQUEST['thetables']){
						echo "\n<option value=\"{$row[0]}\" selected>$thetable</option>";
					} else {
						echo "\n<option value=\"{$row[0]}\">$thetable</option>";
					}
				}
				echo "</select>\n";
				?>
				</td>
			</tr>
			<tr>
				<td style="text-align: right; color: #8B0000;"><b>Number of events</b></td>
				<td><select name="numevents">
				<?php
				$done=false;
				for($i=5; $i<=50; $i+=5){
					if($i==$_REQUEST['numevents']){
						echo "\n<option selected>$i</option>";
						$done=true;
					} else if($i==15 && $done==false){
						echo "\n<option selected>$i</option>";
					} else {
						echo "\n<option>$i</option>";
					}
				}
				?>
				</select></td>
			</tr>
			<tr>
				<td style="text-align: right;"><b>Host</b> (<font style="font-style: italic; font-size: 13px;">Single IP, dashes, commas, subnet masks,</font><br /><font style="font-style: italic; font-size: 13px;">and * accepted. Combinations allowed</font>)</td>
				<td><input type="text" name="hostfilter" style="width: 200px; text-align: left;" value='<?php if($_REQUEST['hostfilter']) echo $_REQUEST['hostfilter']; ?>' /></td>
			</tr>
			<tr>
				<td style="text-align: right;"><b>Level</b> (<font style="font-style: italic; font-size: 13px;">Cannot be used with Host filtering</font>)</td>
				<td>
					<select name="levelselect">
						<option value="alert"<?php if($_REQUEST['levelselect']=='alert') echo " selected"; ?>>1 - Alert and greater</option>
						<option value="crit"<?php if($_REQUEST['levelselect']=='crit') echo " selected"; ?>>2 - Critical and greater</option>
						<option value="err"<?php if($_REQUEST['levelselect']=='err') echo " selected"; ?>>3 - Error and greater</option>
						<option value="warning"<?php if($_REQUEST['levelselect']=='warning') echo " selected"; ?>>4 - Warning and greater</option>
						<option value="noticenowarnnoxlate"<?php if($_REQUEST['levelselect']=='noticenowarnnoxlate') echo " selected"; ?>>5 - Notice and greater no Warning no "No xlate" (ASA's)</option>
						<option value="noticenowarn"<?php if($_REQUEST['levelselect']=='noticenowarn') echo " selected"; ?>>5 - Notice and greater no Warning (ASA's)</option>
						<option value="notice"<?php if($_REQUEST['levelselect']=='notice') echo " selected"; ?>>5 - Notice and greater</option>
						<option value="info"<?php if($_REQUEST['levelselect']=='info') echo " selected"; ?>>6 - Info and greater</option>
						<option value="select"<?php if($_REQUEST['levelselect']=='select' || $_REQUEST['levelselect']=='') echo " selected"; ?>>--------------- Optional Level (Select for no filter) ---------------</option>
						<option value="emergonly"<?php if($_REQUEST['levelselect']=='emergonly') echo " selected"; ?>>0 - Emergency only</option>
						<option value="alertonly"<?php if($_REQUEST['levelselect']=='alertonly') echo " selected"; ?>>1 - Alert only</option>
						<option value="critonly"<?php if($_REQUEST['levelselect']=='critonly') echo " selected"; ?>>2 - Critical only</option>
						<option value="erronly"<?php if($_REQUEST['levelselect']=='erronly') echo " selected"; ?>>3 - Error only</option>
						<option value="warningonly"<?php if($_REQUEST['levelselect']=='warningonly') echo " selected"; ?>>4 - Warning only</option>
						<option value="noticeonly"<?php if($_REQUEST['levelselect']=='noticeonly') echo " selected"; ?>>5 - Notice only</option>
						<option value="infoonly"<?php if($_REQUEST['levelselect']=='infoonly') echo " selected"; ?>>6 - Info only</option>
						<option value="debugonly"<?php if($_REQUEST['levelselect']=='debugonly') echo " selected"; ?>>7 - Debug only</option>
					</select>
				</td>
			</tr>
			<tr>
				<td style="text-align: right;"><b>Program</b></td>
				<td>
					<input type="text" name="programfilter" style="width: 150px; text-align: left;" value='<?php if($_REQUEST['programfilter']) echo $_REQUEST['programfilter']; ?>' />
					<input type='checkbox' name="notprogram"<?php if($_POST['notprogram']) echo "checked"; ?> />&nbsp;Not
				</td>
			</tr>
			<tr>
				<td style="text-align: right;"><b>Show SQL</b></td>
				<td>
					<input type='checkbox' name="showsql"<?php if($_POST['showsql']) echo "checked"; ?> />
				</td>
			</tr>
			<tr>
				<td>
					<input type=hidden name=thedatabase value="<?php echo $_REQUEST['thedatabase']; ?>">
					<input type="submit" value="Submit Filter" name="submitfilter" />
				</td>
				<td>&nbsp;</td>
			</tr>
		</table>
	</form>
</div><br />
<?php
//Do not allow more than 5000 host filtering
//Do not allow host and level filtering
//Do not allow program filtering for ASA, AudioCodes, Default Logs, and Switch
if($sizeof<5000 && !($_REQUEST['hostfilter'] && $_REQUEST['levelselect']!='select')){
	echo "<button onclick=\"getLog('start');\">Start Log</button>\n";
	echo "<button onclick=\"stopTail();\">Stop Log</button>\n";
//Error message for host and level filtering
} else if($_REQUEST['hostfilter'] && $_REQUEST['levelselect']!='select'){
	echo "Combined host and level filtering not allowed. It causes extremely high CPU usage in live log mode.";
//Error message for querying large amount of hosts
} else {
	echo "You are trying to query for " . number_format($sizeof,0,".",",") . " hosts. Reduce the amount of hosts to less than 5,000.<br />\n";
}
//Warning message for querying large amount of hosts
if($sizeof>1000 && $sizeof<5000){
	echo "<br /><br />You are trying to query for " . number_format($sizeof,0,".",",") . " hosts. You may experience semi-slower results. Reduce the number of hosts if possible.\n";
}
?>
<div id="log" style="padding: 10px 10px 20px 5px; margin: 10px 0px 10px 20px; text-align:left;">
</div>
<?php
require("include/end.php");
?>