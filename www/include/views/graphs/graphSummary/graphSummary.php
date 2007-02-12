<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	if (!isset($oreon))
		exit();
		
	include_once("./include/common/common-Func.php");	
	include_once("./DBOdsConnect.php");
	
	$lcaHostByID = getLcaHostByID($pearDB);	
	
	function isPerfparseEntry($host_name, $service_description){
		global $pearDBpp;
		$DBRESULT =& $pearDBpp->query("SELECT * FROM `perfdata_service` WHERE host_name = '$host_name' AND service_description = '$service_description'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		return $DBRESULT->numRows();
	}
	
	function return_period($service_id){
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT graph_id FROM extended_service_information WHERE service_service_id = '".$service_id."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		$DBRESULT->fetchInto($service_ext);
		$DBRESULT =& $pearDB->query("SELECT period FROM giv_graphs_template WHERE graph_id = '".$service_ext["graph_id"]."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		$DBRESULT->fetchInto($graph);
		return $graph["period"];
	}

	if (is_file("./DBOdsConnect.php")){
		if (isset($_GET["host_name"]))
			$host_id = getMyHostID($_GET["host_name"]);
		else if (isset($_GET["database"]) && !isset($_GET["host_name"])){
			$database =& $_GET["database"];
			$host_id = getMyHostID($database[0]);
			$db_name = $database[1];
		} else if (isset($_GET["host_id"]))
			$host_id = $_GET["host_id"];
		else 
			$host_id = NULL;
	
		if (isset($_GET["service_description"]))
			$service_id = getMyServiceID($_GET["service_description"], $host_id);
		else if (isset($_GET["service_id"]))
			$service_id = $_GET["service_id"];
		else
			$service_id = NULL;
		
		if (isset($_GET["host_name"]) && isset($_GET["service_description"])){
			$DBRESULT2 =& $pearDBO->query("SELECT id FROM index_data WHERE host_name = '".$_GET["host_name"]."' AND service_description = '".$_GET["service_description"]."'");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo();
			$DBRESULT2->fetchInto($svc_id);
			$_GET["o"] = "vs";
			$_GET["index"] = $svc_id["id"];
			require("./include/views/graphs/graphODS/summaryODS.php");
		} else if (isset($_GET["index"])) {
			require("./include/views/graphs/graphODS/summaryODS.php");
		}
	} else {
		
		if (!$host_id && !$service_id){
			if ($oreon->optGen["graph_preferencies"] == 1)
				require_once("./include/views/graphs/graphPlugins/graphPlugins.php");
			else
				require_once("./include/views/graphs/simpleRenderer/simpleRenderer.php");
		} else { 
			if (!isset($_GET["steps"]))
				$_GET["steps"] = 1;
			
			if ($oreon->user->admin || !$isRetreint || ($isRetreint && isset($lcaHostByID["LcaHost"][$host_id])))	{
				$_GET["submitC"] = "Grapher";
				if (!isset($_GET["period"]))
					$_GET["period"] = return_period($service_id);
				
				if (!isset($_GET["grapht_graph_id"]))
					$_GET["grapht_graph_id"] = "";
					
				if (!$oreon->optGen["perfparse_installed"]){
					if (isset($_GET["host_name"]) && isset($_GET["service_description"])){
						$_GET["database"] = array("1" => $host_id."_".$service_id.".rrd", "0" => $_GET["host_name"]);
						$p = "40203";
						if (!file_exists($oreon->optGen["oreon_rrdbase_path"].$host_id."_".$service_id.".rrd"))
							$msg_error = $lang["giv_db_unavailable"];
						require_once("./include/views/graphs/graphPlugins/graphPlugins.php");
					} else {
						$_GET["database"] = array("1" => $db_name, "0" => $database[0]);
						$p = "40203";
						if (!file_exists($oreon->optGen["oreon_rrdbase_path"].$db_name))
							$msg_error = $lang["giv_db_unavailable"];
						require_once("./include/views/graphs/graphPlugins/graphPlugins.php");			
					}
				} else {
					clearstatcache();
					require_once("./DBPerfparseConnect.php");
						
					if ((isset($_GET["host_name"]) && $_GET["host_name"])&& (isset($_GET["service_description"]) && $_GET["service_description"]) && isPerfparseEntry($_GET["host_name"], $_GET["service_description"])){
						$p = "40202";
						$_GET["database"] = array("1" => $host_id."_".$service_id.".rrd", "0" => $_GET["host_name"]);
						require_once("./include/views/graphs/simpleRenderer/simpleRenderer.php");
					} else {
						if (!isset($service_id)){
							$database =& $_GET["database"];
							preg_match("/[0-9]+_([0-9]+)\.rrd/", $database[1], $tab);
							if (isset($tab[1]))
								$service_id = $tab[1]; 
						}	
						if (!file_exists($oreon->optGen["oreon_rrdbase_path"].$host_id."_".$service_id.".rrd")){
							$p = "40202";
							$msg_error = $lang["giv_db_unavailable"];
							require_once("./include/views/graphs/simpleRenderer/simpleRenderer.php");				
						} else	{
							if (!isset($_GET["host_name"]))
								$_GET["host_name"] = getMyHostName($host_id);
							$_GET["database"] = array("1" => $host_id."_".$service_id.".rrd", "0" => $_GET["host_name"]);
							$p = "40203";
							require_once("./include/views/graphs/graphPlugins/graphPlugins.php");
						}
					}
				}
			} else
				require_once("./alt_error.php");
			}
	}
?>