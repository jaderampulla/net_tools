<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$title="Common Port Scan";
require("../include/options/snmpincludes.php");
require("../include/header.php");
require ("../include/functions.php");
$time_start=microtime_float();
?>
<style type="text/css">
/* Enter Custom CSS Here */
table.scanout {
	border-width: 1px;
	border-spacing: 2px;
	border-style: outset;
	border-color: black;
}
table.scanout th {
	border-width: 1px;
	padding: 1px;
	border-style: inset;
	border-color: black;
}
table.scanout td {
	text-align: center;
	border-width: 1px;
	padding: 4px;
	border-style: inset;
	border-color: black;
}
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
<br />(More info about ports <a target="_NEW" href="http://en.wikipedia.org/wiki/TCP_and_UDP_port_numbers">here</a>)<br /><br />
<form method="post" style="display: inline;" name="inputstuff">
<table border=0>
	<tr>
		<td>IP Address:</td>
		<td style="text-align: right;">
			<input type="text" name="ip" style="width: 150px; text-align: left;" <?php if($_POST['ip']) echo " value=\"{$_POST['ip']}\"";?> />
		</td>
		<td>
			(Examples: <font style="color: red;">10.0.0.1</font> or <font style="color: green;">10.0.0.1-5</font> or <font style="color: blue;">10.0.0.1,6,8</font> or <font style="color: white;">10.0.0.1-7,11,20-30</font> or 10.1-3.0.1-5,12,18-20)
		</td>
	</tr>
	<tr>
		<td style="vertical-align: top;">Ports:</td>
		<td colspan=2>
			<table border=0>
				<tr>
					<td><input name="ftpdata" type="checkbox" <?php if($_POST['ftpdata']=="on") echo "checked"; ?> />&nbsp;<a target="_NEW" href="http://en.wikipedia.org/wiki/FTP">FTP Data</a>&nbsp;(TCP 20)</td>
					<td><input name="ftpcontrol" type="checkbox" <?php if(!($_POST['scan']) || ($_POST['scan'] && $_POST['ftpcontrol']=="on")) echo "checked"; ?> />&nbsp;<a target="_NEW" href="http://en.wikipedia.org/wiki/FTP">FTP Control</a>&nbsp;(TCP 21)</td>
				</tr>
				<tr>
					<td><input name="ssh" type="checkbox" <?php if(!($_POST['scan']) || ($_POST['scan'] && $_POST['ssh']=="on")) echo "checked"; ?> />&nbsp;<a target="_NEW" href="http://en.wikipedia.org/wiki/Secure_Shell">SSH</a>&nbsp;(TCP 22)</td>
					<td><input name="telnet" type="checkbox" <?php if(!($_POST['scan']) || ($_POST['scan'] && $_POST['telnet']=="on")) echo "checked"; ?> />&nbsp;<a target="_NEW" href="http://en.wikipedia.org/wiki/Telnet">Telnet</a>&nbsp;(TCP 23)</td>
				</tr>
				<tr>
					<td><input name="smtp" type="checkbox" <?php if($_POST['smtp']) echo "checked"; ?> />&nbsp;<a target="_NEW" href="http://en.wikipedia.org/wiki/Simple_Mail_Transfer_Protocol">SMTP</a>&nbsp;(TCP 25)</td>
					<td><input name="wins" type="checkbox" <?php if($_POST['wins']) echo "checked"; ?> />&nbsp;<a target="_NEW" href="http://en.wikipedia.org/wiki/Windows_Internet_Name_Service">WINS</a>&nbsp;(TCP/UDP 42)</td>
				</tr>
				<tr>
					<td><input name="dns" type="checkbox" <?php if($_POST['dns']) echo "checked"; ?> />&nbsp;<a target="_NEW" href="http://en.wikipedia.org/wiki/Domain_Name_System">DNS Server</a>&nbsp;(TCP/UDP 53)</td>
					<td><input name="http" type="checkbox" <?php if(!($_POST['scan']) || ($_POST['scan'] && $_POST['http']=="on")) echo "checked"; ?> />&nbsp;<a target="_NEW" href="http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol">HTTP</a>&nbsp;(TCP 80)</td>
				</tr>
				<tr>
					<td><input name="netbios" type="checkbox" <?php if(!($_POST['scan']) || ($_POST['scan'] && $_POST['netbios']=="on")) echo "checked"; ?> />&nbsp;<a target="_NEW" href="http://en.wikipedia.org/wiki/NetBIOS">NetBIOS</a>&nbsp;(TCP/UDP 137-139)</td>
					<td colspan="2"><input name="netbiosextra" type="checkbox" <?php if(!($_POST['scan']) || ($_POST['scan'] && $_POST['netbiosextra']=="on")) echo "checked"; ?> />&nbsp;NetBIOS extra info</td>
				</tr>
				<tr>
					<td><input name="snmp" type="checkbox" <?php if(!($_POST['scan']) || ($_POST['scan'] && $_POST['snmp']=="on")) echo "checked"; ?> />&nbsp;<a target="_NEW" href="http://en.wikipedia.org/wiki/Simple_Network_Management_Protocol">SNMP</a>&nbsp;(UDP 161)</td>
					<td><input name="https" type="checkbox" <?php if(!($_POST['scan']) || ($_POST['scan'] && $_POST['https']=="on")) echo "checked"; ?> />&nbsp;<a target="_NEW" href="http://en.wikipedia.org/wiki/HTTPS">HTTPS</a>&nbsp;(TCP 443)</td>
				</tr>
				<tr>
					<td><input name="winshare" type="checkbox" <?php if(!($_POST['scan']) || ($_POST['scan'] && $_POST['winshare']=="on")) echo "checked"; ?> />&nbsp;<a target="_NEW" href="http://en.wikipedia.org/wiki/Server_Message_Block">SMB (Windows Shares)</a>&nbsp;(TCP 445)</td>
					<td><input name="rdp" type="checkbox" <?php if(!($_POST['scan']) || ($_POST['scan'] && $_POST['rdp']=="on")) echo "checked"; ?> />&nbsp;<a target="_NEW" href="http://en.wikipedia.org/wiki/Remote_Desktop_Protocol">RDP</a>&nbsp;(TCP/UDP 3389)</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>Additional Ports:</td>
		<td style="text-align: right;">
			<input type="text" name="moreports" style="width: 150px; text-align: left;" <?php if($_POST['moreports']) echo " value=\"{$_POST['moreports']}\"";?> />
		</td>
		<td>
			Examples (T is for TCP, U is for UDP): <font style="color: red;">T:123</font> or <font style="color: green;">T:123-133</font> or <font style="color: blue;">T:123,U:370,T:465-475</font>. Ranges will be treated as one output
		</td>
	</tr>
</table><br />
<input type="submit" value="Scan" name="scan" />
</form><br />
<!-- Default cursor location -->
<script type="text/javascript">
	document.inputstuff.ip.focus();
</script>
<?php
if($_POST['scan']){
	//Assign POST values to variables
	$iprange=$_POST['ip'];
	$moreports=$_POST['moreports'];
	$ftpdata=$_POST['ftpdata'];
	$ftpcontrol=$_POST['ftpcontrol'];
	$ssh=$_POST['ssh'];
	$telnet=$_POST['telnet'];
	$smtp=$_POST['smtp'];
	$wins=$_POST['wins'];
	$dns=$_POST['dns'];
	$http=$_POST['http'];
	$netbios=$_POST['netbios'];
	$netbiosextra=$_POST['netbiosextra'];
	$snmp=$_POST['snmp'];
	$https=$_POST['https'];
	$winshare=$_POST['winshare'];
	$rdp=$_POST['rdp'];
	if($iprange){
		//Build the scan string
		$scanstr="sudo nmap -PS -sS -sU -n -v -p ";
		if($ftpdata=="on"){	$scanstr=$scanstr . "T:20,"; }
		if($ftpcontrol=="on"){ $scanstr=$scanstr . "T:21,";	}
		if($ssh=="on"){ $scanstr=$scanstr . "T:22,"; }
		if($telnet=="on"){ $scanstr=$scanstr . "T:23,"; }
		if($smtp=="on"){ $scanstr=$scanstr . "T:25,"; }
		if($wins=="on"){ $scanstr=$scanstr . "T:42,U:42,"; }
		if($dns=="on"){ $scanstr=$scanstr . "T:53,U:53,"; }
		if($http=="on"){ $scanstr=$scanstr . "T:80,"; }
		if($netbios=="on"){ $scanstr=$scanstr . "T:137-139,U:137-139,"; }
		if($snmp=="on"){ $scanstr=$scanstr . "U:161,"; }
		if($https=="on"){ $scanstr=$scanstr . "T:443,"; }
		if($winshare=="on"){ $scanstr=$scanstr . "T:445,"; }
		if($rdp=="on"){ $scanstr=$scanstr . "T:3389,U:3389,"; }
		if($moreports){ $scanstr=$scanstr . "$moreports,"; }
		//Remove the last comma whether moreports is used or not
		$scanstr=substr($scanstr,0,-1);
		$scanstr=$scanstr . " $iprange | grep \"open port\"";
		//echo "SCANSTR: $scanstr<br />\n";
		$resultsar=preg_split('/\n/',shell_exec("$scanstr"));
		//Check to make sure there are results
		if(sizeof($resultsar)>0 && $resultsar[0]){
			//Create output table
			echo "<br /><table class=\"output\" id=\"floater\">\n";
			echo "<thead><tr>";
			echo "<th>IP Address</th>";
			if($ftpdata=="on"){	echo "<th>FTP Data</th>"; }
			if($ftpcontrol=="on"){ echo "<th>FTP Control</th>";	}
			if($ssh=="on"){ echo "<th>SSH</th>"; }
			if($telnet=="on"){ echo "<th>Telnet</th>"; }
			if($smtp=="on"){ echo "<th>SMTP</th>"; }
			if($wins=="on"){ echo "<th>WINS</th>"; }
			if($dns=="on"){ echo "<th>DNS</th>"; }
			if($http=="on"){ echo "<th>HTTP</th>"; }
			if($netbios=="on"){ echo "<th>NetBIOS</th>"; }
			if($netbiosextra=="on"){ echo "<th style=\"width: 150px;\">NetBIOS Name</th><th style=\"width: 180px;\">NetBIOS Workgroup</th><th>NetBIOS MAC Address</th>"; }
			if($netbiosextra){
				if(file("oui.csv")){
					$macouifilear=file("oui.csv",FILE_IGNORE_NEW_LINES);
					//Get lines in array that have the MAC address and associated vendor
					foreach($macouifilear as $macouiline){
						list($macregistry,$macoui,$macorg)=explode(',',$macouiline);
						if($macregistry!="Registry"){
							$macoui=wordwrap($macoui,2,':',true);
							$macorg=trim(preg_replace('/"/','',$macorg));
							$macouiar[$macoui]=$macorg;
						}
					}
				}
			}
			if($snmp=="on"){ echo "<th>SNMP</th>"; }
			if($https=="on"){ echo "<th>HTTPS</th>"; }
			if($winshare=="on"){ echo "<th>SMB</th>"; }
			if($rdp=="on"){ echo "<th>RDP</th>"; }
			//Code to handle extra specified ports
			if($moreports){
				$moreportsar=explode(',',$moreports);
				$count=0;
				foreach($moreportsar as $moreport){
					list($protoletter,$moreport)=explode(':',$moreport);
					if($protoletter=="T") $protoletter="TCP";
					if($protoletter=="U") $protoletter="UDP";
					if(preg_match('/-/',$moreport)){
						list($startrange,$endrange)=explode('-',$moreport);
						for($i=$startrange; $i<=$endrange; $i++){
							$moreportfinar[$count]=$i . "," . strtolower($protoletter);
							$count+=1;
						}
					} else {
						$moreportfinar[$count]=$moreport . "," . strtolower($protoletter);
						$count+=1;
					}
					echo "<th>$protoletter $moreport</th>";
				}
				//print_r($moreportsar);
			}
			echo "</tr></thead><tbody>\n";
			foreach($resultsar as $result){
				if($result){
					//Get info out of nmap line into $ip, $port, and $proto variables
					$result=str_replace('Discovered open port ','',$result);
					list($port,$ip)=explode(' on ',$result);
					$ip=rtrim($ip);
					list($port,$proto)=explode('/',$port);
					//Put info into array
					if(!array_key_exists($ip,$finar)) $finar[$ip]=array();
					if(!in_array("$port,$proto",$finar[$ip])) array_push($finar[$ip],"$port,$proto");
					//echo "IP: $ip PORT: $port PROTO: $proto<br />";
				}
			}
			/*echo "<pre>";
			print_r($finar);
			echo "</pre>";*/
			
			//Sort the array by IP address (Key of $finar)
			ksort($finar,SORT_STRING);
			$fontredbegin="<font style=\"color: red;\">";
			$fontgreenbegin="<font style=\"font-weight:bold; color: green;\">";
			$fontend="</font>";
			foreach($finar as $key=>$ar){
				echo "<tr>";
				echo "<td style=\"text-align: left;\">$key</td>";
				if($ftpdata=="on" && in_array(20,$ar)){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else if($ftpdata=="on") { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
				if($ftpcontrol=="on" && in_array(21,$ar)){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else if($ftpcontrol=="on") { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
				if($ssh=="on" && in_array(22,$ar)){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else if($ssh=="on") { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
				if($telnet=="on" && in_array(23,$ar)){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else if($telnet=="on") { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
				if($smtp=="on" && in_array(25,$ar)){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else if($smtp=="on") { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
				if($wins=="on" && in_array(42,$ar)){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else if($wins=="on") { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
				if($dns=="on" && in_array(53,$ar)){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else if($dns=="on") { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
				if($http=="on" && in_array(80,$ar)){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else if($http=="on") { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
				if($netbios=="on" && (in_array(137,$ar) || in_array(138,$ar) || in_array(139,$ar))){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else if($netbios=="on") { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
				//Extra NetBIOS
				if($netbios=="on" && (in_array(137,$ar) || in_array(138,$ar) || in_array(139,$ar)) && $netbiosextra){
					unset($extranetbiosar);
					/*
					NetBIOS script obtained here:
					http://nmap.org/nsedoc/scripts/nbstat.html
					*/
					$extranetbiosstring="sudo nmap -sU -v --script nbstat.nse -p137 $key | grep -e NetBIOS -e group | grep -v '<00>' | grep -v MSBROWSE | sed 's/.*nbstat: //'";
					$extranetbiosar=preg_split('/\n/',shell_exec("$extranetbiosstring"));
					foreach($extranetbiosar as $netbiosentry){
						if($netbiosentry){
							$netbiosentry=preg_replace('/\|/','',$netbiosentry);
							$netbiosentry=preg_replace('/\_/','',$netbiosentry);
							/*
							First line example:
							NetBIOS name: JRAMPU2083, NetBIOS user: , NetBIOS MAC: d4:be:d9:23:49:60 (Dell)
							*/
							if(strstr($netbiosentry,'NetBIOS')){
								list($netbiosname,$netbiosuser,$netbiosmac)=explode(',',$netbiosentry);
								list($junk,$netbiosname)=explode(': ',$netbiosname);
								list($junk,$netbiosmac)=explode(': ',$netbiosmac);
								list($netbiosmac,$junk)=explode(' (',$netbiosmac);
								$netbiosmac=trim(strtoupper($netbiosmac));
								//Prevent empty entries
								if(strstr($netbiosmac,':')){
									//Find MAC address OUI
									list($a,$b,$c,$d,$e,$f)=explode(':',$netbiosmac);
									$oui=$macouiar["$a:$b:$c"];
									$netbiosmac="$netbiosmac <i>($oui)</i>";
								}
							/*
							Second line example:
							RONCO<1e> Flags:
							*/
							} else {
								list($netbiosworkgroup,$junk)=explode('<',$netbiosentry);
							}
							//echo "$netbiosentry<br />\n";
						}
					}
					if($netbiosname){ echo "<td>$netbiosname</td>"; } else { echo "<td>&nbsp;</td>"; }
					if($netbiosworkgroup){ echo "<td>$netbiosworkgroup</td>"; } else { echo "<td>&nbsp;</td>"; }
					if($netbiosmac){ echo "<td style=\"text-align: left;\">$netbiosmac</td>"; } else { echo "<td>&nbsp;</td>"; }
				} else if($netbiosextra){
					echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
				}
				if($snmp=="on" && in_array(161,$ar)){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else if($snmp=="on") { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
				if($https=="on" && in_array(443,$ar)){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else if($https=="on") { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
				if($winshare=="on" && in_array(445,$ar)){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else if($winshare=="on") { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
				if($rdp=="on" && in_array(3389,$ar)){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else if($rdp=="on") { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
				//Code to handle extra specified ports
				if($moreports){
					foreach($moreportsar as $moreport){
						list($protoletter,$moreport)=explode(':',$moreport);
						if($protoletter=="T") $protoletter="tcp";
						if($protoletter=="U") $protoletter="udp";
						if(preg_match('/-/',$moreport)){
							list($startrange,$endrange)=explode('-',$moreport);
							$found=false;
							for($i=$startrange; $i<=$endrange; $i++){
								$moreport=$i . ",$protoletter";
								if(in_array($moreport,$ar)){
									$found=true;
								}
							}
							if($found==true){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
						} else {
							$moreport=$moreport . ",$protoletter";
							if(in_array($moreport,$ar)){ echo "<td>{$fontgreenbegin}X{$fontend}</td>"; } else { echo "<td>{$fontredbegin}X{$fontend}</td>"; }
						}
					}
				}
				echo "</tr>\n";
			}
			echo "</tbody></table>\n";
			?>
			<script>
			$("#floater").thfloat();
			</script>
			<?php
			$time=end_time($time_start);
			echo "\n<br />Port scan completed in {$time}seconds.<br />";
		} else {
			echo "<br />No results";
		}
	} else if($_POST['ip']==null){
		echo "<br />Please enter an IP address or range\n";
	}
}
?>