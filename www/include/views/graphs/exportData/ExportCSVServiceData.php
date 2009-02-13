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

	function check_injection(){
		if ( eregi("(<|>|;|UNION|ALL|OR|AND|ORDER|SELECT|WHERE)", $_GET["sid"])) {
			get_error('sql injection detected');
			return 1;
		}
		return 0;
	}

	function get_error($str){
		echo $str."<br />";
		exit(0);
	}

	include_once "@CENTREON_ETC@/centreon.conf.php";
	include_once $centreon_path."www/class/centreonDB.php";
	
	$pearDB = new CentreonDB();
	$pearDBO = new CentreonDB("centstorage");
	
	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if (!$session =& $res->fetchRow())
			get_error('bad session id');
	} else
		get_error('need session identifiant !');

	isset($_GET["index"]) ? $index = $_GET["index"] : $index = NULL;
	isset($_POST["index"]) ? $index = $_POST["index"] : $index = $index;

	$path = "./include/views/graphs/graphODS/";

	$period = (isset($_POST["period"])) ? $_POST["period"] : "today"; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;

	$DBRESULT =& $pearDBO->query("SELECT host_name, service_description FROM index_data WHERE id = '$index'");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	while ($res =& $DBRESULT->fetchRow()){
		$hName = $res["host_name"];
		$sName = $res["service_description"];
	}	

	header("Content-Type: application/csv-tab-delimited-table");
	if (isset($hName) && isset($sName))
		header("Content-disposition: filename=".$hName."_".$sName.".csv");
	else
		header("Content-disposition: filename=".$index.".csv");

	$listMetric = array();
	$datas = array();
	$DBRESULT =& $pearDBO->query("SELECT metric_id, metric_name FROM metrics, index_data WHERE metrics.index_id = index_data.id AND id = '$index'");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	while ($index_data =& $DBRESULT->fetchRow()){	
		if (!isset($listMetric[$index_data["metric_name"]]))
			$listMetric[$index_data["metric_name"]] = $index_data["metric_name"];
		$DBRESULT2 =& $pearDBO->query("SELECT ctime,value FROM data_bin WHERE id_metric = '".$index_data["metric_id"]."' AND ctime >= '".$_GET["start"]."' AND ctime < '".$_GET["end"]."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		while ($data =& $DBRESULT2->fetchRow()){
			if (!isset($datas[$data["ctime"]]))
				$datas[$data["ctime"]] = array();
			$datas[$data["ctime"]][$index_data["metric_id"]] = $data["value"];
		}
	}
	
	print "time";
	foreach ($listMetric as $table)
		print ";".$table;
	print "\n";
	foreach ($datas as $key => $tab){
		print $key;
		foreach($tab as $value)
			print ";".$value;
		print "\n";
	}
	exit();
?>