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

if (!isset($centreon)) {
    exit();
}

function sanitizeShellString($string)
{
    $string = str_replace('\'', ' ', trim(urldecode($string)));
    $string = str_replace('`', ' ', $string);
    $string = str_replace('$(', '(', $string);

    return $string;
}

/**
 *
 * Ack hosts massively
 * @param $key
 * @return string
 */
function massiveHostAck($key)
{
    global $pearDB, $is_admin, $centreon;
    static $processedHosts = array();

    $actions = $centreon->user->access->checkAction("host_acknowledgement");

    $key = urldecode($key);
    $tmp = preg_split("/\;/", $key);
    $hostName = $tmp[0];
    if (isset($processedHosts[$hostName])) {
        return null;
    }
    $processedHosts[$hostName] = true;

    $persistent = isset($_POST['persistent']) && $_POST['persistent'] == "true" ? "1" : "0";
    $notify = isset($_POST['notify']) && $_POST['notify'] == "true" ? "1" : "0";
    $sticky = isset($_POST['sticky']) && $_POST['sticky'] == "true" ? "2" : "1";
    $force_check = isset($_POST['force_check']) && $_POST['force_check'] == "true" ? "1" : "0";

    $_POST["comment"] = sanitizeShellString($_POST["comment"]);

    $extCmdObj = new CentreonExternalCommand($centreon);

    if ($actions == true || $is_admin) {
        $extCmdObj->acknowledgeHost(
            $hostName,
            $sticky,
            $notify,
            $persistent,
            $_POST["author"],
            $_POST["comment"]
        );

        if ($force_check == 1) {
            $extCmdObj->scheduleForcedCheckHost(
                $hostName
            );
        }
    }

    $actions = $centreon->user->access->checkAction("service_acknowledgement");
    if (($actions == true || $is_admin) && isset($_POST['ackhostservice']) && $_POST['ackhostservice'] == "true") {
        $DBRES = $pearDB->query("SELECT host_id FROM `host` WHERE host_name = '" . $hostName . "' LIMIT 1");
        $row = $DBRES->fetchRow();
        $svc_tab = getMyHostServices($row['host_id']);
        if (count($svc_tab)) {
            foreach ($svc_tab as $key2 => $value) {
                $extCmdObj->acknowledgeService(
                    $hostName,
                    $value,
                    $sticky,
                    $notify,
                    $persistent,
                    $_POST["author"],
                    $_POST["comment"]
                );
                if (
                    $force_check == 1
                    && $centreon->user->access->checkAction("service_schedule_forced_check") == true
                ) {
                    $extCmdObj->scheduleForcedCheckService(
                        $hostName,
                        $value
                    );
                }
            }
        }
    }

    /*
	 * Set param in memory
	 */
    set_user_param($centreon->user->user_id, $pearDB, "ack_sticky", $sticky);
    set_user_param($centreon->user->user_id, $pearDB, "ack_notify", $notify);
    set_user_param($centreon->user->user_id, $pearDB, "ack_persistent", $persistent);
    set_user_param($centreon->user->user_id, $pearDB, "force_check", $force_check);

    return _("Your command has been sent");
}

/**
 *
 * Ack services massively
 * @param $key
 * @return null|string
 * @throws Exception
 */
function massiveServiceAck($key)
{
    global $pearDB, $is_admin, $centreon;

    $actions = $centreon->user->access->checkAction("service_acknowledgement");

    $key = urldecode($key);

    $tmp = preg_split("/\;/", $key);

    if (!isset($tmp[0])) {
        throw new Exception('No host found');
    }
    $hostName = $tmp[0];

    if (!isset($tmp[1])) {
        throw new Exception('No service found');
    } else {
        $serviceDescription = $tmp[1];
    }

    isset($_POST['persistent']) && $_POST['persistent'] == "true" ? $persistent = "1" : $persistent = "0";
    isset($_POST['notify']) && $_POST['notify'] == "true" ? $notify = "1" : $notify = "0";
    isset($_POST['sticky']) && $_POST['sticky'] == "true" ? $sticky = "2" : $sticky = "1";
    isset($_POST['force_check']) && $_POST['force_check'] == "true" ? $force_check = "1" : $force_check = "0";

    if ($actions == true || $is_admin) {
        $_POST["comment"] = sanitizeShellString($_POST["comment"]);

        $extCmdObj = new CentreonExternalCommand($centreon);
        $extCmdObj->acknowledgeService(
            $hostName,
            $serviceDescription,
            $sticky,
            $notify,
            $persistent,
            $_POST["author"],
            $_POST["comment"]
        );

        if ($force_check == 1 && $centreon->user->access->checkAction("service_schedule_forced_check") == true) {
            $extCmdObj->scheduleForcedCheckService(
                $hostName,
                $serviceDescription
            );
        }
        set_user_param($centreon->user->user_id, $pearDB, "ack_sticky", $sticky);
        set_user_param($centreon->user->user_id, $pearDB, "ack_notify", $notify);
        set_user_param($centreon->user->user_id, $pearDB, "ack_persistent", $persistent);
        set_user_param($centreon->user->user_id, $pearDB, "force_check", $force_check);

        return _("Your command has been sent");
    }
    return null;
}


/**
 *
 * Sets host downtime massively
 * @param $key
 * @return null
 * @throws Exception
 */
function massiveHostDowntime($key)
{
    global $is_admin, $centreon;
    static $processedHosts = array();

    $actions = $centreon->user->access->checkAction("host_schedule_downtime");

    if ($actions == true || $is_admin) {
        $key = urldecode($key);

        $tmp = preg_split("/\;/", $key);
        if (!isset($tmp[0])) {
            throw new Exception('No host found');
        }

        $host_name = $tmp[0];
        if (isset($processedHosts[$host_name])) {
            return null;
        }
        $processedHosts[$host_name] = true;

        $start = isset($_POST['start']) && $_POST['start'] ? $_POST['start'] : time();
        $end = isset($_POST['end']) && $_POST['end'] ? $_POST['end'] : time();
        $comment = isset($_POST['comment']) && $_POST['comment'] ? sanitizeShellString($_POST["comment"]) : "";
        $fixed = isset($_POST['fixed']) && $_POST['fixed'] == "true" ? $fixed = 1 : $fixed = 0;
        $duration = isset($_POST['duration']) && $_POST['duration'] && is_numeric($_POST['duration'])
            ? $duration = $_POST['duration']
            : $duration = 0;
        $duration_scale = isset($_POST['duration_scale']) && $_POST['duration_scale']
            ? $duration_scale = $_POST['duration_scale']
            : $duration_scale = "s";
        $hostTime = isset($_POST['host_or_centreon_time']) && $_POST['host_or_centreon_time'] == "true" ? "1" : "0";

        if ($duration > 0) {
            switch ($duration_scale) {
                default:
                case 's':
                    $duration = $duration;
                    break;
                case 'm':
                    $duration = $duration * 60;
                    break;
                case 'h':
                    $duration = $duration * 60 * 60;
                    break;
                case 'd':
                    $duration = $duration * 60 * 60 * 24;
                    break;
            }
        }

        $host = getMyHostID($host_name);

        $with_services = false;
        if (
            ($centreon->user->access->checkAction("service_schedule_downtime") == true)
            && isset($_POST['downtimehostservice'])
            && $_POST['downtimehostservice'] == "true"
        ) {
            $with_services = true;
        }

        $extCmdObj = new CentreonExternalCommand($centreon);
        $extCmdObj->addHostDowntime($host, $comment, $start, $end, $fixed, $duration, $with_services, $hostTime);
    }

    return null;
}

/*
 *  Sets service downtime massively
 */
function massiveServiceDowntime($key)
{
    global $is_admin, $centreon;

    $actions = $centreon->user->access->checkAction("service_schedule_downtime");

    if ($actions == true || $is_admin) {
        $key = urldecode($key);
        $tmp = preg_split("/\;/", $key);

        if (!isset($tmp[0])) {
            throw new Exception('No host found');
        }
        $host_name = $tmp[0];

        if (!isset($tmp[1])) {
            throw new Exception('No service found');
        } else {
            $svc_description = $tmp[1];
        }

        $start = isset($_POST['start']) && $_POST['start'] ? $_POST['start'] : time();
        $end = isset($_POST['end']) && $_POST['end'] ? $_POST['end'] : time();
        $comment = isset($_POST['comment']) && $_POST['comment'] ? sanitizeShellString($_POST["comment"]) : "";
        $fixed = isset($_POST['fixed']) && $_POST['fixed'] == "true" ? 1 : 0;
        $duration = isset($_POST['duration']) && $_POST['duration'] && is_numeric($_POST['duration']) ?
            $_POST['duration'] : 0;
        $duration_scale = isset($_POST['duration_scale']) && $_POST['duration_scale'] ? $_POST['duration_scale'] : "s";
        $hostTime = isset($_POST['host_or_centreon_time']) && $_POST['host_or_centreon_time'] == "true" ? "1" : "0";

        if ($duration > 0) {
            switch ($duration_scale) {
                default:
                case 's':
                    $duration = $duration;
                    break;

                case 'm':
                    $duration = $duration * 60;
                    break;

                case 'h':
                    $duration = $duration * 60 * 60;
                    break;

                case 'd':
                    $duration = $duration * 60 * 60 * 24;
                    break;
            }
        }

        $host = getMyHostID($host_name);
        $service = getMyServiceID($svc_description, $host);

        $extCmdObj = new CentreonExternalCommand($centreon);
        $extCmdObj->addSvcDowntime($host, $service, $comment, $start, $end, $fixed, $duration, $hostTime);
    }
    return null;
}
