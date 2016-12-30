<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
function sql2table ($sql,$db,$hide) {
	$result=mysqli_query($db,$sql);
	if($result==FALSE){
		echo "FALSE<br />\n";
		echo mysqli_error();
		return;
	}
	$found=mysqli_num_rows($result);
	$fields=mysqli_num_fields($result);
	if($result->field_count>0 && $result->num_rows>0 && preg_match("/^\s*select/i",$sql)==1){
		echo "Rows: ".$result->num_rows."\n";
	}else if($result->field_count>0 && $result->num_rows==0){
		echo "Rows: 0\n";
	} else {
		echo "Rows: ".$db->affected_rows."\n";
	}
	echo "<table border=1 align=center>"."\r\n<tr>";
	$headerar=array();
	for($x=0;$x<$fields;$x++) {
		$columnname=mysqli_fetch_field($result);
		$field[$x]=$columnname->name;
		if(strpos($hide,$field[$x])==0){
			echo "<th>$field[$x]</th>";
			//Store header titles in array for PHP to XLSX
			$headerar[]=$field[$x];
		}
		if($field[$x]=="timestamp") $remembertime=$x;
	}
	echo "</tr>\n";
	$dataar=array();
	$rowcnt=0;
	while($row = mysqli_fetch_array ($result,MYSQLI_BOTH)) {
		echo "<tr>";
		for($x=0;$x<$fields;$x++) {
			if(!strpos($hide,$field[$x]))
			if($row[$x]==''){
				echo "<td>&nbsp;</td>";
			} else {
				$rowval=preg_replace('/\//','&#47;',$row[$x]);
				$rowval=preg_replace('/</','&#60;',$rowval);
				$rowval=preg_replace('/>/','&#62;',$rowval);
				$rowval=preg_replace('/\n/','<br>',$rowval);
				if($x==$remembertime){
					echo "<td class=\"timestamp\">$rowval</td>";
				} else {
					echo "<td>$rowval</td>";
				}
			}
			//Store data in array for PHP to XLSX
			if($x==0){
				$dataar[$rowcnt]=array($x=>$rowval);
			} else {
				array_push($dataar[$rowcnt],$rowval);
			}
		}
		echo "</tr>\n";
		$rowcnt+=1;
	}
	echo "</table>";
	mysqli_free_result($result);
	return array($headerar,$dataar);
}

function dropdown($dbconn,$thesql,$varname,$txtname,$selected) {
    $rs_query=mysqli_query($dbconn,$thesql);
	$found=mysqli_num_rows($rs_query);
	$dropbox="<select name=\"$varname\">\n";
    for($x=0;$x<$found;$x++) {
        $row = mysqli_fetch_array ($rs_query, MYSQLI_BOTH);
		if($row[$varname]!="information_schema" && $row[$varname]!="cacti" && $row[$varname]!="mysql" && $row[$varname]!="performance_schema"){
			$dropbox=$dropbox."<option value=\"".$row[$varname]."\"";
			if($row[$varname]==$selected) $dropbox=$dropbox." selected";
			$dropbox=$dropbox.">".$row[$txtname]."\n";
		}
    }
    $dropbox=$dropbox."</select>\n";
    return $dropbox;
}

function NmapFindIP($iprange){
	$ipar=split("\n",`nmap -sL -n $iprange | grep -v done | grep -v Starting | sed 's/Nmap scan report for //g'`);
	$returnar=array();
	foreach($ipar as $ar){
		if($ar) $returnar[]=$ar;
	}
	return $returnar;
}

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
}

function fixmac($oldmac) {
   $delims=array(".",":"," ","-");
   $m=str_replace($delims, "", $oldmac);   // take out all delimiters
   if (! eregi("([0-9|a-f]{12})",$m)) return false;
   return strtoupper(substr(chunk_split($m,2,':'),0,-1)); 
}

function microtime_float() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function end_time($time_start) {
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	$time = round($time,3);
	return $time;
}

?>