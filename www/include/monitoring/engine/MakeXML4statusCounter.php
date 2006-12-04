<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Cedrick Facon

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	$debug = 0;

	## pearDB init

	require_once 'DB.php';	

	$oreonPath = '/srv/oreon/';
	$oreonPath = isset($_POST["fileOreonConf"]) ? $_POST["fileOreonConf"] : $oreonPath;
	$oreonPath = isset($_GET["fileOreonConf"]) ? $_GET["fileOreonConf"] : $oreonPath;
	
	$buffer = "";
	
	if($oreonPath == ""){
		$buffer = null;
		$buffer .= '<reponse>';	
		$buffer .= 'none';
		$buffer .= '</reponse>';
		header('Content-Type: text/xml');
		echo $buffer;
	}

	include_once($oreonPath . "www/oreon.conf.php");
	include_once($oreonPath . "www/include/common/common-Func-ACL.php");

	/* Connect to oreon DB */
	
	$dsn = array(
		     'phptype'  => 'mysql',
		     'username' => $conf_oreon['user'],
		     'password' => $conf_oreon['password'],
		     'hostspec' => $conf_oreon['host'],
		     'database' => $conf_oreon['db'],
		     );
	
	$options = array(
			 'debug'       => 2,
			 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,
			 );
	
	$pearDB =& DB::connect($dsn, $options);
	if (PEAR::isError($pearDB)) 
	  die("Connecting probems with oreon database : " . $pearDB->getMessage());
	
	$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);

	function read($version,$sid,$file){
		global $pearDB, $flag;
		$MyLog = date('l dS \of F Y h:i:s A'). "\n";

		$buffer = null;
		$buffer  = '<?xml version="1.0"?>';
		$buffer .= '<reponse>';
	
		$buffer .= '<infos>';
		$buffer .= '<filetime>'.filectime($file). '</filetime>';
		$buffer .= '</infos>';
	
		$oreon = "";
		$search = "";
		$search_type_service = 0;
		$search_type_host = 0;
	
		include("../load_status_log.php");
	
		## calcul stat for resume
		
		$statistic_host = array("UP" => 0, "DOWN" => 0, "UNREACHABLE" => 0, "PENDING" => 0);
		$statistic_service = array("OK" => 0, "WARNING" => 0, "CRITICAL" => 0, "UNKNOWN" => 0, "PENDING" => 0);
		
		
		if (isset($host_status))
			foreach ($host_status as $hs)
				$statistic_host[$hs["current_state"]]++;
		if (isset($service_status))
			foreach ($service_status as $s)
				$statistic_service[$s["current_state"]]++;
	
		$buffer .= '<stats>';
		$buffer .= '<statistic_service_ok>'. $statistic_service["OK"] . '</statistic_service_ok>';
		$buffer .= '<statistic_service_warning>'. $statistic_service["WARNING"] . '</statistic_service_warning>';
		$buffer .= '<statistic_service_critical>'. $statistic_service["CRITICAL"] . '</statistic_service_critical>';
		$buffer .= '<statistic_service_unknown>'. $statistic_service["UNKNOWN"] . '</statistic_service_unknown>';
		$buffer .= '<statistic_service_pending>'. $statistic_service["PENDING"] . '</statistic_service_pending>';
		$buffer .= '<statistic_host_up>'.$statistic_host["UP"]. '</statistic_host_up>';
		$buffer .= '<statistic_host_down>'.$statistic_host["DOWN"]. '</statistic_host_down>';
		$buffer .= '<statistic_host_unreachable>'.$statistic_host["UNREACHABLE"]. '</statistic_host_unreachable>';
		$buffer .= '<statistic_host_pending>'.$statistic_host["PENDING"]. '</statistic_host_pending>';
		$buffer .= '</stats>';	
		$buffer .= '</reponse>';
		
		header('Content-Type: text/xml');
		echo $buffer;
	//echo "la";
	/*
		global $debug;
		if($debug)
		{
			$file = "logb.xml";
			$inF = fopen($file,"w");
			fwrite($inF,$buffer);
			fclose($inF);
			
		
			$file = "logb.txt";
			$inF = fopen($file,"w");
			fwrite($inF,"log:\n ".$MyLog."\n\n");
			fwrite($inF,"sid:\n ".$sid."\n\n");
			fwrite($inF,"uid:\n ".$uid."\n\n");
			fwrite($inF,"admin:\n ".$IsAdmin."\n\n");
	
			if($oreonLCA && is_array($oreonLCA))
				foreach($oreonLCA as $key => $h)
				fwrite($inF,"lca h: ".$h."\n\n");	
			
			fwrite($inF,"log:\n----------\n\n");
			fclose($inF);
		}
		*/
	}

	if(isset($_POST["version"]) && isset($_POST["sid"])&& isset($_POST["fileStatus"])){
		read($_POST["version"],$_POST["sid"],$_POST["fileStatus"]);
	} else if (isset($_GET["version"]) && isset($_GET["sid"])&& isset($_GET["fileStatus"])) {
		read($_GET["version"],$_GET["sid"],$_GET["fileStatus"]);
	} else {
		$buffer = null;
		$buffer .= '<reponse>';	
		$buffer .= 'none';	
		$buffer .= '</reponse>';	
		header('Content-Type: text/xml');
		echo $buffer;
	}
?>