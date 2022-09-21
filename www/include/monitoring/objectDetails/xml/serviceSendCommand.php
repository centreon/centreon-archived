<?php

/*
 * Copyright 2005-2020 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
require_once _CENTREON_PATH_ . "/www/class/centreonUtils.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreon.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonXML.class.php";

CentreonSession::start(1);
$centreon = $_SESSION["centreon"];
if (!isset($_SESSION["centreon"], $_POST["host_id"], $_POST["service_id"], $_POST["cmd"], $_POST["actiontype"])) {
    exit();
}

$pearDB = new CentreonDB();
$hostObj = new CentreonHost($pearDB);
$svcObj = new CentreonService($pearDB);

$hostId = filter_var(
    $_POST['host_id'] ?? false,
    FILTER_VALIDATE_INT
);

$serviceId = filter_var(
    $_POST['service_id'] ?? false,
    FILTER_VALIDATE_INT
);

$pollerId = $hostObj->getHostPollerId($hostId);

$cmd = \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['cmd'] ?? '');

$cmd = CentreonUtils::escapeSecure($cmd, CentreonUtils::ESCAPE_ILLEGAL_CHARS);

$actionType = (int) $_POST['actiontype'];

$pearDB = new CentreonDB();

if ($sessionId = session_id()) {
    $res = $pearDB->prepare("SELECT * FROM `session` WHERE `session_id` = :sid");
    $res->bindValue(':sid', $sessionId, PDO::PARAM_STR);
    $res->execute();
    if (!$session = $res->fetch(PDO::FETCH_ASSOC)) {
        exit();
    }
} else {
    exit();
}

/* If admin variable equals 1 it means that user admin
 * otherwise it means that it is a simple user under ACL
 */
$isAdmin = (int) $centreon->user->access->admin;
if ($centreon->user->access->admin === 0) {
    if (!$centreon->user->access->checkAction($cmd)) {
        exit();
    }
    if (!$centreon->user->access->checkHost($hostId)) {
        exit();
    }
    if (!$centreon->user->access->checkService($serviceId)) {
        exit();
    }
}


$command = new CentreonExternalCommand($centreon);
$commandList = $command->getExternalCommandList();

$sendCommand = $commandList[$cmd][$actionType];

$sendCommand .= ";" . $hostObj->getHostName($hostId) . ";" . $svcObj->getServiceDesc($serviceId) . ";" . time();
$command->setProcessCommand($sendCommand, $pollerId);
$returnType = $actionType ? 1 : 0;
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
