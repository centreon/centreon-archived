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

require_once "@CENTREON_ETC@/centreon.conf.php";
require_once $centreon_path . "/www/class/centreonExternalCommand.class.php";
require_once $centreon_path . "/www/class/Session.class.php";
require_once $centreon_path . "/www/class/Oreon.class.php";
require_once $centreon_path . "/www/class/centreonXML.class.php";
  
Session::start();
if (!isset($_SESSION["oreon"]) || !isset($_GET["poller"]) || !isset($_GET["cmd"]))
	exit();

$oreon =& $_SESSION["oreon"];

$poller = $_GET["poller"];
$cmd = $_GET["cmd"];

$command = new CentreonExternalCommand($oreon);
$command->set_process_command($cmd, $poller);
$result = $command->write();

$buffer = new CentreonXML();
$buffer->startElement("root");
$buffer->writeElement("result", $result);
$buffer->writeElement("cmd", $cmd);
$buffer->endElement();
header('Content-type: text/xml; charset=iso-8859-1');
header('Cache-Control: no-cache, must-revalidate');
$buffer->output();
?>