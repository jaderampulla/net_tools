<?phpclass mysql{	function getLogsTable(){		return "defaultlogs";	}	function getSwitchLogsTable(){		return "switchlogs";	}	function connect($dbchoice){		require("options/m_s.php");		$server=$mysqlserver;		require("options/m_un.php");		$username=$mysqlusername;		require("options/m_p.php");		$password=$mysqlpassword;		switch($dbchoice){			case 1: $database="syslog"; break;		}		$connection = mysql_connect($server,$username,$password) or die("<b>MySQL Error</b>: " . mysql_error());		if (!@mysql_select_db($database)){			echo "<br /><br /><b>Error</b>: Could not connect to MySQL Database. The error is " . mysql_error() . "<br />";		}		return array($database,$connection);	}	function disconnect($connection){		if(!@mysql_close($connection)){			echo "<br />Error: Could not close MySQL Connection<br />";		} 	}}?>