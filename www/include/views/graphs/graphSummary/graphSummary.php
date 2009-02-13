<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	if (!isset($oreon))
		exit();
				
	include_once("./include/common/common-Func.php");	
	include_once("./class/centreonDB.class.php");
	
	$pearDBO = new CentreonDB("centstorage");
	
	$lcaHostByID = getLcaHostByID($pearDB);	
	
	function return_period($service_id){
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT graph_id FROM extended_service_information WHERE service_service_id = '".$service_id."'");
		$service_ext =& $DBRESULT->fetchRow();
		$DBRESULT =& $pearDB->query("SELECT period FROM giv_graphs_template WHERE graph_id = '".$service_ext["graph_id"]."'");
		$graph =& $DBRESULT->fetchRow();
		return $graph["period"];
	}


	if (is_file("./class/centreonDB.class.php")){
		if (isset($_GET["service_osm_id"])){
			$tab_host_svc = split('_', $_GET["service_osm_id"]);			
			$_GET["host_name"] = getMyHostName($tab_host_svc[0]);
			$_GET["service_description"] = getMyServiceName($tab_host_svc[1], $tab_host_svc[0]);
		}
		
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
			$svc_id =& $DBRESULT2->fetchRow();
			$_GET["o"] = "vs";
			$_GET["index"] = $svc_id["id"];
			require("./include/views/graphs/graphODS/summaryODS.php");
		} else if (isset($_GET["index"])) {
			require("./include/views/graphs/graphODS/summaryODS.php");
		}
	}
?>