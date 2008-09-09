<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon 
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

	require_once 'DB.php';
	require_once("@CENTREON_PATH@/centreon.conf.php");
	require_once $centreon_path.'www/include/reporting/dashboard/common-Func.php';
	require_once $centreon_path.'www/class/other.class.php';
	require_once $centreon_path.'www/include/reporting/dashboard/xmlInformations/common-Func.php';
	/*
	 * Definition of status
	 */
	$state["UP"] = _("UP");
	$state["DOWN"] = _("DOWN");
	$state["UNREACHABLE"] = _("UNREACHABLE");
	$state["UNDETERMINED"] = _("UNDETERMINED");
	
	$buffer  = '<?xml version="1.0"?>';
	$buffer .= '<data>';
	
	if (isset($_GET["id"]) && isset($_GET["color"])){
		$color = array();
		$get_color = $_GET["color"];
		foreach ($get_color as $key => $value) {
			$color[$key] = $value;
		}

		$pearDBO = getCentStorageConnection();
		$pearDB = getCentreonConnection();
		$str = "";
		$request = "SELECT host_host_id FROM `hostgroup_relation` WHERE `hostgroup_hg_id` = '" . $_GET["id"] ."'";
		$DBRESULT = & $pearDB->query($request);
		if (PEAR::isError($DBRESULT))
			die( "MySQL Error : ".$DBRESULT->getDebugInfo());
		while ($hg =& $DBRESULT->fetchRow()) {
			if ($str != "")
				$str .= ", ";
			$str .= $hg["host_host_id"]; 
		}
		unset($hg);
		unset($DBRESULT);
		
		$rq = 'SELECT `date_start`, `date_end`, sum(`UPnbEvent`) as UPnbEvent, sum(`DOWNnbEvent`) as DOWNnbEvent, sum(`UNREACHABLEnbEvent`) as UNREACHABLEnbEvent, ' .
				'avg( `UPTimeScheduled` ) as "UPTimeScheduled", '.
				'avg( `DOWNTimeScheduled` ) as "DOWNTimeScheduled", ' .
				'avg( `UNREACHABLETimeScheduled` ) as "UNREACHABLETimeScheduled", ' .
				'avg( `UNDETERMINEDTimeScheduled` ) as "UNDETERMINEDTimeScheduled" ' .
				'FROM `log_archive_host` WHERE `host_id` IN ('.$str.') GROUP BY date_end, date_start ORDER BY date_start desc';
		$DBRESULT = & $pearDBO->query($rq);
		if (PEAR::isError($DBRESULT))
			die( "MySQL Error : ".$DBRESULT->getDebugInfo());
			$statesTab = array("UP", "DOWN", "UNREACHABLE");
		while ($row =& $DBRESULT->fetchRow()) {
			$buffer = fillBuffer($statesTab, $row, $color, $buffer);
		  }
	} else	{
		$buffer .= '<error>error</error>';
	}

	$buffer .= '</data>';
	header('Content-Type: text/xml');
	echo $buffer;
?>