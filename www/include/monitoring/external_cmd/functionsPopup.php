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

if (!isset($oreon)) {
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
 */
function massiveHostAck($key){
    global $pearDB, $is_admin, $oreon;
    static $processedHosts = array();

    $actions = $oreon->user->access->checkAction("host_acknowledgement");

    $key = urldecode($key);
    $tmp = preg_split("/\;/", $key);
    $hostName = $tmp[0];
    if (isset($processedHosts[$hostName])) {
        return null;
    }
    $processedHosts[$hostName] = true;

    $persistent = isset($_GET['persistent']) && $_GET['persistent'] == "true" ? "1" : "0";
    $notify = isset($_GET['notify']) && $_GET['notify'] == "true" ? "1" :"0";
    $sticky = isset($_GET['sticky']) && $_GET['sticky'] == "true" ? "2" : "1";
    $force_check = isset($_GET['force_check']) && $_GET['force_check'] == "true" ? "1" : "0";

    $_GET["comment"] = sanitizeShellString($_GET["comment"]);

    $extCmdObj = new CentreonExternalCommand($oreon);

    if ($actions == true || $is_admin) {
        $extCmdObj->acknowledgeHost(
            $hostName,
            $sticky,
            $notify,
            $persistent,
            $_GET["author"],
            $_GET["comment"]
        );
    }

    if ($force_check == 1) {
        $extCmdObj->scheduleForcedCheckHost(
            $hostName
        );
    }

    $actions = $oreon->user->access->checkAction("service_acknowledgement");
    if (($actions == true || $is_admin) && isset($_GET['ackhostservice']) && $_GET['ackhostservice'] == "true") {
        $DBRES = $pearDB->query("SELECT host_id FROM `host` WHERE host_name = '".$hostName."' LIMIT 1");
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
                    $_GET["author"],
                    $_GET["comment"]
                );
                if ($force_check == 1 &&
                    $oreon->user->access->checkAction("service_schedule_forced_check") == true) {
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
    set_user_param($oreon->user->user_id, $pearDB, "ack_sticky", $sticky);
    set_user_param($oreon->user->user_id, $pearDB, "ack_notify", $notify);
    set_user_param($oreon->user->user_id, $pearDB, "ack_persistent", $persistent);
    set_user_param($oreon->user->user_id, $pearDB, "force_check", $force_check);

    return _("Your command has been sent");
}

/**
 *
 * Ack services massively
 * @param $key
 */
function massiveServiceAck($key){
    global $pearDB, $is_admin, $oreon;

    $actions = $oreon->user->access->checkAction("service_acknowledgement");

    $key = urldecode($key);

    $tmp = preg_split("/\;/", $key);

    if (!isset($tmp[0])) {
        throw new Exception('No host found');
    }
    $hostName = $tmp[0];

    if (!isset($tmp[1])) {
        throw new Exception('No service found');
    } else {
        $serviceDescription  = $tmp[1];
    }

    isset($_GET['persistent']) && $_GET['persistent'] == "true" ? $persistent = "1" : $persistent = "0";
    isset($_GET['notify']) && $_GET['notify'] == "true" ? $notify = "1" : $notify = "0";
    isset($_GET['sticky']) && $_GET['sticky'] == "true" ? $sticky = "2" : $sticky = "1";
    isset($_GET['force_check']) && $_GET['force_check'] == "true" ? $force_check = "1" : $force_check = "0";

    if ($actions == true || $is_admin) {
        $_GET["comment"] = sanitizeShellString($_GET["comment"]);

        $extCmdObj = new CentreonExternalCommand($oreon);
        $extCmdObj->acknowledgeService(
            $hostName,
            $serviceDescription,
            $sticky,
            $notify,
            $persistent,
            $_GET["author"],
            $_GET["comment"]
        );

        if ($force_check == 1 && $oreon->user->access->checkAction("service_schedule_forced_check") == true) {
            $extCmdObj->scheduleForcedCheckService(
                $hostName,
                $serviceDescription
            );
        }
        set_user_param($oreon->user->user_id, $pearDB, "ack_sticky", $sticky);
        set_user_param($oreon->user->user_id, $pearDB, "ack_notify", $notify);
        set_user_param($oreon->user->user_id, $pearDB, "ack_persistent", $persistent);
        set_user_param($oreon->user->user_id, $pearDB, "force_check", $force_check);

        return _("Your command has been sent");
    }
    return null;
}


/**
 *
 * Sets host downtime massively
 * @param $key
 */
function massiveHostDowntime($key)
{
    global $is_admin, $oreon;
    static $processedHosts = array();

    $actions = $oreon->user->access->checkAction("host_schedule_downtime");

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

        $start = isset($_GET['start']) && $_GET['start'] ? $_GET['start'] : time();
        $end = isset($_GET['end']) && $_GET['end'] ? $_GET['end'] : time();
        $comment = isset($_GET['comment']) && $_GET['comment'] ? sanitizeShellString($_GET["comment"]) : "";
        $fixed = isset($_GET['fixed']) && $_GET['fixed'] == "true" ? $fixed = 1 : $fixed = 0;
        $duration = isset($_GET['duration']) && $_GET['duration'] && is_numeric($_GET['duration']) ?
            $duration = $_GET['duration'] : $duration = 0;
        $duration_scale = isset($_GET['duration_scale']) && $_GET['duration_scale'] ?
            $duration_scale = $_GET['duration_scale'] : $duration_scale = "s";
        $hostTime = isset($_GET['host_or_centreon_time']) && $_GET['host_or_centreon_time'] == "true" ? "1" : "0";

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
        if (($oreon->user->access->checkAction("service_schedule_downtime") == true)
            && isset($_GET['downtimehostservice']) && $_GET['downtimehostservice'] == "true") {
            $with_services = true;
        }

        $extCmdObj = new CentreonExternalCommand($oreon);
        $extCmdObj->addHostDowntime($host, $comment, $start, $end, $fixed, $duration, $with_services, $hostTime);
    }

    return null;
}

/*
 *  Sets service downtime massively
 */
function massiveServiceDowntime($key) {
    global $is_admin, $oreon;

    $actions = $oreon->user->access->checkAction("service_schedule_downtime");

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

        $start = isset($_GET['start']) && $_GET['start'] ? $_GET['start'] : time();
        $end = isset($_GET['end']) && $_GET['end'] ? $_GET['end'] : time();
        $comment = isset($_GET['comment']) && $_GET['comment'] ? sanitizeShellString($_GET["comment"]) : "";
        $fixed = isset($_GET['fixed']) && $_GET['fixed'] == "true" ? 1 : 0;
        $duration = isset($_GET['duration']) && $_GET['duration'] && is_numeric($_GET['duration']) ?
            $_GET['duration'] : 0;
        $duration_scale = isset($_GET['duration_scale']) && $_GET['duration_scale'] ? $_GET['duration_scale'] : "s";
        $hostTime = isset($_GET['host_or_centreon_time']) && $_GET['host_or_centreon_time'] == "true" ? "1" : "0";

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

        $extCmdObj = new CentreonExternalCommand($oreon);
        $extCmdObj->AddSvcDowntime($host, $service, $comment, $start, $end, $fixed, $duration, $hostTime);
    }
    return null;
}
