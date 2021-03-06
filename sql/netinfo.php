<?php
	/*
	Windows OpenSSL here: http://slproweb.com/products/Win32OpenSSL.html
	Windows SNMP here: http://sourceforge.net/projects/net-snmp/files/net-snmp%20binaries/5.5-binaries/
	Be sure to use an OpenSSL version less than 1.0 for compatibility with encryption support (SNMPv3) in the Net-SNMP tools
	Good IF-MIB reference: http://www.net-snmp.org/docs/mibs/interfaces.html
	Review total bandwidth calculation from this link: https://supportforums.cisco.com/discussion/10126841/snmp-oid-bandwidth-usage
	*/
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	session_start();
	$title="Router/Switch Info";
	require("../include/options/snmpincludes.php");
	//Adjust page width depending on what's selected
	$widthmodset=true;
	$basewidth=1250;
	if($_POST['ciscointpoe']){
		$basewidth+=200;
	}
	if($_POST['ciscointpoedev']){
		$basewidth+=230;
	}
	if($_POST['vlanchooser']){
		$basewidth+=300;
	}
	if($_POST['clientmac']){
		$basewidth+=200;
	}
	if($_POST['macoui']){
		$basewidth+=350;
	}
	if($_POST['clientarp'] && $_POST['ignoredns']){
		$basewidth+=150;
	} else if($_POST['clientarp']){
		$basewidth+=350;
	}
	if($_POST['trafficstats']){
		$basewidth+=200;
	}
	if($_POST['errorsdiscard']){
		$basewidth+=350;
	}
	if($_POST['debugintid']){
		$basewidth+=100;
	}
	if($_POST['cdpname']){
		$basewidth+=300;
	}
	if($_POST['cdpip']){
		$basewidth+=100;
	}
	if($_POST['cdpdev']){
		$basewidth+=230;
	}
	if($_POST['cdpint']){
		$basewidth+=180;
	}
	if($_POST['lldpname']){
		$basewidth+=300;
	}
	if($_POST['lldpip']){
		$basewidth+=100;
	}
	if($_POST['lldpdev']){
		$basewidth+=230;
	}
	if($_POST['lldpint']){
		$basewidth+=180;
	}
	if($_POST['ciscovoicetype']=="cme"){
		$basewidth+=650;
	}
	if($_POST['ciscovoicetype']=="cucm"){
		$basewidth+=850;
	}
	$widthmod=" style=\"min-width: {$basewidth}px;\" ";
	require("../include/header.php");
	require ("../include/functions.php");
	$time_start=microtime_float();
	?>
	<style type='text/css'>
		table.output {
			border-spacing: 3px;
		}
		.output th {
			color: black;
			padding: 2px 4px;
			text-align: left;
			border-width: 1px;
			border-spacing: 4px;
			border-style: outset;
			border-color: gray;
			background-color: #A9A9A9;
		}
		.output td {
			color: black;
			padding: 2px 4px;
			text-align: left;
			border-width: 1px;
			border-spacing: 4px;
			border-style: outset;
			border-color: gray;
			background-color: #DCDCDC;
		}
	</style>
	<br />
	<script type="text/javascript">
		//Check all functions to grey out any boxes
		function checker() {
			disable_enable_commstring();
			ignoredns_changer();
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
		function disable_arp(){
			document.getElementById("showarp").checked = false;
		}
	</script>
	<form method="post" style="display: inline;" name="inputstuff" id="inputstuff">
	<table border=0 style="display: inline-table;">
		<tr>
			<td>Device IP:</td>
			<td><input type="text" name="theip" style="width: 150px; text-align: left;" <?php if($_POST['theip']) echo " value=\"{$_POST['theip']}\"";?> /></td>
		</tr>
		<tr>
			<td>SNMP Version:</td>
			<td>
				<select name="snmpversion" id="snmpversion" onchange="disable_enable_commstring()">
					<option value="2c"<?php if($_POST['snmpversion']=="2c" || (!$_POST['snmpversion'] && $defaultsnmpversion=="2c")) echo " selected";?>>2c</option>
						<option value="3"<?php if($_POST['snmpversion']=="3" || (!$_POST['snmpversion'] && $defaultsnmpversion=="3")) echo " selected";?>>3</option>
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
	<table frame=box style="display: inline-table;">
		<tr>
			<td><b>Options:</b></td>
		</tr>
		<tr>
			<td><input name="vlanchooser" id="vlanchooser" onclick="toggleVLAN()" type="checkbox" <?php if($_POST['vlanchooser']) echo "checked"; ?> />&nbsp;Show VLAN Port assignments</td>
		</tr>
		<tr name="vlanrow" id="vlanrow" <?php if($_POST['vlanchooser']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;
				<table border=0 style="display: inline-table;">
					<tr>
						<td style="text-align: left;">Switch Type:</td>
						<td><input type="radio" name="vlanchoice" id="vlanchoice" onclick="toggleVLANextra(this)" value="cisco"<?php if($_POST['vlanchoice']=="cisco" || (!$_POST['vlanchoice']=="cisco" && $defaultvlanchoice=="cisco")) echo " checked"; ?>>Cisco</td>
						<td><input type="radio" name="vlanchoice" id="vlanchoice" onclick="toggleVLANextra(this)" value="avaya"<?php if($_POST['vlanchoice']=="avaya" || (!$_POST['vlanchoice']=="avaya" && $defaultvlanchoice=="avaya")) echo " checked"; ?>>Avaya</td>
						<td><input type="radio" name="vlanchoice" id="vlanchoice" onclick="toggleVLANextra(this)" value="juniper"<?php if($_POST['vlanchoice']=="juniper" || (!$_POST['vlanchoice']=="juniper" && $defaultvlanchoice=="juniper")) echo " checked"; ?>>Juniper</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><input type="radio" name="vlanchoice" id="vlanchoice" onclick="toggleVLANextra(this)" value="netgear"<?php if($_POST['vlanchoice']=="netgear" || (!$_POST['vlanchoice']=="netgear" && $defaultvlanchoice=="netgear")) echo " checked"; ?>>Netgear</td>
						<td><input type="radio" name="vlanchoice" id="vlanchoice" onclick="toggleVLANextra(this)" value="h3c"<?php if($_POST['vlanchoice']=="h3c" || (!$_POST['vlanchoice']=="h3c" && $defaultvlanchoice=="h3c")) echo " checked"; ?>>H3C</td>
						<td>&nbsp;</td>
					</tr>
				</table><br />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="vlanextra" id="vlanextra" type="checkbox" <?php if($_POST['vlanextra']) echo "checked"; if($_POST['vlanchoice']!="cisco" && $_POST['vlanchoice']!="avaya" && ($defaultvlanchoice!="avaya" && $defaultvlanchoice!="cisco" && !$_POST['vlanchoice'])) echo " disabled=\"disabled\""; ?> />&nbsp;Show Extra VLAN Info</td>
			</td>
		</tr>
		<script type="text/javascript">
			function toggleVLAN(vlanchoice) {
				if (document.getElementById("vlanrow").style.display=="none") {
					document.getElementById("vlanrow").style.display="table-row";
				} else {
					document.getElementById("vlanrow").style.display="none";
					//document.getElementById("vlanextra").checked = false;
				}
			}
		</script>
		<script type="text/javascript">
			function toggleVLANextra(selected) {
				if (selected.value=="cisco" || selected.value=="avaya"){
					document.getElementById("vlanextra").removeAttribute("disabled");
					//alert('Cisco selected: ' + selected.value);
				} else {
					document.getElementById("vlanextra").setAttribute("disabled","disabled");
					//alert('Something else selected: ' + selected.value);
					document.getElementById("vlanextra").checked = false;
				}
			}
		</script>
		<tr>
			<td><input name="clientmac" id="clientmac" type="checkbox" onchange="disable_clientarp()" <?php if($_POST['clientmac'] || $_POST['clientarp']) echo "checked"; ?> />&nbsp;Show client MAC addresses</td>
		</tr>
		<tr name="macstandardrow" id="macstandardrow" <?php if($_POST['clientmac']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="macchoice" id="macchoice" value="standard" <?php if($_POST['macchoice']=="standard") echo "checked"; ?>>&nbsp;Standard Method <i>(MAC Only)</i></td>
		</tr>
		<tr name="macaltrow" id="macaltrow" <?php if($_POST['clientmac']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="macchoice" id="macchoice" value="alt" <?php if($_POST['macchoice']=="alt" || !$_POST['macchoice']) echo "checked"; ?>>&nbsp;Alternative Method <i>(MAC & VLAN)</i></td>
		</tr>
		<tr name="macciscorow" id="macciscorow" <?php if($_POST['clientmac']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="macchoice" id="macchoice" value="cisco" <?php if($_POST['macchoice']=="cisco") echo "checked"; ?>>&nbsp;Cisco Method (Requirements <button style="background: none; border: none; padding: 0; margin: 0px -3px 0px -2px; color: blue; cursor: pointer; font-size: 1em;" onclick="CiscoRequirements()">here</button>)</td>
		</tr>
		<tr name="macextremerow" id="macextremerow" <?php if($_POST['clientmac']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="macchoice" id="macchoice" value="extreme" <?php if($_POST['macchoice']=="extreme") echo "checked"; ?>>&nbsp;Extreme Switches <i>(MAC & VLAN)</i></td>
		</tr>
		<tr name="macouirow" id="macouirow" <?php if($_POST['clientmac']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;
			<input name="macoui" id="macoui" type="checkbox" <?php if($_POST['macoui']) echo "checked"; ?> />&nbsp;Show MAC address OUI Info</td>
		</tr>
		<script type="text/javascript">
			function disable_clientarp() {
				if (document.getElementById("clientmac").checked==false) {
					document.getElementById("macstandardrow").style.display="none";
					document.getElementById("macaltrow").style.display="none";
					document.getElementById("macciscorow").style.display="none";
					document.getElementById("macextremerow").style.display="none";
					document.getElementById("macouirow").style.display="none";
					document.getElementById("macoui").checked = false;
					document.getElementById("routeriprow").style.display="none";
					document.getElementById("clientarp").checked = false;
				} else {
					document.getElementById("macstandardrow").style.display="table-row";
					document.getElementById("macaltrow").style.display="table-row";
					document.getElementById("macciscorow").style.display="table-row";
					document.getElementById("macextremerow").style.display="table-row";
					document.getElementById("macouirow").style.display="table-row";
				}
			}
			function CiscoRequirements() {
				alert("### SNMPv2 ###\n\nNo requirements\n\n### SNMPv3 ###\n\nsnmp-server group snmp_admin v3 priv\nsnmp-server group snmp_admin v3 auth context vlan- match prefix\nsnmp-server user snmpadmin snmp_admin v3 auth sha MYAUTHPASS priv aes 128 MYPRIVPASS");
			}
		</script>
		<tr>
			<td><input name="clientarp" id="clientarp" type="checkbox" onclick="toggleRouterIP()" <?php if($_POST['clientarp']) echo "checked"; ?> />&nbsp;Show IP addresses and host names</td>
		</tr>
		<tr name="routeriprow" id="routeriprow" <?php if($_POST['clientarp']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
			<td>&nbsp;&nbsp;
			<table border=0 style="display: inline-table;">
				<tr>
					<td colspan="2"><input type="radio" name="arpchoice" id="arpchoice" value="nmap" onclick="toggleARPSourceInput()" <?php if($_POST['arpchoice']=="nmap") echo "checked"; ?> />ARP from NMAP (Local interface on this server)</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;&nbsp;&nbsp;Interface:&nbsp;
						<select name="nmaparp" id="nmaparp"<?php if($_POST['arpchoice']=="snmp" || !$_POST['arpchoice']) echo " disabled"; ?>>
							<?php
									//Function to convert CIDR (255.255.252.0) to Netmask (/22)
									function netmask2cidr($netmask){
										$bits = 0;
										$netmask = explode(".", $netmask);
										foreach($netmask as $octect)
										$bits += strlen(str_replace("0", "", decbin($octect)));
										return $bits;
									}
									$ifconfstring="ifconfig | grep -e \"Link encap\" -e \"inet addr\"";
									$ifconf=shell_exec($ifconfstring);
									$ifconftempar=preg_split('/\n/',$ifconf);
									$last="";
									foreach($ifconftempar as $ifconfline){
										if(strstr($ifconfline,'Link encap')){
											list($intname,$remain)=explode(' ',trim($ifconfline),2);
											//Remember the interface name
											$last="$intname";
										} else if(strstr($ifconfline,'Bcast')){
											$ifconfline=trim(str_replace('inet addr:','',$ifconfline));
											$ifconfline=str_replace('Bcast:','',$ifconfline);
											$ifconfline=str_replace('Mask:','',$ifconfline);
											//Replace multiple white spaces with a single white space
											$ifconfline=preg_replace('!\s+!',' ', $ifconfline);
											list($intip,$intbcast,$intmask)=explode(' ',$ifconfline,3);
											$intmask=netmask2cidr($intmask);
											//Store the interface name with IP info in an array
											$ifconfar[$last]="$intip/$intmask";
										}
									}
									//Loop through each interface with IP info - Used to feed NMAP IP info
									foreach($ifconfar as $intname=>$value){
										echo "<option value=\"$value\""; if($_POST['nmaparp']=="$value") echo " selected"; echo ">$intname: $value</option>\n";
									}
									echo "<pre>"; print_r($ifconfar); echo "</pre>";
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td><input type="radio" name="arpchoice" id="arpchoice" value="snmp" onclick="toggleARPSourceInput2()" <?php if($_POST['arpchoice']=="snmp" || !$_POST['arpchoice']) echo "checked"; ?> />ARP from Router:</td>
					<td><input type="text" name="routerip" id="routerip" style="width: 150px; text-align: left;" <?php if($_POST['routerip']){ echo " value=\"{$_POST['routerip']}\""; } else { echo " value=\"$defaultrouterip\""; } if($_POST['arpchoice']=="nmap") echo " disabled"; ?> /></td>
				</tr>
				<script type="text/javascript">
				function toggleARPSourceInput() {
					document.getElementById("routerip").disabled=true;
					document.getElementById("nmaparp").disabled=false;
				}
				function toggleARPSourceInput2() {
					document.getElementById("routerip").disabled=false;
					document.getElementById("nmaparp").disabled=true;
				}
				</script>
				<tr>
					<td>DNS Server:</td>
					<td><input type="text" name="dnsserver" id="dnsserver" style="width: 150px; text-align: left;" <?php if($_POST['dnsserver']){ echo " value=\"{$_POST['dnsserver']}\""; } else if(!$_POST['ignoredns'] || $_POST['routerip']) { echo " value=\"$defaultdnsserver\""; }?> /></td>
				</tr>
				<tr>
					<td colspan="2"><input name="showarp" id="showarp" type="checkbox" <?php if($_POST['showarp']) echo "checked"; ?> />&nbsp;Show ARP table</td>
				</tr>
				<tr>
					<td colspan="2"><input name="ignoredns" id="ignoredns" type="checkbox" onchange="ignoredns_changer()" <?php if($_POST['ignoredns']) echo "checked"; ?> />&nbsp;Ignore DNS (Can reduce script run time)</td>
				</tr>
				<script type="text/javascript">
				function ignoredns_changer() {
					if (document.getElementById("ignoredns").checked==true) {
						document.getElementById("dnsserver").setAttribute("disabled","disabled");
					} else {
						document.getElementById("dnsserver").removeAttribute("disabled");
					}
				}
				</script>
			</table>
			</td>
		</tr>
		<script type="text/javascript">
			function toggleRouterIP() {
				if (document.getElementById("routeriprow").style.display=="none") {
					document.getElementById("clientmac").checked = true;
					document.getElementById("macstandardrow").style.display="table-row";
					document.getElementById("macaltrow").style.display="table-row";
					document.getElementById("macciscorow").style.display="table-row";
					document.getElementById("macextremerow").style.display="table-row";
					document.getElementById("macouirow").style.display="table-row";
					document.getElementById("routeriprow").style.display="table-row";
				} else {
					document.getElementById("routeriprow").style.display="none";
					disable_arp();
				}
			}
		</script>
		<tr>
			<td><input name="statsrow" id="statsrow" type="checkbox" onclick="toggleStatsRow()" <?php if($_POST['statsrow']) echo "checked"; ?> />&nbsp;Show Interface Stats</td>
		</tr>
		<tr name="statsrowextra" id="statsrowextra" <?php if($_POST['statsrow']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
			<td>&nbsp;&nbsp;
				<table border=0 style="display: inline-table;">
					<tr>
						<td><input name="trafficstats" id="trafficstats" type="checkbox" <?php if($_POST['trafficstats']) echo "checked"; ?> />&nbsp;Total Bandwidth</td>
						<td><input name="errorsdiscard" id="errorsdiscard" type="checkbox" <?php if($_POST['errorsdiscard']) echo "checked"; ?> />&nbsp;Errors and Discards</td>
					</tr>
					<tr>
						<td colspan="2"><input name="ciscopps" id="ciscopps" type="checkbox" <?php if($_POST['ciscopps']) echo "checked"; ?> />&nbsp;Cisco PPS (5 min average)</td>
					</tr>
					<tr>
						<td colspan="2"><input name="ciscoinoutrate" id="ciscoinoutrate" type="checkbox" <?php if($_POST['ciscoinoutrate']) echo "checked"; ?> />&nbsp;Cisco Input/Output Rate (5 min average)</td>
					</tr>
				</table>
			</td>
		</tr>
		<script type="text/javascript">
			function toggleStatsRow() {
				if (document.getElementById("statsrowextra").style.display=="none") {
					document.getElementById("statsrowextra").style.display="table-row";
				} else {
					document.getElementById("statsrowextra").style.display="none";
					document.getElementById("trafficstats").checked = false;
					document.getElementById("errorsdiscard").checked = false;
					document.getElementById("ciscopps").checked = false;
					document.getElementById("ciscoinoutrate").checked = false;
				}
			}
		</script>
		<tr>
			<td><input name="hidecolumns" id="hidecolumns" type="checkbox" onclick="toggleHideColumns()" <?php if($_POST['hidecolumns']) echo "checked"; ?> />&nbsp;Hide Output Columns</td>
		</tr>
		<tr name="hidecolumnsextra" id="hidecolumnsextra" <?php if($_POST['hidecolumns']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
			<td>&nbsp;&nbsp;
				<table border=0 style="display: inline-table;">
					<tr>
						<td><input name="hidealias" id="hidealias" type="checkbox" <?php if($_POST['hidealias']) echo "checked"; ?> />&nbsp;Alias</td>
						<td><input name="hideadminstatus" id="hideadminstatus" type="checkbox" <?php if($_POST['hideadminstatus']) echo "checked"; ?> />&nbsp;Admin Status</td>
						<td><input name="hideopstatus" id="hideopstatus" type="checkbox" <?php if($_POST['hideopstatus']) echo "checked"; ?> />&nbsp;Operational Status</td>
					</tr>
					<tr>
						<td><input name="hidespeed" id="hidespeed" type="checkbox" <?php if($_POST['hidespeed']) echo "checked"; ?> />&nbsp;Speed</td>
						<td><input name="hideduplex" id="hideduplex" type="checkbox" <?php if($_POST['hideduplex']) echo "checked"; ?> />&nbsp;Duplex</td>
						<td>&nbsp;</td>
					</tr>
				</table>
			</td>
		</tr>
		<script type="text/javascript">
			function toggleHideColumns() {
				if (document.getElementById("hidecolumnsextra").style.display=="none") {
					document.getElementById("hidecolumnsextra").style.display="table-row";
				} else {
					document.getElementById("hidecolumnsextra").style.display="none";
					document.getElementById("hidealias").checked = false;
					document.getElementById("hideadminstatus").checked = false;
					document.getElementById("hideopstatus").checked = false;
					document.getElementById("hidespeed").checked = false;
					document.getElementById("hideduplex").checked = false;
				}
			}
		</script>
		
		<tr>
			<td><input name="hideextra" id="hideextra" type="checkbox" onclick="toggleHideExtra()" <?php if($_POST['hideextra']) echo "checked"; ?> />&nbsp;Hide Extra Output</td>
		</tr>
		<tr name="hidecolumnsextraextra" id="hidecolumnsextraextra" <?php if($_POST['hideextra']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
			<td>&nbsp;&nbsp;
				<table border=0 style="display: inline-table;">
					<tr>
						<td><input name="hidenull" id="hidenull" type="checkbox" <?php if($_POST['hidenull']) echo "checked"; ?> />&nbsp;Null Interfaces</td>
						<td><input name="hidestackports" id="hidestackports" type="checkbox" <?php if($_POST['hidestackports']) echo "checked"; ?> />&nbsp;Stack Ports</td>
						<td><input name="hidevlanint" id="hidevlanint" type="checkbox" <?php if($_POST['hidevlanint']) echo "checked"; ?> />&nbsp;VLAN Interfaces</td>
					</tr>
					<tr>
						<td><input name="hidevr" id="hidevr" type="checkbox" <?php if($_POST['hidevr']) echo "checked"; ?> />&nbsp;Virtual Router</td>
						<td colspan="2"><input name="hidemgt" id="hidemgt" type="checkbox" <?php if($_POST['hidemgt']) echo "checked"; ?> />&nbsp;Management Ports</td>
					</tr>
					<tr>
						<td colspan="3"><input name="hidertif" id="hidertif" type="checkbox" <?php if($_POST['hidertif']) echo "checked"; ?> />&nbsp;Extreme rtif ports</td>
					</tr>
					<tr>
						<td colspan="3"><input name="hideintid" id="hideintid" type="checkbox" <?php if($_POST['hideintid']) echo "checked"; ?> />&nbsp;SNMP Interface ID's (CSV's):&nbsp;&nbsp;<input type="text" name="hideintidval" id="hideintidval" style="width: 100px; text-align: left;" <?php if($_POST['hideintid'] && $_POST['hideintidval']) echo " value=\"{$_POST['hideintidval']}\""; ?> /></td>
					</tr>
					<tr>
						<td colspan="3"><input name="hidemacciscotrunk" id="hidemacciscotrunk" type="checkbox" <?php if($_POST['hidemacciscotrunk']) echo "checked"; ?> />&nbsp;MAC's for Cisco Trunks</td>
					</tr>
					<tr>
						<td colspan="3"><input name="hidemacintid" id="hidemacintid" type="checkbox" onclick="toggleClearCSVList()" <?php if($_POST['hidemacintid']) echo "checked"; ?> />&nbsp;MAC's for SNMP Interface ID's (CSV's):&nbsp;&nbsp;<input type="text" name="hidemacintidval" id="hidemacintidval" style="width: 100px; text-align: left;" <?php if($_POST['hidemacintid'] && $_POST['hidemacintidval']) echo " value=\"{$_POST['hidemacintidval']}\""; ?> /></td>
					</tr>
				</table>
			</td>
		</tr>
		<script type="text/javascript">
			function toggleHideExtra() {
				if (document.getElementById("hidecolumnsextraextra").style.display=="none") {
					document.getElementById("hidecolumnsextraextra").style.display="table-row";
				} else {
					document.getElementById("hidecolumnsextraextra").style.display="none";
					document.getElementById("hidenull").checked = false;
					document.getElementById("hidestackports").checked = false;
					document.getElementById("hidevlanint").checked = false;
					document.getElementById("hidevr").checked = false;
					document.getElementById("hidemgt").checked = false;
					document.getElementById("hidemacciscotrunk").checked = false;
					document.getElementById("hidemacintid").checked = false;
					document.getElementById("hidemacintidval").value = null;
				}
			}
			function toggleClearCSVList() {
				document.getElementById("hidemacintidval").value = null;
			}
		</script>
		
		<tr>
			<td><input name="debug" id="debug" type="checkbox" onclick="toggleDebug()" <?php if($_POST['debug']) echo "checked"; ?> />&nbsp;Debug Mode</td>
		</tr>
		<tr name="debugextra" id="debugextra" <?php if($_POST['debug']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
			<td>
				&nbsp;&nbsp;&nbsp;&nbsp;<input name="debugcommands" id="debugcommands" type="checkbox" <?php if($_POST['debugcommands']) echo "checked"; ?> />&nbsp;<font style="color: purple;">Commands</font>
				&nbsp;&nbsp;<input name="debugoutput" id="debugoutput" type="checkbox" <?php if($_POST['debugoutput']) echo "checked"; ?> />&nbsp;<font style="color: red;">Output</font>
				&nbsp;&nbsp;<input name="debugintid" id="debugintid" type="checkbox" <?php if($_POST['debugintid']) echo "checked"; ?> />&nbsp;<font style="color: #008000;">Show Interface ID's</font>
			</td>
		</tr>
		<script type="text/javascript">
			function toggleDebug() {
				if (document.getElementById("debugextra").style.display=="none") {
					document.getElementById("debugextra").style.display="table-row";
				} else {
					document.getElementById("debugextra").style.display="none";
					document.getElementById("debugcommands").checked = false;
					document.getElementById("debugoutput").checked = false;
					document.getElementById("debugintid").checked = false;
				}
			}
		</script>
		<tr>
			<td><input name="addfeatures" id="addfeatures" type="checkbox" onclick="toggleAddFeatures()" <?php if($_POST['addfeatures']) echo "checked"; ?> />&nbsp;Additional Features</td>
		</tr>
		<tr name="addfeaturesextra" id="addfeaturesextra" <?php if($_POST['addfeatures']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
			<td>&nbsp;&nbsp;
				<table border=0 style="display: inline-table;">
					<tr>
						<td><input name="ciscointpoe" id="ciscointpoe" type="checkbox" <?php if($_POST['ciscointpoe']) echo "checked"; ?> />&nbsp;Cisco Interface PoE Stats</td>
						<td><input name="ciscointpoedev" id="ciscointpoedev" type="checkbox" <?php if($_POST['ciscointpoedev']) echo "checked"; ?> />&nbsp;Cisco Interface PoE Device</td>
					</tr>
					<tr>
						<td><input name="cdpname" id="cdpname" type="checkbox" <?php if($_POST['cdpname']) echo "checked"; ?> />&nbsp;CDP Name</td>
						<td><input name="cdpip" id="cdpip" type="checkbox" <?php if($_POST['cdpip']) echo "checked"; ?> />&nbsp;CDP IP</td>
					</tr>
					<tr>
						<td><input name="cdpdev" id="cdpdev" type="checkbox" <?php if($_POST['cdpdev']) echo "checked"; ?> />&nbsp;CDP Device</td>
						<td><input name="cdpint" id="cdpint" type="checkbox" <?php if($_POST['cdpint']) echo "checked"; ?> />&nbsp;CDP Remote Interface</td>
					</tr>
					<tr>
						<td><input name="lldpname" id="lldpname" type="checkbox" <?php if($_POST['lldpname']) echo "checked"; ?> />&nbsp;LLDP Name</td>
						<td><input name="lldpip" id="lldpip" type="checkbox" <?php if($_POST['lldpip']) echo "checked"; ?> />&nbsp;LLDP MAC/IP (Limited support)</td>
					</tr>
					<tr>
						<td><input name="lldpdev" id="lldpdev" type="checkbox" <?php if($_POST['lldpdev']) echo "checked"; ?> />&nbsp;LLDP Device</td>
						<td><input name="lldpint" id="lldpint" type="checkbox" <?php if($_POST['lldpint']) echo "checked"; ?> />&nbsp;LLDP Remote Interface</td>
					</tr>
					<tr>
						<td colspan="2"><input name="edpdev" id="edpdev" type="checkbox" <?php if($_POST['edpdev']) echo "checked"; ?> />&nbsp;Extreme EDP Remote Device</td>
					</tr>
					<tr>
						<td colspan="2"><input name="edpint" id="edpint" type="checkbox" <?php if($_POST['edpint']) echo "checked"; ?> />&nbsp;Extreme EDP Remote Interface</td>
					</tr>
					<tr>
						<td colspan="2"><input name="exportfileformatrow" id="exportfileformatrow" type="checkbox" onclick="toggleFileFormats()" <?php if($_POST['exportfileformatrow']) echo "checked"; ?> />&nbsp;Adjust export file format</td>
					</tr>
					<tr name="exportfileformatrowextra" id="exportfileformatrowextra" <?php if($_POST['exportfileformatrow']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
						<td colspan="2">&nbsp;&nbsp;
							<table border=0 style="display: inline-table;">
								<tr>
									<td><input type="radio" name="exportfileformatchoice" id="exportfileformatchoice" value="ipname" <?php if($_POST['exportfileformatchoice']=="ipname" || !$_POST['exportfileformatchoice']) echo "checked"; ?>>"&#60;ip&#62; -  &#60;name&#62; - Network Info"</td>
								</tr>
								<tr>
									<td><input type="radio" name="exportfileformatchoice" id="exportfileformatchoice" value="nameip" <?php if($_POST['exportfileformatchoice']=="nameip") echo "checked"; ?>>"&#60;name&#62; -  &#60;ip&#62; - Network Info"</td>
								</tr>
								<tr>
									<td><input type="radio" name="exportfileformatchoice" id="exportfileformatchoice" value="custom" <?php if($_POST['exportfileformatchoice']=="custom") echo "checked"; ?>>Custom:&nbsp;&nbsp;<input type="text" name="customfilename" id="customfilename" style="width: 150px; text-align: left;" <?php if($_POST['customfilename']){ echo " value=\"{$_POST['customfilename']}\""; } else { echo " value=\"<ip>_<name>_Network-Info\""; } ?> /></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<script type="text/javascript">
			function toggleAddFeatures() {
				if (document.getElementById("addfeaturesextra").style.display=="none") {
					document.getElementById("addfeaturesextra").style.display="table-row";
				} else {
					document.getElementById("addfeaturesextra").style.display="none";
					document.getElementById("exportfileformatrowextra").style.display="none";
					document.getElementById("ciscointpoe").checked = false;
					document.getElementById("ciscointpoedev").checked = false;
					document.getElementById("cdpname").checked = false;
					document.getElementById("cdpip").checked = false;
					document.getElementById("cdpdev").checked = false;
					document.getElementById("cdpint").checked = false;
					document.getElementById("lldpname").checked = false;
					document.getElementById("lldpip").checked = false;
					document.getElementById("lldpdev").checked = false;
					document.getElementById("lldpint").checked = false;
					document.getElementById("edpdev").checked = false;
					document.getElementById("edpint").checked = false;
					document.getElementById("exportfileformatrow").checked = false;
					document.getElementById("ciscovoice").checked = false;
					document.getElementById("ciscovoiceextra").style.display="none";
					document.getElementById("ciscovoicesnmpbox").checked = false;
					document.getElementById("ciscovoicesnmpcomm").setAttribute("disabled","disabled");
				}
			}
			function toggleFileFormats() {
				if (document.getElementById("exportfileformatrowextra").style.display=="none") {
					document.getElementById("exportfileformatrowextra").style.display="table-row";
				} else {
					document.getElementById("exportfileformatrowextra").style.display="none";
				}
			}
		</script>
		<tr>
			<td><input name="ciscovoice" id="ciscovoice" type="checkbox" onclick="toggleCiscoVoice()" <?php if($_POST['ciscovoice']) echo "checked"; ?> />&nbsp;Cisco Voice (<i><b>MUST</b> show LLDP or CDP Name</i>)</td>
		</tr>
		<tr name="ciscovoiceextra" id="ciscovoiceextra" <?php if($_POST['ciscovoice']){ echo "style=\"display: table-row;\""; } else { echo "style=\"display: none;\""; } ?>>
			<td>&nbsp;&nbsp;
				<table border=0 style="display: inline-table;">
					<tr>
						<td>&nbsp;&nbsp;CME/CUCM IP address:&nbsp;<input type="text" name="ciscovoiceip" id="ciscovoiceip" style="width: 100px; text-align: left;" <?php if($_POST['ciscovoiceip']) echo " value=\"{$_POST['ciscovoiceip']}\""; ?> /></td>
					</tr>
					<tr>
						<td><input type="radio" name="ciscovoicetype" id="ciscovoicetype" value="cme" <?php if($_POST['ciscovoicetype']=="cme") echo "checked"; ?>>&nbsp;CME</td>
					</tr>
					<tr>
						<td><input type="radio" name="ciscovoicetype" id="ciscovoicetype" value="cucm" <?php if($_POST['ciscovoicetype']=="cucm" || !$_POST['ciscovoicetype']) echo "checked"; ?>>&nbsp;CUCM</td>
					</tr>
					<tr>
						<td><input name="ciscovoicesnmpbox" id="ciscovoicesnmpbox" type="checkbox" onclick="toggleCiscoSNMP()" <?php if($_POST['ciscovoicesnmpbox']) echo "checked"; ?> />&nbsp;Alternate SNMPv2 Community:&nbsp;&nbsp;<input type="text" name="ciscovoicesnmpcomm" id="ciscovoicesnmpcomm" style="width: 100px; text-align: left;" <?php if($_POST['ciscovoicesnmpcomm']) echo " value=\"{$_POST['ciscovoicesnmpcomm']}\""; if(!$_POST['ciscovoicesnmpbox']) echo " disabled"; ?> /></td>
					</tr>
				</table>
			</td>
		</tr>
		<script type="text/javascript">
			function toggleCiscoVoice() {
				if (document.getElementById("ciscovoiceextra").style.display=="none") {
					document.getElementById("addfeatures").checked = true;
					document.getElementById("addfeaturesextra").style.display="table-row";
					document.getElementById("ciscovoiceextra").style.display="table-row";
				} else {
					document.getElementById("ciscovoiceextra").style.display="none";
					document.getElementById("ciscovoicesnmpbox").checked = false;
					document.getElementById("ciscovoicesnmpcomm").setAttribute("disabled","disabled");
				}
			}
			function toggleCiscoSNMP() {
				if (document.getElementById("ciscovoicesnmpbox").checked==true) {
					document.getElementById("ciscovoicesnmpcomm").removeAttribute("disabled");
				} else {
					document.getElementById("ciscovoicesnmpcomm").setAttribute("disabled","disabled");
				}
			}
		</script>
	</table>
	</form><br />
	<!-- Default cursor location -->
	<script type="text/javascript">
		document.inputstuff.theip.focus();
	</script>
	
	<?php
	session_start();
	function HexToBin($hexin){
		//Loop through each hex character and convert to binary
		$chars = str_split($hexin);
		foreach($chars as $char){
			$char=decbin(hexdec($char));
			//Insert leading zero's if needed
			if(strlen($char)==1){
				$bin=$bin . "000" . $char;
			} else if(strlen($char)==2){
				$bin=$bin . "00" . $char;
			} else if(strlen($char)==3){
				$bin=$bin . "0" . $char;
			} else if(strlen($char)==4){
				$bin=$bin . $char;
			}		
		}
		return $bin;
	}
	function HexToASCII($instr){
		$p='';
		for ($i=0; $i < strlen($instr); $i=$i+2){
			$p.= chr(hexdec(substr($instr,$i,2)));
		}
		return $p;
	}
	
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
		if($_POST['debug'] && $_POST['debugcommands']){
			echo "<font style=\"color: purple;\"><b>COMMAND:</b> $command</font><br />";
		}
		return preg_replace('/"/','',shell_exec($command));
	}
	function StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,$commandstring,$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,$invlan){
		if($snmpversion=="2c"){
			$versioncmd="-c $snmpcommstring";
		} else if($snmpversion=="3" && $snmpv3seclevel=="authPriv"){
			$versioncmd="-u $snmpv3user -a $snmpv3authproto -A $snmpv3authpass -l $snmpv3seclevel -x $snmpv3privproto -X $snmpv3privpass";
		} else if($snmpversion=="3" && $snmpv3seclevel=="authNoPriv"){
			$versioncmd="-u $snmpv3user -a $snmpv3authproto -A $snmpv3authpass -l $snmpv3seclevel";
		}
		if(strstr($commandstring,'Q-BRIDGE-MIB::dot1qTpFdbPort')){
			$command="snmpbulkwalk -r 1 -L n -v $snmpversion $versioncmd -O Xsq $theip $commandstring";
		} else if(strstr($commandstring,'1.0.8802.1.1.2.1.4.1.1.9')){
			$command="snmpbulkwalk -r 1 -L n -v $snmpversion $versioncmd $theip $commandstring";
		} else if(strstr($commandstring,'BRIDGE-MIB::dot1dTpFdbPort') || strstr($commandstring,'1.3.6.1.2.1.17.1.4.1.2')){
			if($snmpversion=="2c"){
				$command="snmpbulkwalk -r 1 -L n -v $snmpversion $versioncmd@$invlan -O Xsq $theip $commandstring";
			} else if($snmpversion=="3"){
				$command="snmpbulkwalk -r 1 -L n -v $snmpversion $versioncmd -n vlan-$invlan -O Xsq $theip $commandstring";
			}
		} else {
			$command="snmpbulkwalk -r 1 -L n -v $snmpversion $versioncmd -O sq $theip $commandstring";
		}
		if($_POST['debug'] && $_POST['debugcommands']){
			echo "<font style=\"color: purple;\"><b>COMMAND:</b> $command</font><br />";
		}
		$walkresult=preg_split('/\n/',shell_exec($command));
		//Needed for H3C VLAN Hex
		$h3cidcnt=0; $h3clast=0; $vlanidtmp=""; $vlanhextmp=""; $rowcounter=0;
		foreach($walkresult as $snmpval){
			if($snmpval){
				//Handle several MIBS that have interface ID at the end of the MIB, then a space, then the value
				/*
				SNMPv2-SMI::transmission.7.2.1.19 			- Interface duplex
				SNMPv2-SMI::transmission.7.2.1.7 			- Interface duplex alternative method
				SNMPv2-SMI::enterprises.9.9.68.1.2.2.1.2 	- Cisco VLAN
				SNMPv2-SMI::enterprises.9.9.46.1.3.1.1.2.1	- Cisco VLAN status
				SNMPv2-SMI::enterprises.2272.1.3.3.1.7 		- Avaya VLAN port PVID
				SNMPv2-SMI::enterprises.2272.1.3.3.1.4 		- Avaya VLAN port tagging
				SNMPv2-SMI::enterprises.2272.1.3.3.1.3 		- Avaya VLAN port members
				SNMPv2-SMI::enterprises.2272.1.3.2.1.2		- Avaya VLAN names
				SNMPv2-SMI::enterprises.2272.1.3.2.1.6		- Avaya VLAN Index ID
				1.3.6.1.4.1.2636.3.40.1.5.1.5.1.5			- Juniper VLAN ID's
				1.3.6.1.4.1.2636.3.40.1.5.1.7.1.5			- Juniper VLAN port mode
				1.3.6.1.2.1.47.1.1.1.1.14					- Cisco interface alias
				SNMPv2-SMI::enterprises.9.9.402.1.2.1.11.x	- Cisco Interface PoE ID's
				SNMPv2-SMI::enterprises.9.9.402.1.3.1.1		- Cisco PoE Switch Numbers
				SNMPv2-SMI::mib-2.47.1.1.1.1.7				- Cisco Interface PoE ID's (For translation to another ID table)...come on Cisco!
				1.3.6.1.4.1.9.2.2.1.1.7						- Cisco PPS in (5 min average)
				1.3.6.1.4.1.9.2.2.1.1.9						- Cisco PPS out (5 min average)
				1.3.6.1.4.1.9.2.2.1.1.6						- Cisco input rate (5 minute average)
				1.3.6.1.4.1.9.2.2.1.1.8						- Cisco output rate (5 minute average)
				1.3.6.1.4.1.1916.1.2.1.2.1.10				- Extreme VLAN ID for MAC address table
				1.3.6.1.4.1.9.9.439.1.2.6.1.1				- Cisco Voice CME SEP ID's
				1.3.6.1.4.1.9.9.439.1.1.43.1.3				- Cisco Voice CME phone IP's
				1.3.6.1.4.1.9.9.439.1.1.43.1.5				- Cisco Voice CME phone models
				1.3.6.1.4.1.9.9.439.1.2.6.1.4				- Cisco Voice CME phone status
				1.3.6.1.4.1.9.9.439.1.1.47.1.4				- Cisco Voice CME DN
				1.3.6.1.4.1.9.9.439.1.1.47.1.7				- Cisco Voice CME labels
				1.3.6.1.4.1.9.9.439.1.1.47.1.6				- Cisco Voice CME names
				1.3.6.1.4.1.9.9.156.1.2.1.1.20				- Cisco Voice CUCM SEP ID's
				1.3.6.1.4.1.9.9.156.1.2.1.1.6				- Cisco Voice CUCM phone IP's
				1.3.6.1.4.1.9.9.156.1.2.1.1.18				- Cisco Voice CUCM phone model ID
				1.3.6.1.4.1.9.9.156.1.1.8.1.3				- Cisco Voice CUCM phone device ID
				1.3.6.1.4.1.9.9.156.1.2.1.1.19				- Cisco Voice CUCM phone protocol
				1.3.6.1.4.1.9.9.156.1.2.1.1.4				- Cisco Voice CUCM phone description
				1.3.6.1.4.1.9.9.156.1.2.1.1.7				- Cisco Voice CUCM phone status
				1.3.6.1.4.1.9.9.156.1.2.1.1.5				- Cisco Voice CUCM phone username
				*/
				if($snmpval && ($commandstring=="SNMPv2-SMI::transmission.7.2.1.19" || $commandstring=="SNMPv2-SMI::transmission.7.2.1.7" || $commandstring=="SNMPv2-SMI::enterprises.9.9.68.1.2.2.1.2" || strstr($commandstring,'SNMPv2-SMI::enterprises.2272.1.3.3.1') || $commandstring=="SNMPv2-SMI::enterprises.9.9.46.1.6.1.1.13" || $commandstring=="SNMPv2-SMI::enterprises.9.9.46.1.6.1.1.14" || $commandstring=="SNMPv2-SMI::mib-2.17.1.4.1.2" || $commandstring=="1.3.6.1.4.1.2636.3.40.1.5.1.5.1.5" || $commandstring=="1.3.6.1.4.1.2636.3.40.1.5.1.7.1.5" || $commandstring=="SNMPv2-SMI::enterprises.9.9.46.1.3.1.1.4.1" || $commandstring=="SNMPv2-SMI::enterprises.9.9.46.1.3.1.1.2.1" || $commandstring=="SNMPv2-SMI::enterprises.2272.1.3.2.1.2") || $commandstring=="SNMPv2-SMI::enterprises.2272.1.3.2.1.6" || $commandstring=="1.3.6.1.2.1.47.1.1.1.1.14" || strstr($commandstring,'SNMPv2-SMI::mib-2.105.1.1.1.9.') || strstr($commandstring,'SNMPv2-SMI::enterprises.9.9.402.1.2.1.11.') || strstr($commandstring,'SNMPv2-SMI::enterprises.9.9.402.1.2.1.8.') || strstr($commandstring,'SNMPv2-SMI::enterprises.9.9.402.1.2.1.10.') || $commandstring=="SNMPv2-SMI::enterprises.9.9.402.1.3.1.1" || $commandstring=="SNMPv2-SMI::mib-2.47.1.1.1.1.7" || strstr($commandstring,'1.3.6.1.4.1.9.2.2.1.1.7') || strstr($commandstring,'1.3.6.1.4.1.9.2.2.1.1.9') || strstr($commandstring,'1.3.6.1.4.1.9.2.2.1.1.6') || strstr($commandstring,'1.3.6.1.4.1.9.2.2.1.1.8') || strstr($commandstring,'1.3.6.1.4.1.1916.1.2.1.2.1.10') || $commandstring=='1.3.6.1.4.1.9.9.439.1.2.6.1.1' || $commandstring=="1.3.6.1.4.1.9.9.439.1.1.43.1.3" || $commandstring=="1.3.6.1.4.1.9.9.439.1.1.43.1.5" || $commandstring=="1.3.6.1.4.1.9.9.439.1.2.6.1.4" || $commandstring=="1.3.6.1.4.1.9.9.439.1.1.47.1.4" || $commandstring=="1.3.6.1.4.1.9.9.439.1.1.47.1.7" || $commandstring=="1.3.6.1.4.1.9.9.439.1.1.47.1.6" || $commandstring=="1.3.6.1.4.1.9.9.156.1.2.1.1.20" || $commandstring=="1.3.6.1.4.1.9.9.156.1.2.1.1.6" || $commandstring=="1.3.6.1.4.1.9.9.156.1.2.1.1.18" || $commandstring=="1.3.6.1.4.1.9.9.156.1.1.8.1.3" || $commandstring=="1.3.6.1.4.1.9.9.156.1.2.1.1.19" || $commandstring=="1.3.6.1.4.1.9.9.156.1.2.1.1.4" || $commandstring=="1.3.6.1.4.1.9.9.156.1.2.1.1.7" || $commandstring=="1.3.6.1.4.1.9.9.156.1.2.1.1.5"){
					list($remain,$val)=explode(' ',$snmpval,2);
					//Get ID by reversing string and exploding on first instance of "."
					list($id,$junk)=explode(".",strrev($remain));
					//Reverse the ID back again
					$id=strrev($id);
					//Standard way using 7.2.1.19
					if($commandstring=="SNMPv2-SMI::transmission.7.2.1.19"){
						if($val==3){
							$val="Full";
						} else if($val==2){
							$val="Half";
						} else {
							$val="";
						}
					/*
					Non-standard way using 7.2.1.7
					http://tools.cisco.com/Support/SNMP/do/BrowseOID.do?local=en&translate=Translate&objectInput=1.3.6.1.2.1.10.7.2.1.7
					*/
					} else if($commandstring=="SNMPv2-SMI::transmission.7.2.1.7"){
						if($val==0){
							$val="Full";
						} else if($val>0){
							$val="Half";
						} else {
							$val="";
						}
					} else if($commandstring=="SNMPv2-SMI::enterprises.2272.1.3.3.1.4"){
						if($val==1){
							$val="UntagAll";
						} else if($val==2){
							$val="TagAll";
						} else if($val==5){
							$val="UntagPvidOnly";
						} else if($val==6){
							$val="TagPvidOnly";
						} else {
							$val="Unknown";
						}
					//Avaya VLAN port members
					} else if($commandstring=="SNMPv2-SMI::enterprises.2272.1.3.3.1.3"){
						//Get rid of quotes and extra space
						$val=trim(preg_replace('/"/','',$val));
						//Replace every other space with a comma
						/*
						VLAN values are in hex like this:
						00 01 00 28 00 29 03 E8
						Replacing every other space with a comma lets the code isolate each VLAN
						00 01,00 28,00 29,03 E8
						Got code from here: http://stackoverflow.com/questions/4194818/how-to-replace-every-second-white-space
						*/
						$val=preg_replace('/(\S+\s+\S+)\s/', '$1,', $val);
						$valar=explode(',',$val);
						//Get rid of the current value so the hex to decimal values can replace it
						unset($val);
						//Convert each hex VLAN value to decimal
						foreach($valar as $v){
							$val[]=hexdec($v);
						}
						//If there's only 1 value, don't store it as an array
						if(count($val)==1) $val=$val[0];
					} else if($commandstring=="SNMPv2-SMI::enterprises.9.9.46.1.6.1.1.13"){
						if($val==1){
							$val="Trunk";
						} else if($val==2){
							$val="DTP Disabled";
						} else if($val==3){
							$val="Trunk Desirable";
						} else if($val==4){
							$val="Auto";
						} else if($val==5){
							$val="Trunk NoNegotiate";
						}
					} else if($commandstring=="SNMPv2-SMI::enterprises.9.9.46.1.6.1.1.14"){
						if($val==2){
							$val="Access";
						} else if($val==1){
							$val="Trunk";
						} else {
							$val="Unknown";
						}
					} else if($commandstring=="1.3.6.1.4.1.2636.3.40.1.5.1.7.1.5"){
						if($val==1){
							$val="Access";
						} else if($val==2){
							$val="Trunk";
						} else {
							$val="Unknown";
						}
					//Remove quotes for certain values
					} else if($commandstring=="SNMPv2-SMI::enterprises.9.9.46.1.3.1.1.4.1" || $commandstring=="SNMPv2-SMI::enterprises.2272.1.3.2.1.2" || $commandstring=="1.3.6.1.2.1.47.1.1.1.1.14" || strstr($commandstring,'SNMPv2-SMI::mib-2.105.1.1.1.9.') || $commandstring=="SNMPv2-SMI::mib-2.47.1.1.1.1.7" || $commandstring=="1.3.6.1.4.1.9.9.156.1.1.8.1.3" || $commandstring=="1.3.6.1.4.1.9.9.156.1.2.1.1.4" || $commandstring=="1.3.6.1.4.1.9.9.156.1.2.1.1.5"){
						$val=trim(preg_replace('/\"/','',$val));
					/*
					Cisco VLAN Name
					Avaya VLAN Name
					*/
					} else if($commandstring=="SNMPv2-SMI::enterprises.9.9.46.1.3.1.1.2.1"){
						if($val==1){
							$val="Operational";
						} else if($val==2){
							$val="Suspended";
						} else if($val==3){
							$val="mtuTooBigForDevice";
						} else if($val==4){
							$val="mtuTooBigForTrunk";
						}
					//Cisco interface PoE available power
					} else if(strstr($commandstring,'SNMPv2-SMI::enterprises.9.9.402.1.2.1.8.')){
						$val=number_format(round(((trim($val))/1000),1),1);
					} else if(strstr($commandstring,'SNMPv2-SMI::enterprises.9.9.402.1.2.1.10.')){
						if($val>0){
							$val=number_format(round(((trim($val))/1000),2),2);
						}
					//Cisco CUCM phone protocol
					} else if($commandstring=="1.3.6.1.4.1.9.9.156.1.2.1.1.19"){
						if($val==1){
							$val="Unknown";
						} else if($val==2){
							$val="SCCP";
						} else if($val==3){
							$val="SIP";
						}
					//Cisco CUCM phone status
					} else if($commandstring=="1.3.6.1.4.1.9.9.156.1.2.1.1.7"){
						if($val==1){
							$val="Unknown";
						} else if($val==2){
							$val="Registered";
						} else if($val==3){
							$val="Unregistered";
						} else if($val==4){
							$val="Rejected";
						} else if($val==5){
							$val="Partially Registered";
						}
					}
					//Modify speed and bandwidth values
					if(strstr($commandstring,'1.3.6.1.4.1.9.2.2.1.1.6') || strstr($commandstring,'1.3.6.1.4.1.9.2.2.1.1.8')){
						$val=round($val/1000000,3);
					}
					//Get rid of quotes
					if($commandstring=="1.3.6.1.4.1.9.9.439.1.2.6.1.1" || $commandstring=="1.3.6.1.4.1.9.9.439.1.1.43.1.3" || $commandstring=="1.3.6.1.4.1.9.9.439.1.1.43.1.5" || $commandstring=="1.3.6.1.4.1.9.9.439.1.1.47.1.4" || $commandstring=="1.3.6.1.4.1.9.9.439.1.1.47.1.7" || $commandstring=="1.3.6.1.4.1.9.9.439.1.1.47.1.6" || $commandstring=="1.3.6.1.4.1.9.9.156.1.2.1.1.20"){
						$val=preg_replace('/"/','',$val);
					}
					$finar[$id]=$val;
				//Handle the index to MAC MIB
				} else if($snmpval && (strstr($commandstring,'SNMPv2-SMI::mib-2.17.4.3.1') || strstr($commandstring,'1.3.6.1.4.1.25506.8.35.3.1.1'))){
					//echo "SNMPVAL: $snmpval<br />";
					//1.3.6.1.4.1.25506.8.35.3.1.1 - H3C MAC format
					if($commandstring=="SNMPv2-SMI::mib-2.17.4.3.1.1" || $commandstring=="1.3.6.1.4.1.25506.8.35.3.1.1.1"){
						list($remain,$val)=explode(' ',$snmpval,2);
						//Remove quotes, get rid of extra spaces on the right, replace spaces between octets with colons, and convert lower case to upper case
						$val=strtoupper(preg_replace('/ /',':',rtrim(preg_replace('/"/','',$val))));
						if($commandstring=="SNMPv2-SMI::mib-2.17.4.3.1.1"){
							$id=preg_replace('/mib-2.17.4.3.1.1./','',$remain);
						} else if($commandstring=="1.3.6.1.4.1.25506.8.35.3.1.1.1"){
							$id=preg_replace('/iso.3.6.1.4.1.25506.8.35.3.1.1.1./','',$remain);
						}
					} else {
						list($remain,$id)=explode(' ',$snmpval);
						if($commandstring=="1.3.6.1.4.1.25506.8.35.3.1.1.3"){
							$val=preg_replace('/iso.3.6.1.4.1.25506.8.35.3.1.1.3./','',$remain);
						} else {
							$val=preg_replace('/mib-2.17.4.3.1.2./','',$remain);
						}
					}
					//Put data into array
					if($commandstring=="SNMPv2-SMI::mib-2.17.4.3.1.1" || $commandstring=="1.3.6.1.4.1.25506.8.35.3.1.1.1"){
						$finar[$id]=$val;
					//ID 0 is ID's used for MAC address of the device itself. ID's over 1000 are VLAN's
					//} else if($id!=0 && $id<1000){
					} else if($id!=0){
						//Temporary array to keep track of what keys have been used already
						if(!in_array($id,$tmpused)){
							$tmpused[]=$id;
							$finar[$id]=array($val);
						} else {
							array_push($finar[$id],$val);
						}
					}
				//Handle the alternative MAC MIB
				} else if($snmpval && strstr($commandstring,'Q-BRIDGE-MIB::dot1qTpFdbPort')){
					$snmpval=preg_replace("/dot1qTpFdbPort/",'',$snmpval);
					list($remain,$id)=explode(' ',$snmpval);
					list($macvlan,$macadd)=explode('][',$remain);
					//Remove [ at the beginning of the VLAN
					$macvlan=preg_replace('/\[/','',$macvlan);
					//Remove ] at the end of the MAC address
					$macadd=preg_replace('/]/','',$macadd);
					//Convert 0:b:ab:7 to 00:0b:ab:07
					$octet=preg_split('/:/',$macadd);
					$macadd="";
					foreach($octet as $oct) {
						if(strlen($oct)==1) $oct="0" . $oct;
						$macadd=$macadd . $oct . ":";
					}
					//Remove last colon from string and covert to uppercase
					$macadd=strtoupper(substr($macadd,0,-1));
					if(!array_key_exists($id,$finar)){
						$finar[$id]=array(0=>"$macadd");
					} else {
						array_push($finar[$id],"$macadd");
					}
					$macvlanar[$macadd]=$macvlan;
				//Handle the Cisco MAC MIB
				} else if($snmpval && strstr($commandstring,'BRIDGE-MIB::dot1dTpFdbPort') && !strstr($snmpval,'OID')){
					$snmpval=preg_replace("/dot1dTpFdbPort/",'',$snmpval);
					list($remain,$id)=explode(' ',$snmpval);
					//Remove [ at the beginning of the MAC address
					$macadd=preg_replace('/\[/','',$remain);
					//Remove ] at the end of the MAC address
					$macadd=preg_replace('/]/','',$macadd);
					//Convert 0:b:ab:7 to 00:0b:ab:07
					$octet=preg_split('/:/',$macadd);
					$macadd="";
					foreach($octet as $oct) {
						if(strlen($oct)==1) $oct="0" . $oct;
						$macadd=$macadd . $oct . ":";
					}
					//Remove last colon from string and covert to uppercase
					$macadd=strtoupper(substr($macadd,0,-1));
					//echo "MACADD: '$macadd' ID: '$id'<br />\n";
					if(!array_key_exists($id,$finar)){
						$finar[$id]=array(0=>"$macadd");
					} else {
						array_push($finar[$id],"$macadd");
					}
					$macvlanar[$macadd]=$invlan;
				//Handle the Cisco MAC index ID to interface ID mapping MIB
				} else if($snmpval && strstr($commandstring,'1.3.6.1.2.1.17.1.4.1.2') && !strstr($snmpval,'OID')){
					list($remain,$id)=explode(' ',$snmpval);
					$val=end(explode('.',$remain));
					$finar[$val]=$id;
				//Handle the Extreme MAC index ID to interface ID mapping MIB
				} else if($snmpval && strstr($commandstring,'1.3.6.1.4.1.1916.1.16.4.1.3')){
					list($id,$val)=explode(' ',$snmpval);
					$id=preg_replace('/enterprises.1916.1.16.4.1.3./','',$id);
					$id=strrev($id);
					list($junk,$id)=explode('.',$id,2);
					$id=strrev($id);
					$finar[$id]=$val;
				//Handle the Extreme MAC index ID to interface ID mapping MIB
				} else if($snmpval && strstr($commandstring,'1.3.6.1.4.1.1916.1.16.4.1.1')){
					list($id,$val)=explode(' ',$snmpval,2);
					$val=trim(preg_replace('/"/','',$val));
					$val=preg_replace('/ /',':',$val);
					$id=preg_replace('/enterprises.1916.1.16.4.1.1./','',$id);
					$id=strrev($id);
					list($vlanid,$id)=explode('.',$id,2);
					$id=strrev($id);
					$vlanid=strrev($vlanid);
					$macvlanar[$id]=$vlanid;
					$finar[$id]=$val;
				//Handle the ARP MIB
				} else if($snmpval && $commandstring=="IP-MIB::ipNetToMediaPhysAddress"){
					list($remain,$id)=explode(' ',$snmpval);
					//Isolate the IP address
					list($junk,$remain)=explode('.',$remain,2);
					list($junk,$val)=explode('.',$remain,2);
					//Convert 0:b:ab:7 to 00:0b:ab:07
					$octet=preg_split('/:/',$id);
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
				//Handle Juniper VLAN Tagging
				} else if($snmpval && $commandstring=="1.3.6.1.4.1.2636.3.40.1.5.1.7.1.4"){
					//Get the tagging info
					list($remain,$tagging)=explode(' ',$snmpval);
					//Get the interface and VLAN ID
					$remain=preg_replace('/enterprises.2636.3.40.1.5.1.7.1.4./','',$remain);
					list($vlan,$intid)=explode('.',$remain);
					$finar[$intid][$vlan]=$tagging;
				//Handle Avaya VLAN IP/Subnet
				} else if($snmpval && ($commandstring=="SNMPv2-SMI::enterprises.2272.1.8.2.1.2" || $commandstring=="SNMPv2-SMI::enterprises.2272.1.8.2.1.3")){
					//Use tmp to replace in next line
					list($junk,$commandstringtmp)=explode('::',$commandstring); $commandstringtmp=$commandstringtmp . ".";
					$snmpval=preg_replace("/$commandstringtmp/",'',$snmpval);
					//Separate value
					list($extra,$val)=explode(' ',$snmpval);
					//Separate ID
					list($id,$junk)=explode('.',$extra,2);
					$finar[$id]=$val;
				//Handle Netgear VLAN Members
				} else if($snmpval && ($commandstring=="1.3.6.1.4.1.4526.11.13.1.1.3" || $commandstring=="1.3.6.1.4.1.4526.11.13.1.1.4")){
					//Port members in hex format converted to binary
					list($junk,$vlanhextmp)=explode(' ',$snmpval);
					$vlanhextmp=trim(preg_replace('/\"/','',preg_replace('/ /','',$vlanhextmp)));
					/*
					Switch tested on had 8 ports and reported 8 binary bits in "FF" format
					If there's 16 ports the results might come back as "FF FF" and the next line will help
					*/
					$vlanhextmp=preg_replace('/ /','',$vlanhextmp);
					$vlanhex=HexToBin($vlanhextmp);
					//Get VLAN
					$junk=trim(strrev($junk));
					list($vlan,$junk)=explode('.',$junk,2);
					$vlan=strrev($vlan);
					//echo "VLAN: $vlan VLANHEXTMP: a'$vlanhextmp'a VLANBIN: a'$vlanhex'a<br />\n";
					$binar=str_split($vlanhex);
					foreach($binar as $b){
						if(sizeof($finar[$vlan])==0){
							$finar[$vlan]=array(1=>$b);
						} else {
							array_push($finar[$vlan],$b);
						}
					}
				//Handle H3C Hex VLAN
				} else if($snmpval && $commandstring=="1.3.6.1.2.1.17.7.1.4.3.1.2"){
					$rowcounter+=1;
					/*
					Turn this:
					
					iso.3.6.1.2.1.17.7.1.4.3.1.2.1 "EF FF FF CF 00 00 00 00 00 00 00 3F FF FF E3 80
					00 00 00 00 00 00 3F FF FF F3 C0 00 00 00 00 00
					00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
					00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
					00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
					00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
					00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
					00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 "
					iso.3.6.1.2.1.17.7.1.4.3.1.2.2 "20 00 01 08 00 00 00 00 00 00 00 40 00 00 84 00
					00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
					00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
					00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
					00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
					00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
					00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
					00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 "
					
					Into this:
					ID: 1 HexVLAN: EFFFFFCF000000000000003FFFFFE3800000000000003FFFFFF3C000000000......
					ID: 2 HexVLAN: 20000108000000000000004000008400000000000000000000000000000000......
					*/
					if(strstr($snmpval,'iso')){
						$h3cidcnt+=1;
						if($h3cidcnt>$h3clast && $h3cidcnt>1){
							$h3chexar[$vlanidtmp]=$vlanhextmp;
							$vlanidtmp=""; $vlanhextmp="";
							$h3clast+=1;
						}
						$val=preg_replace('/iso.3.6.1.2.1.17.7.1.4.3.1.2./','',trim($snmpval));
						list($vlanidtmp,$vlanhextmp)=explode(' ',$val,2);
						$vlanhextmp=preg_replace('/ /','',trim(preg_replace('/"/','',$vlanhextmp)));
					} else {
						$snmpval=preg_replace('/ "/','',$snmpval);
						$vlanhextmp=$vlanhextmp . preg_replace('/ /','',trim($snmpval));
					}
					//After all rows from SNMP results have been processed
					if(($rowcounter+1)==sizeof($walkresult)){
						//Put last temporary VLAN ID and Hex info into array
						$h3chexar[$vlanidtmp]=$vlanhextmp;
						//echo "<pre>"; print_r($h3chexar); echo "</pre>";
						//Convert Hex VLAN string into binary and create an array that contains VLAN's with a list of ports inside each VLAN represented by a binary status
						foreach($h3chexar as $vlanid=>$h3chex){
							if($h3chex){
								//Convert Hex VLAN to binary
								$binstr=HexToBin($h3chex);
								//echo "BINSTR: $binstr<br />\n";
								$binar=str_split($binstr);
								$tmpcnt=1;
								//Put each VLAN into an array. Inside each VLAN, put a list of port membership status (1 or 0)
								foreach($binar as $b){
									if(!array_key_exists($vlanid,$finar)){
										$finar[$vlanid]=array($tmpcnt=>$b);
									} else {
										array_push($finar[$vlanid],$b);
									}
									$tmpcnt+=1;
								}
							}
						}
					}
				//Handle CDP Name, Device, and Remote Interface
				} else if($snmpval && ($commandstring=="SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.6" || $commandstring=="SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.8" || $commandstring=="SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.7")){
					list($id,$val)=explode(' ',$snmpval,2);
					$val=trim(preg_replace('/"/','',$val));
					if($commandstring=="SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.6"){
						$id=trim(preg_replace('/enterprises.9.9.23.1.2.1.1.6./','',$id));
						//Check first 3 characters of CDP name for "SEP" and make everything uppercase. Sometimes phones don't come back uppercase
						if(substr($val,0,3)=="SEP"){
							$val=strtoupper($val);
						}
					} else if($commandstring=="SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.8"){
						$id=trim(preg_replace('/enterprises.9.9.23.1.2.1.1.8./','',$id));
					} else if($commandstring=="SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.7"){
						$id=trim(preg_replace('/enterprises.9.9.23.1.2.1.1.7./','',$id));
					}
					list($id,$remain)=explode('.',$id);
					$finar[$id]=$val;
				//Handle CDP IP
				} else if($snmpval && $commandstring=="SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.4"){
					list($id,$val)=explode(' "',$snmpval,2);
					$val=trim(preg_replace('/"/','',$val));
					list($ipa,$ipb,$ipc,$ipd)=explode(' ',$val);
					$ipa=hexdec($ipa); $ipb=hexdec($ipb); $ipc=hexdec($ipc); $ipd=hexdec($ipd);
					$val="$ipa.$ipb.$ipc.$ipd";
					$id=trim(preg_replace('/enterprises.9.9.23.1.2.1.1.4./','',$id));
					list($id,$remain)=explode('.',$id);
					$finar[$id]=$val;
				//Handle LLDP Neighbor Info
				} else if($snmpval && ($commandstring=="1.0.8802.1.1.2.1.4.1.1.10" || $commandstring=="1.0.8802.1.1.2.1.4.1.1.9" || $commandstring=="1.0.8802.1.1.2.1.4.1.1.8" || $commandstring=="1.0.8802.1.1.2.1.4.1.1.5")){
					list($id,$val)=explode(' "',$snmpval,2);
					//Get values and ID's. Treat name differently because sometimes it's in HEX format
					if($commandstring=="1.0.8802.1.1.2.1.4.1.1.9"){
						$val=trim(preg_replace('/"/','',$val));
						if(strstr($id,'Hex')){
							list($junk,$val)=explode('STRING: ',$id);
							$val=HexToASCII(preg_replace('/ /','',$val));
						}
						list($id,$junk)=explode(' = ',$id);
						list($junk,$id)=explode('.',strrev($id));
						$id=strrev($id);
					} else {
						$val=trim(preg_replace('/"/','',$val));
						list($junk,$id)=explode('.',strrev($id));
						list($id,$junk)=explode('.',$id);
						$id=strrev($id);
					}
					//Get rid of domain names if there are any
					if($commandstring=="1.0.8802.1.1.2.1.4.1.1.9" && strstr($val,'.')){
						list($val,$junk)=explode('.',$val);
					}
					//Convert hex value to IP's if not in MAC address format (17 characters)
					if($commandstring=="1.0.8802.1.1.2.1.4.1.1.5" && strlen($val)<17){
						$val=strrev($val);
						list($d,$c,$b,$a)=explode(' ',$val);
						$val=hexdec(strrev($a)).".".hexdec(strrev($b)).".".hexdec(strrev($c)).".".hexdec(strrev($d));
					//Format "00 11 22 33 44 55"
					} else if($commandstring=="1.0.8802.1.1.2.1.4.1.1.5" && strlen($val)==17){
						$val=preg_replace('/ /',':',$val);
					}
					$finar[$id]=$val;
				//Handle EDP remote device
				} else if($snmpval && $commandstring=="1.3.6.1.4.1.1916.1.13.2.1.3" || $commandstring=="1.3.6.1.4.1.1916.1.13.2.1.6"){
					list($id,$val)=explode(' ',$snmpval,2);
					$id=preg_replace('/enterprises.1916.1.13.2.1.3./','',$id);
					$id=preg_replace('/enterprises.1916.1.13.2.1.6./','',$id);
					$val=preg_replace('/"/','',$val);
					list($id,$junk)=explode('.',$id,2);
					$finar[$id]=$val;
				//Handle Cisco Voice CME phone button layout
				} else if($snmpval && $commandstring=="1.3.6.1.4.1.9.9.439.1.1.46.1.2"){
					list($id,$val)=explode(' ',$snmpval,2);
					$val=preg_replace('/"/','',$val);
					if(substr($val,-1)=="."){
						$val=rtrim($val,".");
					}
					list($junk,$id)=explode('.',strrev($id));
					$id=strrev($id);
					list($button,$dn)=explode(':',$val);
					$button=preg_replace('/Btn /','',$button);
					$dn=preg_replace('/Uses Dn /','',$dn);
					//Create array of SEP ID"s to DN ID's with DN values
					/* EXAMPLE:
					Array
					(
						[1] => Array
							(
								[1] =>  298
								[2] =>  291
								[3] =>  296
								[4] =>  294
							)

						[2] => Array
							(
								[1] =>  297
								[2] =>  289
								[3] =>  296
								[4] =>  295
								[5] =>  294
								[6] =>  291
							)

						[3] => Array
							(
								[1] =>  296
								[2] =>  289
								[3] =>  298
								[4] =>  297
								[5] =>  294
								[6] =>  291
							)
					)
					*/
					if(sizeof($finar[$id])==0){
						$finar[$id]=array($button=>$dn);
					} else {
						$finar[$id][$button]=$dn;
					}
				//Handle Cisco Voice CUCM Extensions
				} else if($snmpval && $commandstring=="1.3.6.1.4.1.9.9.156.1.2.5.1.2"){
					list($id,$val)=explode(' ',$snmpval,2);
					list($junk,$id)=explode('.',strrev($id));
					$id=strrev($id);
					$val=preg_replace('/"/','',$val);
					if(sizeof($finar[$id])==0){
						$finar[$id]=array(0=>$val);
					} else {
						array_push($finar[$id],$val);
					}
				//Handle everything else
				} else {
					//Get rid of ifDescr, ifName, ifAlias, etc
					list($junk,$remain)=explode('.',$snmpval,2);
					//Get ID. Rest of string is value
					list($id,$val)=explode(' ',$remain,2);
					//Get rid of "Avaya/Nortel Ethernet Routing Switch" portion of string
					if(strstr($val,'Avaya Ethernet Routing Switch') || strstr($val,'Nortel Ethernet Routing Switch') || strstr($val,'Nortel Networks BayStack')){
						list($junk,$val)=explode(' - ',$val);
					}
					//Fix interface description on Avaya 8600/8800
					if((strstr($val,'Port') && strstr($val,'Name')) || (strstr($val,'Gbic') && strstr($val,'Port'))){
						//Remove everything after "Name" which can include port descriptions
						$val=substr($val,0,strpos($val,' Name'));
						//Remove everything before "Port"
						$val=strstr($val,"Port");
					}
					//Modify speed and bandwidth values
					if($commandstring=="IF-MIB::ifSpeed" || $commandstring=="IF-MIB::ifInOctets" || $commandstring=="IF-MIB::ifOutOctets"){
						$val=round($val/1000000,3);
					}
					//Switch the ID and value
					if($commandstring=="IP-MIB::ipAdEntIfIndex"){
						$tmpval=$val;
						$val=$id;
						$id=$tmpval;
					}
					$finar[$id]=$val;
				}
			}
		}
		if($macvlanar){
			return array($finar,$macvlanar);
		} else {
			return $finar;
		}
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
		$routerip=$_POST['routerip'];
		$arpmethod=$_POST['arpmethod'];
		$dnsserver=$_POST['dnsserver'];
		if(!$theip){
			echo "<br />Please enter a device IP address<br />\n";
		} else if (!$snmpcommstring && $snmpversion=="2c"){
			echo "<br />Please enter an SNMPv2 community string<br />\n";
		} else if($snmpversion==3 && !$snmpv3user){
			echo "<br />Please enter an SNMPv3 username\n";
		} else if($snmpversion==3 && !$snmpv3authpass){
			echo "<br />Please enter an SNMPv3 authentication password\n";
		} else if($snmpversion==3 && !$snmpv3privpass && $snmpv3seclevel=="authPriv"){
			echo "<br />Please enter an SNMPv3 privacy password\n";
		} else if($_POST['clientarp'] && $_POST['arpchoice']=="snmp" && !$routerip) {
			echo "<br />Please enter a router IP to grab the ARP table from\n";
		} else if($_POST['clientarp'] && !$dnsserver && !$_POST['ignoredns']){
			echo "<br />Please enter a DNS Server IP\n";
		} else {
			if($_POST['debug']){
				echo "<br />\n";
			}
			if($_POST['ignoreping']){
				$ignoreping=true;
			} else {
				$nmapstring="nmap -PO -sn -PE -n --open -v $theip | grep \"scan report\" | grep -v \"host down\" | sed 's/Nmap scan report for //g'";
				if($_POST['debug'] && $_POST['debugcommands']){
					echo "<font style=\"color: purple;\"><b>COMMAND:</b> $nmapstring</font><br />";
				}
				$testip=shell_exec($nmapstring);
				$nmaprouterstring="nmap -PO -sn -PE -n --open -v $routerip | grep \"scan report\" | grep -v \"host down\" | sed 's/Nmap scan report for //g'";
				if($_POST['debug'] && $_POST['debugcommands']){
					echo "<font style=\"color: purple;\"><b>COMMAND:</b> $nmaprouterstring</font><br />";
				}
				$testrouterip=shell_exec($nmaprouterstring);
			}
			if($ignoreping==true || strlen($testip)>1){
				//Check to make sure the device is SNMP capable
				$testsnmp=StandardSNMPGet($theip,$snmpversion,$snmpcommstring,"SNMPv2-MIB::sysName.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,"-O qv","showerrors");
				//echo "TESTSNMP: $testsnmp<br />";
				if(strstr($testsnmp,'user name')){
					echo "<br />The SNMPv3 username you entered is incorrect or SNMPv3 is not configured on the device.\n";
				} else if(strstr($testsnmp,'Authentication failure')){
					echo "<br />The SNMPv3 authentication protocol and/or password you entered is incorrect.\n";
				} else if(strstr($testsnmp,'Decryption error')){
					echo "<br />The SNMPv3 privacy protocol you entered is incorrect.\n";
				} else if((strlen($testsnmp)==0 || strstr($testsnmp,'Timeout')) && $snmpversion==3){
					echo "<br />The IP address '" . $_POST['theip'] . "' is up but not responsive to SNMP queries.<br />Either the SNMPv3 privacy password you entered is incorrect, or SNMPv3 is not configured on the device.\n";
				} else if(strlen($testsnmp)==0 && $snmpversion==2){
					echo "<br />The IP address '" . $_POST['theip'] . "' is up but not responsive to SNMP queries with RO community string you entered.\n";
				} else if(strlen($testsnmp)>0){
					$devheaderar[]='Device Info';
					$devheaderar[]='Value';
					//Get system info
					//Replace multiple spaces with a single space: http://stackoverflow.com/questions/2368539/php-replacing-multiple-spaces-with-a-single-space
					$sysdescr=preg_replace('!\s+!', ' ',StandardSNMPGet($theip,$snmpversion,$snmpcommstring,"SNMPv2-MIB::sysDescr.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null));
					$syscontact=StandardSNMPGet($theip,$snmpversion,$snmpcommstring,"SNMPv2-MIB::sysContact.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
					$syslocation=StandardSNMPGet($theip,$snmpversion,$snmpcommstring,"SNMPv2-MIB::sysLocation.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
					$sysuptime=StandardSNMPGet($theip,$snmpversion,$snmpcommstring,"DISMAN-EVENT-MIB::sysUpTimeInstance",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,"-O v",null);
					//Add system info to array for Excel export
					$devdataar[]=array('System Name:',$testsnmp);
					$devdataar[]=array('System Description:',$sysdescr);
					$devdataar[]=array('System Contact:',$syscontact);
					$devdataar[]=array('System Location:',$syslocation);
					$devdataar[]=array('System Uptime:',$sysuptime);
					//Print system info
					echo "<br /><b>System Name:</b> $testsnmp<br />\n";
					echo "<b>System Description:</b> <div style=\"width: 500px;\">$sysdescr</div>\n";
					echo "<b>System Contact:</b> $syscontact<br />\n";
					echo "<b>System Location:</b> $syslocation<br />\n";
					echo "<b>System Uptime:</b> $sysuptime<br /><br />\n";
					//Add system table to Excel Array for multi-table printout format
					$excelar[]=array($devheaderar,$devdataar);
					//Get all the necessary interface info
					$ifdescartemp=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifDescr",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
					//Hide SNMP interface ID's
					if($_POST['hideintid'] && $_POST['hideintidval']){
						unset($ifdescartemptemp);
						$ifdescartemptemp=$ifdescartemp;
						unset($ifdescartemp);
						$hideintidar=explode(',',$_POST['hideintidval']);
						foreach($ifdescartemptemp as $id=>$desc){
							if(!in_array($id,$hideintidar)){
								$ifdescartemp[$id]=$desc;
							}
						}
					}
					//Hide Null interfaces
					if($_POST['hidenull']){
						unset($ifdescartemptemp);
						$ifdescartemptemp=$ifdescartemp;
						unset($ifdescartemp);
						foreach($ifdescartemptemp as $id=>$desc){
							if(!stristr($desc,'Null')){
								$ifdescartemp[$id]=$desc;
							}
						}
					}
					//Hide Stack Ports
					if($_POST['hidestackports']){
						unset($ifdescartemptemp);
						$ifdescartemptemp=$ifdescartemp;
						unset($ifdescartemp);
						foreach($ifdescartemptemp as $id=>$desc){
							if(!stristr($desc,'Stack')){
								$ifdescartemp[$id]=$desc;
							}
						}
					}
					//Hide VLAN Interfaces
					if($_POST['hidevlanint']){
						unset($ifdescartemptemp);
						$ifdescartemptemp=$ifdescartemp;
						unset($ifdescartemp);
						foreach($ifdescartemptemp as $id=>$desc){
							if(!stristr($desc,'VLAN')){
								$ifdescartemp[$id]=$desc;
							} else {
								//Used to keep track of SNMP ID's for L3 VLAN interfaces
								$vlanifdesc[$id]=preg_replace('/Vlan/','',$desc);
							}
						}
					}
					//Hide Virtual Router Ports
					if($_POST['hidevr']){
						unset($ifdescartemptemp);
						$ifdescartemptemp=$ifdescartemp;
						unset($ifdescartemp);
						foreach($ifdescartemptemp as $id=>$desc){
							if(!stristr($desc,'VirtualRouter')){
								$ifdescartemp[$id]=$desc;
							}
						}
					}
					//Hide Management Ports
					if($_POST['hidemgt']){
						unset($ifdescartemptemp);
						$ifdescartemptemp=$ifdescartemp;
						unset($ifdescartemp);
						foreach($ifdescartemptemp as $id=>$desc){
							if(!stristr($desc,'Management')){
								$ifdescartemp[$id]=$desc;
							}
						}
					}
					//Hide Extreme rtif Ports
					if($_POST['hidertif']){
						unset($ifdescartemptemp);
						$ifdescartemptemp=$ifdescartemp;
						unset($ifdescartemp);
						foreach($ifdescartemptemp as $id=>$desc){
							if(!stristr($desc,'rtif')){
								$ifdescartemp[$id]=$desc;
							}
						}
					}
					//Check for duplicate VLAN and interface names
					foreach($ifdescartemp as $id=>$desc){
						if(!in_array($desc,$ifdescar)){
							//echo "ID: $id, DESC: $desc<br />\n";
							$ifdescar[$id]=$desc;
						}
					}
					if($_POST['debug'] && $_POST['debugoutput']){
						echo "<pre><font style=\"color: red;\">"; print_r($ifdescar); echo "</font></pre>";
					}//$ifnamear=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifName",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
					if(!$_POST['hidealias']){
						$ifaliasar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifAlias",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ifaliasar); echo "</font></pre>";
						}
					}
					$ifinoctetsar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifInOctets",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
					if($_POST['debug'] && $_POST['debugoutput']){
						echo "<pre><font style=\"color: red;\">"; print_r($ifinoctetsar); echo "</font></pre>";
					}
					$ifoutoctetsar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifOutOctets",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
					if($_POST['debug'] && $_POST['debugoutput']){
						echo "<pre><font style=\"color: red;\">"; print_r($ifoutoctetsar); echo "</font></pre>";
					}
					if(!$_POST['hideadminstatus']){
						$ifadminstatusar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifAdminStatus",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ifadminstatusar); echo "</font></pre>";
						}
					}
					if(!$_POST['hideopstatus']){
						$ifoperstatusar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifOperStatus",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ifoperstatusar); echo "</font></pre>";
						}
					}
					if(!$_POST['hidespeed']){
						$ifspeedar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifSpeed",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ifspeedar); echo "</font></pre>";
						}
					}
					if(!$_POST['hideduplex']){
						$ifduplexar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::transmission.7.2.1.19",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						//Check for a different method of getting the duplex stats
						if(count($ifspeedar)>=10 && count($ifduplexar)<=10){
							unset($ifduplexar);
							$ifduplexar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::transmission.7.2.1.7",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							//The alternative method didn't work either
							if(count($ifspeedar)>=10 && count($ifduplexar)<=10){
								unset($ifduplexar);
								echo "<font style=\"color: red;\">Duplex could not be determined through SNMP</font><br /><br />";
							} else {
								echo "<font style=\"color: red;\">Duplex was determined with a non-standard SNMP method. Some half duplex ports may be missing but it is unlikely</font><br /><br />";
							}
						}
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ifduplexar); echo "</font></pre>";
						}
					}
					if($_POST['vlanchooser'] && $_POST['vlanchoice']=="cisco"){
						//VLAN MIB here: https://supportforums.cisco.com/thread/164782
						//SNMPv2-SMI::enterprises.9.9.68.1.2.2.1.2
						$ciscovlanar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.68.1.2.2.1.2",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ciscovlanar); echo "</font></pre>";
						}
						/*Find trunk or access port:
						Complete answer:	http://blog.glinskiy.com/2010/06/monitoring-trunk-status-via-snmp.html
						Old answer:			https://supportforums.cisco.com/thread/179460
						*/
						$ciscotrunkstatear=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.46.1.6.1.1.13",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ciscotrunkstatear); echo "</font></pre>";
						}
						$ciscotaggingar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.46.1.6.1.1.14",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ciscotaggingar); echo "</font></pre>";
						}
						if($_POST['vlanextra']){
							$vlanstatusar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.46.1.3.1.1.2.1",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($vlanstatusar); echo "</font></pre>";
							}
							$vlannamear=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.46.1.3.1.1.4.1",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($vlannamear); echo "</font></pre>";
							}
							//Create VLAN members array
							$vlanmembersar=array();
							$count=0;
							foreach($ciscovlanar as $intid=>$vlan){
								//For some reason can't use array_push here so using counters instead
								if($vlan!=$lastvlan){
									$count=0;
								} else {
									$count+=1;
								}
								if(sizeof($vlanmembersar[$vlan]==0)){
									$vlanmembersar[$vlan][$count]=$ifdescar[$intid];
								}
								$lastvlan=$vlan;
							}
							//L3 VLAN IP
							$l3vlanaddrartemp=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IP-MIB::ipAdEntIfIndex",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							ksort($l3vlanaddrartemp);
							//Map ifdesc ID to VLAN number
							if(!$_POST['hidevlanint']){
								foreach($ifdescar as $k=>$v){
									if(strstr($v,'Vlan')){
										$vlanifdesc[$k]=preg_replace('/Vlan/','',$v);
									}
								}
							}
							foreach($l3vlanaddrartemp as $vlanidtemp=>$vlanip){
								$l3vlanaddrar[$vlanifdesc[$vlanidtemp]]=$vlanip;
							}
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($l3vlanaddrar); echo "</font></pre>";
							}
							$ciscol3vlanmasktmpar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IP-MIB::ipAdEntNetMask",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							//Key the subnet mask by the VLAN
							foreach($ciscol3vlanmasktmpar as $ip=>$mask){
								$l3vlanmaskar[array_search($ip,$l3vlanaddrar)]=$mask;
							}
							ksort($l3vlanmaskar);
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($l3vlanmaskar); echo "</font></pre>";
							}
						}
					}
					if($_POST['vlanchooser'] && $_POST['vlanchoice']=="avaya"){
						//Great info here under RC-VLAN-MIB: http://www.mibdepot.com/cgi-bin/vendor_index.cgi?r=avaya
						//Or here: http://www.mibdepot.com/cgi-bin/getmib3.cgi?win=mib_a&n=RAPID-CITY&r=avaya&f=rc.mib&t=tree&v=v2&i=0
						$avayavlanar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.2272.1.3.3.1.7",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($avayavlanar); echo "</font></pre>";
						}
						$avayataggingar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.2272.1.3.3.1.4",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($avayataggingar); echo "</font></pre>";
						}
						$avayavlanmembersar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.2272.1.3.3.1.3",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($avayavlanmembersar); echo "</font></pre>";
						}
						if($_POST['vlanextra']){
							$vlannamear=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.2272.1.3.2.1.2",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($vlannamear); echo "</font></pre>";
							}
							$vlanindexidar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.2272.1.3.2.1.6",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							foreach($vlanindexidar as $vlan=>$vlanindex){
								$vlanstatusar[$vlan]=$ifoperstatusar[$vlanindex];
							}
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($vlanstatusar); echo "</font></pre>";
							}
							$l3vlanaddrtmpar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.2272.1.8.2.1.2",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							foreach($l3vlanaddrtmpar as $id=>$l3vlanaddr){
								$l3vlanaddrar[array_search($id,$vlanindexidar)]=$l3vlanaddr;
							}
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($l3vlanaddrar); echo "</font></pre>";
							}
							$l3vlanmasktmpar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.2272.1.8.2.1.3",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							foreach($l3vlanmasktmpar as $id=>$l3mask){
								$l3vlanmaskar[array_search($id,$vlanindexidar)]=$l3mask;
							}
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($l3vlanmaskar); echo "</font></pre>";
							}
							//Create array of port descriptions to VLAN mappings
							foreach($avayavlanmembersar as $snmpid=>$vlans){
								//If the port is part of multiple VLAN's
								if(count($vlans)>1){
									$tmpcnt=0;
									foreach($vlans as $vlan){
										if($tmpcnt==0){
											$tmpvlan=$vlan;
										} else {
											$tmpvlan=$tmpvlan . ",$vlan";
										}
										$tmpcnt+=1;
									}
									$vlans=$tmpvlan;
								}
								//Stacks use the format "Unit 1 Port 1" where everything else uses the format "Port 1" or "Port 1/1"
								if(strstr($ifdescar[$snmpid],'Unit')){
									$port=trim(preg_replace('/ Port /','/',preg_replace('/Unit /','',$ifdescar[$snmpid])));
								} else {
									list($junk,$port)=explode(' ',$ifdescar[$snmpid]);
								}
								$avayavlanportar[$port]=$vlans;
							}
							function AvayaVLANRange($vlanmembersar,$lastvlan,$vlans,$port,&$lastvlanport){
								//First VLAN entry
								if($lastvlan==0){
									$vlanmembersar[$vlans]=$port;
								//Same VLAN entry
								} else if($lastvlan==$vlans){
									//If there's already a range and the last element is 1 less
									if(strstr(end(preg_split('/,/',$vlanmembersar[$vlans])),'-') && ($port-1)==$lastvlanport[$vlans]){
										//Reverse the string, remove anything before a dash, then reverse it again and add the port
										$vlanmembersar[$vlans]=strrev(strstr(strrev($vlanmembersar[$vlans]),'-')) . "$port";
									//Last member was a single port not a range and it's 1 less than the current, so create a range
									} else if(($port-1)==$lastvlanport[$vlans]){
										$vlanmembersar[$vlans]=$vlanmembersar[$vlans] . "-$port";
									//If there's not a range OR there's a range but the last element isn't 1 less
									} else {
										$vlanmembersar[$vlans]=$vlanmembersar[$vlans] . ",$port";
									}
								//Different VLAN entry
								} else if($lastvlan!=$vlans){
									//No existing members
									if(count($vlanmembersar[$vlans])==0){
										$vlanmembersar[$vlans]=$port;
									//Last member was 1 less, so increment the range
									} else if(strstr(end(preg_split('/,/',$vlanmembersar[$vlans])),'-') && ($port-1)==$lastvlanport[$vlans]){
										//Reverse the string, remove anything before a dash, then reverse it again and add the port
										$vlanmembersar[$vlans]=strrev(strstr(strrev($vlanmembersar[$vlans]),'-')) . "$port";
									//Last member was a single port not a range and it's 1 less than the current, so create a range
									} else if(($port-1)==$lastvlanport[$vlans]){
										$vlanmembersar[$vlans]=$vlanmembersar[$vlans] . "-$port";
									//Last member was a single port not a range and it's not 1 less than the current, so just add the port
									} else {
										$vlanmembersar[$vlans]=$vlanmembersar[$vlans] . ",$port";
									}
								}
								//Record the last port used in the VLAN. Used for adding a port to a different VLAN than the last
								$lastvlanport[$vlans]=$port;
								return $vlanmembersar;
							}
							/*
							Starting a new switch so go back through what was found for the existing switch and put slashes before each membership
							Example:
							1,3,9,11,13-46
							2/1,2/3,2/9,2/11,2/13-2/46
							*/
							function AddSlashesToSwitch($vlanar,$switch){
								//Loop through each VLAN
								foreach($vlanar as $tmpvlan=>$tmpport){
									//Isolate each port or range in the VLAN
									$tmpportar=explode(',',$tmpport);
									unset($pt);
									unset($beginar);
									//If it's a new switch, add a slash to the port or range
									foreach($tmpportar as $p){
										if(!preg_match('/\//',$p)){
											$pt=$pt . "$switch/$p,";
									//If it's a previous switch that already has a slash, store it for later use
										} else {
											$beginar[$tmpvlan]=$beginar[$tmpvlan] . ",$p";
										}
									}
									//Remove trailing comma from new port list
									$pt=rtrim($pt,",");
									//Remove beginning comma from old port list
									$beginar[$tmpvlan]=ltrim($beginar[$tmpvlan],",");
									//If it's the first switch, build the port list for the VLAN
									if($switch==1){
										$returnar[$tmpvlan]=$pt;
									//If it's not the first switch, add to the port list for the VLAN if there are new ports
									} else if($pt){
										$returnar[$tmpvlan]=ltrim($beginar[$tmpvlan] . ",$pt",",");
									//If it's not the first switch and there are no new ports, keep the list the same
									} else {
										$returnar[$tmpvlan]=$beginar[$tmpvlan];
									}
								}
								return $returnar;
							}
							$lastvlan=0;
							$lastswitch=0;
							foreach($avayavlanportar as $port=>$vlans){
								//Sometimes there are empty ports with VLAN's...weird
								if($port){
									//echo "<font style=\"color: red;\">PORT: $port</font><br />\n";
									//If there's multiple VLAN's on the port
									if(strstr($vlans,',')){
										//Handle Chassis
										if(preg_match('/\//',$port)){
											//Separate switch and port
											list($currentswitch,$port)=explode('/',$port);
											//Once all the ports are done for a switch, add slashes before the ports for the switch they're part of
											if($currentswitch!=$lastswitch && $lastswitch>0){
												$vlanmembersar=AddSlashesToSwitch($vlanmembersar,$lastswitch);
											}
										}
										//Loop through each VLAN on the port and add it to the array
										$tmpvlans=explode(',',$vlans);
										foreach($tmpvlans as $vlan){
											$vlanmembersar=AvayaVLANRange($vlanmembersar,$lastvlan,$vlan,$port,$lastvlanport);
											//echo "<pre>"; print_r($vlanmembersar); echo "</pre>";
											$lastvlan=$vlan;
										}
									//Single switch with single port on this line
									//If VLAN = 0 it wipes out all other ports in the entry because the first line of AvayaVLANRange indicates the first entry of the first VLAN
									//Don't need to worry about VLAN 0 for multiple VLAN's on a port because manual configuration is required for that and VLAN 0 cannot exist anyways
									} else if($vlans>0){
										//Handle Chassis
										if(preg_match('/\//',$port)){
											//Separate switch and port
											list($currentswitch,$port)=explode('/',$port);
											//Once all the ports are done for a switch, add slashes before the ports for the switch they're part of
											if($currentswitch!=$lastswitch && $lastswitch>0){
												$vlanmembersar=AddSlashesToSwitch($vlanmembersar,$lastswitch);
											}
										}
										//Add the port to the array
										$vlanmembersar=AvayaVLANRange($vlanmembersar,$lastvlan,$vlans,$port,$lastvlanport);
										//echo "<pre>"; print_r($vlanmembersar); echo "</pre>";
										$lastvlan=$vlans;
										$lastport=$port;
									}
									//Keep track of the last switch. Used when adding slashes once the list of all ports for a switch is known
									$lastswitch=$currentswitch;
								}
							}
							$foundslash=false;
							foreach($vlanmembersar as $testing){
								if(preg_match('/\//',$testing)){
									$foundslash=true;
								}
							}
							//Double check the ports. Sometimes there are only VLAN's in a stack configured on a single switch
							if($foundslash==false){
								foreach($avayavlanportar as $testing=>$testvlan){
									if(preg_match('/\//',$testing)){
										$foundslash=true;
									}
								}
							}
							//If the string for the first VLAN has slashes in it
							if($foundslash==true){
								//The last switch doesn't get slashes for ports during the foreach, so run it after
								$vlanmembersar=AddSlashesToSwitch($vlanmembersar,$lastswitch);
							}
						}
						//VLAN membership: http://www.mibdepot.com/cgi-bin/getmib3.cgi?win=mib_a&i=1&n=RAPID-CITY&r=avaya&f=rc.mib&v=v2&t=tab&o=rcVlanPortVlanIds
					}
					/*
					Lots of good info here:
					http://www.oidview.com/mibs/2636/JUNIPER-VLAN-MIB.html
					http://www.juniper.net/techpubs/en_US/junos12.1/information-products/topic-collections/nce/snmp-ex-vlan-retrieving/snmp-ex-vlan-retrieving.pdf
					*/
					if($_POST['vlanchooser'] && $_POST['vlanchoice']=="juniper"){
						$junipervlanidar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.2636.3.40.1.5.1.5.1.5",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						$junipervlantaggingar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.2636.3.40.1.5.1.7.1.4",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						//Use VLAN tagging info to create a tagged port array and untagged port array
						foreach($junipervlantaggingar as $intid=>$vlanar){
							foreach($vlanar as $vlanid=>$tagging){
								//Handle tagged ports
								if($tagging==1){
									$tagging="tagged";
									if(sizeof($junipervlantaggedar[$intid])==0){
										$junipervlantaggedar[$intid]=array(0=>$junipervlanidar[$vlanid]);
									} else {
										array_push($junipervlantaggedar[$intid],$junipervlanidar[$vlanid]);
									}
								//Handle untagged ports
								} else if($tagging==2){
									$tagging="untagged";
									$junipervlanuntaggedar[$intid]=$junipervlanidar[$vlanid];
								}
								//echo "INTID: $intid VLAN: {$junipervlanidar[$vlanid]} TAGGING: $tagging<br />\n";
							}
						}
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($junipervlanuntaggedar); echo "</font></pre>";
							echo "<pre><font style=\"color: red;\">"; print_r($junipervlantaggedar); echo "</font></pre>";
						}
						$junipervlanmodear=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.2636.3.40.1.5.1.7.1.5",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($junipervlanmodear); echo "</font></pre>";
						}
					}
					/*
					VLAN membership is in this OID:
					SNMPv2-SMI::enterprises.4526.11.13.1.1
					http://www.snmplink.org/cgi-bin/nd/m/Ent/N/Netgear,%20Inc/%5BE.%5D%20Netgear,%20Inc/Switch/NMS200/700%20Smart%20Switch%20%28Broadcom%20FASTPATH%29/NETGEAR-SMARTSWITCH-MIB
					*/
					if($_POST['vlanchooser'] && $_POST['vlanchoice']=="netgear"){
						$netgearvlanar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"Q-BRIDGE-MIB::dot1qPvid",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($netgearvlanar); echo "</font></pre>";
						}
						//Get all VLAN port memberships into binary format
						$netgearbinar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.4526.11.13.1.1.3",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						//Get VLAN port untagged memberships into binary format
						$untaggednetgearbinar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.4526.11.13.1.1.4",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						//Map binary array to port VLAN membership
						foreach($ifdescar as $tmpvalid=>$ifdesctmpval){
							foreach($untaggednetgearbinar as $vlan=>$memberar){
								if($memberar[$tmpvalid]==1){
									if(sizeof($netgearvlanmembersar[$tmpvalid])==0){
										$netgearvlanmembersar[$tmpvalid]=array(0=>$vlan);
									} else {
										array_push($netgearvlanmembersar[$tmpvalid],$vlan);
									}
								}
							}
						}
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($netgearvlanmembersar); echo "</font></pre>";
						}
						//Convert all memberships to binary string
						foreach($netgearbinar as $vlan=>$binar){
							foreach($binar as $bin){
								$memallar[$vlan]=$memallar[$vlan] . $bin;
							}
						}
						//Convert untagged memberships to binary string
						foreach($untaggednetgearbinar as $vlan=>$binar){
							foreach($binar as $bin){
								$memuntagar[$vlan]=$memuntagar[$vlan] . $bin;
							}
						}
						//Find tagged VLAN's for each port and put them in an array
						foreach($memallar as $vlan=>$memstr){
							$binar=str_split($memstr);
							$tmpcnt=1;
							foreach($binar as $b){
								if($untaggednetgearbinar[$vlan][$tmpcnt]==0 && $b==1){
									//echo "<font style=\"color: red;\">VLAN: $vlan PORTID: $tmpcnt</font><br />\n";
									if(sizeof($netgearvlantaggedmembersar[$tmpcnt])==0){
										$netgearvlantaggedmembersar[$tmpcnt]=array(0=>$vlan);
									} else {
										array_push($netgearvlantaggedmembersar[$tmpcnt],$vlan);
									}
								}
								$tmpcnt+=1;
							}
						}
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($netgearvlantaggedmembersar); echo "</font></pre>";
						}
					}
					if($_POST['vlanchooser'] && $_POST['vlanchoice']=="h3c"){
						//1.3.6.1.2.1.17.7.1.4.5 - dot1qPvid tree http://tools.cisco.com/Support/SNMP/do/BrowseOID.do?local=en&translate=Translate&objectInput=1.3.6.1.2.1.17.7.1.4.5.1.1
						//1.3.6.1.2.1.17.7.1.4.5.1.1 - Pvid
						$hpvlanar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"Q-BRIDGE-MIB::dot1qPvid",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($hpvlanar); echo "</font></pre>";
						}
						//VLAN port membership
						$h3cbinar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.2.1.17.7.1.4.3.1.2",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						/*
						H3C puts their VLAN membership in binary status like this
						
						VLAN 1
						GigabitEthernet1/0/1 - 1
						GigabitEthernet1/0/2 - 1
						GigabitEthernet1/0/3 - 0
						GigabitEthernet1/0/4 - 1
						....
						GigabitEthernet1/0/24 - 1
						Ten-GigabitEthernet1/0/25 - 1
						Ten-GigabitEthernet1/0/26 - 1
						Ten-GigabitEthernet1/0/27 - 1
						Ten-GigabitEthernet1/0/28 - 1
						Ten-GigabitEthernet1/1/1 - 1
						Ten-GigabitEthernet1/1/2 - 1
						Ten-GigabitEthernet1/1/3 - 1
						Ten-GigabitEthernet1/1/4 - 1
						...Lots of Zero's (Padding up to 89 interfaces)
						
						VLAN 2
						GigabitEthernet2/0/1 - 1
						GigabitEthernet2/0/2 - 1
						GigabitEthernet2/0/3 - 0
						GigabitEthernet2/0/4 - 1
						....
						GigabitEthernet2/0/24 - 1
						Ten-GigabitEthernet2/0/25 - 1
						Ten-GigabitEthernet2/0/26 - 1
						Ten-GigabitEthernet2/0/27 - 1
						Ten-GigabitEthernet2/0/28 - 1
						Ten-GigabitEthernet2/1/1 - 1
						Ten-GigabitEthernet2/1/2 - 1
						Ten-GigabitEthernet2/1/3 - 1
						Ten-GigabitEthernet2/1/4 - 1
						...Lots of Zero's (Padding up to 89 interfaces)
						
						Need to map the interfaces from ifDescr to an array that matches the VLAN binary format
						*/
						//Create an array of ports
						$lastswitchnum=0;
						foreach($ifdescar as $ifdesctmpval){
							if(strstr($ifdesctmpval,'Ethernet') && $ifdesctmpval){
								list($junk,$extra)=explode('Ethernet',$ifdesctmpval);
								list($switchnum,$junk)=explode('/',$extra);
								if($lastswitchnum<$switchnum || $lastswitchnum==0){
									$h3cswitchportsar[$switchnum]=array(1=>$ifdesctmpval);
								} else {
									array_push($h3cswitchportsar[$switchnum],$ifdesctmpval);
								}
								$lastswitchnum=$switchnum;
							}
						}
						//Create port array to match H3C VLAN format. Used in next step
						$tmpcnt=1;
						$h3cportvlanmapar=array();
						foreach($h3cswitchportsar as $switchnum=>$port){
							foreach($port as $p){
								//echo "TMPCNT: $tmpcnt P: $p<br />\n";
								$h3cportvlanmapar[$tmpcnt]=$p;
								//echo "<pre>"; print_r($h3cportvlanmapar); echo "</pre>";
								$tmpcnt+=1;
							}
							while($tmpcnt%89!=1){
								$h3cportvlanmapar[$tmpcnt]="NONE";
								$tmpcnt+=1;
							}
							//echo "SWITCHNUM: $switchnum PORTSIZE: " . sizeof($port) . "<br />\n";
						}
						//Add in extra padding to match size of VLAN array
						/*while(sizeof($h3cportvlanmapar)!=1024){
							$h3cportvlanmapar[$tmpcnt]="NONE";
							$tmpcnt+=1;
						}*/
						//Create master array mapping SNMP interface indexes to VLAN membership...FINALLY!!!
						foreach($h3cportvlanmapar as $portid=>$port){
							if($port!="NONE"){
								foreach($h3cbinar as $vlanid=>$binar){
									if($vlanid){
										foreach($binar as $binid=>$bin){
											if($binid==$portid && $bin==1){
												if(sizeof($h3cvlanmembersar[array_search($port,$ifdescar)])==0){
													$h3cvlanmembersar[array_search($port,$ifdescar)]=array(0=>$vlanid);
												} else {
													array_push($h3cvlanmembersar[array_search($port,$ifdescar)],$vlanid);
												}
												//echo "PORT: $port SNMPID: " . array_search($port,$ifdescar) . " VLAN: $vlanid<br />\n";
											}
										}
									}
								}
							}
						}
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($h3cvlanmembersar); echo "</font></pre>";
						}
					}
					//Get MAC addresses for ports
					/* Excellent MIB for MAC address info: BRIDGE-MIB::dot1dTpFdbTable */
					if($_POST['clientmac'] || $_POST['clientarp']){
						//Alternative SNMP method
						if($_POST['macchoice']=="alt"){
							list($ifindextomacar,$macvlanar)=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"Q-BRIDGE-MIB::dot1qTpFdbPort",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($ifindextomacar); echo "</font></pre>";
							}
						} else if($_POST['macchoice']=="cisco"){
							//http://www.cisco.com/c/en/us/support/docs/ip/simple-network-management-protocol-snmp/40367-camsnmp40367.html
							//Get list of active VLAN's on switch
							$vlanstatusar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.46.1.3.1.1.2.1",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							//Loop through each VLAN and grab the MAC address table
							foreach($vlanstatusar as $vlan=>$status){
								//Don't include VLAN's 1002 (fddi-default), 1003 (token-ring-default), 1004 (fddinet-default), 1005 (trnet-default)
								if($vlan!=1002 && $vlan!=1003 && $vlan!=1004 && $vlan!=1005){
									//echo "VLAN: '$vlan'<br />\n";
									//Grab the MAC address table for the current VLAN
									list($macartemp,$macvlanartemp)=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"BRIDGE-MIB::dot1dTpFdbPort",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,$vlan,null);
									//The ID from the MAC address table (For each VLAN) has to be mapped to the interface index...STUPID CISCO!...more excessive SNMP walks!
									$tmpintindexmapar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.2.1.17.1.4.1.2",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,$vlan,null);
									//Add the MAC addresses from each VLAN to the final array (Ports can have MAC addresses in multiple VLAN's)
									foreach($macartemp as $id=>$tempar){
										foreach($tempar as $tmpmac){
											//echo "<font style=\"color: red;\">ID: $id MAC: $tmpmac</font><br />\n";
											if(!array_key_exists($tmpintindexmapar[$id],$ifindextomacar)){
												$ifindextomacar[$tmpintindexmapar[$id]]=array(0=>"$tmpmac");
											} else {
												array_push($ifindextomacar[$tmpintindexmapar[$id]],"$tmpmac");
											}
										}
									}
									//Add the MAC address to VLAN mapping to the final array
									foreach($macvlanartemp as $macadd=>$vlan){
										$macvlanar[$macadd]=$vlan;
									}
									unset($macartemp);
									unset($macvlanartemp);
								}
							}
						} else if($_POST['macchoice']=="extreme"){
							$ifindextomacindexar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.1916.1.16.4.1.3",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($ifindextomacindexar); echo "</font></pre>";
							}
							list($ifmacindextomacaddar,$tmpmacvlanar)=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.1916.1.16.4.1.1",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($ifmacindextomacaddar); echo "</font></pre>";
							}
							//Put together arrays to match port ID and MAC Address
							foreach($ifmacindextomacaddar as $ifindexkey=>$macadd){
								//Make sure it's a MAC address (Sometimes values are messed up)
								if(preg_match('/:/',$macadd)){
									if(!array_key_exists($ifindextomacindexar[$ifindexkey],$ifindextomacar)){
										$ifindextomacar[$ifindextomacindexar[$ifindexkey]]=array(0=>"$macadd");
									} else {
										array_push($ifindextomacar[$ifindextomacindexar[$ifindexkey]],"$macadd");
									}
								}
							}
							$vlanidar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.1916.1.2.1.2.1.10",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($vlanidar); echo "</font></pre>";
							}
							//Put together arrays to match MAC address and VLAN
							foreach($tmpmacvlanar as $macid=>$vlanid){
								$macvlanar[$ifmacindextomacaddar[$macid]]=$vlanidar[$vlanid];
							}
						} else {
							$ifindextomacindexar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::mib-2.17.4.3.1.2",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($ifindextomacindexar); echo "</font></pre>";
							}
							$ifmacindextomacaddar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::mib-2.17.4.3.1.1",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($ifmacindextomacaddar); echo "</font></pre>";
							}
							/*
							Good references here:
							http://people.csse.uwa.edu.au/ryan/tech/findmac.php.txt
							http://people.csse.uwa.edu.au/ryan/tech/mac_addresses.html
							*/
							$ifmacindexmapar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::mib-2.17.1.4.1.2",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($ifmacindexmapar); echo "</font></pre>";
							}
							//Clear previously used temp array
							unset($tmpused);
							//Put together arrays to match port ID to MAC Address
							foreach($ifindextomacindexar as $ifindexkey=>$array){
								foreach($array as $snmpkey){
									//Temporary array to keep track of what keys have been used already
									if(!in_array($ifindexkey,$tmpused)){
										$tmpused[]=$ifindexkey;
										$ifindextomacar[$ifindexkey]=array($ifmacindextomacaddar[$snmpkey]);
									} else {
										array_push($ifindextomacar[$ifindexkey],$ifmacindextomacaddar[$snmpkey]);
									}
								}
							}
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<font style=\"color: red;\"><b>IFINDEXTOMACAR:</b></font><br />";
								echo "<pre><font style=\"color: red;\">"; print_r($ifindextomacar); echo "</font></pre>";
							}
							//Figure out if there's additional ID mapping which is used by some Cisco switches
							$newid=false; $cnt=0;
							foreach($ifmacindexmapar as $ifmacindexid=>$ifmacindex){
								if($cnt==0 && $ifmacindexid!=$ifmacindex){
									$newid=true;
								}
								$cnt+=1;
							}
							//Some Cisco switches need an additional interface ID mapping
							if(count($ifmacindexmapar) && $newid==true){
								//Store index id to mac address array in temporary variable
								$ifindextomacartemp=$ifindextomacar;
								unset($ifindextomacar);
								//Build new index id to mac address array with new id's
								foreach($ifmacindexmapar as $oldid=>$ifmacindexid){
									if($ifmacindexid){
										$ifindextomacar[$ifmacindexid]=$ifindextomacartemp[$oldid];
									}
								}
							}
							//Standard way didn't work, try the H3C way
							if(count($ifindextomacindexar)<=2 && count($ifmacindextomacaddar)<=2){
								$ifindextomacindexar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.25506.8.35.3.1.1.3",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
								if($_POST['debug'] && $_POST['debugoutput']){
									echo "<pre><font style=\"color: red;\">"; print_r($ifindextomacindexar); echo "</font></pre>";
								}
								$ifmacindextomacaddar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.25506.8.35.3.1.1.1",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
								if($_POST['debug'] && $_POST['debugoutput']){
									echo "<pre><font style=\"color: red;\">"; print_r($ifmacindextomacaddar); echo "</font></pre>";
								}
								//Clear previously used temp array
								unset($tmpused);
								//Put together arrays to match port ID to MAC Address
								foreach($ifindextomacindexar as $ifindexkey=>$array){
									foreach($array as $snmpkey){
										//Temporary array to keep track of what keys have been used already
										if(!in_array($ifindexkey,$tmpused)){
											$tmpused[]=$ifindexkey;
											$ifindextomacar[$ifindexkey]=array($ifmacindextomacaddar[$snmpkey]);
										} else {
											array_push($ifindextomacar[$ifindexkey],$ifmacindextomacaddar[$snmpkey]);
										}
									}
								}
								//Standard and H3C ways didn't work
								if(count($ifindextomacindexar)<1 && count($ifmacindextomacaddar)<1){
									echo "<font style=\"color: red;\">The MAC address table could not be determined through SNMP</font><br /><br />";
								} else if($_POST['debug'] && $_POST['debugoutput']){
									echo "<pre><font style=\"color: red;\">"; print_r($ifmacindextomacaddar); echo "</font></pre>";
								}
							}
						}
					}
					if($_POST['hidemacciscotrunk']){
						$ciscotrunkar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.46.1.6.1.1.14",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						//echo "<pre><font style=\"color: red;\">"; print_r($ciscotrunkar); echo "</font></pre>";
						foreach($ciscotrunkar as $theid=>$trunkval){
							if($trunkval=="Trunk"){
								$hideidar[]=$theid;
							}
						}
						foreach($hideidar as $hideid){
							unset($ifindextomacar[$hideid]);
						}
					}
					if($_POST['hidemacintidval']){
						$hideidar=explode(',',$_POST['hidemacintidval']);
						foreach($hideidar as $hideid){
							unset($ifindextomacar[$hideid]);
						}
					}
					if($_POST['macoui']){
						//Get OUI file into array. Prefer CSV file
						if(file("oui.csv")){
							$macouifilear=file("oui.csv");
							//Get lines in array that have the MAC address and associated vendor
							foreach($macouifilear as $macouiline){
								list($macregistry,$macoui,$macorg)=explode(',',$macouiline);
								if($macregistry!="Registry"){
									$macoui=wordwrap($macoui,2,':',true);
									$macorg=trim(preg_replace('/"/','',$macorg));
									$macouiar[$macoui]=$macorg;
								}
							}
							//echo "<pre>"; print_r($macouiar); echo "</pre>";
						} else if(file("oui.txt")){
							$macouifilear=file("oui.txt");
							//Get lines in array that have the MAC address and associated vendor
							foreach($macouifilear as $macouiline){
								if(strstr($macouiline,'hex')){
									$macouitmpar[]=$macouiline;
								}
							}
							//Create array keyed by MAC address with the value of the vendor
							foreach($macouitmpar as $macouiline){
								list($macadd,$remain)=explode('(',$macouiline);
								$macadd=preg_replace('/-/',':',strtoupper(trim($macadd)));
								list($junk,$vendor)=explode(')',$remain);
								$vendor=trim($vendor);
								$macouiar[$macadd]=$vendor;
							}
						}
						//echo "<pre>"; print_r($macouiar); echo "</pre>";
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($macouiar); echo "</font></pre>";
						}
					}
					$arpworks=false;
					if($_POST['clientarp'] && ($ignoreping==true || strlen($testrouterip)>1) && $_POST['arpchoice']=="snmp"){
						$testroutersnmp=StandardSNMPGet($routerip,$snmpversion,$snmpcommstring,"SNMPv2-MIB::sysName.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,"-O qv","showerrors");
						//echo "TESTROUTERSNMP: $testroutersnmp<br />";
						if(strstr($testroutersnmp,'user name')){
							echo "<br />The SNMPv3 username you entered is incorrect for the router IP.<br /><font style=\"color: red;\">The ARP table is unavailable.</font><br /><br />\n";
						} else if(strstr($testroutersnmp,'Authentication failure')){
							echo "<br />The SNMPv3 authentication protocol and/or password you entered is incorrect for the router IP.<br /><font style=\"color: red;\">The ARP table is unavailable.</font><br /><br />\n";
						} else if(strstr($testroutersnmp,'Decryption error')){
							echo "<br />The SNMPv3 privacy protocol you entered is incorrect for the router IP.<br /><font style=\"color: red;\">The ARP table is unavailable.</font><br /><br />\n";
						} else if((strlen($testroutersnmp)==0 || strstr($testroutersnmp,'Timeout')) && $snmpversion==3){
							echo "<br />The router IP address '$routerip' is up but not responsive to SNMP queries.<br />Either the SNMPv3 privacy password you entered is incorrect, or SNMPv3 is not configured on the router.<br /><font style=\"color: red;\">The ARP table is unavailable.</font><br /><br />\n";
						} else if(strlen($testroutersnmp)==0 && $snmpversion==2){
							echo "<br />The router IP address '$routerip' is up but not responsive to SNMP queries with RO community string you entered.<br /><font style=\"color: red;\">The ARP table is unavailable.</font><br /><br />\n";
						} else if(strlen($testroutersnmp)>0){
							//Get arp table via SNMP
							$arpar=StandardSNMPWalk($routerip,$snmpversion,$snmpcommstring,"IP-MIB::ipNetToMediaPhysAddress",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($arpar); echo "</font></pre>";
							}
							if(sizeof($arpar)>2){
								$arpworks=true;
							} else {
								echo "<font style=\"color: red;\">The router was reachable through SNMP, but the ARP table is unavailable.</font><br /><br />";
							}
						}
					} else if($_POST['arpchoice']=="nmap"){
						$nmapstring="sudo nmap -PO -sn -PE -n --open {$_POST['nmaparp']} | grep -e \"scan report\" -e \"MAC Address\" | grep -v \"host down\" | sed 's/Nmap scan report for //g' | sed 's/MAC Address: //g'";
						$nmaparptempar=shell_exec($nmapstring);
						$nmaparptempar=preg_split('/\n/',$nmaparptempar);
						$last="";
						foreach($nmaparptempar as $line){
							if(strstr($line,':')){
								list($mac,$remain)=explode(' ',$line,2);
								if($mac!="FF:FF:FF:FF:FF:FF"){
									$arpar[$mac]=$last;
								}
							} else if($line){
								//Save the IP
								$last=$line;
							}
						}
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($arpar); echo "</font></pre>";
						}
						if(sizeof($arpar)>0){
							$arpworks=true;
						} else {
							echo "<font style=\"color: red;\">NMAP did not return any ARP results.</font><br /><br />";
						}
					} else if(strlen($testrouterip)<1 && $_POST['arpchoice']=="snmp"){
						echo "<font style=\"color: red;\">The router was not reachable through ICMP. Trying to ignore the ping test</font><br /><br />";
					}
					if($_POST['trafficstats']){
						$ifinoctetsar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifInOctets",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ifinoctetsar); echo "</font></pre>";
						}
						$ifoutoctetsar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifOutOctets",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ifoutoctetsar); echo "</font></pre>";
						}
					}
					if($_POST['errorsdiscard']){
						$ifinerrorsar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifInErrors",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ifinerrorsar); echo "</font></pre>";
						}
						$ifouterrorsar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifOutErrors",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ifouterrorsar); echo "</font></pre>";
						}
						$ifindiscardsar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifInDiscards",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ifindiscardsar); echo "</font></pre>";
						}
						$ifoutdiscardsar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"IF-MIB::ifOutDiscards",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ifoutdiscardsar); echo "</font></pre>";
						}
					}
					if($_POST['ciscopps']){
						$ciscoppsinar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.9.2.2.1.1.7",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ciscoppsinar); echo "</font></pre>";
						}
						$ciscoppsoutar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.9.2.2.1.1.9",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ciscoppsoutar); echo "</font></pre>";
						}
					}
					if($_POST['ciscoinoutrate']){
						$ciscoinratear=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.9.2.2.1.1.6",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ciscoinratear); echo "</font></pre>";
						}
						$ciscooutratear=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.9.2.2.1.1.8",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ciscooutratear); echo "</font></pre>";
						}
					}
					if($_POST['cdpname']){
						$cdpnamear=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.6",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($cdpnamear); echo "</font></pre>";
						}
					}
					if($_POST['cdpip']){
						$cdpipar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.4",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($cdpipar); echo "</font></pre>";
						}
					}
					if($_POST['cdpdev']){
						$cdpdevar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.8",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($cdpdevar); echo "</font></pre>";
						}
					}
					if($_POST['cdpint']){
						$cdpintar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.7",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($cdpintar); echo "</font></pre>";
						}
					}
					function LLDPIDChecker($inar,$ifdescar){
						$lldpidcount=0;
						//Check if LLDP ID's exist in interface ID's
						foreach($inar as $lldpid=>$lldpname){
							//echo "ID: $lldpid, NAME: $lldpname<br />\n";
							if(array_key_exists($lldpid,$ifdescar)){
								$lldpidcount+=1;
							}
						}
						//echo "LLDPIDCOUNT: $lldpidcount<br />\n";
						//Try adding 1000 to ID values...for Extreme switches
						if($lldpidcount==0){
							$inartemp=$inar;
							unset($inar);
							foreach($inartemp as $lldpid=>$lldpname){
								$inar[$lldpid+1000]=$lldpname;
							}
						}
						return $inar;
					}
					if($_POST['lldpname']){
						$lldpnamear=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.0.8802.1.1.2.1.4.1.1.9",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						$lldpnamear=LLDPIDChecker($lldpnamear,$ifdescar);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($lldpnamear); echo "</font></pre>";
						}
					}
					if($_POST['lldpip']){
						$lldpipar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.0.8802.1.1.2.1.4.1.1.5",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						$lldpipar=LLDPIDChecker($lldpipar,$ifdescar);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($lldpipar); echo "</font></pre>";
						}
					}
					if($_POST['lldpdev']){
						$lldpdevar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.0.8802.1.1.2.1.4.1.1.10",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						$lldpdevar=LLDPIDChecker($lldpdevar,$ifdescar);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($lldpdevar); echo "</font></pre>";
						}
					}
					if($_POST['lldpint']){
						$lldpintar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.0.8802.1.1.2.1.4.1.1.8",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						$lldpintar=LLDPIDChecker($lldpintar,$ifdescar);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($lldpintar); echo "</font></pre>";
						}
					}
					if($_POST['edpdev']){
						$edpdevar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.1916.1.13.2.1.3",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($edpdevar); echo "</font></pre>";
						}
					}
					if($_POST['edpint']){
						$edpintar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.4.1.1916.1.13.2.1.6",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($edpintar); echo "</font></pre>";
						}
					}
					//Good article on total switch power - http://forum.nedi.ch/index.php?topic=600.0
					if($_POST['ciscointpoe'] || $_POST['ciscointpoedev']){
						//Grab entPhysicalAlias ID's
						$entPhysicalAliasar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"1.3.6.1.2.1.47.1.1.1.1.14",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						//Check if the array has any values for PoE interface ID to ifDescr ID. If not, need to use alternate method to get 
						$poeidcheck=true;
						if(count(array_filter($entPhysicalAliasar))==0){
							$poeidcheck=false;
							$ifpoeidtempar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::mib-2.47.1.1.1.1.7",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							foreach($ifpoeidtempar as $tempid=>$intname){
								if(in_array($intname,$ifdescar)){
									//Mash arrays together...identify PoE interface ID to ifDescr interface ID
									$ifpoeidaltar[$tempid]=array_search($intname,$ifdescar);
								}
							}
						}
						$poeswitchnumar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.402.1.3.1.1",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
						foreach($poeswitchnumar as $switchnum=>$junkid){
							//Grab mapping of PoE MIB interface ID to entPhysicalAlias ID for each switch
							$ciscointpoeidtempar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.402.1.2.1.11.$switchnum",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							foreach($ciscointpoeidtempar as $intpoeid=>$intpoe){
								$ciscointpoeidar[$switchnum][$intpoeid]=$intpoe;
							}
						}
					}
					if($_POST['ciscointpoe']){
						//Loop through each PoE capable switch
						foreach($poeswitchnumar as $switchnum=>$junkid){
							unset($ciscointpoeavailtempar);
							//Grab PoE interface stats for available PoE power on each port (Organized by an ID within the PoE MIB)
							//Used to use .1.2.1.7 but changed to .1.2.1.8....7 shows power from power supply and 8 shows power sent to device
							$ciscointpoeavailtempar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.402.1.2.1.8.$switchnum",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							//Create array for current switch
							foreach($ciscointpoeavailtempar as $availid=>$avail){
								$ciscointpoeavailar[$switchnum][$availid]=$avail;
							}
							unset($ciscointpoeactualtempar);
							//Grab PoE interface stats for actual used PoE power on each port (Organized by an ID within the PoE MIB)
							$ciscointpoeactualtempar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::enterprises.9.9.402.1.2.1.10.$switchnum",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							//Create array for current switch
							foreach($ciscointpoeactualtempar as $actualid=>$actual){
								$ciscointpoeactualar[$switchnum][$actualid]=$actual;
							}
							//Create array of interface ID to available PoE
							foreach($ciscointpoeavailar as $switchid=>$availar){
								foreach($availar as $availid=>$poeavail){
									if($poeidcheck==true){
										$ciscopoeavailar[$entPhysicalAliasar[$ciscointpoeidar[$switchid][$availid]]]=$poeavail;
									} else {
										$ciscopoeavailar[$ifpoeidaltar[$ciscointpoeidar[$switchid][$availid]]]=$poeavail;
									}
								}
							}
							//Create array of interface ID to actual used PoE
							foreach($ciscointpoeactualar as $switchid=>$actualar){
								foreach($actualar as $actualid=>$poeactual){
									if($poeidcheck==true){
										$ciscopoeactualar[$entPhysicalAliasar[$ciscointpoeidar[$switchid][$actualid]]]=$poeactual;
									} else {
										$ciscopoeactualar[$ifpoeidaltar[$ciscointpoeidar[$switchid][$actualid]]]=$poeactual;
									}
								}
							}
						}
						if($_POST['debug'] && $_POST['debugoutput']){
							echo "<pre><font style=\"color: red;\">"; print_r($ciscopoeavailar); echo "</font></pre>";
							echo "<pre><font style=\"color: red;\">"; print_r($ciscopoeactualar); echo "</font></pre>";
						}
					}
					if($_POST['ciscointpoedev']){
						foreach($poeswitchnumar as $switchnum=>$junkid){
							unset($ciscointpoedevar);
							//Grab PoE device on each port (Organized by an ID within the PoE MIB)
							$ciscointpoedevtempar=StandardSNMPWalk($theip,$snmpversion,$snmpcommstring,"SNMPv2-SMI::mib-2.105.1.1.1.9.$switchnum",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
							foreach($ciscointpoedevtempar as $devid=>$dev){
								$ciscointpoedevar[$switchnum][$devid]=$dev;
							}
							//Create array of interface ID to PoE device
							foreach($ciscointpoedevar as $switchid=>$devar){
								foreach($devar as $devid=>$poedev){
									if($poeidcheck==true){
										$ciscopoedevar[$entPhysicalAliasar[$ciscointpoeidar[$switchid][$devid]]]=$poedev;
									} else {
										$ciscopoedevar[$ifpoeidaltar[$ciscointpoeidar[$switchid][$devid]]]=$poedev;
									}
								}
							}
							if($_POST['debug'] && $_POST['debugoutput']){
								echo "<pre><font style=\"color: red;\">"; print_r($ciscopoedevar); echo "</font></pre>";
							}
						}
					}
					if($_POST['ciscovoice']){
						$ciscocmeworks=false;
						$ciscocucmworks=false;
						//Make sure LLDP/CDP name was checked
						if($_POST['lldpname'] || $_POST['cdpname']){
							//Make sure voice IP was entered
							if($_POST['ciscovoiceip']){
								//Make sure if SNMP alternate box was checked, a string was entered
								if(($_POST['ciscovoicesnmpbox'] && $_POST['ciscovoicesnmpcomm']!=null) || !$_POST['ciscovoicesnmpbox']){
									$ciscovoiceip=$_POST['ciscovoiceip'];
									//Use alternate SNMP if checked
									if($_POST['ciscovoicesnmpbox'] && $_POST['ciscovoicesnmpcomm']){
										$snmpstringciscovoice=$_POST['ciscovoicesnmpcomm'];
										//Switch can be v3 and Cisco voice v2
										$snmpversionciscovoice="2c";
									} else {
										$snmpstringciscovoice=$snmpcommstring;
										$snmpversionciscovoice=$snmpversion;
									}
									//Test SNMP by grabbing hostname
									$testciscovoicesnmp=StandardSNMPGet($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"SNMPv2-MIB::sysName.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,"-O qv","showerrors");
									if(strstr($testciscovoicesnmp,'user name')){
										echo "<font style=\"color: red;\">The SNMPv3 username you entered is incorrect for CME/CUCM device '$ciscovoiceip' or SNMPv3 is not configured on the device.</font><br /><br />\n";
									} else if(strstr($testciscovoicesnmp,'Authentication failure')){
										echo "<font style=\"color: red;\">The SNMPv3 authentication protocol and/or password you entered is incorrect for CME/CUCM device '$ciscovoiceip'.</font><br /><br />\n";
									} else if(strstr($testciscovoicesnmp,'Decryption error')){
										echo "<font style=\"color: red;\">The SNMPv3 privacy protocol you entered is incorrect for CME/CUCM device '$ciscovoiceip'.</font><br /><br />\n";
									} else if((strlen($testciscovoicesnmp)==0 || strstr($testciscovoicesnmp,'Timeout')) && $snmpversionciscovoice==3){
										echo "<font style=\"color: red;\">The IP address '$ciscovoiceip' is not responsive to SNMP queries.<br />Either the SNMPv3 privacy password you entered is incorrect, or SNMPv3 is not configured on the device.</font><br /><br />\n";
									} else if(strlen($testciscovoicesnmp)==0 && $snmpversionciscovoice==2){
										echo "<font style=\"color: red;\">The IP address '$ciscovoiceip' is not responsive to SNMP queries with RO community string you entered.</font><br /><br />\n";
									} else if(strlen($testciscovoicesnmp)>0){
										//All HTML input correct and SNMP works, check for CME/CUCM
										if($_POST['ciscovoicetype']=="cme"){
											$cmeversion=StandardSNMPGet($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.439.1.1.2.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
											if($cmeversion && !strstr($cmeversion,'No Such')){
												$ciscocmeworks=true;
												echo "<b>Cisco CME System Name:</b> $testciscovoicesnmp<br />\n";
												$cmesysdescr=preg_replace('!\s+!', ' ',StandardSNMPGet($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"SNMPv2-MIB::sysDescr.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass));
												$cmesystemmsg=StandardSNMPGet($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.439.1.1.37.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
												echo "<b>Cisco CME System Message:</b> $cmesystemmsg<br />\n";
												echo "<b>Cisco CME System Description:</b> $cmesysdescr<br />\n";
												$cmeuptime=StandardSNMPGet($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"DISMAN-EVENT-MIB::sysUpTimeInstance",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,"-O v");
												echo "<b>Cisco CME Uptime:</b> $cmeuptime<br />\n";
												echo "<b>Cisco CME Version:</b> $cmeversion<br />\n";
												$cmemaxephones=StandardSNMPGet($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.439.1.1.6.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
												echo "<b>Cisco CME Max Ephones:</b> $cmemaxephones<br />\n";
												$cmeephonesconfigured=StandardSNMPGet($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.439.1.2.2.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
												echo "<b>Cisco CME Ephones Configured:</b> $cmeephonesconfigured<br />\n";
												$cmeephonesregistered=StandardSNMPGet($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.439.1.2.3.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
												echo "<b>Cisco CME Ephones Registered:</b> $cmeephonesregistered<br />\n";
												$ephonesonswitch=0;
												$usecdpforsep=true;
												if($_POST['cdpname']){
													foreach($cdpnamear as $cdpname){
														//Check first 3 characters of CDP name for "SEP"
														if(substr($cdpname,0,3)=="SEP"){
															$ephonesonswitch+=1;
														}
													}
												}
												//Only check LLDP if it's selected and CDP showed nothing...prefer CDP results
												if($_POST['lldpname'] && $ephonesonswitch==0){
													$usecdpforsep=false;
													foreach($lldpnamear as $lldpname){
														//Check first 3 characters of LLDP name for "SEP"
														if(substr($lldpname,0,3)=="SEP"){
															$ephonesonswitch+=1;
														}
													}
												}
												echo "<b>Cisco CME Ephones on $theip:</b> $ephonesonswitch<br /><br />\n";
												//Only get phone info if there are phones on the switch
												if($ephonesonswitch>0){
													$cmesepidar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.439.1.2.6.1.1",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													if($usecdpforsep==true){
														foreach($cdpnamear as $cdpid=>$cdpname){
															if(substr($cdpname,0,3)=="SEP"){
																//Array ID is interface ID, value is SEP ID
																$cmesepidtosepidar[$cdpid]=array_search($cdpname,$cmesepidar);
															}
														}
													} else {
														foreach($lldpnamear as $lldpid=>$lldpname){
															if(substr($lldpname,0,3)=="SEP"){
																//Array ID is interface ID, value is SEP ID
																$cmesepidtosepidar[$lldpid]=array_search($lldpname,$cmesepidar);
															}
														}
													}
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cmesepidtosepidar); echo "</font></pre>";
													}
													$cmephoneipstempar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.439.1.1.43.1.3",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													foreach($cmephoneipstempar as $phoneipsepid=>$phoneip){
														if($phoneip && $phoneip!='0.0.0.0' && array_search($phoneipsepid,$cmesepidtosepidar)!=0){
															$cmephoneipsar[array_search($phoneipsepid,$cmesepidtosepidar)]=$phoneip;
														}
													}
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cmephoneipsar); echo "</font></pre>";
													}
													$cmephonemodeltempar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.439.1.1.43.1.5",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													foreach($cmephonemodeltempar as $phonemodelsepid=>$phonemodel){
														if($phonemodel && array_search($phonemodelsepid,$cmesepidtosepidar)!=0){
															$cmephonemodelar[array_search($phonemodelsepid,$cmesepidtosepidar)]=$phonemodel;
														}
													}
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cmephonemodelar); echo "</font></pre>";
													}
													$cmephonestatustempar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.439.1.2.6.1.4",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													foreach($cmephonestatustempar as $phonestatussepid=>$phonestatus){
														if($phonestatus && array_search($phonestatussepid,$cmesepidtosepidar)!=0){
															if($phonestatus=="1"){
																$phonestatus="On Hook";
															} else if($phonestatus=="2"){
																$phonestatus="Off Hook";
															} else if($phonestatus=="3"){
																$phonestatus="Ringing";
															} else if($phonestatus=="4"){
																$phonestatus="Paging";
															}
															$cmephonestatusar[array_search($phonestatussepid,$cmesepidtosepidar)]=$phonestatus;
														}
													}
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cmephonestatusar); echo "</font></pre>";
													}
													$cmednar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.439.1.1.47.1.4",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cmednar); echo "</font></pre>";
													}
													$cmebuttonlayouttempar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.439.1.1.46.1.2",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													foreach($cmebuttonlayouttempar as $buttonlayoutid=>$buttonlayout){
														if(substr($buttonlayout,-1)=="."){
															$buttonlayout=rtrim($buttonlayout,".");
														}
														$buttonlayoutid=array_search($buttonlayoutid,$cmesepidtosepidar);
														//Match SNMP DN ID with actual ID
														foreach($buttonlayout as $buttonid=>$dnid){
															$dnid=trim($dnid);
															$buttonlayoutar[$dnid]="button: $buttonid, ephone-dn: $dnid, number: " . $cmednar[$dnid];
														}
														if($buttonlayoutid>0){
															$cmebuttonlayoutar[$buttonlayoutid]=$buttonlayoutar;
														}
														unset($buttonlayoutar);
													}
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cmebuttonlayoutar); echo "</font></pre>";
													}
													$cmednlabeltempar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.439.1.1.47.1.7",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													//Loop through button layout and create arrays for each port matching the DN label
													foreach($cmebuttonlayoutar as $intid=>$tmpbuttonlayoutar){
														foreach($tmpbuttonlayoutar as $labelid=>$unused){
															$cmednlabelar[$intid][$labelid]=$cmednlabeltempar[$labelid];
														}
													}
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cmednlabelar); echo "</font></pre>";
													}
													$cmednnametempar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.439.1.1.47.1.6",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													//Loop through button layout and create arrays for each port matching the DN label
													foreach($cmebuttonlayoutar as $intid=>$tmpbuttonlayoutar){
														foreach($tmpbuttonlayoutar as $nameid=>$unused){
															$cmednnamear[$intid][$nameid]=$cmednnametempar[$nameid];
														}
													}
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cmednnamear); echo "</font></pre>";
													}
												}
											} else {
												echo "<font style=\"color: red;\">'$ciscovoiceip' is responsive to SNMP but it's not a CME device.</font><br /><br />\n";
											}
										} else if($_POST['ciscovoicetype']=="cucm"){
											$cucmversion=StandardSNMPGet($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.156.1.1.2.1.4.1",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass);
											if($cucmversion && !strstr($cucmversion,'No Such')){
												$ciscocucmworks=true;
												echo "<b>Cisco CUCM System Name:</b> $testciscovoicesnmp<br />\n";
												$cucmsysdescr=preg_replace('!\s+!', ' ',StandardSNMPGet($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"SNMPv2-MIB::sysDescr.0",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass));
												echo "<b>Cisco CUCM System Description:</b> $cucmsysdescr<br />\n";
												$cucmuptime=StandardSNMPGet($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"DISMAN-EVENT-MIB::sysUpTimeInstance",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,"-O v");
												echo "<b>Cisco CUCM Uptime:</b> $cucmuptime<br />\n";
												echo "<b>Cisco CUCM Version:</b> $cucmversion<br />\n";
												
												$ephonesonswitch=0;
												$usecdpforsep=true;
												if($_POST['cdpname']){
													foreach($cdpnamear as $cdpname){
														//Check first 3 characters of CDP name for "SEP"
														if(substr($cdpname,0,3)=="SEP"){
															$ephonesonswitch+=1;
														}
													}
												}
												//Only check LLDP if it's selected and CDP showed nothing...prefer CDP results
												if($_POST['lldpname'] && $ephonesonswitch==0){
													$usecdpforsep=false;
													foreach($lldpnamear as $lldpname){
														//Check first 3 characters of LLDP name for "SEP"
														if(substr($lldpname,0,3)=="SEP"){
															$ephonesonswitch+=1;
														}
													}
												}
												$cucmsepidar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.156.1.2.1.1.20",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
												echo "<b>Cisco CUCM Phones Configured:</b> " . count($cucmsepidar) . "<br />\n";
												if($usecdpforsep==true){
													foreach($cdpnamear as $cdpid=>$cdpname){
														if(substr($cdpname,0,3)=="SEP"){
															//Array ID is interface ID, value is SEP ID
															$cucmsepidtosepidar[$cdpid]=array_search($cdpname,$cucmsepidar);
														}
													}
												} else {
													foreach($lldpnamear as $lldpid=>$lldpname){
														if(substr($lldpname,0,3)=="SEP"){
															//Array ID is interface ID, value is SEP ID
															$cucmsepidtosepidar[$lldpid]=array_search($lldpname,$cucmsepidar);
														}
													}
												}
												//Only get phone info if there are phones on the switch
												if($ephonesonswitch>0){
													$cucmphoneipstempar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.156.1.2.1.1.6",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													foreach($cucmphoneipstempar as $phoneipsepid=>$phoneip){
														if($phoneip && $phoneip!='0.0.0.0' && array_search($phoneipsepid,$cucmsepidtosepidar)!=0){
															$cucmphoneipsar[array_search($phoneipsepid,$cucmsepidtosepidar)]=$phoneip;
														}
													}
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cucmphoneipsar); echo "</font></pre>";
													}
													$cucmphonemodelidar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.156.1.2.1.1.18",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													$cucmphonemodeldevicear=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.156.1.1.8.1.3",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													//Merge SEP ID to model ID array with model ID to actual model and finally tie all that to a physical port
													foreach($cucmphonemodelidar as $phonemodelsepid=>$phonemodel){
														if($phonemodel && array_search($phonemodelsepid,$cucmsepidtosepidar)!=0){
															$cucmphonemodelar[array_search($phonemodelsepid,$cucmsepidtosepidar)]=$cucmphonemodeldevicear[$phonemodel];
														}
													}
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cucmphonemodelar); echo "</font></pre>";
													}
													$cucmphoneprototempar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.156.1.2.1.1.19",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													foreach($cucmphoneprototempar as $phoneprotosepid=>$phoneproto){
														if($phoneproto && array_search($phoneprotosepid,$cucmsepidtosepidar)!=0){
															$cucmphoneprotoar[array_search($phoneprotosepid,$cucmsepidtosepidar)]=$phoneproto;
														}
													}
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cucmphoneprotoar); echo "</font></pre>";
													}
													$cucmphonestatustempar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.156.1.2.1.1.7",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													$registeredphones=0;
													foreach($cucmphonestatustempar as $phonestatussepid=>$phonestatus){
														if($phonestatus=="Registered"){
															$registeredphones+=1;
														}
														if($phonestatus && array_search($phonestatussepid,$cucmsepidtosepidar)!=0){
															$cucmphonestatusar[array_search($phonestatussepid,$cucmsepidtosepidar)]=$phonestatus;
														}
													}
													echo "<b>Cisco CUCM Phones Registered:</b> $registeredphones<br />\n";
													echo "<b>Cisco CUCM Phones on $theip:</b> $ephonesonswitch<br /><br />\n";
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cucmphonestatusar); echo "</font></pre>";
													}
													$cucmphonedesctempar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.156.1.2.1.1.4",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													foreach($cucmphonedesctempar as $phonedescsepid=>$phonedesc){
														if($phonedesc && array_search($phonedescsepid,$cucmsepidtosepidar)!=0){
															$cucmphonedescar[array_search($phonedescsepid,$cucmsepidtosepidar)]=$phonedesc;
														}
													}
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cucmphonedescar); echo "</font></pre>";
													}
													$cucmphoneusertempar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.156.1.2.1.1.5",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													foreach($cucmphoneusertempar as $phoneusersepid=>$phoneuser){
														if($phoneuser && array_search($phoneusersepid,$cucmsepidtosepidar)!=0){
															$cucmphoneuserar[array_search($phoneusersepid,$cucmsepidtosepidar)]=$phoneuser;
														}
													}
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cucmphoneuserar); echo "</font></pre>";
													}
													$cucmphoneexttempar=StandardSNMPWalk($ciscovoiceip,$snmpversionciscovoice,$snmpstringciscovoice,"1.3.6.1.4.1.9.9.156.1.2.5.1.2",$snmpv3user,$snmpv3authproto,$snmpv3authpass,$snmpv3seclevel,$snmpv3privproto,$snmpv3privpass,null,null);
													//Create phone extension array based on interface ID
													foreach($cucmphoneexttempar as $phoneextsepid=>$phoneextar){
														if(array_search($phoneextsepid,$cucmsepidtosepidar)!=0){
															//If there's only one extension
															if(sizeof($phoneextar)==1){
																$cucmphoneextar[array_search($phoneextsepid,$cucmsepidtosepidar)]=array(0=>$phoneextar[0]);
															//If there are more extensions
															} else {
																//Loop through each extension
																foreach($phoneextar as $phoneext){
																	//If it's the first extension
																	if(sizeof($cucmphoneextar[array_search($phoneextsepid,$cucmsepidtosepidar)])==0){
																		$cucmphoneextar[array_search($phoneextsepid,$cucmsepidtosepidar)]=array(0=>$phoneext);
																	//If it's an additional extension
																	} else {
																		array_push($cucmphoneextar[array_search($phoneextsepid,$cucmsepidtosepidar)],$phoneext);
																	}
																}
															}
														}
													}
													if($_POST['debug'] && $_POST['debugoutput']){
														echo "<pre><font style=\"color: red;\">"; print_r($cucmphoneextar); echo "</font></pre>";
													}
												}
											} else {
												echo "<font style=\"color: red;\">'$ciscovoiceip' is responsive to SNMP but it's not a CUCM device.</font><br /><br />\n";
											}
										}
									}
								} else {
									echo "<font style=\"color: red;\">You selected to use an alternate SNMPv2 community for Cisco Voice. Please enter one.</font><br /><br />\n";
								}
							} else {
								echo "<font style=\"color: red;\">Please enter CME/CUCM IP address</font><br /><br />\n";
							}
						} else {
							echo "<font style=\"color: red;\">Please select <b><i>LLDP Name</i></b> or <b><i>CDP Name</i></b> in Additional Features to get Cisco phone device names</font><br /><br />\n";
						}
					}
					if($_POST['debug']){
						echo "<br />\n";
					}
					if(count($ifdescar)>0){
						//VLAN Table
						if($_POST['vlanextra'] && sizeof($vlannamear)>1){
							//Headerar used for Excel export
							$vlanheaderar[]="VLAN";
							$vlanheaderar[]="Status";
							$vlanheaderar[]="Name";
							$vlanheaderar[]="IP Address";
							$vlanheaderar[]="Subnet Mask";
							$vlanarstring='$vlanid,$vlanstatusar[$vlanid],$vlannamear[$vlanid],$l3vlanaddrar[$vlanid],$l3vlanmaskar[$vlanid]';
							if($_POST['vlanchoice']=="cisco"){
								$vlanheaderar[]="Port Members";
								$vlanarstring=$vlanarstring . ',$tmpar';
								//$vlanarstring=$vlanarstring . ',$vlanmembersar[$vlanid]';
							}
							if($_POST['vlanchoice']=="avaya"){
								$vlanheaderar[]="Port Members";
								$vlanarstring=$vlanarstring . ',$vlanmembersar[$vlanid]';
							}
							echo "<table class=\"output\" id=\"floater\">\n";
							echo "<thead><tr>";
							//Print out headerar for table
							foreach($vlanheaderar as $header){
								echo "<th>$header</th>";
							}
							echo "</tr></thead><tbody>\n";
							foreach($vlannamear as $vlanid=>$vlanname){
								echo "<tr>";
								echo "<td>$vlanid</td>";
								echo "<td>" . $vlanstatusar[$vlanid] . "</td>";
								echo "<td>$vlanname</td>";
								echo "<td>" . $l3vlanaddrar[$vlanid] . "</td>";
								echo "<td>" . $l3vlanmaskar[$vlanid] . "</td>";
								if($_POST['vlanchoice']=="cisco"){
									echo "<td style=\"width: 500px;\">";
									$count=0;
									$portcount=0;
									$tmparcnt=0;
									unset($tmpar);
									foreach($vlanmembersar[$vlanid] as $port){
										if($count==0){
											echo "$port";
										} else {
											echo ", $port";
										}
										//Used in Excel output so each line in a cell has 3 ports
										if($portcount==3){
											$tmparcnt+=1;
											$portcount=1;
											$tmpar[$tmparcnt]=$port;
										} else if($tmparcnt>0 || ($tmparcnt==0 && $portcount>0)){
											$tmpar[$tmparcnt]=$tmpar[$tmparcnt] . ", $port";
											$portcount+=1;
										} else {
											$tmpar[$tmparcnt]=$port;
											$portcount+=1;
										}
										$count+=1;
									}
									echo "</td>";
								}
								if($_POST['vlanchoice']=="avaya"){
									echo "<td>" . $vlanmembersar[$vlanid] . "</td>";
								}
								echo "</tr>\n";
								eval('$vlandataar[] = array(' . $vlanarstring . ');');
							}
							$excelar[]=array($vlanheaderar,$vlandataar);
							echo "</tbody><tfoot></tfoot></table><br />\n";
							?>
							<script>
							$("#floater").thfloat();
							</script>
							<?php
						}
						
						//Headerar used for Excel export
						if($_POST['debug'] && $_POST['debugintid']){
							$headerar[]="Interface ID";
							$dataarstring='$theid,$ifdescar[$theid]';
						} else {
							$dataarstring='$ifdescar[$theid]';
						}
						$headerar[]="Description";
						//$headerar[]="Name";
						if(!$_POST['hidealias']){
							$headerar[]="Alias";
							$dataarstring=$dataarstring . ',$ifaliasar[$theid]';
						}
						if(!$_POST['hideadminstatus']){
							$headerar[]="Admin Status";
							$dataarstring=$dataarstring . ',$ifadminstatusar[$theid]';
						}
						if(!$_POST['hideopstatus']){
							$headerar[]="Operational Status";
							$dataarstring=$dataarstring . ',$ifoperstatusar[$theid]';
						}
						if(!$_POST['hidespeed']){
							$headerar[]="Speed (Mbps)";
							$dataarstring=$dataarstring . ',$ifspeedar[$theid]';
						}
						if(!$_POST['hideduplex']){
							$headerar[]="Duplex";
							$dataarstring=$dataarstring . ',$ifduplexar[$theid]';
						}
						if($_POST['ciscointpoe']){
							$headerar[]="PoE Available";
							$headerar[]="PoE Used";
							$dataarstring=$dataarstring . ',$ciscopoeavailar[$theid],$ciscopoeactualar[$theid]';
						}
						if($_POST['ciscointpoedev']){
							$headerar[]="PoE Device";
							$dataarstring=$dataarstring . ',$ciscopoedevar[$theid]';
						}
						if($_POST['vlanchooser'] && $_POST['vlanchoice']=="cisco"){
							$headerar[]="VLAN";
							$headerar[]="DTP Mode";
							$headerar[]="Operational Mode";
							$dataarstring=$dataarstring . ',$ciscovlanar[$theid],$ciscotrunkstatear[$theid],$ciscotaggingar[$theid]';
						}
						if($_POST['vlanchooser'] && $_POST['vlanchoice']=="avaya"){
							$headerar[]="VLAN PVID";
							$headerar[]="Port VLAN Members";
							$headerar[]="Port Tagging";
							$dataarstring=$dataarstring . ',$avayavlanar[$theid],$avayavlanmembersar[$theid],$avayataggingar[$theid]';
						}
						if($_POST['vlanchooser'] && $_POST['vlanchoice']=="juniper"){
							$headerar[]="Untagged VLAN's";
							$headerar[]="Tagged VLAN's";
							$headerar[]="Port Type";
							$dataarstring=$dataarstring . ',$junipervlanuntaggedar[$theid],$junipervlantaggedar[$theid],$junipervlanmodear[$theid]';
						}
						if($_POST['vlanchooser'] && $_POST['vlanchoice']=="netgear"){
							$headerar[]="VLAN PVID";
							$headerar[]="Untagged VLAN's";
							$headerar[]="Tagged VLAN's";
							$dataarstring=$dataarstring . ',$netgearvlanar[$theid],$netgearvlanmembersar[$theid],$netgearvlantaggedmembersar[$theid]';
						}
						if($_POST['vlanchooser'] && $_POST['vlanchoice']=="h3c"){
							$headerar[]="VLAN PVID";
							$headerar[]="Port VLAN Members";
							$headerar[]="Port Type";
							$dataarstring=$dataarstring . ',$hpvlanar[$theid],$h3cvlanmembersar[$theid],$h3cportcapabilities[$theid]';
						}
						if($_POST['clientmac'] || $_POST['clientarp']){
							if($_POST['macchoice']=="alt" || $_POST['macchoice']=="cisco" || $_POST['macchoice']=="extreme"){
								$headerar[]="MAC Address(es) - VLAN";
							} else {
								$headerar[]="MAC Address(es)";
							}
							$dataarstring=$dataarstring . ',$tmpmacadd';
						}
						if($_POST['macoui']){
							$headerar[]="MAC Address OUI";
							$dataarstring=$dataarstring . ',$tmpoui';
						}
						if($_POST['clientarp']){
							$headerar[]="IP Address(es)";
							if(!$_POST['ignoredns']){
								$headerar[]="Host Name(s)";
								$dataarstring=$dataarstring . ',$tmpipadd,$tmphostadd';
							} else {
								$dataarstring=$dataarstring . ',$tmpipadd';
							}
						}
						if($_POST['trafficstats']){
							$headerar[]="In Bandwidth (MB)";
							$headerar[]="Out Bandwidth (MB)";
							$dataarstring=$dataarstring . ',$ifinoctetsar[$theid],$ifoutoctetsar[$theid]';
						}
						if($_POST['errorsdiscard']){
							$headerar[]="In Errors";
							$headerar[]="Out Errors";
							$headerar[]="In Discards";
							$headerar[]="Out Discards";
							$dataarstring=$dataarstring . ',$ifinerrorsar[$theid],$ifouterrorsar[$theid],$ifindiscardsar[$theid],$ifoutdiscardsar[$theid]';
						}
						if($_POST['ciscopps']){
							$headerar[]="PPS In";
							$headerar[]="PPS Out";
							$dataarstring=$dataarstring . ',$ciscoppsinar[$theid],$ciscoppsoutar[$theid]';
						}
						if($_POST['ciscoinoutrate']){
							$headerar[]="In Rate (mbps)";
							$headerar[]="Out Rate (mbps)";
							$dataarstring=$dataarstring . ',$ciscoinratear[$theid],$ciscooutratear[$theid]';
						}
						if($_POST['cdpname']){
							$headerar[]="CDP Name";
							$dataarstring=$dataarstring . ',$cdpnamear[$theid]';
						}
						if($_POST['cdpip']){
							$headerar[]="CDP IP";
							$dataarstring=$dataarstring . ',$cdpipar[$theid]';
						}
						if($_POST['cdpdev']){
							$headerar[]="CDP Device";
							$dataarstring=$dataarstring . ',$cdpdevar[$theid]';
						}
						if($_POST['cdpint']){
							$headerar[]="CDP Remote Interface";
							$dataarstring=$dataarstring . ',$cdpintar[$theid]';
						}
						if($_POST['lldpname']){
							$headerar[]="LLDP Name";
							$dataarstring=$dataarstring . ',$lldpnamear[$theid]';
						}
						if($_POST['lldpip']){
							$headerar[]="LLDP MAC/IP";
							$dataarstring=$dataarstring . ',$lldpipar[$theid]';
						}
						if($_POST['lldpdev']){
							$headerar[]="LLDP Device";
							$dataarstring=$dataarstring . ',$lldpdevar[$theid]';
						}
						if($_POST['lldpint']){
							$headerar[]="LLDP Remote Interface";
							$dataarstring=$dataarstring . ',$lldpintar[$theid]';
						}
						if($_POST['edpdev']){
							$headerar[]="EDP Device";
							$dataarstring=$dataarstring . ',$edpdevar[$theid]';
						}
						if($_POST['edpint']){
							$headerar[]="EDP Remote Interface";
							$dataarstring=$dataarstring . ',$edpintar[$theid]';
						}
						if($ciscocmeworks==true){
							$headerar[]="CME Phone IP";
							$headerar[]="CME Phone Model";
							$headerar[]="CME Phone Status";
							$headerar[]="CME Phone Button Info";
							$headerar[]="CME DN Label(s)";
							$headerar[]="CME DN Name(s)";
							$dataarstring=$dataarstring . ',$cmephoneipsar[$theid],$cmephonemodelar[$theid],$cmephonestatusar[$theid],$cmebuttonlayoutar[$theid],$cmednlabelar[$theid],$cmednnamear[$theid]';
						}
						if($ciscocucmworks==true){
							$headerar[]="CUCM Phone IP";
							$headerar[]="CUCM Phone Model";
							$headerar[]="CUCM Phone Protocol";
							$headerar[]="CUCM Phone Status";
							$headerar[]="CUCM Phone Description";
							$headerar[]="CUCM Phone Username";
							$headerar[]="CUCM Phone Extension(s)";
							$dataarstring=$dataarstring . ',$cucmphoneipsar[$theid],$cucmphonemodelar[$theid],$cucmphoneprotoar[$theid],$cucmphonestatusar[$theid],$cucmphonedescar[$theid],$cucmphoneuserar[$theid],$cucmphoneextar[$theid]';
						}
						echo "<table class=\"output\" id=\"floater2\">\n";
						echo "<thead><tr>";
						//Print out headerar for table
						foreach($headerar as $header){
							//Adjust header widths - Makes the floating header stay on 1 line when scrolling
							if($header=='Interface ID'){
								echo "<th style=\"width: 100px;\">$header</th>";
							} else if($header=='VLAN PVID'){
								echo "<th style=\"width: 90px;\">$header</th>";
							} else if($header=="Admin Status"){
								echo "<th style=\"width: 110px;\">$header</th>";
							} else if($header=="Operational Status"){
								echo "<th style=\"width: 150px;\">$header</th>";
							} else if($header=="Speed (Mbps)"){
								echo "<th style=\"min-width: 120px;\">$header</th>";
							} else if($header=="Operational Mode"){
								echo "<th style=\"width: 150px;\">$header</th>";
							} else if($header=="MAC Address(es) - VLAN"){
								echo "<th style=\"width: 200px;\">$header</th>";
							} else if($header=="IP Address(es)"){
								echo "<th style=\"width: 130px;\">$header</th>";
							} else if($header=="In Rate (mbps)"){
								echo "<th style=\"width: 125px;\">$header</th>";
							} else if($header=="Out Rate (mbps)"){
								echo "<th style=\"width: 130px;\">$header</th>";
							} else if($header=="LLDP MAC/IP"){
								echo "<th style=\"min-width: 80px;\">$header</th>";
							} else if($header=="LLDP Device"){
								echo "<th style=\"width: 230px;\">$header</th>";
							} else if($header=="LLDP Remote Interface"){
								echo "<th style=\"min-width: 180px;\">$header</th>";
							} else if($header=="EDP Device"){
								echo "<th style=\"width: 230px;\">$header</th>";
							} else if($header=="EDP Remote Interface"){
								echo "<th style=\"min-width: 180px;\">$header</th>";
							} else if($header=="CME Phone IP"){
								echo "<th style=\"min-width: 120px;\">$header</th>";
							} else if($header=="CME Phone Model"){
								echo "<th style=\"min-width: 150px;\">$header</th>";
							} else if($header=="CME Phone Status"){
								echo "<th style=\"min-width: 150px;\">$header</th>";
							} else if($header=="CME Phone Button Info"){
								echo "<th style=\"min-width: 330px;\">$header</th>";
							} else if($header=="CME DN Label(s)"){
								echo "<th style=\"min-width: 180px;\">$header</th>";
							} else if($header=="CME DN Name(s)"){
								echo "<th style=\"min-width: 180px;\">$header</th>";
							} else {
								echo "<th>$header</th>";
							}
						}
						echo "</tr></thead><tbody>\n";
						foreach($ifdescar as $theid => $ifdesc){
							if($ifdesc){
								echo "<tr>";
								if($_POST['debug'] && $_POST['debugintid']){
									echo "<td><font style=\"color: #008000;\">$theid</font></td>";
								}
								echo "<td>" . $ifdescar[$theid] . "</td>";
								//echo "<td>" . $ifnamear[$theid] . "</td>";
								if(!$_POST['hidealias']){ echo "<td>" . $ifaliasar[$theid] . "</td>"; }
								if(!$_POST['hideadminstatus']){ echo "<td>" . $ifadminstatusar[$theid] . "</td>"; }
								if(!$_POST['hideopstatus']){ echo "<td>" . $ifoperstatusar[$theid] . "</td>"; }
								if(!$_POST['hidespeed']){ echo "<td>" . $ifspeedar[$theid] . "</td>"; }
								if(!$_POST['hideduplex']){ 
									if($ifduplexar[$theid]=="Half"){
										echo "<td><font style=\"color: red;\">" . $ifduplexar[$theid] . "</font></td>";
									} else {
										echo "<td>" . $ifduplexar[$theid] . "</td>";
									}
								}
								if($_POST['ciscointpoe']){
									echo "<td>" . $ciscopoeavailar[$theid] . "</td>";
									echo "<td>" . $ciscopoeactualar[$theid] . "</td>";
								}
								if($_POST['ciscointpoedev']){
									echo "<td>" . $ciscopoedevar[$theid] . "</td>";
								}
								if($_POST['vlanchooser'] && $_POST['vlanchoice']=="cisco"){
									echo "<td>" . $ciscovlanar[$theid] . "</td>";
									echo "<td>" . $ciscotrunkstatear[$theid] . "</td>";
									echo "<td>" . $ciscotaggingar[$theid] . "</td>";
								}
								if($_POST['vlanchooser'] && $_POST['vlanchoice']=="avaya"){
									//Catch the case where a PVID was misconfigured and the PVID is not part of the VLAN membership of the port
									if(!in_array($avayavlanar[$theid],$avayavlanmembersar[$theid]) && $avayavlanar[$theid]!=$avayavlanmembersar[$theid]){
										echo "<td><font style=\"color: red; font-weight: bold;\">" . $avayavlanar[$theid] . "</font></td>";
									} else {
										echo "<td>" . $avayavlanar[$theid] . "</td>";
									}
									echo "<td>";
									if(count($avayavlanmembersar[$theid])==1){
										echo $avayavlanmembersar[$theid];
									} else {
										foreach($avayavlanmembersar[$theid] as $member){
											echo "$member<br />";
										}
									}
									echo "</td>";
									echo "<td>" . $avayataggingar[$theid] . "</td>";
								}
								if($_POST['vlanchooser'] && $_POST['vlanchoice']=="juniper"){
									echo "<td>" . $junipervlanuntaggedar[$theid] . "</td>";
									echo "<td>";
									foreach($junipervlantaggedar[$theid] as $tagged){
										echo "$tagged<br />";
									}
									echo "</td>";
									echo "<td>" . $junipervlanmodear[$theid] . "</td>";
								}
								if($_POST['vlanchooser'] && $_POST['vlanchoice']=="netgear"){
									echo "<td>" . $netgearvlanar[$theid] . "</td>";
									echo "<td>";
									if(sizeof($netgearvlanmembersar)==1){
										echo $netgearvlanmembersar[$theid][0];
									} else {
										foreach($netgearvlanmembersar[$theid] as $member){
											echo "$member<br />";
										}
									}
									echo "</td>";
									echo "<td>";
									if(sizeof($netgearvlantaggedmembersar[$theid])==1){
										echo $netgearvlantaggedmembersar[$theid][0];
									} else {
										foreach($netgearvlantaggedmembersar[$theid] as $member){
											echo "$member<br />";
										}
									}
									echo "</td>";
								}
								if($_POST['vlanchooser'] && $_POST['vlanchoice']=="h3c"){
									echo "<td>" . $hpvlanar[$theid] . "</td>";
									echo "<td>";
									if(count($h3cvlanmembersar[$theid])==1){
										echo $h3cvlanmembersar[$theid][0];
									} else {
										foreach($h3cvlanmembersar[$theid] as $member){
											echo "$member<br />";
										}
									}
									echo "</td>";
									echo "<td>";
									if(count($h3cvlanmembersar[$theid])==1){
										$h3cportcapabilities[$theid]="access";
										echo "access";
									} else if(count($h3cvlanmembersar[$theid])>1){
										$h3cportcapabilities[$theid]="trunk";
										echo "trunk";
									} else {
										echo "&nbsp;";
									}
									echo "</td>";
								}
								if($_POST['clientmac'] || $_POST['clientarp']){
									echo "<td>";
									unset($tmpmacadd);
									foreach($ifindextomacar[$theid] as $macadd){
										if($_POST['macchoice']=="alt" || $_POST['macchoice']=="cisco" || $_POST['macchoice']=="extreme"){
											$tmpmacadd[]="$macadd - " . $macvlanar[$macadd];
											echo "$macadd - " . $macvlanar[$macadd] . "<br />";
										} else {
											$tmpmacadd[]=$macadd;
											echo "$macadd<br />";
										}
									}
									//Don't create an array for single MAC address entries - Used in Excel export
									if(sizeof($tmpmacadd)==1){
										$tmpmacadd=$tmpmacadd[0];
									}
									echo "</td>";
								}
								if($_POST['macoui']){
									echo "<td>";
									unset($tmpoui);
									if(sizeof($tmpmacadd)==1){
										list($a,$b,$c,$d,$e,$f)=explode(':',$tmpmacadd);
										//Store for Excel export
										$tmpoui=$macouiar["$a:$b:$c"];
										if($tmpoui){
											echo $tmpoui;
										} else {
											if(!in_array("$a:$b:$c",$nomacoui)){
												$nomacoui[]="$a:$b:$c";
											}
										}
									} else {
										foreach($tmpmacadd as $macadd){
											list($a,$b,$c,$d,$e,$f)=explode(':',$macadd);
											$tmpouitmp=$macouiar["$a:$b:$c"];
											if($tmpouitmp){
												//Store for Excel export
												$tmpoui[]=$tmpouitmp;
												echo "$tmpouitmp<br />";
											} else {
												$tmpoui[]=null;
												if(!in_array("$a:$b:$c",$nomacoui)){
													$nomacoui[]="$a:$b:$c";
												}
											}
										}
									}
									echo "</td>";
								}
								//Make sure there's an ARP table to utilize before proceeding
								if($_POST['clientarp'] && $arpworks==true){
									echo "<td>";
									unset($tmpipadd);
									unset($tmphostadd);
									$ipaddcnt=0;
									foreach($ifindextomacar[$theid] as $macadd){
										//If there's an IP for the MAC
										if($arpar[$macadd]){
											echo $arpar[$macadd] . "<br />";
											//Keep track if there were IP's for the entry
											$ipaddcnt+=1;
											//Keep track of which IP's needed for name resolution
											$tmpipadd[]=$arpar[$macadd];
										//Print a line break for empty lines so when there's multiple MAC's and IP's, the MAC and IP line up correctly
										} else {
											$tmpipadd[]="&nbsp;";
											echo "<br />";
										}
									}
									echo "</td>";
									//Print empty line for ports without an IP address found
									if($ipaddcnt==0 && !$_POST['ignoredns']){
										echo "<td>&nbsp;</td>";
									} else if(!$_POST['ignoredns']){
										echo "<td>";
										foreach($tmpipadd as $tmpip){
											//Handle empty lines - Used for multiple IP's and MAC's on a port
											if($tmpip=="&nbsp;"){
												echo "<br />";
												$tmphostadd[]="&nbsp;";
											} else {
												//Do a DNS lookup
												//grep -m only matches the first entry...didn't code for multiple PTR DNS records even though there could be
												$dnslookupstring="host -W 2 -R 1 $tmpip $dnsserver | grep -m 1 pointer";
												$dns=preg_replace("/\.$/","",shell_exec($dnslookupstring));
												list($junk,$dns)=explode('domain name pointer ',$dns);
												echo trim($dns) . "<br />";
												if($dns){
													$tmphostadd[]=trim($dns);
												} else {
													$tmphostadd[]="&nbsp;";
												}
											}
										}
										//Don't create an array for single IP address entries - Used in Excel export
										if(sizeof($tmpipadd)==1) $tmpipadd=$tmpipadd[0];
										//Don't create an array for single host name entries - Used in Excel export
										if(sizeof($tmphostadd)==1) $tmphostadd=$tmphostadd[0];
										echo "</td>";
									}
								//ARP table and DNS are unavailable
								} else if($_POST['clientarp'] && $arpworks==false){
									echo "<td>&nbsp;</td>";
									if(!$_POST['ignoredns']){
										echo "<td>&nbsp;</td>";
									}
								}
								if($_POST['trafficstats']){
									echo "<td>" . $ifinoctetsar[$theid] . "</td>";
									echo "<td>" . $ifoutoctetsar[$theid] . "</td>";
								}
								if($_POST['errorsdiscard']){
									//Get number of seconds
									list($junk,$syscalctime)=explode('(',$sysuptime);
									list($syscalctime,$junk)=explode(')',$syscalctime);
									//Remove last 2 characters from time ticks - milliseconds
									$syscalctime=substr($syscalctime,0,-2);
									//If there's 1 or more errors or discards per minute, print it red
									if(($ifinerrorsar[$theid]/($syscalctime/60))>=1){
										echo "<td><font style=\"color: red;\">" . $ifinerrorsar[$theid] . "</font></td>";
									} else {
										echo "<td>" . $ifinerrorsar[$theid] . "</td>";
									}
									if(($ifouterrorsar[$theid]/($syscalctime/60))>=1){
										echo "<td><font style=\"color: red;\">" . $ifouterrorsar[$theid] . "</font></td>";
									} else {
										echo "<td>" . $ifouterrorsar[$theid] . "</td>";
									}
									if(($ifindiscardsar[$theid]/($syscalctime/60))>=1){
										echo "<td><font style=\"color: red;\">" . $ifindiscardsar[$theid] . "</font></td>";
									} else {
										echo "<td>" . $ifindiscardsar[$theid] . "</td>";
									}
									if(($ifoutdiscardsar[$theid]/($syscalctime/60))>=1){
										echo "<td><font style=\"color: red;\">" . $ifoutdiscardsar[$theid] . "</font></td>";
									} else {
										echo "<td>" . $ifoutdiscardsar[$theid] . "</td>";
									}
								}
								if($_POST['ciscopps']){
									echo "<td>" . $ciscoppsinar[$theid] . "</td>";
									echo "<td>" . $ciscoppsoutar[$theid] . "</td>";
								}
								if($_POST['ciscoinoutrate']){
									echo "<td>" . $ciscoinratear[$theid] . "</td>";
									echo "<td>" . $ciscooutratear[$theid] . "</td>";
								}
								if($_POST['cdpname']){
									echo "<td>" . $cdpnamear[$theid] . "</td>";
								}
								if($_POST['cdpip']){
									echo "<td>" . $cdpipar[$theid] . "</td>";
								}
								if($_POST['cdpdev']){
									echo "<td>" . $cdpdevar[$theid] . "</td>";
								}
								if($_POST['cdpint']){
									echo "<td>" . $cdpintar[$theid] . "</td>";
								}
								if($_POST['lldpname']){
									echo "<td>" . $lldpnamear[$theid] . "</td>";
								}
								if($_POST['lldpip']){
									echo "<td>" . $lldpipar[$theid] . "</td>";
								}
								if($_POST['lldpdev']){
									echo "<td>" . $lldpdevar[$theid] . "</td>";
								}
								if($_POST['lldpint']){
									echo "<td>" . $lldpintar[$theid] . "</td>";
								}
								if($_POST['edpdev']){
									echo "<td>" . $edpdevar[$theid] . "</td>";
								}
								if($_POST['edpint']){
									echo "<td>" . $edpintar[$theid] . "</td>";
								}
								if($ciscocmeworks==true){
									echo "<td>" . $cmephoneipsar[$theid] . "</td>";
									echo "<td>" . $cmephonemodelar[$theid] . "</td>";
									echo "<td>" . $cmephonestatusar[$theid] . "</td>";
									echo "<td>";
									foreach($cmebuttonlayoutar[$theid] as $cmebutton){
										echo "$cmebutton<br />";
									}
									echo "</td>";
									echo "<td>";
									foreach($cmednlabelar[$theid] as $dnlabel){
										echo "$dnlabel<br />";
									}
									echo "</td>";
									echo "<td>";
									foreach($cmednnamear[$theid] as $dnname){
										echo "$dnname<br />";
									}
									echo "</td>";
								}
								if($ciscocucmworks==true){
									echo "<td>" . $cucmphoneipsar[$theid] . "</td>";
									echo "<td>" . $cucmphonemodelar[$theid] . "</td>";
									echo "<td>" . $cucmphoneprotoar[$theid] . "</td>";
									echo "<td>" . $cucmphonestatusar[$theid] . "</td>";
									echo "<td>" . $cucmphonedescar[$theid] . "</td>";
									echo "<td>" . $cucmphoneuserar[$theid] . "</td>";
									echo "<td>";
									foreach($cucmphoneextar[$theid] as $cucmext){
										echo "$cucmext<br />";
									}
									echo "</td>";
								}
								echo "</tr>\n";
								/*
								Array for Excel export - Kudos to Brett Langdon for this line of code:
								http://www.phphelp.com/forum/general-php-help/echo-list-of-variables-into-array/
								*/
								eval('$dataar[] = array(' . $dataarstring . ');');
							}
						}
						echo "</tbody><tfoot></tfoot></table><br />\n";
						?>
						<script>
						$("#floater2").thfloat();
						</script>
						<?php
						//Add system table to Excel Array for multi-table printout format
						$excelar[]=array($headerar,$dataar);
						if($_POST['showarp'] && sizeof($arpar)>2){
							natsort($arpar);
							//echo "<pre>"; print_r($arpar); echo "</pre>";
							$arpheaderar[]="MAC Address";
							$arpheaderar[]="IP Address";
							echo "<table class=\"output\" id=\"floater3\">\n";
							echo "<thead><tr>";
							//Print out headerar for table
							foreach($arpheaderar as $header){
								echo "<th>$header</th>";
							}
							echo "</tr></thead><tbody>\n";
							foreach($arpar as $macadd => $ipadd){
								if($macadd){
									echo "<tr>";
									echo "<td>$macadd</td>";
									echo "<td>$ipadd</td>";
									echo "</tr>\n";
									$newarpar[]=array($macadd,$ipadd);
								}
							}
							echo "</tbody><tfoot></tfoot></table><br />\n";
							?>
							<script>
							$("#floater3").thfloat();
							</script>
							<?php
							$excelar[]=array($arpheaderar,$newarpar);
						}
						//echo "<pre>"; print_r($excelar); echo "</pre>\n";
						$_SESSION['excelar']=$excelar;
						if($_POST['vlanextra'] && sizeof($vlannamear)>1){
							//Don't use a frozen pane while the VLAN table is on the page because there isn't enough room
							$_SESSION['freezepanearnum']=-1;
						} else {
							//Freeze the 2nd array (#1) for scrolling in Excel
							$_SESSION['freezepanearnum']=1;
						}
						//Properties for excel file
						if($_POST['exportfileformatrow']){
							$devtempname="";
							list($devtempname,$junk)=explode('.',$testsnmp,2);
							if($_POST['exportfileformatchoice']=="ipname"){
								$subjectstring="$theip - $devtempname - Network Info";
							} else if($_POST['exportfileformatchoice']=="nameip"){
								$subjectstring="$devtempname - $theip - Network Info";
							} else if($_POST['exportfileformatchoice']=="custom"){
								$subjectstring=preg_replace('/<name>/',$devtempname,preg_replace('/<ip>/',$theip,$_POST['customfilename']));
							}
							$excelpropertiesar=array(
								 "setTitle"=>"$theip",
								 "setDescription"=>"Network Info",
								 "setSubject"=>$subjectstring,
								 "setKeywords"=>"Network Info",
								 "setCategory"=>"Network Info",
								 "filename"=>"netinfo.xlsx"
							);
						} else {
							$excelpropertiesar=array(
								 "setTitle"=>"$theip",
								 "setDescription"=>"Network Info",
								 "setSubject"=>"Network Info",
								 "setKeywords"=>"Network Info",
								 "setCategory"=>"Network Info",
								 "filename"=>"netinfo.xlsx"
							);
						}
						$_SESSION['excelpropertiesar']=$excelpropertiesar;
						//Adjustment for system description column width
						if(strlen($sysdescr)>=40){
							$columnwidthar=array(
								 "B"=>"50"
							);
							$_SESSION['columnwidth']=$columnwidthar;
							$celltextwrapar=array(
								 "B3"
							);
							$_SESSION['celltextwrap']=$celltextwrapar;
						}
						//Export XLSX Button
						if(sizeof($excelar)>0){
							echo "&nbsp;<form action='../excel/multitabletoxls.php' method='post' style='display: inline;'>\n";
							echo "<input type='submit' value='Export to XLSX' />\n";
							echo "</form>\n";
						}
						$time=end_time($time_start);
						echo "\n<br /><br />SNMP queries completed in {$time}seconds.";
						//Print error messages about the MAC OUI file not updated
						if(count($nomacoui)){
							echo "<br /><br /><font style=\"color: red;\"><b>The following MAC address OUI's were not in your version of the MAC OUI list:</b><br />\n";
							foreach($nomacoui as $macoui){
								echo "$macoui<br />\n";
							}
							echo "</font><br />\n";
							echo "The MAC OUI list is a static file on your server/PC obtained <a target=\"_NEW\" href=\"http://standards.ieee.org/develop/regauth/oui/oui.txt\">here</a>. The file is automatically updated every hour.<br />\n";
							echo "There's a small chance your list might not be up to date. Please try manually updating your static file. If you already did, then the MAC address might be spoofed and you can ignore this message.<br /><br />\n";
							echo "For linux, execute this command at the CLI as root or sudo:<br />\n";
							echo "<i><b>wget -N /var/www/sql/oui.txt -N http://standards.ieee.org/develop/regauth/oui/oui.txt</b></i><br /><br />\n";
							echo "For Windows, replace the file '<b><i>C:\\xampp\htdocs\sql\oui.txt</i></b>' with the contents of the web server version <a target=\"_NEW\" href=\"http://standards.ieee.org/develop/regauth/oui/oui.txt\">here</a>.";
						}
					} else {
						echo "<br />SNMP did not return any results. Something is wrong.\n";
					}
				}
			} else {
				echo "<br />The IP address '$theip' is not responsive. Please try something else.\n";
			}
		}
	}
	require("../include/end.php");
?>