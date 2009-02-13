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
	require_once '../../../class/centreonDB.class.php';
	
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

	isset ($_GET["metric_id"]) ? $mtrcs = $_GET["metric_id"] : $mtrcs = NULL;
	isset ($_POST["metric_id"]) ? $mtrcs = $_POST["metric_id"] : $mtrcs = $mtrcs;

	$path = "./include/views/graphs/graphODS/";
	require_once '../../../class/other.class.php';
	require_once '../../common/common-Func.php';	

	$period = (isset($_POST["period"])) ? $_POST["period"] : "today"; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;

	header("Content-Type: application/csv-tab-delimited-table");
	header("Content-disposition: filename=".$mhost.".csv");

	print "Date;value\n";
	$begin = time() - 26000;
		
	$res =& $pearDB->query("SELECT ctime,value FROM data_bin WHERE id_metric = '".$mtrcs."' AND CTIME >= '".$begin."'");
	while ($data =& $res->fetchRow()){
		print $data["ctime"].";".$data["value"]."\n";
	}
	exit();
?>