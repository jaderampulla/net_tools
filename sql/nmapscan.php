<?php
	/*
	Windows OpenSSL here: http://slproweb.com/products/Win32OpenSSL.html
	Windows SNMP here: http://sourceforge.net/projects/net-snmp/files/net-snmp%20binaries/5.5-binaries/
	Be sure to use an OpenSSL version less than 1.0 for compatibility with encryption support (SNMPv3) in the Net-SNMP tools
	*/
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	session_start();
	$title="NMAP Scan";
	require("snmpincludes.php");
	require("../include/header.php");
	require ("../include/functions.php");
	$time_start=microtime_float();
	?>
	<br />
	<script type="text/javascript">
		//Check all functions to grey out any boxes
		function checker() {
			if(document.getElementById("theip").disabled==true){
				document.getElementById("routerip").focus();
			}
			disable_enable_commstring();
			if(document.getElementById("snmpversion").value=="3"){
				disable_enable_v3privacy();
			}
			disable_enable_downhosts();
		}
		window.onload = checker;
		
		function disable_checkboxes(){
			document.getElementById("hostnames").setAttribute("disabled","disabled");
			document.getElementById("hostnames").checked = false;
			document.getElementById("snmpdevname").setAttribute("disabled","disabled");
			document.getElementById("snmpdevname").checked = false;
			document.getElementById("snmpsernum").setAttribute("disabled","disabled");
			document.getElementById("snmpsernum").checked = false;
		}
		function enable_checkboxes(){
			document.getElementById("hostnames").removeAttribute("disabled");
			document.getElementById("snmpdevname").removeAttribute("disabled");
			document.getElementById("snmpsernum").removeAttribute("disabled");
		}
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
	<tr><td style="vertical-align: top;">
		<table border=0 style="display: inline-table;">
			<tr>
				<td style="color: #B8860B;">Enter range:</td>
				<td><input type="text" name="theip" id="theip" style="width: 150px; text-align: left;" <?php if($_POST['routerrow']){ echo "disabled"; } else if($_POST['theip']){ echo " value=\"{$_POST['theip']}\""; }?> /></td>
			</tr>
			<tr>
				<td colspan="2"><b>OR</b></td>
			</tr>
			<tr>
				<td style="color: #B8860B;">Use Router ARP Table:</td>
				<td><input name="routerrow" id="routerrow" type="checkbox" onclick="toggleARPRow()" <?php if($_POST['routerrow']) echo "checked"; ?> /></td>
			</tr>
			<tr name="routerrowextra" id="routerrowextra" <?php if($_POST['routerrow']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
				<td style="color: #B8860B;">Router IP:</td>
				<td><input type="text" name="routerip" id="routerip" style="width: 150px; text-align: left;" <?php if($_POST['routerip']){ echo " value=\"{$_POST['routerip']}\""; } else { echo " value=\"$defaultrouterip\""; }?> /></td>
			</tr>
			<tr name="routerrowprint" id="routerrowprint" <?php if($_POST['routerrow']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
				<td style="color: #B8860B;">Print Router ARP String:</td>
				<td><input name="arpprint" id="arpprint" type="checkbox" <?php if($_POST['arpprint']) echo "checked"; ?> /></td>
			</tr>
			<tr name="routerrownote" id="routerrownote" <?php if($_POST['routerrow']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
				<td colspan="2" style="text-align: center; color: #B8860B;">*** ARP table grabbed via SNMP ***</td>
			</tr>
			<script type="text/javascript">
				function toggleARPRow() {
					if (document.getElementById("routerrowextra").style.display=="none") {
						document.getElementById("routerrowextra").style.display="table-row";
						document.getElementById("routerrowprint").style.display="table-row";
						document.getElementById("routerrownote").style.display="table-row";
						document.getElementById("theip").value=null;
						document.getElementById("theip").setAttribute("disabled","disabled");
						document.getElementById("routerip").focus();
					} else {
						document.getElementById("routerrowextra").style.display="none";
						document.getElementById("routerrowprint").style.display="none";
						document.getElementById("routerrownote").style.display="none";
						document.getElementById("theip").removeAttribute("disabled");
						document.getElementById("theip").focus();
					}
				}
			</script>
			<tr>
				<td>Exclusions (Optional):<br /><i>(Comma separated list)</i></td>
				<td style="vertical-align: bottom;"><input type="text" name="exclusions" style="width: 150px; text-align: left;" <?php if($_POST['exclusions']) echo " value=\"{$_POST['exclusions']}\"";?> /></td>
			</tr>
			<tr>
				<td>Hosts to see:</td>
				<td>
					<select name="updown" id="updown" onchange="disable_enable_downhosts();">
						<option value="a"<?php if($_POST['updown']=="a") echo "selected"; ?>>All Hosts</option>
						<option value="u"<?php if($_POST['updown']=="u") echo "selected"; ?>>Up Hosts</option>
						<option value="d"<?php if($_POST['updown']=="d") echo "selected"; ?>>Down Hosts</option>
					</select>
				</td>
			</tr>
			<tr>
				<td style="color: #800000;">DNS hostnames:</td>
				<td><input name="hostnames" id="hostnames" onclick="toggleHostname()" type="checkbox" <?php if($_POST['hostnames']) echo "checked"; ?> />&nbsp;&nbsp;&nbsp;(<a target="_NEW" href="http://help.dnsmadeeasy.com/records/ptr-record/">DNS PTR records</a> for IP's)</td>
			</tr>
			<tr id="dnsinput" <?php if($_POST['hostnames']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
				<td style="color: #800000;">DNS Server:</td>
				<td><input type="text" name="dnsserverip" style="width: 150px; text-align: left;" <?php if($_POST['dnsserverip']){ echo " value=\"{$_POST['dnsserverip']}\""; } else { echo " value=\"$defaultdnsserver\""; }?> /></td>
			</tr>
			<script type="text/javascript">
			function toggleHostname() {
				var elem=document.getElementById("dnsinput");
				var hide=elem.style.display=="none";
				if (hide) {
					elem.style.display="table-row";
				} else {
					elem.style.display="none";
				}
			}
			</script>
			<tr>
				<td style="color: green;">SNMP device name:</td>
				<td><input name="snmpdevname" id="snmpdevname" type="checkbox" <?php if($_POST['snmpdevname']) echo "checked"; ?> /></td>
			</tr>
			<script type="text/javascript">
				function disable_enable_downhosts(){
					if(document.getElementById("updown").value=="d"){
						disable_checkboxes();
						disable_snmpversionopt();
						disable_v2commstring();
						disable_allv3();
					} else if(document.getElementById("snmpversion").value=="2c"){
						enable_checkboxes();
						enable_snmpversionopt();
						enable_v2commstring();
					} else if(document.getElementById("snmpversion").value=="3" && document.getElementById("v3seclevel").value=="authNoPriv"){
						enable_checkboxes();
						enable_snmpversionopt();
						enable_allv3();
						disable_v3privacy();
					} else if(document.getElementById("snmpversion").value=="3"){
						enable_checkboxes();
						enable_snmpversionopt();
						enable_allv3();
					}
				}
			</script>
			<tr>
				<td style="color: green;">SNMP serial number(s):</td>
				<td><input name="snmpsernum" id="snmpsernum" type="checkbox" <?php if($_POST['snmpsernum']) echo "checked"; ?> /></td>
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
				<td><input type="submit" value="Scan Range" name="snmpscan" /></td>
			</tr>
		</table>
	</td><td style="vertical-align: top;">
		<table frame=box style="display: inline-table;">
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
					<select name="v3seclevel" id="v3seclevel" onchange="disable_enable_v3privacy()">
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
	</td><td style="vertical-align: top;">
		<script type="text/javascript">
		function toggleTable() {
			var elem=document.getElementById("examples");
			var hide = elem.style.display=="none";
			if (hide) {
				elem.style.display="inline-table";
				document.getElementById("extrabr").style.display="inline";
				document.getElementById("linktext").innerHTML="Hide Examples";
			} else {
				elem.style.display="none";
				document.getElementById("extrabr").style.display="none";
				document.getElementById("linktext").innerHTML="Show Examples";
			}
		}
		</script>
		<a style="display: inline;" id="togglelink" href="#" onclick="toggleTable();"><div id="linktext" style="display: inline;">Show Examples</div></a><br />
		<table id="examples" frame=box style="display: none;">
			<tr><th colspan=2 style="text-align: left;"><font style="color: #808000;">Range Examples</font></th></tr>
			<tr><th>Result Type</th><th>Range</th></tr>
			<tr style="background-color: #808080;">
				<td>Range of hosts</td>
				<td>10.1.1.10-30</td>
			</tr>
			<tr>
				<td>Same host in different networks</td>
				<td>10.1-50,215,225.0.100</td>
			</tr>
			<tr style="background-color: #808080;">
				<td>All hosts in a subnet</td>
				<td>10.30.100.*</td>
			</tr>
			<tr>
				<td>All hosts in multiple subnets</td>
				<td>10.40.50-51.*</td>
			</tr>
			<tr style="background-color: #808080;">
				<td>Subnet mask</td>
				<td>10.26.4.0/24</td>
			</tr>
			<tr>
				<td>Multiple hosts, ranges, and networks<br />(Single line with different ranges)</td>
				<td style="width: 225px;"><font style="color: brown;">192.168.0.254</font> 192.168.1-22.1 <font style="color: brown;">192.168.0.6-9</font> 192.168.1-22.8</td>
			</tr>
		</table><div id="extrabr" style="display: none;"><br /></div>	
	</td></tr></table>
	</form><br /><br />
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
	
	function StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,$commandstring,$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass){
		if($snmpversion=="2c"){
			$versioncmd="-c $snmpcommstring";
		} else if($snmpversion=="3" && $snmpv3seclevel=="authPriv"){
			$versioncmd="-u $snmpv3user -a $snmpv3authproto -A $snmpv3authpass -l $snmpv3seclevel -x $snmpv3privproto -X $snmpv3privpass";
		} else if($snmpversion=="3" && $snmpv3seclevel=="authNoPriv"){
			$versioncmd="-u $snmpv3user -a $snmpv3authproto -A $snmpv3authpass -l $snmpv3seclevel";
		}
		$command="snmpbulkwalk -r 1 -L n -v $snmpversion $versioncmd -O sq $theip $commandstring";
		//echo "<b>COMMAND:</b> $command<br />\n";
		$walkresult=preg_split('/\n/',shell_exec($command));
		foreach($walkresult as $snmpval){
			//Handle the ARP MIB
			if($snmpval && $commandstring=="IP-MIB::ipNetToMediaPhysAddress"){
				list($remain,$id)=explode(' ',$snmpval);
				//Isolate the IP address
				list($junk,$remain)=explode('.',$remain,2);
				list($junk,$val)=explode('.',$remain,2);
				//Convert 0:b:ab:7 to 00:0b:ab:07
				$octet=split(":",$id);
				$id="";
				foreach($octet as $oct) {
					if(strlen($oct)==1) $oct="0" . $oct;
					$id=$id . $oct . ":";
				}
				//Remove last colon from string and covert to uppercase
				$id=strtoupper(substr($id,0,-1));
				if($id && $id!='FF:FF:FF:FF:FF:FF'){
					$finar[$id]=$val;
				}
			} else if($snmpval){
				if($commandstring=="1.3.6.1.4.1.14988.1.1.7.3.0"){
					list($junk,$val)=explode(' ',$snmpval);
					return preg_replace('/\"/','',trim($val));
				} else {
					//echo "SNMPVAL: $snmpval<br />\n";
					//Get rid of ifDescr, ifName, ifAlias, etc
					list($junk,$remain)=explode('.',$snmpval);
					//Get ID. Rest of string is value
					list($id,$val)=explode(' ',$remain,2);
					if($val){
						$finar[$id]=trim($val);
					}
				}
			}
		}
		return $finar;
	}
	
	if($_POST['snmpscan']){
		$theip=$_POST['theip'];
		$routerip=$_POST['routerip'];
		//Give a value for exclusions if there's nothing
		if($_POST['exclusions']){
			$exclusions=preg_replace('/ /',',',$_POST['exclusions']);
		} else {
			$exclusions='""';
		}
		$dnsserver=$_POST['dnsserverip'];
		$updown=$_POST['updown'];
		$snmpcommstring=$_POST['snmpcommstring'];
		$snmpversion=$_POST['snmpversion'];
		$snmpv3user=$_POST['v3user'];
		$snmpv3authproto=$_POST['v3authproto'];
		$snmpv3authpass=$_POST['v3authpass'];
		$snmpv3seclevel=$_POST['v3seclevel'];
		$snmpv3privproto=$_POST['v3privproto'];
		$snmpv3privpass=$_POST['v3privpass'];
		//Check for missing input
		if(!$theip && !$_POST['routerrow']){
			echo "<br />Please enter a range\n";
		} else if(!$theip && $_POST['routerrow'] && !$routerip){
			echo "<br />Please enter a router IP\n";
		} else if (!$snmpcommstring && $snmpversion=="2c"){
			echo "<br />Please enter an SNMPv2 community string\n";
		} else if($snmpversion==3 && !$snmpv3user){
			echo "<br />Please enter an SNMPv3 username\n";
		} else if($snmpversion==3 && !$snmpv3authpass){
			echo "<br />Please enter an SNMPv3 authentication password\n";
		} else if($snmpversion==3 && !$snmpv3privpass && $snmpv3seclevel=="authPriv"){
			echo "<br />Please enter an SNMPv3 privacy password\n";
		} else if($_POST['hostnames'] && !$dnsserver){
			echo "<br />Please enter a DNS Server IP\n";
		} else {
			if($_POST['routerrow'] && $routerip){
				$testroutersnmp=StandardSNMPGet($routerip,$snmpversion,$snmpcommstring,"SNMPv2-MIB::sysName.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,"-O qv","showerrors");
				//echo "TESTROUTERSNMP: $testroutersnmp<br />";
				if(preg_match('/user name/',$testroutersnmp)){
					echo "<br />The SNMPv3 username you entered is incorrect for the router IP.<br /><font style=\"color: red;\">The ARP table is unavailable.</font><br /><br />\n";
				} else if(preg_match('/Authentication failure/',$testroutersnmp)){
					echo "<br />The SNMPv3 authentication protocol and/or password you entered is incorrect for the router IP.<br /><font style=\"color: red;\">The ARP table is unavailable.</font><br /><br />\n";
				} else if(preg_match('/Decryption error/',$testroutersnmp)){
					echo "<br />The SNMPv3 privacy protocol you entered is incorrect for the router IP.<br /><font style=\"color: red;\">The ARP table is unavailable.</font><br /><br />\n";
				} else if((strlen($testroutersnmp)==0 || preg_match('/Timeout/',$testroutersnmp)) && $snmpversion==3){
					echo "<br />The router IP address '$routerip' is up but not responsive to SNMP queries.<br />Either the SNMPv3 privacy password you entered is incorrect, or SNMPv3 is not configured on the router.<br /><font style=\"color: red;\">The ARP table is unavailable.</font><br /><br />\n";
				} else if(strlen($testroutersnmp)==0 && $snmpversion==2){
					echo "<br />The router IP address '$routerip' is up but not responsive to SNMP queries with RO community string you entered.<br /><font style=\"color: red;\">The ARP table is unavailable.</font><br /><br />\n";
				} else if(strlen($testroutersnmp)>0){
					//Get arp table via SNMP
					$arpar=StandardSNMPWalk($routerip,$snmpversion,$snmpcommstring,"IP-MIB::ipNetToMediaPhysAddress",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
					//echo "<pre><font style=\"color: red;\">"; print_r($arpar); echo "</font></pre>";
					if(sizeof($arpar)<2){
						echo "<font style=\"color: red;\">The router was reachable through SNMP, but the ARP table is unavailable.</font><br /><br />";
					} else {
						$tmpcnt=0;
						foreach($arpar as $arp){
							if($tmpcnt==0){
								$theip=$arp;
							} else {
								$theip=$theip . " $arp";
							}
							$tmpcnt+=1;
						}
						if($_POST['arpprint']){
							echo "Router ARP String: <input type=\"text\" style=\"width: 400px; text-align: left;\" value=\"$theip\" /><br /><br />\n";
						}
					}
				}
			}
			//Determine what type of NMAP scan to do
			if($updown=="a"){
				$nmapstring="sudo nmap -PO -sP -PE -n --open -v $theip --exclude $exclusions | grep \"scan report\" | sed 's/Nmap scan report for //g'";
			} else if($updown=="u"){
				$nmapstring="sudo nmap -PO -sP -PE -n --open -v $theip --exclude $exclusions | grep \"scan report\" | grep -v \"host down\" | sed 's/Nmap scan report for //g'";
			} else if($updown=="d"){
				$nmapstring="sudo nmap -PO -sP -PE -n --open -v $theip --exclude $exclusions | grep \"scan report\" | grep \"host down\" | sed 's/Nmap scan report for //g' | sed 's/ \[host down\]//g'";
			}
			//echo "COMMAND: $nmapstring<br />";
			//Run the NMAP scan
			$returnartemp=preg_split('/\n/',shell_exec($nmapstring));
			//Create array from returned values keyed by IP address
			foreach($returnartemp as $return){
				if($return){
					if($_POST['updown']=="a"){
						if(preg_match('/host down/',$return)){
							$iptmp=trim(preg_replace('/\[host down\]/','',$return));
							$returnar[$iptmp]=array($iptmp,"down");
						} else {
							$returnar[$return]=array($return,"up");
						}
					} else if($_POST['updown']=="u"){
						$returnar[trim($return)]=array($return,"up");
					} else if($_POST['updown']=="d"){
						$returnar[trim($return)]=array($return,"down");
					}
				}
			}
			//Get SNMP values if requested
			if($_POST['snmpdevname']){
				foreach($returnar as $ip=>$return){
					//Only get SNMP if the device is up
					if($return[1]=="up"){
						$testsnmp=trim(StandardSNMPGet($ip,$snmpversion,$snmpcommstring,"SNMPv2-MIB::sysName.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,"-O qv","showerrors"));
						//echo "TESTSNMP: $testsnmp<br />";
						//Store IP's in arrays for error output after the export button
						if(preg_match('/user name/',$testsnmp)){
							$incorrectv3user[]=$ip;
						} else if(preg_match('/Authentication failure/',$testsnmp)){
							$incorrectv3auth[]=$ip;
						} else if(preg_match('/Decryption error/',$testsnmp)){
							$incorrectv3privproto[]=$ip;
						} else if((strlen($testsnmp)==0 || preg_match('/Timeout/',$testsnmp)) && $snmpversion==3){
							$incorrectv3privpass[]=$ip;
						} else if(strlen($testsnmp)==0 && $snmpversion==2){
							$downv2[]=$ip;
							//echo "<br />The IP address '" . $_POST['theip'] . "' is up but not responsive to SNMP queries with RO community string you entered.\n";
						} else if(strlen($testsnmp)>0){
							//echo "FOUND: $testsnmp<br />";
							$sysdescr=preg_replace('!\s+!',' ',StandardSNMPGet($ip,$snmpversion,$snmpcommstring,"SNMPv2-MIB::sysDescr.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null));
							$returnar[$ip][2]=$testsnmp;
							$returnar[$ip][3]=$sysdescr;
						}
					}
				}
			}
			//Get host names if requested
			if($_POST['hostnames']){
				foreach($returnar as $ip=>$return){
					//Only get SNMP if the device is up
					if($return[1]=="up"){
						//grep -m only matches the first entry...didn't code for multiple PTR DNS records even though there could be
						$dnslookupstring="host -W 2 -R 1 $ip $dnsserver | grep -m 1 pointer";
						$dns=preg_replace("/\.$/","",shell_exec($dnslookupstring));
						list($junk,$dns)=explode('domain name pointer ',$dns);
						if($dns){
							$returnar[$ip][4]=trim($dns);
						}
					}
				}
			}
			//Get SNMP Serial Numbers if requested
			if($_POST['snmpsernum']){
				foreach($returnar as $ip=>$return){
					//If you don't want the SNMP device name, still need to make sure SNMP works anyways
					if(!$_POST['snmpdevname']){
						$sysdescr=preg_replace('!\s+!',' ',StandardSNMPGet($ip,$snmpversion,$snmpcommstring,"SNMPv2-MIB::sysDescr.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null));
					}
					if(strlen($return[3])>0 || strlen($sysdescr)>0){
						$sernumar=StandardSNMPWalk($ip,$snmpversion,$snmpcommstring,"ENTITY-MIB::entPhysicalSerialNum",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
						$first_key=key($sernumar);
						//Not Mikrotik
						if($first_key<1000 && $first_key){
							$sernumdescar=StandardSNMPWalk($ip,$snmpversion,$snmpcommstring,"ENTITY-MIB::entPhysicalDescr",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
							if(sizeof($sernumar)==1){
								$returnar[$ip][5]=$sernumar[$first_key];
								$returnar[$ip][6]=$sernumdescar[$first_key];
							} else {
								$sernumstr=null;
								unset($sernumdup);
								//Put serial number values and descriptions into arrays
								foreach($sernumar as $tmpid=>$tmpsernum){
									if(sizeof($returnar[$ip][5])==0){
										$returnar[$ip][5]=array(0=>$tmpsernum);
									} else if(!in_array($tmpsernum,$returnar[$ip][5])){
										array_push($returnar[$ip][5],$tmpsernum);
									} else {
										//Keep track of duplicates so the description ignores the duplicates also
										$sernumdup[]=$tmpid;
									}
									if(sizeof($returnar[$ip][6])==0){
										$returnar[$ip][6]=array(0=>$sernumdescar[$tmpid]);
									} else if(!in_array($tmpid,$sernumdup)){
										array_push($returnar[$ip][6],$sernumdescar[$tmpid]);
									}
								}
							}
						//No value
						} else if(!$first_key){
							$returnar[$ip][5]=null;
							$returnar[$ip][6]=null;
						//Mikrotik router so just grab the serial number
						} else {
							$sernum=StandardSNMPWalk($ip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.14988.1.1.7.3.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
							$returnar[$ip][5]=$sernum;
							$returnar[$ip][6]=null;
						}
						if($returnar[$ip][5]==null && $returnar[$ip][6]==null){
							//echo "HELLO<br />\n";
							$nosernum[]=$ip;
						}
					//If device is up and not capable of SNMP and not in the array of devices that aren't SNMP capable
					} else if($returnar[$ip][1]=="up" && !in_array($ip,$downv2)){
						//Device is up but not SNMP capable
						$downv2[]=$ip;
					}
				}
			}
			uksort($returnar, "strnatcasecmp");
			//echo "<pre>"; print_r($returnar); echo "</pre>";
			//Print out results
			if(count($returnar)>0){
				//Headerar used for Excel export
				$headerar[]="IP";
				$headerar[]="Status";
				$dataarstring='$result[0],$result[1]';
				if($_POST['snmpdevname']){
					$headerar[]="SNMP Device Name";
					$headerar[]="SNMP Device Info";
					$dataarstring=$dataarstring . ',$result[2],$result[3]';
				}
				if($_POST['hostnames']){
					$headerar[]="DNS Hostname";
					$dataarstring=$dataarstring . ',$result[4]';
				}
				if($_POST['snmpsernum']){
					$headerar[]="Serial Number(s)";
					$headerar[]="Serial Number Description(s)";
					$dataarstring=$dataarstring . ',$result[5],$result[6]';
				}
				echo "<table border=1>\n";
				echo "<tr>";
				//Print out headerar for table
				foreach($headerar as $header){
					echo "<th>$header</th>";
				}
				echo "</tr>\n";
				//echo "<pre>"; print_r($returnar); echo "</pre>";
				foreach($returnar as $ip=>$result){
					if($result[1]=="down"){
						echo "<tr style=\"color: red;\">";
					} else {
						echo "<tr>";
					}
					echo "<td>" . $result[0] . "</td>";
					echo "<td>" . $result[1] . "</td>";
					if($_POST['snmpdevname']){
						echo "<td>" . $result[2] . "</td>";
						echo "<td style=\"max-width: 400px;\">" . $result[3] . "</td>";
					}
					if($_POST['hostnames']){
						echo "<td>" . $result[4] . "</td>";
					}
					if($_POST['snmpsernum']){
						echo "<td>";
						if(is_array($result[5])){
							foreach($result[5] as $thesernum){
								echo "$thesernum<br />";
							}
						} else {
							echo $result[5];
						}
						echo "</td>";
						echo "<td>";
						if(is_array($result[6])){
							foreach($result[6] as $thesernumdesc){
								echo "$thesernumdesc<br />";
							}
						} else {
							echo $result[6];
						}
						echo "</td>";
					}
					echo "</tr>\n";
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
					 "setTitle"=>"NMAP Scan",
					 "setSubject"=>"NMAP Scan",
					 "setDescription"=>"NMAP Scan",
					 "setKeywords"=>"NMAP Scan",
					 "setCategory"=>"NMAP Scan",
					 "filename"=>"nmapscan.xlsx"
				);
				$_SESSION['excelpropertiesar']=$excelpropertiesar;
				//Export XLSX Button
				if(sizeof($excelar)>0){
					echo "&nbsp;<form action='../excel/multitabletoxls.php' method='post' style='display: inline;'>\n";
					echo "<input type='submit' value='Export to XLSX' />\n";
					echo "</form>\n";
				}
				$time=end_time($time_start);
				if($_POST['snmpdevname']){
					echo "\n<br /><br />NMAP and SNMP completed in {$time}seconds.<br />";
				} else {
					echo "\n<br /><br />NMAP completed in {$time}seconds.<br />";
				}
				//Print out SNMP error messages
				if(count($incorrectv3user)){
					echo "<br /><font style=\"color: red;\"><b>The following IP's are up but the SNMPv3 username you entered is incorrect:</b><br />\n";
					foreach($incorrectv3user as $v3user){
						echo "$v3user<br />\n";
					}
					echo "</font>";
				}
				if(count($incorrectv3auth)){
					echo "<br /><font style=\"color: red;\"><b>The following IP's are up but the SNMPv3 authentication protocol and/or password you entered is incorrect:</b><br />\n";
					foreach($incorrectv3auth as $v3auth){
						echo "$v3auth<br />\n";
					}
					echo "</font>";
				}
				if(count($incorrectv3privproto)){
					echo "<br /><font style=\"color: red;\"><b>The following IP's are up but the SNMPv3 privacy protocol you entered is incorrect:</b><br />\n";
					foreach($incorrectv3privproto as $v3privproto){
						echo "$v3privproto<br />\n";
					}
					echo "</font>";
				}
				if(count($incorrectv3privpass)){
					echo "<br /><font style=\"color: red;\"><b>The following IP's are up but the SNMPv3 privacy password you entered is incorrect, or SNMPv3 is not configured on the device:</b><br />\n";
					foreach($incorrectv3privpass as $v3privpass){
						echo "$v3privpass<br />\n";
					}
					echo "</font>";
				}
				if(count($downv2)){
					echo "<br /><font style=\"color: red;\"><b>The following IP's are up but not responsive to SNMPv2 queries with the RO community string you entered:</b><br />\n";
					foreach($downv2 as $v2){
						echo "$v2<br />\n";
					}
					echo "</font>";
				}
				if(count($nosernum)){
					echo "<br /><font style=\"color: red;\"><b>The following IP's are up and responsive to SNMP queries, but serial number info is not available:</b><br />\n";
					foreach($nosernum as $tmpnoser){
						echo "$tmpnoser<br />\n";
					}
					echo "</font>";
				}
			}
		}
	}
	require("../include/end.php");
?>