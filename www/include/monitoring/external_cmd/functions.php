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

$tab = array("1" => "ENABLE", "0" => "DISABLE");

function write_command($cmd, $poller)
{
    global $centreon, $key, $pearDB;

    $str = null;

    /*
     * Destination is centcore pipe path
     */
    if (defined("_CENTREON_VARLIB_")) {
        $destination = _CENTREON_VARLIB_."/centcore.cmd";
    } else {
        $destination = "/var/lib/centreon/centcore.cmd";
    }
    
    $cmd = str_replace("`", "&#96;", $cmd);
    $cmd = str_replace("\n", "<br>", $cmd);
    $informations = preg_split("/\;/", $key);

    if (!mb_detect_encoding($cmd, 'UTF-8', true)) {
        $cmd = utf8_encode($cmd);
    }
    setlocale(LC_CTYPE, 'en_US.UTF-8');

    $str = "echo ". escapeshellarg("EXTERNALCMD:$poller:[" . time() . "]" . $cmd . "\n") . " >> " . $destination;
    return passthru($str);
}

function send_cmd($cmd, $poller = null)
{
    if (isset($cmd)) {
        $flg = write_command($cmd, $poller);
    }
    isset($flg) && $flg ? $ret = $flg : $ret = _("Command execution problem");
    return $ret;
}

/*
 * 	Re-Schedule for all services of an host
 */

function schedule_host_svc_checks($arg, $forced)
{
    global $pearDB, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_checks_for_services");

    if ($actions == true || $is_admin) {
        $tab_forced = array("0" => "", "1" => "_FORCED");
        $flg = send_cmd(" SCHEDULE" . $tab_forced[$forced] . "_HOST_SVC_CHECKS;" . $arg . ";" . time(), GetMyHostPoller($pearDB, $arg));
        return $flg;
    }
    return null;
}

/*
 * SCHEDULE_SVC_CHECK
 */

function schedule_svc_checks($arg, $forced)
{
    global $pearDB, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("service_schedule_check");
    if ($forced == "1") {
        $actions = $centreon->user->access->checkAction("service_schedule_forced_check");
    }
    
    if ($actions == true || $is_admin) {
        $tab_forced = array("0" => "", "1" => "_FORCED");
        $tab_data = preg_split("/\;/", $arg);
        $flg = send_cmd(" SCHEDULE" . $tab_forced[$forced] . "_SVC_CHECK;" . urldecode($tab_data[0]) . ";" . urldecode($tab_data[1]) . ";" . time(), GetMyHostPoller($pearDB, urldecode($tab_data[0])));
        return $flg;
    }
    return null;
}

/*
* SCHEDULE_HOST_CHECK
*/
function schedule_host_checks($arg, $forced)
{
    global $pearDB, $is_admin, $oreon;
    $actions = false;
    $actions = $oreon->user->access->checkAction("host_schedule_check");
    if ($forced == "1") {
        $actions = $oreon->user->access->checkAction("host_schedule_forced_check");
    }

    if ($actions == true || $is_admin) {
        $tab_forced = array("0" => "", "1" => "_FORCED");
        $tab_data = preg_split("/\;/", $arg);
        $flg = send_cmd(" SCHEDULE" . $tab_forced[$forced] . "_HOST_CHECK;" . urldecode($tab_data[0]) . ";" . time(), GetMyHostPoller($pearDB, urldecode($tab_data[0])));
        return $flg;
    }
    return null;
}

/*
 * host check
 */

function host_check($arg, $type)
{
    global $tab, $pearDB, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_checks");

    if ($actions == true || $is_admin) {
        $flg = send_cmd(" " . $tab[$type] . "_HOST_CHECK;" . urldecode($arg), GetMyHostPoller($pearDB, urldecode($arg)));
        return $flg;
    }

    return null;
}

/*
 * 	host notification
 */

function host_notification($arg, $type)
{
    global $tab, $pearDB, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_notifications");

    if ($actions == true || $is_admin) {
        $flg = send_cmd(" " . $tab[$type] . "_HOST_NOTIFICATIONS;" . urldecode($arg), GetMyHostPoller($pearDB, urldecode($arg)));
        return $flg;
    }
    return null;
}

/*
 * ENABLE_HOST_SVC_NOTIFICATIONS
 */

function host_svc_notifications($arg, $type)
{
    global $tab, $pearDB, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_notifications_for_services");

    if ($actions == true || $is_admin) {
        $flg = send_cmd(" " . $tab[$type] . "_HOST_SVC_NOTIFICATIONS;" . urldecode($arg), GetMyHostPoller($pearDB, urldecode($arg)));
        return $flg;
    }
    return null;
}

/*
 * ENABLE_HOST_SVC_CHECKS
 */

function host_svc_checks($arg, $type)
{
    global $tab, $pearDB, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_checks_for_services");

    if ($actions == true || $is_admin) {
        $flg = send_cmd(" " . $tab[$type] . "_HOST_SVC_CHECKS;" . urldecode($arg) . ";" . time(), GetMyHostPoller($pearDB, urldecode($arg)));
        return $flg;
    }
    return null;
}

/*
 * ENABLE_HOST_SVC_CHECKS
 */

function svc_check($arg, $type)
{
    global $tab, $pearDB, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("service_checks");

    if ($actions == true || $is_admin) {
        $tab_data = preg_split("/\;/", $arg);
        $flg = send_cmd(" " . $tab[$type] . "_SVC_CHECK;" . urldecode($tab_data["0"]) . ";" . urldecode($tab_data["1"]), GetMyHostPoller($pearDB, urldecode($tab_data["0"])));
        return $flg;
    }
    return null;
}

/*
 * PASSIVE_SVC_CHECKS
 */

function passive_svc_check($arg, $type)
{
    global $pearDB, $tab, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("service_passive_checks");

    if ($actions == true || $is_admin) {
        $tab_data = preg_split("/\;/", $arg);
        $flg = send_cmd(" " . $tab[$type] . "_PASSIVE_SVC_CHECKS;" . urldecode($tab_data[0]) . ";" . urldecode($tab_data[1]), GetMyHostPoller($pearDB, urldecode($tab_data["0"])));
        return $flg;
    }
    return null;
}

/*
 * SVC_NOTIFICATIONS
 */

function svc_notifications($arg, $type)
{
    global $pearDB, $tab, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("service_notifications");

    if ($actions == true || $is_admin) {
        $tab_data = preg_split("/\;/", $arg);
        $flg = send_cmd(" " . $tab[$type] . "_SVC_NOTIFICATIONS;" . urldecode($tab_data[0]) . ";" . urldecode($tab_data[1]), GetMyHostPoller($pearDB, urldecode($tab_data["0"])));
        return $flg;
    }
    return null;
}

/*
 * SVC_EVENT_HANDLER
 */

function svc_event_handler($arg, $type)
{
    global $pearDB, $tab, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("service_event_handler");

    if ($actions == true || $is_admin) {
        $tab_data = preg_split("/\;/", $arg);
        $flg = send_cmd(" " . $tab[$type] . "_SVC_EVENT_HANDLER;" . urldecode($tab_data[0]) . ";" . urldecode($tab_data[1]), GetMyHostPoller($pearDB, urldecode($tab_data["0"])));
        return $flg;
    }
    return null;
}

/*
 * HOST_EVENT_HANDLER
 */

function host_event_handler($arg, $type)
{
    global $pearDB, $tab, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_event_handler");

    if ($actions == true || $is_admin) {
        $tab_data = preg_split("/\;/", $arg);
        $flg = send_cmd(" " . $tab[$type] . "_HOST_EVENT_HANDLER;" . urldecode($arg), GetMyHostPoller($pearDB, urldecode($arg)));
        return $flg;
    }
    return null;
}

/*
 * Enable or disable Flap detection
 */

function svc_flapping_enable($arg, $type)
{
    global $pearDB, $tab, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("service_flap_detection");

    if ($actions == true || $is_admin) {
        $tab_data = preg_split("/\;/", $arg);
        $flg = send_cmd(" " . $tab[$type] . "_SVC_FLAP_DETECTION;" . urldecode($tab_data[0]) . ";" . urldecode($tab_data[1]), GetMyHostPoller($pearDB, urldecode($tab_data[0])));
        return $flg;
    }
    return null;
}

/*
 * HOST_FLAP_DETECTION
 */

function host_flapping_enable($arg, $type)
{
    global $pearDB, $tab, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_flap_detection");

    if ($actions == true || $is_admin) {
        $tab_data = preg_split("/\;/", $arg);
        $flg = send_cmd(" " . $tab[$type] . "_HOST_FLAP_DETECTION;" . urldecode($arg), GetMyHostPoller($pearDB, urldecode($arg)));
        return $flg;
    }
    return null;
}

/*
 * enable or disable notification for a hostgroup
 */

function notifi_host_hostgroup($arg, $type)
{
    global $pearDB, $tab, $is_admin;
    $tab_data = preg_split("/\;/", $arg);
    $flg = send_cmd(" " . $tab[$type] . "_HOST_NOTIFICATIONS;" . urldecode($tab_data[0]), GetMyHostPoller($pearDB, urldecode($tab_data[0])));
    return $flg;
}

/*
 * Ack a host
 */

function acknowledgeHost($param)
{
    global $pearDB, $tab, $key, $is_admin, $centreon;

    $actions = false;
    $actions = $centreon->user->access->checkAction("host_acknowledgement");

    if ($actions == true || $is_admin) {
        $key = $param["host_name"];
        isset($param['sticky']) && $param['sticky'] == "1" ? $sticky = "2" : $sticky = "1";
        $host_poller = GetMyHostPoller($pearDB, htmlentities($param["host_name"], ENT_QUOTES, "UTF-8"));
        $flg = write_command(" ACKNOWLEDGE_HOST_PROBLEM;" . urldecode($param["host_name"]) . ";$sticky;" . htmlentities($param["notify"], ENT_QUOTES, "UTF-8") . ";" . htmlentities($param["persistent"], ENT_QUOTES, "UTF-8") . ";" . htmlentities($param["author"], ENT_QUOTES, "UTF-8") . ";" . htmlentities($param["comment"], ENT_QUOTES, "UTF-8"), urldecode($host_poller));

        if (isset($param['ackhostservice']) && $param['ackhostservice'] == 1) {
            $svc_tab = getMyHostServices(getMyHostID(htmlentities($param["host_name"], ENT_QUOTES, "UTF-8")));
            if (count($svc_tab)) {
                foreach ($svc_tab as $key2 => $value) {
                    write_command(" ACKNOWLEDGE_SVC_PROBLEM;" . htmlentities(urldecode($param["host_name"]), ENT_QUOTES, "UTF-8") . ";" . $value . ";" . $sticky . ";" . htmlentities($param["notify"], ENT_QUOTES, "UTF-8") . ";" . htmlentities($param["persistent"], ENT_QUOTES, "UTF-8") . ";" . htmlentities($param["author"], ENT_QUOTES, "UTF-8") . ";" . htmlentities($param["comment"], ENT_QUOTES, "UTF-8"), urldecode($host_poller));
                }
            }
        }
        set_user_param($centreon->user->user_id, $pearDB, "ack_sticky", $param["sticky"]);
        set_user_param($centreon->user->user_id, $pearDB, "ack_notify", $param["notify"]);
        set_user_param($centreon->user->user_id, $pearDB, "ack_services", $param["ackhostservice"]);
        set_user_param($centreon->user->user_id, $pearDB, "ack_persistent", $param["persistent"]);
        return _("Your command has been sent");
    }
    return null;
}

/*
 * Remove ack for a host
 */

function acknowledgeHostDisable()
{
    global $pearDB, $tab, $_GET, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_disacknowledgement");

    if ($actions == true || $is_admin) {
        $flg = send_cmd(" REMOVE_HOST_ACKNOWLEDGEMENT;" . urldecode($_GET["host_name"]), GetMyHostPoller($pearDB, urldecode($_GET["host_name"])));
        return $flg;
    }

    return null;
}

/*
 * Remove ack for a service
 */

function acknowledgeServiceDisable()
{
    global $pearDB, $tab, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("service_disacknowledgement");

    if ($actions == true || $is_admin) {
        $flg = send_cmd(" REMOVE_SVC_ACKNOWLEDGEMENT;" . urldecode($_GET["host_name"]) . ";" . urldecode($_GET["service_description"]), GetMyHostPoller($pearDB, urldecode($_GET["host_name"])));
        return $flg;
    }
    return null;
}

/*
 * Ack a service
 */

function acknowledgeService($param)
{
    global $pearDB, $tab, $is_admin, $centreon;

    $actions = false;
    $actions = $centreon->user->access->checkAction("service_acknowledgement");

    if ($actions == true || $is_admin) {
        $param["comment"] = $param["comment"];
        $param["comment"] = str_replace('\'', ' ', $param["comment"]);
        isset($param['sticky']) && $param['sticky'] == "1" ? $sticky = "2" : $sticky = "1";
        $flg = send_cmd(" ACKNOWLEDGE_SVC_PROBLEM;" . urldecode($param["host_name"]) . ";" . urldecode($param["service_description"]) . ";" . $sticky . ";" . $param["notify"] . ";" . $param["persistent"] . ";" . $param["author"] . ";" . $param["comment"], GetMyHostPoller($pearDB, urldecode($param["host_name"])));
        isset($param['force_check']) && $param['force_check'] ? $force_check = 1 : $force_check = 0;
        if ($force_check == 1 && $centreon->user->access->checkAction("service_schedule_forced_check") == true) {
            send_cmd(" SCHEDULE_FORCED_SVC_CHECK;" . urldecode($param["host_name"]) . ";" . urldecode($param["service_description"]) . ";" . time(), GetMyHostPoller($pearDB, urldecode($param["host_name"])));
        }
        set_user_param($centreon->user->user_id, $pearDB, "ack_sticky", $param["sticky"]);
        set_user_param($centreon->user->user_id, $pearDB, "ack_notify", $param["notify"]);
        set_user_param($centreon->user->user_id, $pearDB, "ack_persistent", $param["persistent"]);
        set_user_param($centreon->user->user_id, $pearDB, "force_check", $force_check);
        return $flg;
    }
    return null;
}

function submitPassiveCheck()
{
    global $pearDB, $key, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("service_submit_result");

    if ($actions == true || $is_admin) {
        $key = $_GET["host_name"];
        $flg = send_cmd(" PROCESS_SERVICE_CHECK_RESULT;" . urldecode($_GET["host_name"]) . ";" . urldecode($_GET["service_description"]) . ";" . $_GET["return_code"] . ";" . $_GET["output"] . "|" . $_GET["dataPerform"], GetMyHostPoller($pearDB, urldecode($_GET["host_name"])));
        return $flg;
    }
    return null;
}

function submitHostPassiveCheck()
{
    global $pearDB, $key, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_submit_result");

    if ($actions == true || $is_admin) {
        $key = $_GET["host_name"];
        $flg = send_cmd(" PROCESS_HOST_CHECK_RESULT;" . urldecode($_GET["host_name"]) . ";" . $_GET["return_code"] . ";" . $_GET["output"] . "|" . $_GET["dataPerform"], GetMyHostPoller($pearDB, urldecode($_GET["host_name"])));
        return $flg;
    }
    return null;
}

function notifi_svc_host_hostgroup($arg, $type)
{
    global $tab, $pearDB, $is_admin;
    /* 	$res = $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$arg."'");
      while ($r = $res->fetchRow()){
      $resH = $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$r["host_host_id"]."'");
      $rH = $resH->fetchRow();
      $flg = send_cmd(" " . $tab[$type] . "_HOST_NOTIFICATIONS;". $rH["host_name"]);
      }
     */
    return $flg;
}

function checks_svc_host_hostgroup($arg, $type)
{
    global $tab, $pearDB, $is_admin;
    /* $res = $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$arg."'");
      $r = $res->fetchRow();
      $flg = send_cmd(" " . $tab[$type] . "_HOST_SVC_CHECKS;". $rH["host_name"]);
     */
    return $flg;
}

#############################################################################
# Monitoring Quick Actions
#############################################################################

/*
 * Quick Action -> service ack : Stop and start
 */

function autoAcknowledgeServiceStart($key)
{
    global $pearDB, $tab, $centreon, $is_admin;
    $actions = false;
    $actions = $centreon->user->access->checkAction("service_acknowledgement");

    if ($actions == true || $is_admin) {
        $comment = "Service Auto Acknowledge by " . $centreon->user->alias . "\n";
        $ressource = preg_split("/\;/", $key);
        $flg = send_cmd(" ACKNOWLEDGE_SVC_PROBLEM;" . urldecode($ressource[0]) . ";" . urldecode($ressource[1]) . ";1;1;1;" . $centreon->user->alias . ";" . $comment, GetMyHostPoller($pearDB, urldecode($ressource[0])));
        return $flg;
    }
}

function autoAcknowledgeServiceStop($key)
{
    global $pearDB, $tab, $centreon, $is_admin;
    $actions = false;
    $actions = $centreon->user->access->checkAction("service_disacknowledgement");

    if ($actions == true || $is_admin) {
        $comment = "Service Auto Acknowledge by " . $centreon->user->alias . "\n";
        $ressource = preg_split("/\;/", $key);
        $flg = send_cmd(" REMOVE_SVC_ACKNOWLEDGEMENT;" . urldecode($ressource[0]) . ";" . urldecode($ressource[1]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
        return $flg;
    }
}

/*
 * Quick Action -> host ack : Stop and start
 */

function autoAcknowledgeHostStart($key)
{
    global $pearDB, $tab, $centreon, $is_admin;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_acknowledgement");

    if ($actions == true || $is_admin) {
        $comment = "Host Auto Acknowledge by " . $centreon->user->alias . "\n";
        $ressource = preg_split("/\;/", $key);
        $flg = send_cmd(" ACKNOWLEDGE_HOST_PROBLEM;" . urldecode($ressource[0]) . ";1;1;1;" . $centreon->user->alias . ";" . $comment, GetMyHostPoller($pearDB, urldecode($ressource[0])));
        return $flg;
    }
}

function autoAcknowledgeHostStop($key)
{
    global $pearDB, $tab, $centreon, $is_admin;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_disacknowledgement");

    if ($actions == true || $is_admin) {
        $comment = "Host Auto Acknowledge by " . $centreon->user->alias . "\n";
        $ressource = preg_split("/\;/", $key);
        $flg = send_cmd(" REMOVE_HOST_ACKNOWLEDGEMENT;" . urldecode($ressource[0]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
        return $flg;
    }
}

/*
 * Quick Action -> service notification : Stop and start
 */

function autoNotificationServiceStart($key)
{
    global $pearDB, $tab, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_notifications");

    if ($actions == true || $is_admin) {
        $ressource = preg_split("/\;/", $key);
        $flg = send_cmd(" ENABLE_SVC_NOTIFICATIONS;" . urldecode($ressource[0]) . ";" . urldecode($ressource[1]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
        return $flg;
    }
}

function autoNotificationServiceStop($key)
{
    global $pearDB, $tab, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("service_notifications");

    if ($actions == true || $is_admin) {
        $ressource = preg_split("/\;/", $key);
        $flg = send_cmd(" DISABLE_SVC_NOTIFICATIONS;" . urldecode($ressource[0]) . ";" . urldecode($ressource[1]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
        return $flg;
    }
}

/*
 * Quick Action -> host notification : Stop and start
 */

function autoNotificationHostStart($key)
{
    global $pearDB, $tab, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_notifications");

    if ($actions == true || $is_admin) {
        $ressource = preg_split("/\;/", $key);
        $flg = send_cmd(" ENABLE_HOST_NOTIFICATIONS;" . urldecode($ressource[0]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
        return $flg;
    }
}

function autoNotificationHostStop($key)
{
    global $pearDB, $tab, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_notifications");

    if ($actions == true || $is_admin) {
        $ressource = preg_split("/\;/", $key);
        $flg = send_cmd(" DISABLE_HOST_NOTIFICATIONS;" . urldecode($ressource[0]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
        return $flg;
    }
}

/*
 * Quick Action -> service check : Stop and start
 */

function autoCheckServiceStart($key)
{
    global $pearDB, $tab, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("service_checks");

    if ($actions == true || $is_admin) {
        $ressource = preg_split("/\;/", $key);
        $flg = send_cmd(" ENABLE_SVC_CHECK;" . urldecode($ressource[0]) . ";" . urldecode($ressource[1]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
        return $flg;
    }
}

function autoCheckServiceStop($key)
{
    global $pearDB, $tab, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("service_checks");

    if ($actions == true || $is_admin) {
        $ressource = preg_split("/\;/", $key);
        $flg = send_cmd(" DISABLE_SVC_CHECK;" . urldecode($ressource[0]) . ";" . urldecode($ressource[1]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
        return $flg;
    }
}

/*
 * Quick Action -> host check : Stop and start
 */

function autoCheckHostStart($key)
{
    global $pearDB, $tab, $is_admin, $centreon;
    $actions = false;
    $actions = $centreon->user->access->checkAction("host_checks");

    if ($actions == true || $is_admin) {
        $ressource = preg_split("/\;/", $key);
        $flg = send_cmd(" ENABLE_HOST_CHECK;" . urldecode($ressource[0]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
        return $flg;
    }
}

function autoCheckHostStop($key)
{
    global $centreon;

    $actions = false;
    $actions = $centreon->user->access->checkAction("host_checks");

    if ($actions == true || $is_admin) {
        global $pearDB, $tab, $is_admin;
        $ressource = preg_split("/\;/", $key);
        $flg = send_cmd(" DISABLE_HOST_CHECK;" . urldecode($ressource[0]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
        return $flg;
    }
}
