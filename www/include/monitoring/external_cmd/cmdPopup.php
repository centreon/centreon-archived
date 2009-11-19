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
	require_once("@CENTREON_ETC@/centreon.conf.php");
	require_once($centreon_path . "www/class/centreonSession.class.php");
	require_once($centreon_path . "www/class/centreon.class.php");
	require_once($centreon_path . "www/class/centreonDB.class.php");
	
	session_start();
	$oreon = $_SESSION['oreon'];
	global $oreon;
	global $pearDB;
	
	$pearDB = new CentreonDB();	

	require_once($centreon_path . "www/include/common/common-Func.php");

	if (!isset($oreon))
		exit();
	include_once($centreon_path . "www/include/monitoring/external_cmd/functionsPopup.php");	
	if (isset($_GET["select"]) && isset($_GET["sid"])){
		$is_admin = isUserAdmin($_GET['sid']);
		foreach ($_GET["select"] as $key => $value){	
			if (isset($_GET["cmd"]))
				switch ($_GET["cmd"]) {		
					case 70:	massiveServiceAck($key); 					break;
					case 72:	massiveHostAck($key); 					 	break;
				}
			}
		}
?>
