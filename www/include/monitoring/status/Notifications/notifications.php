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

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");
require_once realpath(__DIR__ . "/../../../../../bootstrap.php");
require_once _CENTREON_PATH_ . "www/class/centreonXMLBGRequest.class.php";

CentreonSession::start();
$sid = session_id();
if (!isset($sid) || !isset($_GET['refresh_rate'])) {
    exit;
}

$refreshRate = (int)$_GET['refresh_rate'] / 1000;
$refreshRate += ($refreshRate / 2);

$obj = new CentreonXMLBGRequest($dependencyInjector, $sid, 1, 1, 0, 1);

$centreon = $_SESSION['centreon'] ?? null;

if (!isset($_SESSION['centreon'])) {
    exit;
}
if (!isset($obj->session_id) || !CentreonSession::checkSession($sid, $obj->DB)) {
    exit;
}

if (!isset($_SESSION['centreon_notification_preferences'])) {
    $userId = $centreon->user->get_id();
    $resPref = $obj->DB->query("SELECT cp_key, cp_value
         FROM contact_param 
         WHERE cp_key LIKE 'monitoring%notification%'
         AND cp_contact_id = '" . $obj->DB->escape($userId) . "'");
    $notificationPreferences = [];
    while ($rowPref = $resPref->fetch()) {
        $notificationPreferences[$rowPref['cp_key']] = $rowPref['cp_value'];
    }
    $_SESSION['centreon_notification_preferences'] = $notificationPreferences;
} else {
    $notificationPreferences = $_SESSION['centreon_notification_preferences'];
}
$notificationEnabled = false;
foreach ($notificationPreferences as $key => $value) {
    if (preg_match('/monitoring_(host|svc)_notification_/', $key) && !is_null($value) && $value == 1) {
        $notificationEnabled = true;
        break;
    }
}

if ($notificationEnabled === false) {
    $obj->XML->startElement("data");
    $obj->XML->endElement();
    $obj->header();
    $obj->XML->output();
    return;
}

$serviceStateLabel = array(0 => "OK", 1 => "Warning", 2 => "Critical", 3 => "Unknown");
$serviceClassLabel = array(0 => "success", 1 => "warning", 2 => "error", 3 => "alert");
$hostStateLabel = array(0 => "Up", 1 => "Down", 2 => "Unreachable");
$hostClassLabel = array(0 => "success", 1 => "error", 2 => "alert");

$sql = "SELECT name, description, s.state
        FROM services s, hosts h %s
        WHERE h.host_id = s.host_id
        AND h.name NOT LIKE '\_Module\_%%'
        AND (description NOT LIKE 'meta\_%%' AND description NOT LIKE 'ba\_%%')
        AND s.last_hard_state_change > (UNIX_TIMESTAMP(NOW()) - " . (int)$refreshRate . ")
        AND s.scheduled_downtime_depth=0
        AND s.acknowledged=0
        %s
        UNION
        SELECT 'Meta Service', s.display_name, s.state
        FROM services s, hosts h %s
        WHERE h.host_id = s.host_id
        AND h.name LIKE '\_Module\_Meta%%'
        AND description LIKE 'meta\_%%'
        AND s.last_hard_state_change > (UNIX_TIMESTAMP(NOW()) - " . (int)$refreshRate . ")
        AND s.scheduled_downtime_depth=0
        AND s.acknowledged=0
        %s
        UNION
        SELECT 'Business Activity', s.display_name, s.state
        FROM services s, hosts h %s
        WHERE h.host_id = s.host_id
        AND h.name LIKE '\_Module\_BAM%%'
        AND description LIKE 'ba\_%%'
        AND s.last_hard_state_change > (UNIX_TIMESTAMP(NOW()) - " . (int)$refreshRate . ")
        AND s.scheduled_downtime_depth=0
        AND s.acknowledged=0
        %s
        UNION
        SELECT name, NULL, h.state
        FROM hosts h %s
        WHERE name NOT LIKE '\_Module\_%%'
        AND h.last_hard_state_change > (UNIX_TIMESTAMP(NOW()) - " . (int)$refreshRate . ")
        AND h.scheduled_downtime_depth=0
        AND h.acknowledged=0
        %s";
if ($obj->is_admin) {
    $sql = sprintf($sql, "", "", "", "", "", "", "", "");
} else {
    $sql = sprintf(
        $sql,
        ", centreon_acl acl",
        "AND acl.service_id = s.service_id AND acl.host_id = h.host_id " .
        $obj->access->queryBuilder("AND", "acl.group_id", $obj->grouplistStr),
        ", centreon_acl acl",
        "AND acl.service_id = s.service_id AND acl.host_id = h.host_id " .
        $obj->access->queryBuilder("AND", "acl.group_id", $obj->grouplistStr),
        ", centreon_acl acl",
        "AND acl.service_id = s.service_id AND acl.host_id = h.host_id " .
        $obj->access->queryBuilder("AND", "acl.group_id", $obj->grouplistStr),
        ", centreon_acl acl",
        "AND acl.host_id = h.host_id" . $obj->access->queryBuilder("AND", "acl.group_id", $obj->grouplistStr)
    );
}
$res = $obj->DBC->query($sql);
$obj->XML->startElement("data");
while ($row = $res->fetch()) {
    $obj->XML->startElement("message");
    if ($row['description']) {
        if (isset($notificationPreferences['monitoring_svc_notification_' . $row['state']])) {
            $obj->XML->writeAttribute(
                "output",
                sprintf(
                    "%s / %s is %s",
                    $row['name'],
                    $row['description'],
                    $serviceStateLabel[$row['state']]
                )
            );
            $obj->XML->writeAttribute(
                "class",
                $serviceClassLabel[$row['state']]
            );
        }
        if (
            !isset($_SESSION['disable_sound']) &&
            isset($notificationPreferences['monitoring_sound_svc_notification_' . $row['state']]) &&
            $notificationPreferences['monitoring_sound_svc_notification_' . $row['state']]
        ) {
            $obj->XML->writeAttribute(
                "sound",
                $notificationPreferences['monitoring_sound_svc_notification_' . $row['state']]
            );
        }
    } else {
        if (isset($notificationPreferences['monitoring_host_notification_' . $row['state']])) {
            $obj->XML->writeAttribute(
                "output",
                sprintf(
                    "%s is %s",
                    $row['name'],
                    $hostStateLabel[$row['state']]
                )
            );
            $obj->XML->writeAttribute(
                "class",
                $hostClassLabel[$row['state']]
            );
        }
        if (
            !isset($_SESSION['disable_sound']) &&
            isset($notificationPreferences['monitoring_sound_host_notification_' . $row['state']]) &&
            $notificationPreferences['monitoring_sound_host_notification_' . $row['state']]
        ) {
            $obj->XML->writeAttribute(
                "sound",
                $notificationPreferences['monitoring_sound_host_notification_' . $row['state']]
            );
        }
    }
    $obj->XML->endElement();
}
$obj->XML->endElement();

$obj->header();
$obj->XML->output();
