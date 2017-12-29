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
 */

if (!isset($_POST['poller'])) {
    exit();
}

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");
require_once _CENTREON_PATH_.'/www/class/centreonDB.class.php';
require_once _CENTREON_PATH_.'/www/class/centreonXML.class.php';
require_once _CENTREON_PATH_.'/www/class/centreonInstance.class.php';
require_once _CENTREON_PATH_.'/www/class/centreonSession.class.php';

$db = new CentreonDB();

/* Check Session */
CentreonSession::start(1);
if (!CentreonSession::checkSession(session_id(), $db)) {
    print "Bad Session";
    exit();
}

$pollers = explode(',', $_POST['poller']);

$xml = new CentreonXML();

$res = $db->query("SELECT `name`, `id`, `localhost` 
    FROM `nagios_server` 
    WHERE `ns_activate` = '1' 
    ORDER BY `name` ASC");
$xml->startElement('response');
$str = sprintf("<br/><b>%s</b><br/>", _("Post execution command results"));
$ok = true;
$instanceObj = new CentreonInstance($db);
while ($row = $res->fetchRow()) {
    if ($pollers == 0 || in_array($row['id'], $pollers)) {
        $commands = $instanceObj->getCommandData($row['id']);
        if (!count($commands)) {
            continue;
        }
        $str .= "<br/><strong>{$row['name']}</strong><br/>";
        foreach ($commands as $command) {
            $output = array();
            exec($command['command_line'], $output, $result);
            $resultColor = "green";
            if ($result != 0) {
                $resultColor = "red";
                $ok = false;
            }
            $str .= $command['command_name'] . ": <font color='$resultColor'>".implode(";", $output)."</font><br/>";
        }
    }
}
if ($ok === false) {
    $statusStr = "<b><font color='red'>NOK</font></b>";
} else {
    $statusStr = "<b><font color='green'>OK</font></b>";
}

$xml->writeElement('result', $str);
$xml->writeElement('status', $statusStr);
$xml->endElement();
header('Content-Type: application/xml');
header('Cache-Control: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');
$xml->output();
