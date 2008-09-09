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
	require_once("@CENTREON_ETC@/centreon.conf.php");
	require_once $centreon_path.'www/include/reporting/dashboard/common-Func.php';
	require_once $centreon_path.'www/class/other.class.php';
	require_once $centreon_path.'www/include/reporting/dashboard/xmlInformations/common-Func.php';
	
	
	$buffer = null;
	$buffer  = '<?xml version="1.0"?>';
	$buffer .= '<data>';
	
	$state["OK"] = _("OK");
	$state["WARNING"] = _("WARNING");
	$state["CRITICAL"] = _("CRITICAL");
	$state["UNKNOWN"] = _("UNKNOWN");
	$state["UNDETERMINED"] = _("UNDETERMINED");
	
	
	if (isset($_GET["host_id"]) && isset($_GET["id"]) && isset($_GET["color"])){
		$color = array();
		$get_color = $_GET["color"];
		foreach ($get_color as $key => $value) {
			$color[$key] = $value;
		}
		$pearDBO = getCentStorageConnection();
		$request = "SELECT  * FROM `log_archive_service` WHERE host_id = '".$_GET["host_id"]."' AND service_id = ".$_GET["id"]." ORDER BY `date_start` DESC";
		$DBRESULT =& $pearDBO->query($request);
		if (PEAR::isError($DBRESULT)) 
	  		die("Connecting probems with centstorage database : " . $DBRESULT->getDebugInfo());
		$statesTab = array("OK", "WARNING", "CRITICAL", "UNKNOWN");
		while ($row =& $DBRESULT->fetchRow())
			$buffer = fillBuffer($statesTab, $row, $color, $buffer);
	} else {
		$buffer .= '<error>error</error>';
	}

	$buffer .= '</data>';
	header('Content-Type: text/xml');
	echo $buffer;
?>