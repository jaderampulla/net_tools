<?php
	/*
	Windows OpenSSL here: http://slproweb.com/products/Win32OpenSSL.html
	Windows SNMP here: http://sourceforge.net/projects/net-snmp/files/net-snmp%20binaries/5.5-binaries/
	Be sure to use an OpenSSL version less than 1.0 for compatibility with encryption support (SNMPv3) in the Net-SNMP tools
	*/
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	session_start();
	$title="Unused Switch Ports";
	require("../include/options/snmpincludes.php");
	require("../include/header.php");
	require ("../include/functions.php");
	$time_start=microtime_float();
	echo "<br />This page displays a list of ports that have 0 traffic for ifInOctets <font style=\"color: red;\"><b>AND</b></font> ifOutOctets SNMP values<br />since the last time counters were cleared or the device was rebooted.<br />\n";
	echo "<font style=\"color: red;\"><b>NOTE:</b></font> ifInOctets and ifOutOctets SNMP values only begin counting when SNMP is enabled (Either at boot or when configured).<br /><br />"
	?>
	<script type="text/javascript">
		//Check all functions to grey out any boxes
		function checker() {
			disable_enable_commstring();
			if(document.getElementById("snmpversion").value=="3"){
				disable_enable_v3privacy();
			}
		}
		window.onload = checker;
		
		function disable_snmpversionopt(){
			document.getElementById("snmpversion").setAttribute("disabled","disabled");
		}
		function enable_snmpversionopt(){
			document.getElementById("snmpversion").removeAttribute("disabled");
		}
		function disable_v2commstring(){
			document.getElementById("snmpcommstring").setAttribute("disabled","disabled");
		}
		function enable_v2commstring(){
			document.getElementById("snmpcommstring").removeAttribute("disabled");
		}
		function disable_allv3(){
			document.getElementById("v3user").setAttribute("disabled","disabled");
			document.getElementById("v3authproto").setAttribute("disabled","disabled");
			document.getElementById("v3authpass").setAttribute("disabled","disabled");
			document.getElementById("v3seclevel").setAttribute("disabled","disabled");
			document.getElementById("v3privproto").setAttribute("disabled","disabled");
			document.getElementById("v3privpass").setAttribute("disabled","disabled");
		}
		function enable_allv3(){
			document.getElementById("v3user").removeAttribute("disabled");
			document.getElementById("v3authproto").removeAttribute("disabled");
			document.getElementById("v3authpass").removeAttribute("disabled");
			document.getElementById("v3seclevel").removeAttribute("disabled");
			document.getElementById("v3privproto").removeAttribute("disabled");
			document.getElementById("v3privpass").removeAttribute("disabled");
		}
		function disable_v3privacy(){
			document.getElementById("v3privproto").setAttribute("disabled","disabled");
			document.getElementById("v3privpass").setAttribute("disabled","disabled");
		}
		function enable_v3privacy(){
			document.getElementById("v3privproto").removeAttribute("disabled");
			document.getElementById("v3privpass").removeAttribute("disabled");
		}
	</script>
	<form method="post" style="display: inline;" name="inputstuff">
		<table border=0 style="display: inline-table;">
			<tr>
				<td>Device IP:</td>
				<td><input type="text" name="theip" style="width: 150px; text-align: left;" <?php if($_POST['theip']) echo " value=\"{$_POST['theip']}\"";?> /></td>
			</tr>
			<tr>
				<td>SNMP Version:</td>
				<td>
					<select name="snmpversion" id="snmpversion" onchange="disable_enable_commstring()">
						<option value="2c"<?php if($_POST['snmpversion']=="2c" || $defaultsnmpversion=="2c") echo " selected";?>>2c</option>
						<option value="3"<?php if($_POST['snmpversion']=="3" || $defaultsnmpversion=="3") echo " selected";?>>3</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>v2c Community String:</td>
				<td><input type="text" name="snmpcommstring" id="snmpcommstring" style="width: 150px; text-align: left;" <?php if($_POST['snmpcommstring']){ echo " value=\"{$_POST['snmpcommstring']}\""; } else { echo " value=\"$defaultsnmp\""; } ?> /></td>
			</tr>
			<script type="text/javascript">
				function disable_enable_commstring(){
					if(document.getElementById("snmpversion").value=="3"){
						disable_v2commstring();
						enable_allv3();
					} else {
						enable_v2commstring();
						disable_allv3();
					}
					if(document.getElementById("snmpversion").value=="3" && document.getElementById("v3seclevel").value=="authNoPriv"){
						disable_v3privacy();
					}
				}
			</script>
			<tr>
				<td colspan="2"><input name="ignoreping" type="checkbox" <?php if($_POST['ignoreping']) echo "checked"; ?> />&nbsp;Ignore ping test before doing SNMP</td>
			</tr>
			<tr>
				<td><font style="text-align: left;"><input type="submit" value="Scan Device" name="snmpscan" /></font></td>
			</tr>
		</table>
		<table frame=box style="display: inline-table; margin: 0px 10px 0px 10px;">
			<tr>
				<td colspan=2><b>SNMPv3 Options:</b></td>
			</tr>
			<tr>
				<td>Username:</td>
				<td><input type="text" name="v3user" id="v3user" style="width: 150px; text-align: left;" <?php if($_POST['v3user']){ echo " value=\"{$_POST['v3user']}\""; } else { echo " value=\"$defaultv3user\""; } ?> /></td>
			</tr>
			<tr>
				<td>Authentication Protocol:</td>
				<td>
					<select name="v3authproto" id="v3authproto">
						<option value="MD5"<?php if($_POST['v3authproto']=="MD5" || $defaultv3authproto=="MD5") echo " selected";?>>MD5</option>
						<option value="SHA"<?php if($_POST['v3authproto']=="SHA" || $defaultv3authproto=="SHA") echo " selected";?>>SHA</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Authentication Password:</td>
				<td><input type="text" name="v3authpass" id="v3authpass" style="width: 150px; text-align: left;" <?php if($_POST['v3authpass']){ echo " value=\"{$_POST['v3authpass']}\""; } else { echo " value=\"$defaultv3authpass\""; } ?> /></td>
			</tr>
			<tr>
				<td>Security Level:</td>
				<td>
					<select name="v3seclevel" id="v3seclevel" onchange="disable_enable_v3privacy()" onload="disable_enable_v3privacy()">
						<option value="authPriv"<?php if($_POST['v3seclevel']=="authPriv") echo " selected";?>>Authentication and Privacy</option>
						<option value="authNoPriv"<?php if($_POST['v3seclevel']=="authNoPriv") echo " selected";?>>Authentication without Privacy</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Privacy Protocol:</td>
				<td>
					<select name="v3privproto" id="v3privproto">
						<option value="DES"<?php if($_POST['v3privproto']=="DES" || $defaultv3privproto=="DES") echo " selected";?>>DES</option>
						<option value="AES"<?php if($_POST['v3privproto']=="AES" || $defaultv3privproto=="AES") echo " selected";?>>AES</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Privacy Password:</td>
				<td><input type="text" name="v3privpass" id="v3privpass" style="width: 150px; text-align: left;" <?php if($_POST['v3privpass']){ echo " value=\"{$_POST['v3privpass']}\""; } else { echo " value=\"$defaultv3privpass\""; } ?> /></td>
			</tr>
			<script type="text/javascript">
				function disable_enable_v3privacy(){
					if(document.getElementById("v3seclevel").value=="authPriv"){
						enable_v3privacy();
					} else {
						disable_v3privacy();
					}
				}
			</script>
		</table>
	</form>
	<!-- Default cursor location -->
	<script type="text/javascript">
		document.inputstuff.theip.focus();
	</script>
	
	<?php
	session_start();
	function StandardSNMPGet($theip,$snmpversion,$snmpcommstring,$commandstring,$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,$outputmod,$errorreporting){
		if($outputmod){
			$printout=$outputmod;
		} else {
			$printout="-O qv";
		}
		if($snmpversion=="2c"){
			$versioncmd="-c $snmpcommstring";
		} else if($snmpversion=="3" && $snmpv3seclevel=="authPriv"){
			$versioncmd="-u $snmpv3user -a $snmpv3authproto -A $snmpv3authpass -l $snmpv3seclevel -x $snmpv3privproto -X $snmpv3privpass";
		} else if($snmpversion=="3" && $snmpv3seclevel=="authNoPriv"){
			$versioncmd="-u $snmpv3user -a $snmpv3authproto -A $snmpv3authpass -l $snmpv3seclevel";
		}
		if($errorreporting=="showerrors"){
			$errorcmd="-L o";
		} else {
			$errorcmd="-L n";
		}
		$command="snmpget -r 1 $errorcmd -v $snmpversion $versioncmd $printout $theip $commandstring";
		//echo "COMMAND: $command<br />";
		return shell_exec($command);
	}
	//function StandardSNMPWalk($theip,$snmpcommstring,$commandstring){
	function StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,$commandstring,$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass){
		if($snmpversion=="2c"){
			$versioncmd="-c $snmpcommstring";
		} else if($snmpversion=="3" && $snmpv3seclevel=="authPriv"){
			$versioncmd="-u $snmpv3user -a $snmpv3authproto -A $snmpv3authpass -l $snmpv3seclevel -x $snmpv3privproto -X $snmpv3privpass";
		} else if($snmpversion=="3" && $snmpv3seclevel=="authNoPriv"){
			$versioncmd="-u $snmpv3user -a $snmpv3authproto -A $snmpv3authpass -l $snmpv3seclevel";
		}
		$command="snmpbulkwalk -r 1 -L n -v $snmpversion $versioncmd -O sq $theip $commandstring";
		//echo "COMMAND STRING: $command<br />\n";
		$walkresult=preg_split('/\n/',shell_exec($command));
		//echo "<pre>";
		//print_r($walkresult);
		foreach($walkresult as $snmpval){
			if($snmpval){
				//Get rid of ifDescr, ifName, ifAlias, etc
				list($junk,$remain)=explode('.',$snmpval);
				//Get ID. Rest of string is value
				list($id,$val)=explode(' ',$remain,2);
				//Get rid of "Avaya/Nortel Ethernet Routing Switch" portion of string
				if(preg_match('/Avaya Ethernet Routing Switch/',$val) || preg_match('/Nortel Ethernet Routing Switch/',$val)){
					list($junk,$val)=explode(' - ',$val);
				}
				//Exclude VLAN interfaces and put everything else in an array keyed by the SNMP ID's
				if($id<=1000){
					$finar[$id]=$val;
				}
			}
		}
		return $finar;
	}
	
	if($_POST['snmpscan']){
		$theip=$_POST['theip'];
		$snmpcommstring=$_POST['snmpcommstring'];
		$snmpversion=$_POST['snmpversion'];
		$snmpv3user=$_POST['v3user'];
		$snmpv3authproto=$_POST['v3authproto'];
		$snmpv3authpass=$_POST['v3authpass'];
		$snmpv3seclevel=$_POST['v3seclevel'];
		$snmpv3privproto=$_POST['v3privproto'];
		$snmpv3privpass=$_POST['v3privpass'];
		if(!$theip){
			echo "<br />Please enter an IP address\n";
		} else if (!$snmpcommstring && $snmpversion=="2c"){
			echo "<br />Please enter an SNMPv2 community string\n";
		} else if($snmpversion==3 && !$snmpv3user){
			echo "<br />Please enter an SNMPv3 username\n";
		} else if($snmpversion==3 && !$snmpv3authpass){
			echo "<br />Please enter an SNMPv3 authentication password\n";
		} else if($snmpversion==3 && !$snmpv3privpass && $snmpv3seclevel=="authPriv"){
			echo "<br />Please enter an SNMPv3 privacy password\n";
		} else {
			if($_POST['ignoreping']){
				$ignoreping=true;
			} else {
				$nmapstring="nmap -PO -sP -PE -n --open -v $theip | grep \"scan report\" | grep -v \"host down\" | sed 's/Nmap scan report for //g'";
				$testip=shell_exec($nmapstring);
			}
			if($ignoreping==true || strlen($testip)>1){
				//Check to make sure the device is SNMP capable
				$testsnmp=StandardSNMPGet($theip,$snmpversion,$snmpcommstring,"SNMPv2-MIB::sysName.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,"-O qv","showerrors");
				//echo "TESTSNMP: $testsnmp<br />";
				if(preg_match('/user name/',$testsnmp)){
					echo "<br />The SNMPv3 username you entered is incorrect.\n";
				} else if(preg_match('/Authentication failure/',$testsnmp)){
					echo "<br />The SNMPv3 authentication protocol and/or password you entered is incorrect.\n";
				} else if(preg_match('/Decryption error/',$testsnmp)){
					echo "<br />The SNMPv3 privacy protocol you entered is incorrect.\n";
				} else if((strlen($testsnmp)==0 || preg_match('/Timeout/',$testsnmp)) && $snmpversion==3){
					echo "<br />The IP address '" . $_POST['theip'] . "' is up but not responsive to SNMP queries.<br />Either the SNMPv3 privacy password you entered is incorrect, or SNMPv3 is not configured on the device.\n";
				} else if(strlen($testsnmp)==0 && $snmpversion==2){
					echo "<br />The IP address '" . $_POST['theip'] . "' is up but not responsive to SNMP queries with RO community string you entered.\n";
				} else if(strlen($testsnmp)>0){
					$devheaderar[]='Device Info';
					$devheaderar[]='Value';
					//Get system info
					//Replace multiple spaces with a single space: http://stackoverflow.com/questions/2368539/php-replacing-multiple-spaces-with-a-single-space
					$sysdescr=preg_replace('!\s+!', ' ',StandardSNMPGet($theip,$snmpversion,$snmpcommstring,"SNMPv2-MIB::sysDescr.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass));
					$syscontact=StandardSNMPGet($theip,$snmpversion,$snmpcommstring,"SNMPv2-MIB::sysContact.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
					$syslocation=StandardSNMPGet($theip,$snmpversion,$snmpcommstring,"SNMPv2-MIB::sysLocation.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
					$sysuptime=StandardSNMPGet($theip,$snmpversion,$snmpcommstring,"DISMAN-EVENT-MIB::sysUpTimeInstance",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,"-O v");
					//Add system info to array for Excel export
					$devdataar[]=array('System Name:',$testsnmp);
					$devdataar[]=array('System Description:',$sysdescr);
					$devdataar[]=array('System Contact:',$syscontact);
					$devdataar[]=array('System Location:',$syslocation);
					$devdataar[]=array('System Uptime:',$sysuptime);
					//Print system info
					echo "<br /><b>System Name:</b> $testsnmp<br />\n";
					echo "<b>System Description:</b> $sysdescr<br />\n";
					echo "<b>System Contact:</b> $syscontact<br />\n";
					echo "<b>System Location:</b> $syslocation<br />\n";
					echo "<b>System Uptime:</b> $sysuptime<br /><br />\n";
					//Add system table to Excel Array for multi-table printout format
					$excelar[]=array($devheaderar,$devdataar);
					//Get all the necessary interface info
					$ifdescartemp=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifDescr",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
					//Check for duplicate VLAN and interface names
					foreach($ifdescartemp as $id=>$desc){
						if(!in_array($desc,$ifdescar)){
							$ifdescar[$id]=$desc;
						}
					}
					$ifnamear=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifName",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
					$ifaliasar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifAlias",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
					$ifinoctetsar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifInOctets",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
					$ifoutoctetsar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifOutOctets",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
					$ifadminstatusar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifAdminStatus",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
					$ifoperstatusar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifOperStatus",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
					//Find SNMP ID's with no in or out interface traffic. Add the ID's to an array
					foreach ($ifinoctetsar as $inoctetkey => $inoctet) {
						if($inoctet==0 && $ifoutoctetsar[$inoctetkey]==0){
							$notrafficar[]=$inoctetkey;
						}
					}
					$portcount=count($notrafficar);
					echo "<b># Ports without Traffic:</b> $portcount<br /><br />\n";
					if($portcount>0){
						//Headerar used for Excel export
						/*$headerar=array(
							0=>"Description",
							1=>"Name",
							2=>"Alias",
							3=>"Admin Status",
							4=>"Operational Status");*/
						//Headerar used for Excel export
						$headerar[]="Description";
						$headerar[]="Name";
						$headerar[]="Alias";
						$headerar[]="Admin Status";
						$headerar[]="Operational Status";
						$dataarstring='$ifdescar[$theid],$ifnamear[$theid],$ifaliasar[$theid],$ifadminstatusar[$theid],$ifoperstatusar[$theid]';
						echo "<table border=1>\n";
						echo "<tr>";
						foreach($headerar as $header){
							echo "<th>$header</th>";
						}
						echo "</tr>\n";
						foreach($notrafficar as $theid){
							//Array for Excel export
							$dataar[]=array($ifdescar[$theid],$ifnamear[$theid],$ifaliasar[$theid],$ifadminstatusar[$theid],$ifoperstatusar[$theid]);
							echo "<tr>";
							echo "<td>" . $ifdescar[$theid] . "</td>";
							echo "<td>" . $ifnamear[$theid] . "</td>";
							echo "<td>" . $ifaliasar[$theid] . "</td>";
							echo "<td>" . $ifadminstatusar[$theid] . "</td>";
							echo "<td>" . $ifoperstatusar[$theid] . "</td>";
							echo "</tr>\n";
							/*
							Array for Excel export - Kudos to Brett Langdon for this line of code:
							http://www.phphelp.com/forum/general-php-help/echo-list-of-variables-into-array/
							*/
							eval('$dataar[] = array(' . $dataarstring . ');');
						}
						echo "</table><br />\n";
						//Add system table to Excel Array for multi-table printout format
						$excelar[]=array($headerar,$dataar);
						//echo "<pre>"; print_r($excelar); echo "</pre>\n";
						$_SESSION['excelar']=$excelar;
						//Freeze the 2nd array (#1) for scrolling in Excel
						$_SESSION['freezepanearnum']=1;
						//Properties for excel file
						$excelpropertiesar=array(
							 "setTitle"=>"$theip",
							 "setSubject"=>"Unused Ports",
							 "setDescription"=>"Unused Ports",
							 "setKeywords"=>"Unused Ports",
							 "setCategory"=>"Unused Ports",
							 "filename"=>"unusedports.xlsx"
						);
						$_SESSION['excelpropertiesar']=$excelpropertiesar;
						//Export XLSX Button
						if(sizeof($excelar)>0){
							echo "&nbsp;<form action='../excel/multitabletoxls.php' method='post' style='display: inline;'>\n";
							echo "<input type='submit' value='Export to XLSX' />\n";
							echo "</form>\n";
						}
						$time=end_time($time_start);
						echo "\n<br /><br />SNMP queries completed in {$time}seconds.";
					}
				}
			} else {
				echo "<br />The IP address '$theip' is not responsive. Please try something else.\n";
			}
		}
	}
	require("../include/end.php");
?>