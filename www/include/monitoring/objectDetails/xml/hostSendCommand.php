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
require_once $centreon_path . "/www/class/centreonDB.class.php";
require_once $centreon_path . "/www/class/centreonHost.class.php";
require_once $centreon_path . "/www/class/centreonACL.class.php";
require_once $centreon_path . "/www/class/Session.class.php";
require_once $centreon_path . "/www/class/Oreon.class.php";
require_once $centreon_path . "/www/class/centreonXML.class.php";
  
Session::start();
if (!isset($_SESSION["oreon"]) || !isset($_GET["host_id"]) || !isset($_GET["cmd"]) || !isset($_GET["sid"]))
	exit();

$oreon =& $_SESSION["oreon"];
$pearDB = new CentreonDB();
$hostObj = new CentreonHost($pearDB);
$host_id = $_GET["host_id"];
$poller = $hostObj->getHostPollerId($host_id);
$cmd = $_GET["cmd"];
$sid = $_GET["sid"];
$str = $_GET["str"];

$pearDB = new CentreonDB();

$DBRESULT =& $pearDB->query("SELECT session_id FROM session WHERE session.session_id = '".$sid."'");
if (!$DBRESULT->numRows())
	exit();
if (!$oreon->user->access->checkAction($cmd))
	exit();

$command = new CentreonExternalCommand($oreon);
$cmd_list = $command->getExternalCommandList();

$cmd = $cmd_list[$cmd];

$tab = split("\|", $cmd);

if (preg_match(_("/Enable/"), $str) && !preg_match(_("/services/"), $str)) {
	$img_flag = "<img src='./img/icones/16x16/element_previous.gif'>";	
	$switch_str = str_replace(_("Enable"), _("Disable"), $str);	
	$cmd = $tab[0];
	$cmd .= ";" . $hostObj->getHostName($host_id);
	$command->set_process_command($cmd, $poller);
}
elseif (preg_match(_("/Enable/"), $str) && preg_match(_("/services/"), $str)) {
	$img_flag = "<img src='./img/icones/16x16/element_next.gif'>";
	$switch_str = $str;
	$cmd = $tab[0];
	$cmd .= ";" . $hostObj->getHostName($host_id);
	$command->set_process_command($cmd, $poller);
}
elseif (preg_match(_("/Disable/"), $str) && !preg_match(_("/services/"), $str)) {
	$img_flag = "<img src='./img/icones/16x16/element_next.gif'>";	
	$switch_str = str_replace(_("Disable"), _("Enable"), $str);
	$cmd = $tab[1];
	$cmd .= ";" . $hostObj->getHostName($host_id);
	$command->set_process_command($cmd, $poller);
}
elseif (preg_match(_("/Disable/"), $str) && preg_match(_("/services/"), $str)) {
	$img_flag = "<img src='./img/icones/16x16/element_previous.gif'>";	
	$switch_str = $str;
	$cmd = $tab[1];
	$cmd .= ";" . $hostObj->getHostName($host_id);
	$command->set_process_command($cmd, $poller);
}
elseif ($str == _("Schedule an immediate check of all services on this host")) {
	$img_flag = "<img src='./img/icones/16x16/undo.gif'>";	
	$switch_str = str_replace(_("Disable"), _("Enable"), $str);
	$cmd = $tab[0];
	$cmd .= ";" . $hostObj->getHostName($host_id);
	$command->set_process_command($cmd, $poller);
}
elseif ($str == _("Schedule an immediate check of all services on this host (forced)")) {
	$img_flag = "<img src='./img/icones/16x16/undo.gif'>";
	$switch_str = str_replace(_("Disable"), _("Enable"), $str);
	$cmd = $tab[1];
	$cmd .= ";" . $hostObj->getHostName($host_id);
	$command->set_process_command($cmd, $poller);
}

$result = $command->write();
$buffer = new CentreonXML();
$buffer->startElement("root");
	$buffer->writeElement("result", $result);
	$buffer->writeElement("cmd", $cmd);
	$buffer->writeElement("img_flag", $img_flag);	
	$buffer->writeElement("switch_str", $switch_str);
$buffer->endElement();
header('Content-type: text/xml; charset=iso-8859-1');
header('Cache-Control: no-cache, must-revalidate');
$buffer->output();
?>