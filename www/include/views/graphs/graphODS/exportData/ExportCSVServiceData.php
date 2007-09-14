<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon

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

	$oreonPath = '/srv/oreon/';

	function check_injection(){
		if ( eregi("(<|>|;|UNION|ALL|OR|AND|ORDER|SELECT|WHERE)", $_GET["sid"])) {
			get_error('sql injection detected');
			return 1;
		}
		return 0;
	}

	function get_error($str){
		echo $str."<br>";
		exit(0);
	}

	include_once($oreonPath . "www/oreon.conf.php");
	include_once($oreonPath . "www/DBconnect.php");
	$oreon = 1;
	include_once($oreonPath . "www/DBOdsConnect.php");

	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if($res->fetchInto($session)){
			$_POST["sid"] = $sid;
		} else
			get_error('bad session id');
	} else
		get_error('need session identifiant !');
	/* security end 2/2 */

	isset ($_GET["index"]) ? $index = $_GET["index"] : $index = NULL;
	isset ($_POST["index"]) ? $index = $_POST["index"] : $index = $index;

	$path = "./include/views/graphs/graphODS/";

	$period = (isset($_POST["period"])) ? $_POST["period"] : "today"; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;

	header("Content-Type: application/csv-tab-delimited-table");
	header("Content-disposition: filename=".$index.".csv");

	$DBRESULT =& $pearDBO->query("SELECT metric_id FROM metrics, index_data WHERE metrics.index_id = index_data.id AND id = '$index'");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	while ($DBRESULT->fetchInto($index_data)){	
		$DBRESULT2 =& $pearDBO->query("SELECT ctime,value FROM data_bin WHERE id_metric = '".$index_data["metric_id"]."' AND ctime >= '".$_GET["start"]."' AND ctime < '".$_GET["end"]."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		while ($DBRESULT2->fetchInto($data)){
			if (!isset($datas[$data["ctime"]]))
				$datas[$data["ctime"]] = array();
			$datas[$data["ctime"]][$index_data["metric_id"]] = $data["value"];
		}
	}
	foreach ($datas as $key => $tab){
		print $key.";";
		foreach($tab as $value)
			print $value.";";
		print "\n";
	}
	exit();
?>