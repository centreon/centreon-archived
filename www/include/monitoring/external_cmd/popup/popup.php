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
require_once($centreon_path . "www/class/Session.class.php");
require_once($centreon_path . "www/class/centreon.class.php");
require_once($centreon_path . "www/class/centreonDB.class.php");
require_once($centreon_path . "www/include/common/common-Func.php");

$pearDB = new CentreonDB();
session_start();
$oreon = $_SESSION['oreon'];
if (!isset($oreon) || !isset($_GET['o']) || !isset($_GET['cmd']) || !isset($_GET['p'])) {
	exit;
}
if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
	$sid = $_GET["sid"];
	$sid = htmlentities($sid);
	$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
	if (!$session =& $res->fetchRow())
		exit;
}
else {
 	exit;
}

define('SMARTY_DIR', $centreon_path . 'GPL_LIB/Smarty/libs/');
require_once SMARTY_DIR . "Smarty.class.php";

$o = $_GET['o'];
$p = $_GET['p'];
$cmd = $_GET['cmd'];

require_once($centreon_path . 'www/include/monitoring/external_cmd/popup/massive_ack.php');

?>
