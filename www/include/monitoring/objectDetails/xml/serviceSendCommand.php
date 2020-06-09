<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
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

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");
require_once _CENTREON_PATH_ . "/www/class/centreonExternalCommand.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonHost.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonService.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonACL.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreon.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonXML.class.php";

CentreonSession::start(1);
$centreon = $_SESSION["centreon"];
if (!isset($_SESSION["centreon"]) ||
    !isset($_POST["host_id"]) ||
    !isset($_POST["service_id"]) ||
    !isset($_POST["cmd"]) ||
    !isset($_POST["actiontype"])
) {
    exit();
}

$pearDB = new CentreonDB();
$hostObj = new CentreonHost($pearDB);
$svcObj = new CentreonService($pearDB);
$hostId = filter_var($_POST['host_id'], FILTER_VALIDATE_INT);
$svcId = filter_var($_POST['service_id'], FILTER_VALIDATE_INT);
$poller = $hostObj->getHostPollerId($hostId);
$cmd = $_GET["cmd"];
$sid = session_id();
$actType = $_GET["actiontype"];
$actType ? $returnType = 1 : $returnType = 0;

$pearDB = new CentreonDB();

$DBRESULT = $pearDB->query("SELECT session_id FROM session WHERE session.session_id = '" . $sid . "'");
if (!$DBRESULT->rowCount()) {
    exit();
}

if ($centreon->user->is_admin() === 0) {
    if (!$centreon->user->access->checkAction($cmd)) {
        exit();
    }
    if (!$centreon->user->access->checkHost($hostId)) {
        exit();
    }
    if (!$centreon->user->access->checkService($svcId)) {
        exit();
    }
}


$command = new CentreonExternalCommand($centreon);
$cmdList = $command->getExternalCommandList();

$send_cmd = $cmdList[$cmd][$actType];

$send_cmd .= ";" . $hostObj->getHostName($hostId) . ";" . $svcObj->getServiceDesc($svcId) . ";" . time();
$command->setProcessCommand($send_cmd, $poller);
$result = $command->write();
$buffer = new CentreonXML();
$buffer->startElement("root");
$buffer->writeElement("result", $result);
$buffer->writeElement("cmd", $cmd);
$buffer->writeElement("actiontype", $returnType);
$buffer->endElement();
header('Content-type: text/xml; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
$buffer->output();
