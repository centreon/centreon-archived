<?php
/*
 * Copyright 2005-2010 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
 */
		
	require_once "@CENTREON_ETC@/centreon.conf.php";	
	
	require_once $centreon_path."/www/include/common/common-Func.php";
	
	require_once $centreon_path."/www/class/centreonDB.class.php";	
	require_once $centreon_path."/www/class/centreonXML.class.php";
	require_once $centreon_path."/www/class/centreonACL.class.php";
	require_once $centreon_path."/www/class/centreon.class.php";
	require_once $centreon_path."/www/class/centreonSession.class.php";
	require_once $centreon_path."/www/class/centreonLang.class.php";
	
	if (!isset($_GET["sid"]) || !isset($_GET["menu"]))
		exit();
	
	/*
	 * Create MySQL connector
	 */
	$pearDB = new CentreonDB();
	global $pearDB;
	
	/*
	 * Check Session existence
	 */
	$session =& $pearDB->query("SELECT user_id FROM `session` WHERE session_id = '".htmlentities($_GET["sid"], ENT_QUOTES)."'");
	if (!$session->numRows()){
		$buffer = new CentreonXML();
		$buffer->startElement("root");
		$buffer->endElement();
		header('Content-Type: text/xml');
		header('Cache-Control: no-cache');
		$buffer->output();
		exit;
	}
	
	session_start();
	$oreon = $_SESSION['centreon'];
	
	$centreonLang = new CentreonLang($centreon_path, $oreon);
	$centreonLang->bindLang();	
	
	/*
	 * Init XML class
	 */
	$buffer = new CentreonXML();
	
	$user_id = getUserIdFromSID(htmlentities($_GET["sid"], ENT_QUOTES));
	
	if (!$user_id)
		exit();
	
	$is_admin = isUserAdmin(htmlentities($_GET["sid"], ENT_QUOTES));
	$access = new CentreonACL($user_id, $is_admin);
	$topoStr = $access->getTopologyString();	
	
	/*
	 * Get CSS
	 */
	$DBRESULT2 =& $pearDB->query("SELECT css_name FROM `css_color_menu` WHERE menu_nb = '".htmlentities($_GET["menu"], ENT_QUOTES)."' LIMIT 1");
	$menu_style =& $DBRESULT2->fetchRow();
	
	ob_start();
	require_once $centreon_path . "/www/Themes/Centreon-2/Color/" . $menu_style['css_name'];
	ob_end_clean();
	
	
	$buffer->startElement("root");
	$buffer->writeElement("Menu1ID", $menu1_bgcolor);
	$buffer->writeElement("Menu2ID", $menu2_bgcolor);
	$buffer->writeElement("Menu1Color", "menu_1");
	$buffer->writeElement("Menu2Color", "menu_2");
	
	$rq = 	"SELECT * " .
			"FROM topology " .
			"WHERE topology_parent IS NULL ".$access->queryBuilder("AND", "topology_page", $topoStr) .
			" AND topology_show = '1' ORDER BY topology_order";
	$DBRESULT =& $pearDB->query($rq);
	
	$buffer->startElement("level_1");
	while ($elem =& $DBRESULT->fetchRow()) {
		$buffer->startElement("Menu1");
		$buffer->writeElement("Menu1Page", $elem["topology_page"]);
		$buffer->writeElement("Menu1ClassImg", $_GET["menu"] == $elem["topology_page"] ? "Themes/Centreon-2" . substr($menu1_bgimg, 2) : "");
		$buffer->writeElement("Menu1Url", "main.php?p=".$elem["topology_page"].$elem["topology_url_opt"]);
		$buffer->writeElement("Menu1UrlPopup", $elem["topology_popup"]);
		$buffer->writeElement("Menu1UrlPopupOpen", $elem["topology_url"]);
		$buffer->writeElement("Menu1Name", _($elem["topology_name"]), 0);
		$buffer->writeElement("Menu1Popup", $elem["topology_popup"] ? "true" : "false");
		$buffer->endElement();
	}
	$buffer->endElement();
	
	$rq = "SELECT * " .
			"FROM topology " .
			"WHERE topology_parent = '".$_GET["menu"]."' " .$access->queryBuilder("AND", "topology_page", $topoStr) . 
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
		$buffer->writeElement("Menu2UrlPopupOpen", $elem["topology_url"]);
		$buffer->writeElement("Menu2Name", _($elem["topology_name"]), 0);
		$buffer->writeElement("Menu2Popup", $elem["topology_popup"] ? "true" : "false");
		$buffer->endElement();
		$sep = "&nbsp;&nbsp;|&nbsp;&nbsp;";
	}
	$buffer->endElement();
	$buffer->endElement();
	
	header('Content-Type: text/xml');
	header('Cache-Control: no-cache');
	$buffer->output();
?>