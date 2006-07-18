<?php

// path ??


#
## pearDB init
#
	require_once 'DB.php';	

$oreonPath = isset($_POST["fileOreonConf"]) ? $_POST["fileOreonConf"] : "";
$oreonPath = isset($_GET["fileOreonConf"]) ? $_GET["fileOreonConf"] : $oreonPath;

if($oreonPath == "")
{
	$buffer .= '<reponse>';	
	$buffer .= 'none';	
	$buffer .= '</reponse>';	
	header('Content-Type: text/xml');
	echo $buffer;
}

include_once($oreonPath . "www/oreon.conf.php");
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



#
## class init
#

class Duration
{
	function toString ($duration, $periods = null)
    {
        if (!is_array($duration)) {
            $duration = Duration::int2array($duration, $periods);
        }
        return Duration::array2string($duration);
    }
 
    function int2array ($seconds, $periods = null)
    {        
        // Define time periods
        if (!is_array($periods)) {
            $periods = array (
                    'y'	=> 31556926,
                    'M' => 2629743,
                    'w' => 604800,
                    'd' => 86400,
                    'h' => 3600,
                    'm' => 60,
                    's' => 1
                    );
        }
 
        // Loop
        $seconds = (int) $seconds;
        foreach ($periods as $period => $value) {
            $count = floor($seconds / $value);
 
            if ($count == 0) {
                continue;
            }
 
            $values[$period] = $count;
            $seconds = $seconds % $value;
        }
 
        // Return
        if (empty($values)) {
            $values = null;
        }
        return $values;
    }
 
    function array2string ($duration)
    {
        if (!is_array($duration)) {
            return false;
        }
        foreach ($duration as $key => $value) {
            $segment = $value . '' . $key;
            $array[] = $segment;
        }
        $str = implode(' ', $array);
        return $str;
    }
}

function read($time,$arr,$flag,$type,$version,$lca,$file)
{
	global $pearDB;
	global $flag;

	$buffer = null;
	$buffer  = '<?xml version="1.0"?>';
	$buffer .= '<reponse>';

	$ntime = $time;
		
	if( filectime($file) > $ntime)
		$ntime = filectime($file);	

	$buffer .= '<infos>';
	$buffer .= '<flag>'. $flag . '</flag>';
	$buffer .= '<time>'.$ntime. '</time>';
	$buffer .= '<filetime>'.filectime($file). '</filetime>';
	$buffer .= '</infos>';

	if( filectime($file) > $time)
	{
		$tab = array();
		$tab = explode(',', $lca);

		$mtab[0] = "";

		$a=0;
		foreach($tab as $v)
		{
			$mtab[$a+1] = trim($v);		
			$a++;
		}
		include("ReloadForAjax_status_log.php");
		$mtab = array();
		$mtab = explode(',', $arr);

		#
		## calcul stat for resume
		#
		$statistic_host = array("UP" => 0, "DOWN" => 0, "UNREACHABLE" => 0, "PENDING" => 0);
		$statistic_service = array("OK" => 0, "WARNING" => 0, "CRITICAL" => 0, "UNKNOWN" => 0, "PENDING" => 0);
		
		if (isset($host_status))
			foreach ($host_status as $hs)
				$statistic_host[$hs["status"]]++;
		if (isset($service_status))
			foreach ($service_status as $s)
				$statistic_service[$s["status"]]++;
		
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

		#
		## host_group infos
		#			
		/*
		if (isset($host_status) && isset($service_status) && $type == "host_group")
		{
				$hg = array();

				$tab = array("1"=>'list_one', "0" => "list_two"); 
			
				$ret =& $pearDB->query("SELECT * FROM hostgroup WHERE hg_activate = '1' ORDER BY hg_name");
				while ($r =& $ret->fetchRow()){
					$hg[$r["hg_name"]] = array("name" => $r["hg_name"], 'alias' => $r["hg_alias"]);
					$status_hg_h[$r["hg_name"]] = array();
					$status_hg_h[$r["hg_name"]]["UP"] = 0;
					$status_hg_h[$r["hg_name"]]["DOWN"] = 0;
					$status_hg_h[$r["hg_name"]]["UNREACHABLE"] = 0;
					$status_hg_h[$r["hg_name"]]["PENDING"] = 0;
					$status_hg_h[$r["hg_name"]]["UNKNOWN"] = 0;
					$status_hg[$r["hg_name"]] = array();
					$status_hg[$r["hg_name"]]["OK"] = 0;
					$status_hg[$r["hg_name"]]["PENDING"] = 0;
					$status_hg[$r["hg_name"]]["WARNING"] = 0;
					$status_hg[$r["hg_name"]]["CRITICAL"] = 0;
					$status_hg[$r["hg_name"]]["UNKNOWN"] = 0;
					
					$ret_h =& $pearDB->query(	"SELECT host_host_id,host_name FROM hostgroup_relation,host,hostgroup ".
												"WHERE hostgroup_hg_id = '".$r["hg_id"]."' AND hostgroup.hg_id = hostgroup_relation.hostgroup_hg_id ".
												"AND hostgroup_relation.host_host_id = host.host_id AND host.host_register = '1' AND hostgroup.hg_activate = '1'");
					while ($r_h =& $ret_h->fetchRow()){
						!$r_h["host_name"] ? $hostname = getMyHostName($r_h["host_id"]) : $hostname = $r_h["host_name"];
						//print $r["hg_name"]. " : " . $hostname ."-".$host_status[$hostname]["status"] . "<br>";
						if (isset($host_status[$hostname]["status"])){
							$status_hg_h[$r["hg_name"]][$host_status[$hostname]["status"]]++;
							foreach ($tab_host_service[$hostname] as $key => $s){
								$status_hg[$r["hg_name"]][$service_status[$hostname. "_" .$key]["status"]]++;
							} 		
						}
					}
				}
				
				$cpt = 0;
				foreach ($hg as $hgs){
					$hg[$hgs["name"]]["host_stats"] = "";
					if ($status_hg_h[$hgs["name"]]["UP"] != 0)
						$hg[$hgs["name"]]["host_stats"] = "<span style='background:".$oreon->optGen["color_up"]."'>" . $status_hg_h[$hgs["name"]]["UP"] . " UP</span> ";
					if ($status_hg_h[$hgs["name"]]["DOWN"] != 0)
						$hg[$hgs["name"]]["host_stats"] .= "<span style='background:".$oreon->optGen["color_down"]."'>" . $status_hg_h[$hgs["name"]]["DOWN"] . " DOWN</span> ";
					if ($status_hg_h[$hgs["name"]]["UNREACHABLE"] != 0)
						$hg[$hgs["name"]]["host_stats"] .= "<span style='background:".$oreon->optGen["color_unreachable"]."'>" . $status_hg_h[$hgs["name"]]["UNREACHABLE"] . " UNREACHABLE</span> ";
					if ($status_hg_h[$hgs["name"]]["PENDING"] != 0)
						$hg[$hgs["name"]]["host_stats"] .= "<span style='background:".$oreon->optGen["color_pending"]."'>" . $status_hg_h[$hgs["name"]]["PENDING"] . " PENDING</span> ";
					if ($status_hg_h[$hgs["name"]]["UNKNOWN"] != 0)
						$hg[$hgs["name"]]["host_stats"] .= "<span style='background:".$oreon->optGen["color_unknown"]."'>" . $status_hg_h[$hgs["name"]]["UNKNOWN"] . " UNKNOWN</span> ";
					
					$hg[$hgs["name"]]["svc_stats"] = "";
					if ($status_hg[$hgs["name"]]["OK"] != 0)
						$hg[$hgs["name"]]["svc_stats"] = "<span style='background:".$oreon->optGen["color_ok"]."'>" . $status_hg[$hgs["name"]]["OK"] . " OK</span> ";
					if ($status_hg[$hgs["name"]]["WARNING"] != 0)
						$hg[$hgs["name"]]["svc_stats"] .= "<span style='background:".$oreon->optGen["color_warning"]."'>" . $status_hg[$hgs["name"]]["WARNING"] . " WARNING</span> ";
					if ($status_hg[$hgs["name"]]["CRITICAL"] != 0)
						$hg[$hgs["name"]]["svc_stats"] .= "<span style='background:".$oreon->optGen["color_critical"]."'>" . $status_hg[$hgs["name"]]["CRITICAL"] . " CRITICAL</span> ";
					if ($status_hg[$hgs["name"]]["PENDING"] != 0)
						$hg[$hgs["name"]]["svc_stats"] .= "<span style='background:".$oreon->optGen["color_pending"]."'>" . $status_hg[$hgs["name"]]["PENDING"] . " PENDING</span> ";
					if ($status_hg[$hgs["name"]]["UNKNOWN"] != 0)
						$hg[$hgs["name"]]["svc_stats"] .= "<span style='background:".$oreon->optGen["color_unknown"]."'>" . $status_hg[$hgs["name"]]["UNKNOWN"] . " UNKNOWN</span> ";
					$hg[$hgs["name"]]["class"] = $tab[$cpt % 2];
					$cpt++;
				}
			
		}
		*/
		#
		## services infos
		#
		if (isset($service_status) &&  $type == "service")
		{
			$gtab = array();
			for($a=0,$b=1; sizeof($mtab) > $b;$a+=2,$b+=2)
				$gtab[$mtab[$a] . $mtab[$b]] = $a / 2 + $a % 2;

			foreach ($service_status as $name => $svc)
				if(isset($gtab[$svc["host_name"] . $svc["description"]]))
				{
					$passive = ($svc["checks_en"] == 0 && $svc["accept_passive_check"]) ? 1 : 0;
					$active = ($svc["checks_en"] == 0 && $svc["accept_passive_check"] == 0) ? 1 : 0;
										
					$output = ($svc["output"]) ? htmlentities($svc["output"]) : " ";					
					$buffer .= '<line>';
					$buffer .= '<order>'. $gtab[$svc["host_name"] . $svc["description"]] . '</order>';
					$buffer .= '<host>'. $svc["host_name"] . '</host>';
					$buffer .= '<service>'. $svc["description"] . '</service>';
					$buffer .= '<status>'. $svc["status"] . '</status>';
					$buffer .= '<output>'. $output . '</output>';
					$buffer .= '<retry>'. $svc["retry"] . '</retry>';
					$buffer .= '<not_en>'. $svc["not_en"] . '</not_en>';
					$buffer .= '<pb_aknowledged>'. $svc["pb_aknowledged"] . '</pb_aknowledged>';
					$buffer .= '<accept_passive_check>'. $passive . '</accept_passive_check>';
					$buffer .= '<accept_active_check>'. $active . '</accept_active_check>';
					$buffer .= '<ev_handler_en>'. $svc["ev_handler_en"] . '</ev_handler_en>';
					$buffer .= '<svc_is_flapping>'. $svc["svc_is_flapping"] . '</svc_is_flapping>';
					$buffer .= '<flap_detect_en>'. $svc["flap_detect_en"] . '</flap_detect_en>';
					$buffer .= '<last_check>'. date("d/m/Y H:i:s", $svc["last_check"]) . '</last_check>';
					$buffer .= '<last_change>'. Duration::toString(time() - $svc["last_change"]) . '</last_change>';
					$buffer .= '</line>';
				}
		}
		#
		## metaservices infos
		#

		if (isset($metaService_status) && $type == "metaservice")
		{
			$gtab = array();
			$a=0;
			foreach($mtab as $v)
			{
				$gtab[$v] = $a;
				$a++;
			}

		$meta = array();
			$res =& $pearDB->query("SELECT * FROM meta_service WHERE meta_activate = '1'");
			while ($res->fetchInto($meta)){
				$metaService_status_bis["meta_" . $meta["meta_id"]]["real_name"] = $meta["meta_name"]; 
				$metaService_status_bis["meta_" . $meta["meta_id"]]["id"] = $meta["meta_id"]; 
			}

/*
print_r($gtab);
echo "<br>";
*/

			if (isset($metaService_status)){
			foreach ($metaService_status as $name => $svc){
				if (strstr($name, "meta_") && isset($metaService_status[$name]["status"])){
/*
print_r($svc);
echo "<br>";
*/
				if(isset($svc["description"]))
				{					
					$passive = ($svc["checks_en"] == 0 && $svc["accept_passive_check"]) ? 1 : 0;
					$active = ($svc["checks_en"] == 0 && $svc["accept_passive_check"] == 0) ? 1 : 0;
										
					$output = ($svc["output"]) ? htmlentities($svc["output"]) : " ";					
					$buffer .= '<line>';
					$buffer .= '<order>'. $gtab[$metaService_status_bis[$name]["real_name"]] . '</order>';
					$buffer .= '<service>'. $metaService_status_bis[$name]["real_name"] . '</service>';
					$buffer .= '<status>'. $svc["status"] . '</status>';
					$buffer .= '<output>'. $output . '</output>';
					$buffer .= '<retry>'. $svc["retry"] . '</retry>';
					$buffer .= '<not_en>'. $svc["not_en"] . '</not_en>';
					$buffer .= '<pb_aknowledged>'. $svc["pb_aknowledged"] . '</pb_aknowledged>';
					$buffer .= '<accept_passive_check>'. $passive . '</accept_passive_check>';
					$buffer .= '<accept_active_check>'. $active . '</accept_active_check>';
					$buffer .= '<ev_handler_en>'. $svc["ev_handler_en"] . '</ev_handler_en>';
					$buffer .= '<svc_is_flapping>'. $svc["svc_is_flapping"] . '</svc_is_flapping>';
//					$buffer .= '<flap_detect_en>'. $svc["flap_detect_en"] . '</flap_detect_en>';
					$buffer .= '<last_check>'. date("d/m/Y H:i:s", $svc["last_check"]) . '</last_check>';
					$buffer .= '<last_change>'. Duration::toString(time() - $svc["last_change"]) . '</last_change>';
					$buffer .= '</line>';
				}
					
			}
		}
	}
//exit(0);					

		}		
		
		#
		## hosts infos
		#			
		if (isset($host_status) && $type == "host")
		{
			$gtab = array();
			/*
			for($a=0; $mtab[$a];$a++)
				$gtab[$mtab[$a]] = $a;
*/
		$a=0;
		foreach($mtab as $v)
		{
			$gtab[$v] = $a;
			$a++;
		}

			foreach ($host_status as $name => $h)
			{
				if(isset($gtab[$h["host_name"]]))
				{
					$output = ($h["output"]) ? htmlentities($h["output"]) : " ";
					$buffer .= '<line>';
					$buffer .= '<order>'. $gtab[$h["host_name"]] . '</order>';
					$buffer .= '<host>'. $h["host_name"] . '</host>';
					$buffer .= '<status>'. $h["status"] . '</status>';
					$buffer .= '<output>'. $output . '</output>';
					$buffer .= '<last_check>'. date("d/m/Y H:i:s", $h["last_check"]) . '</last_check>';
					$buffer .= '<last_change>'. Duration::toString(time() - $h["last_stat"]) . '</last_change>';
					$buffer .= '</line>';
				}	
			}			
		}
	}
	$buffer .= '</reponse>';
	header('Content-Type: text/xml');
	echo $buffer;
}


#
## sessionID check and refresh
#

$flag = 0;

if(isset($_POST["sid"]) && isset($_POST["slastreload"]) && isset($_POST["smaxtime"]))
{
	$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$_POST["sid"]."'");
	if($session =& $res->fetchRow())
	{
		$flag = $_POST["slastreload"];		
		if(time() - $_POST["slastreload"] > ($_POST["smaxtime"] / 4))
		{		
			$flag = time();
			$sql = "UPDATE `session` SET `last_reload` = '".time()."', `ip_address` = '".
			$_SERVER["REMOTE_ADDR"]."' WHERE CONVERT( `session_id` USING utf8 ) = '".
			$_POST["sid"]."' LIMIT 1";
			$res =& $pearDB->query($sql);
		}
	}
}

/*
if(!$flag)
	exit(1);
*/
if(isset($_POST["time"]) && isset($_POST["arr"]) && isset($_POST["type"])  && isset($_POST["version"]) && isset($_POST["lca"])&& isset($_POST["fileStatus"]))
{
	read($_POST["time"], $_POST["arr"],$flag,$_POST["type"],$_POST["version"],$_POST["lca"],$_POST["fileStatus"]);
}
else if(isset($_GET["time"]) && isset($_GET["arr"]) && isset($_GET["type"]) && isset($_GET["version"]) && isset($_GET["lca"])&& isset($_GET["fileStatus"]) )
{
	read($_GET["time"], $_GET["arr"],$flag, $_GET["type"],$_GET["version"],$_GET["lca"],$_GET["fileStatus"]);
}
else
{
	$buffer .= '<reponse>';	
	$buffer .= 'none';	
	$buffer .= '</reponse>';	
	header('Content-Type: text/xml');
	echo $buffer;
}
?>
