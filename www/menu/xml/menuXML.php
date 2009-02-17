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

header('Content-Type: text/xml');
header('Cache-Control: no-cache');
	
//require_once "@CENTREON_ETC@/centreon.conf.php";
require_once "/etc/centreon/centreon.conf.php";
require_once $centreon_path."/www/class/centreonDB.class.php";	
require_once $centreon_path."/www/class/centreonXML.class.php";
require_once $centreon_path."/www/class/centreonACL.class.php";
require_once $centreon_path."/www/include/common/common-Func.php";

if (!isset($_GET["sid"]) || !isset($_GET["menu"]))
	exit();
global $pearDB;
$pearDB = new CentreonDB();
$buffer = new CentreonXML();
$user_id = getUserIdFromSID($_GET["sid"]);
if (!$user_id)
	exit();
$is_admin = isUserAdmin($_GET["sid"]);
$access = new CentreonACL($user_id, $is_admin);
$topoStr = $access->getTopologyString();

$rq = "SELECT * FROM topology " .
	"WHERE topology_parent IS NULL " .
	$access->queryBuilder("AND", "topology_page", $topoStr) .
	"AND topology_show = '1' ORDER BY topology_order";
$DBRESULT =& $pearDB->query($rq);

switch ($_GET["menu"]) {
	case "1" : //blue
		$bg_img = "../Images/menu_bg_blue.gif";
		$bg_color = "#ebf5ff";
		break;
	case "2" : //green
		$bg_img = "../Images/menu_bg_green.gif";
		$bg_color = "#DFF9E0";
		break;
	case "3" : //pink
		$bg_img = "../Images/menu_bg_purple.gif";
		$bg_color = "#ECE5F9";
		break;
	case "4" : //red
		$bg_img = "../Images/menu_bg_red.gif";
		$bg_color = "#F9EDED";
		break;
	case "5" : //yellow
		$bg_img = "../Images/menu_bg_orange.gif";
		$bg_color = "#FEF7DB";
		break;
	default :
		$bg_img = "../Images/menu_bg_blue.gif";
		$bg_color = "#ebf5ff";
		break;
}

$buffer->startElement("root");
$buffer->writeElement("Menu1ID", "menu1_bgcolor");
$buffer->writeElement("Menu2ID", "menu2_bgcolor");
$buffer->writeElement("Menu1Color", "menu_1");
$buffer->writeElement("Menu2Color", "menu_2");


$buffer->startElement("level_1");
while ($elem =& $DBRESULT->fetchRow()) {
	$buffer->startElement("Menu1");
	$buffer->writeElement("Menu1Page", $elem["topology_id"]);
	$buffer->writeElement("Menu1ClassImg", $_GET["menu"] == $elem["topology_page"] ? "menu1_bgimg" : "id_".$elem["topology_id"]);
	$buffer->writeElement("Menu1Url", "main.php?p=".$elem["topology_page"].$elem["topology_url_opt"]);
	$buffer->writeElement("Menu1UrlPopup", $elem["topology_popup"]);
	$buffer->writeElement("Menu1UrlPopupOpen", $elem["topology_url"]);
	$buffer->writeElement("Menu1Name", _($elem["topology_name"]));
	$buffer->writeElement("Menu1Popup", $elem["topology_popup"] ? "true" : "false");
	$buffer->endElement();
	
}
$buffer->endElement();

$rq = "SELECT * FROM topology " .
	"WHERE topology_parent = '".$_GET["menu"]."' " .
	$access->queryBuilder("AND", "topology_page", $topoStr) . 
	"AND topology_show = '1' " .
	"ORDER BY topology_group, topology_order";
$DBRESULT =& $pearDB->query($rq);
$sep = "&nbsp;";
$buffer->startElement("level_2");
while ($elem =& $DBRESULT->fetchRow()) {
	$buffer->startElement("Menu2");
	$buffer->writeElement("Menu2Sep", $sep);
	$buffer->writeElement("Menu2Url", "main.php?p=".$elem["topology_page"].$elem["topology_url_opt"]);
	$buffer->writeElement("Menu2UrlPopup", $elem["topology_popup"]);
	$buffer->writeElement("Menu2UrlPopupOpen", $elem["topology_url"].$auth);
	$buffer->writeElement("Menu2Name", _($elem["topology_name"]));
	$buffer->writeElement("Menu2Popup", $elem["topology_popup"] ? "true" : "false");
	$buffer->endElement();
	$sep = "&nbsp;&nbsp;|&nbsp;&nbsp;";
}
$buffer->endElement();
$buffer->endElement();

$buffer->output();
?>