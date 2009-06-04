<?php
/*
 * Copyright 2005-2009 MERETHIS
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
require_once $centreon_path . "/www/class/centreonExternalCommand.class.php";
require_once $centreon_path . "/www/class/centreonDB.class.php";
require_once $centreon_path . "/www/class/Session.class.php";
require_once $centreon_path . "/www/class/Oreon.class.php";
require_once $centreon_path . "/www/class/centreonXML.class.php";
  
Session::start();
if (!isset($_SESSION["oreon"]) || !isset($_GET["poller"]) || !isset($_GET["cmd"]) || !isset($_GET["sid"]) || !isset($_GET["type"]))
	exit();

$oreon =& $_SESSION["oreon"];

$locale = $oreon->user->get_lang();
putenv("LANG=$locale");
setlocale(LC_ALL, $locale);
bindtextdomain("messages", $centreon_path."www/locale/");
bind_textdomain_codeset("messages", "UTF-8");
textdomain("messages");

$poller = $_GET["poller"];
$cmd = $_GET["cmd"];
$sid = $_GET["sid"];
$type = $_GET["type"];

$pearDB = new CentreonDB();
$DBRESULT =& $pearDB->query("SELECT session_id FROM session WHERE session.session_id = '".$sid."'");
if (!$DBRESULT->numRows())
	exit();

if (!$oreon->user->access->checkAction($cmd))
	exit();	


$command = new CentreonExternalCommand($oreon);
$cmd_tab = $command->getExternalCommandList();
$command->set_process_command($cmd_tab[$cmd][$type], $poller);
$result = $command->write();

$buffer = new CentreonXML();
$buffer->startElement("root");
$buffer->writeElement("result", $result);
$buffer->writeElement("cmd", $cmd);

$type ? $type = 0 : $type = 1;
$buffer->writeElement("actiontype", $type);

$buffer->endElement();
header('Content-type: text/xml; charset=iso-8859-1');
header('Cache-Control: no-cache, must-revalidate');
$buffer->output();
?>