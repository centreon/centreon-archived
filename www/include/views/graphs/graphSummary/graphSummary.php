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
		
	require("./include/views/graphs/graphSummary/DB-Func.php");

	function return_period($service_id){
		global $pearDB;
		$res =& $pearDB->query("SELECT graph_id FROM extended_service_information WHERE service_service_id = '".$service_id."'");
		$res->fetchInto($service_ext);
		$res =& $pearDB->query("SELECT period FROM giv_graphs_template WHERE graph_id = '".$service_ext["graph_id"]."'");
		$res->fetchInto($graph);
		return $graph["period"];
	}

	if (isset($_GET["host_name"]))
		$host_id = getHostID($_GET["host_name"]);
	else if (isset($_GET["host_id"]))
		$host_id = $_GET["host_id"];
	else
		$host_id = NULL;

	if (isset($_GET["service_description"]))
		$service_id = getServiceID($_GET["host_name"], $_GET["service_description"]);
	else if (isset($_GET["service_id"]))
		$service_id = $_GET["service_id"];
	else
		$service_id = NULL;
	
	if (!isset($_GET["steps"]))
		$_GET["steps"] = 0;
	
	if (array_key_exists($host_id, $oreon->user->lcaHost))	{
		$_GET["period"] = return_period($service_id);
		$_GET["submitC"] = "Grapher";
		$_GET["grapht_graph_id"] = "";
		if (!$oreon->optGen["perfparse_installed"]){
			if (isset($_GET["host_name"]) && isset($_GET["service_description"])){
				$_GET["database"] = array("1" => $host_id."_".$service_id.".rrd", "0" => $_GET["host_name"]);
				$_GET["p"] = "40203";
				require_once("./include/views/graphs/graphPlugins/graphPlugins.php");
			}
		} else {
			clearstatcache();
			if (file_exists($oreon->optGen["oreon_rrdbase_path"].$host_id."_".$service_id.".rrd")){
				$_GET["database"] = array("1" => $host_id."_".$service_id.".rrd", "0" => $_GET["host_name"]);
				$_GET["p"] = "40203";
				require_once("./include/views/graphs/graphPlugins/graphPlugins.php");
			} else	{
				$_GET["p"] = "40202";
				require_once("./include/views/graphs/simpleRenderer/simpleRenderer.php");
			}
		}
	} else
		require_once("./alt_error.php");
?>