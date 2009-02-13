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

	if (isset($_POST["limit"]) && $_POST["limit"])
		$limit = $_POST["limit"];
	else if (isset($_GET["limit"]))
		$limit = $_GET["limit"];
	else if (!isset($_POST["limit"]) && !isset($_GET["limit"]) && isset($oreon->historyLimit[$url]))
		$limit = $oreon->historyLimit[$url];
	else {
		if (($p >= 200 && $p < 300) || ($p >= 20000 && $p < 30000)){
			$DBRESULT =& $pearDB->query("SELECT * FROM `options` WHERE `key` = 'maxViewMonitoring'");
			$gopt =& $DBRESULT->fetchRow();		
			$limit = myDecode($gopt["value"]);
		} else {
			$DBRESULT =& $pearDB->query("SELECT * FROM `options` WHERE `key` = 'maxViewConfiguration'");
			$gopt =& $DBRESULT->fetchRow();		
			$limit = myDecode($gopt["value"]);
		}
	}

	if (isset($_POST["num"]) && $_POST["num"])
		$num = $_POST["num"];
	else if (isset($_GET["num"]) && $_GET["num"])
		$num = $_GET["num"];
	else if (!isset($_POST["num"]) && !isset($_GET["num"]) && isset($oreon->historyPage[$url]))
		$num = $oreon->historyPage[$url];
	else 
		$num = 0;
	
	global $search;
?>