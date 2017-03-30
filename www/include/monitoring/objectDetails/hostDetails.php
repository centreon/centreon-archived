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

include_once("./class/centreonUtils.class.php");

include_once "./class/centreonDB.class.php";
include_once "./class/centreonHost.class.php";


/*
 * Create Object env
 */
$hostObj = new CentreonHost($pearDB);

/*
 * ACL Actions
 */
$GroupListofUser = array();
$GroupListofUser = $centreon->user->access->getAccessGroups();

/*
 * Init Table status
 */
$tab_status_service = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
$tab_host_status = array(0 => "UP", 1 => "DOWN", 2 => "UNREACHABLE");
$tab_host_statusid = array("UP" => 0, "DOWN" => 1, "UNREACHABLE" => 2);

$tab_color_host = array('up' => 'host_up', 'down' => 'host_down', 'unreachable' => 'host_unreachable');
$tab_color_service = array(
    "OK" => 'service_ok',
    "WARNING" => 'service_warning',
    "CRITICAL" => 'service_critical',
    "UNKNOWN" => 'service_unknown',
    "PENDING" => 'pending'
);


$en_acknowledge_text = array("1" => _("Delete Problem Acknowledgement"), "0" => _("Acknowledge Host Problem"));
$en_acknowledge = array("1" => "0", "0" => "1");
$en_inv = array("1" => "1", "0" => "0");
$en_inv_text = array("1" => _("Disable"), "0" => _("Enable"));
$color_onoff = array("1" => "host_up", "0" => "host_down");
$color_onoff_inv = array("0" => "host_up", "1" => "host_up");
$en_disable = array("1" => _("Enabled"), "0" => _("Disabled"));
$img_en = array("0" => "'./img/icons/enabled.png'", "1" => "'./img/icons/disabled.png'");

$tab_status_type = array("1" => "HARD", "0" => "SOFT");

$allActions = false;
if (count($GroupListofUser) > 0 && $is_admin == 0) {
    $authorized_actions = array();
    $authorized_actions = $centreon->user->access->getActions();
}

if (isset($_GET["host_name"]) && $_GET["host_name"]) {
    $host_name = $_GET["host_name"];
    if (isset($_REQUEST['cmd'])) {
        $host_name = utf8_decode($host_name);
    }
} else {
    foreach ($_GET["select"] as $key => $value) {
        $host_name = $key;
    }
}

/*
 * ACL
 */
$haveAccess = 0;
if (!$is_admin) {
    $DBRESULT = $pearDBO->query("SELECT host_id 
                                FROM centreon_acl 
                                WHERE host_id = '" . getMyHostId($host_name) . "' 
                                AND group_id 
                                IN (" . $centreon->user->access->getAccessGroupsString() . ")");
    if ($DBRESULT->numRows()) {
        $haveAccess = 1;
    }
}

if (!$is_admin && !$haveAccess) {
    include_once("alt_error.php");
} else {
    $tab_status = array();

    $path = "./include/monitoring/objectDetails/";

    $en = array("0" => _("No"), "1" => _("Yes"));

    /*
     * Smarty template Init
     */
    $tpl = new Smarty();
    $tpl = initSmartyTpl($path, $tpl, "./template/");

    // Get GMT
    $centreon->CentreonGMT->getMyGMTFromSession(session_id(), $pearDB);

    /*
     * Host Group List
     */
    $host_id = getMyHostID($host_name);
    if (!is_null($host_id)) {
        /* Get HG relations */
        $DBRESULT = $pearDB->query("SELECT DISTINCT hostgroup_hg_id 
                                    FROM hostgroup_relation 
                                    WHERE host_host_id = '" . $host_id . "'");
        for ($i = 0; $hg = $DBRESULT->fetchRow(); $i++) {
            $hostGroups[] = getMyHostGroupName($hg["hostgroup_hg_id"]);
        }
        $DBRESULT->free();

        /* Get service categories */
        $hostIds = array($host_id);
        $hostTemplates = $hostObj->getTemplateChain($host_id);
        foreach ($hostTemplates as $hostTemplate) {
            $hostIds[] = $hostTemplate['host_id'];
        }

        $DBRESULT = $pearDB->query("SELECT DISTINCT hc.* 
                                    FROM hostcategories hc 
                                    INNER JOIN hostcategories_relation hcr 
                                    ON hc.hc_id = hcr.hostcategories_hc_id 
                                    AND hcr.host_host_id IN ('" . implode("','", $hostIds) . "') ");
        while ($hc = $DBRESULT->fetchRow()) {
            $hostCategorie[] = $hc['hc_name'];
        }
        $DBRESULT->free();

        /* Get notifications contacts */
        $retrievedNotificationsInfos = get_notified_infos_for_host($host_id);
        $contacts = $retrievedNotificationsInfos['contacts'];
        $contactGroups = $retrievedNotificationsInfos['contactGroups'];

        /*
         * Get services informations on the current Host
         */
        $rq = "SELECT DISTINCT s.state AS current_state," .
            " s.output as plugin_output," .
            " s.check_attempt as current_attempt," .
            " s.last_update as status_update_time," .
            " s.last_state_change as last_state_change," .
            " s.last_check," .
            " s.notify AS notifications_enabled," .
            " s.next_check," .
            " s.acknowledged," .
            " s.passive_checks," .
            " s.active_checks," .
            " s.event_handler_enabled," .
            " s.flapping AS is_flapping," .
            " s.latency as check_latency," .
            " s.execution_time as check_execution_time," .
            " s.last_notification as last_notification," .
            " s.service_id as service_id," .
            " h.name AS host_name," .
            " h.host_id AS host_id," .
            " s.scheduled_downtime_depth as in_downtime," .
            " s.description as service_description" .
            " FROM services s, hosts h" . ((!$is_admin) ? ', centreon_acl acl' : '') .
            " WHERE s.host_id = h.host_id AND h.host_id = " . $host_id . " " .
            " AND h.enabled = 1 " .
            " AND s.enabled = 1 " .
            ((!$is_admin) ? ' AND acl.host_id = s.host_id AND acl.service_id = s.service_id AND group_id IN (' .
                $centreon->user->access->getAccessGroupsString() . ')' : '') .
            " ORDER BY current_state DESC, service_description ASC";
        $DBRESULT = $pearDBO->query($rq);
        $services = array();
        $class = 'list_one';
        $graphs = array();
        while ($row = $DBRESULT->fetchRow()) {
            $row["last_check"] = $centreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $row["last_check"]);
            $row["current_state"] = $tab_status_service[$row['current_state']];
            $row["status_class"] = $tab_color_service[$row['current_state']];
            $row['line_class'] = $class;

            /* Split the plugin_output */
            $outputLines = explode("\n", $row['plugin_output']);
            $row['short_output'] = $outputLines[0];
            $row["hnl"] = CentreonUtils::escapeSecure(urlencode($row["host_name"]));
            $row["sdl"] = CentreonUtils::escapeSecure(urlencode($row["service_description"]));
            $row["svc_id"] = $row["service_id"];

            /**
             * Get Service Graph index
             */
            if (!isset($graphs[$row["host_id"]]) || !isset($graphs[$row["host_id"]][$row["service_id"]])) {
                $request2 = "SELECT service_id, id 
                              FROM index_data, metrics 
                              WHERE metrics.index_id = index_data.id 
                              AND host_id = '" . $row["host_id"] . "' 
                              AND service_id = '" . $row["service_id"] . "' 
                              AND index_data.hidden = '0'";
                $DBRESULT2 = $pearDBO->query($request2);
                while ($dataG = $DBRESULT2->fetchRow()) {
                    if (!isset($graphs[$row["host_id"]])) {
                        $graphs[$row["host_id"]] = array();
                    }
                    $graphs[$row["host_id"]][$dataG["service_id"]] = $dataG["id"];
                }
                if (!isset($graphs[$row["host_id"]])) {
                    $graphs[$row["host_id"]] = array();
                }
            }
            $row["svc_index"] = (isset($graphs[$row["host_id"]][$row["service_id"]])
                ? $graphs[$row["host_id"]][$row["service_id"]]
                : 0
            );

            $duration = "";
            if ($row["last_state_change"] > 0 && time() > $row["last_state_change"]) {
                $duration = CentreonDuration::toString(time() - $row["last_state_change"]);
            } elseif ($row["last_state_change"] > 0) {
                $duration = " - ";
            }
            $row["duration"] = $duration;

            ($class == 'list_one') ? $class = 'list_two' : $class = 'list_one';

            // Set Data
            $services[] = $row;
        }
        $DBRESULT->free();

        /*
         * Get host informations
         */
        $rq2 = "SELECT state AS current_state, h.name, alias, h.address, host_id, " .
            " acknowledged AS problem_has_been_acknowledged, " .
            " passive_checks AS passive_checks_enabled," .
            " active_checks AS active_checks_enabled," .
            " notify AS notifications_enabled," .
            " execution_time as check_execution_time," .
            " latency as check_latency," .
            " perfdata as performance_data," .
            " check_attempt as current_attempt," .
            " max_check_attempts, " .
            " state_type," .
            " check_type," .
            " last_notification," .
            " next_host_notification AS next_notification," .
            " flapping AS is_flapping," .
            " h.flap_detection AS flap_detection_enabled," .
            " event_handler_enabled," .
            " obsess_over_host," .
            " notification_number AS current_notification_number," .
            " percent_state_change," .
            " scheduled_downtime_depth," .
            " last_state_change," .
            " output as plugin_output," .
            " last_check," .
            " last_notification," .
            " next_check," .
            " h.address," .
            " h.name AS host_name, " .
            " notes_url, " .
            " notes, " .
            " alias, " .
            " action_url, " .
            " h.timezone, " .
            " i.name as instance_name " .
            " FROM hosts h, instances i " .
            " WHERE h.host_id = $host_id AND h.instance_id = i.instance_id " .
            " AND h.enabled = 1 ";
        $DBRESULT = $pearDBO->query($rq2);
        $data = $DBRESULT->fetchRow();

        $host_status[$host_name] = $data;
        $host_status[$host_name]["timezone"] = substr($host_status[$host_name]["timezone"], 1);
        $host_status[$host_name]["plugin_output"] = htmlentities(
            $host_status[$host_name]["plugin_output"],
            ENT_QUOTES,
            "UTF-8"
        );
        $host_status[$host_name]["current_state"] = $tab_host_status[$data["current_state"]];
        if (isset($host_status[$host_name]["notes_url"]) && $host_status[$host_name]["notes_url"]) {
            $host_status[$host_name]["notes_url"] = str_replace("\$HOSTNAME\$", $data["host_name"], $data["notes_url"]);
            $host_status[$host_name]["notes_url"] = str_replace(
                "\$HOSTADDRESS\$",
                $data["address"],
                $data["notes_url"]
            );
            $host_status[$host_name]["notes_url"] = str_replace("\$HOSTALIAS\$", $data["alias"], $data["notes_url"]);
        }
        if (isset($host_status[$host_name]["notes"]) && $host_status[$host_name]["notes"]) {
            $host_status[$host_name]["notes"] = str_replace("\$HOSTNAME\$", $data["host_name"], $data["notes"]);
            $host_status[$host_name]["notes"] = str_replace("\$HOSTADDRESS\$", $data["address"], $data["notes"]);
            $host_status[$host_name]["notes"] = str_replace("\$HOSTALIAS\$", $data["alias"], $data["notes"]);
        }
        if (isset($host_status[$host_name]["action_url"]) && $host_status[$host_name]["action_url"]) {
            $host_status[$host_name]["action_url"] = str_replace(
                "\$HOSTNAME\$",
                $data["host_name"],
                $data["action_url"]
            );
            $host_status[$host_name]["action_url"] = str_replace(
                "\$HOSTADDRESS\$",
                $data["address"],
                $data["action_url"]
            );
            $host_status[$host_name]["action_url"] = str_replace("\$HOSTALIAS\$", $data["alias"], $data["action_url"]);
        }

        $url_id = null;

        /*
         * Get comments for hosts
         */
        $tabCommentHosts = array();
        $rq2 = " SELECT FROM_UNIXTIME(cmt.entry_time) as comment_time, cmt.comment_id, cmt.author AS author_name,
         cmt.data AS comment_data, cmt.persistent AS is_persistent, h.name AS host_name " .
            " FROM comments cmt, hosts h " .
            " WHERE cmt.host_id = '" . $host_id . "' 
                  AND h.host_id = cmt.host_id 
                  AND cmt.service_id IS NULL 
                  AND cmt.expires = 0 
                  AND (cmt.deletion_time IS NULL OR cmt.deletion_time = 0)
                  ORDER BY cmt.entry_time DESC";
        $DBRESULT = $pearDBO->query($rq2);
        for ($i = 0; $data = $DBRESULT->fetchRow(); $i++) {
            $tabCommentHosts[$i] = $data;
            $tabCommentHosts[$i]["is_persistent"] = $en[$tabCommentHosts[$i]["is_persistent"]];
        }
        $DBRESULT->free();
        unset($data);

        /* Get Graphs Listing */
        $graphLists = array();
        $query = "SELECT DISTINCT i.id, i.host_name, i.service_description, i.host_id, i.service_id " .
            " FROM index_data i, metrics m, hosts h, services s" . ((!$is_admin) ? ', centreon_acl acl' : '') .
            " WHERE m.index_id = i.id " .
            " AND i.host_id = '$host_id' " .
            " AND i.host_id = h.host_id " .
            " AND h.enabled = 1 " .
            " AND i.host_id = s.host_id " .
            " AND i.service_id = s.service_id " .
            " AND s.enabled = 1 " .
            ((!$is_admin) ? ' AND acl.host_id = i.host_id AND acl.service_id = i.service_id AND group_id IN (' .
                $centreon->user->access->getAccessGroupsString() . ')' : '') .
            " ORDER BY i.service_description ASC";
        $DBRESULT = $pearDBO->query($query);
        while ($g = $DBRESULT->fetchRow()) {
            $graphLists[$g["host_id"] . '_' . $g['service_id']] = $g['host_name'] . ";" . $g['service_description'];
        }

        $host_status[$host_name]["status_class"] =
            $tab_color_host[strtolower($host_status[$host_name]["current_state"])];
        $host_status[$host_name]["last_check"] =
            $centreon->CentreonGMT->getDate(
                _("Y/m/d - H:i:s"),
                $host_status[$host_name]["last_check"]
            );
        $host_status[$host_name]["next_check"] = $host_status[$host_name]["next_check"]
            ? $centreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $host_status[$host_name]["next_check"])
            : "";
        !$host_status[$host_name]["last_notification"]
            ? $host_status[$host_name]["last_notification"] = ""
            : $host_status[$host_name]["last_notification"] =
            $centreon->CentreonGMT->getDate(
                _("Y/m/d - H:i:s"),
                $host_status[$host_name]["last_notification"]
            );
        !$host_status[$host_name]["next_notification"]
            ? $host_status[$host_name]["next_notification"] = ""
            : $host_status[$host_name]["next_notification"] =
            $centreon->CentreonGMT->getDate(
                _("Y/m/d - H:i:s"),
                $host_status[$host_name]["next_notification"]
            );
        !$host_status[$host_name]["last_state_change"]
            ? $host_status[$host_name]["duration"] = ""
            : $host_status[$host_name]["duration"] =
            CentreonDuration::toString(time() - $host_status[$host_name]["last_state_change"]);
        !$host_status[$host_name]["last_state_change"]
            ? $host_status[$host_name]["last_state_change"] = ""
            : $host_status[$host_name]["last_state_change"] =
            $centreon->CentreonGMT->getDate(
                _("Y/m/d - H:i:s"),
                $host_status[$host_name]["last_state_change"]
            );
        $host_status[$host_name]["last_update"] = $centreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), time());

        if ($host_status[$host_name]["problem_has_been_acknowledged"]) {
            $host_status[$host_name]["current_state"] .= "&nbsp;&nbsp;<b>(" . _("ACKNOWLEDGED") . ")</b>";
        }

        $host_status[$host_name]["state_type"] = $tab_status_type[$host_status[$host_name]["state_type"]];

        $host_status[$host_name]["is_flapping"] = $en[$host_status[$host_name]["is_flapping"]];

        if (isset($host_status[$host_name]["scheduled_downtime_depth"]) &&
            $host_status[$host_name]["scheduled_downtime_depth"]
        ) {
            $host_status[$host_name]["scheduled_downtime_depth"] = 1;
        }

        $host_status[$host_name]["comments"] = $hostDB["host_comment"];

        if (isset($tab_host_service[$host_name]) && count($tab_host_service[$host_name])) {
            foreach ($tab_host_service[$host_name] as $key_name => $s) {
                if (!isset($tab_status[$service_status[$host_name . "_" . $key_name]["current_state"]])) {
                    $tab_status[$service_status[$host_name . "_" . $key_name]["current_state"]] = 0;
                }
                $tab_status[$service_status[$host_name . "_" . $key_name]["current_state"]]++;
            }
        }

        $status = null;
        if (isset($tab_status)) {
            foreach ($tab_status as $key => $value) {
                $status .= "&value[" . $key . "]=" . $value;
            }
        }

        $tpl->assign("m_mon_host", _("Host"));
        $tpl->assign("m_mon_host_info", _("Status Details"));
        $tpl->assign("m_mon_host_poller", _("Poller"));
        $tpl->assign("m_mon_host_poller_name", _("Name"));
        $tpl->assign("m_mon_host_services", _("Services"));
        $tpl->assign("header_service_description", _("Services"));
        $tpl->assign("header_service_status", _("Status"));
        $tpl->assign("header_service_duration", _("Duration"));
        $tpl->assign("header_service_output", _("Ouput"));
        $tpl->assign("m_mon_host_status", _("Host Status"));
        $tpl->assign("m_mon_host_status_info", _("Status information"));
        $tpl->assign("m_mon_performance_data", _("Performance Data"));
        $tpl->assign("m_mon_current_attempt", _("Current Attempt"));
        $tpl->assign("m_mon_state_type", _("State Type"));
        $tpl->assign("m_mon_host_last_check", _("Last Check"));
        $tpl->assign("m_mon_state_type", _("State Type"));
        $tpl->assign("m_mon_next_check", _("Next Check"));
        $tpl->assign("m_mon_check_latency", _("Latency"));
        $tpl->assign("m_mon_check_execution_time", _("Execution Time"));
        $tpl->assign("m_mon_last_change", _("Last State Change"));
        $tpl->assign("m_mon_current_state_duration", _("Current State Duration"));
        $tpl->assign("m_mon_last_notification", _("Last Notification"));
        $tpl->assign("m_mon_next_notification", _("Next Notification"));
        $tpl->assign("m_mon_notification_nb", _("Current Notification Number"));
        $tpl->assign("m_mon_host_flapping", _("Is This Host Flapping?"));
        $tpl->assign("m_mon_percent_state_change", _("Percent State Change"));
        $tpl->assign("m_mon_downtime_sc", _("In Scheduled Downtime?"));
        $tpl->assign("m_mon_last_update", _("Last Update"));
        $tpl->assign("cmt_host_name", _("Host Name"));
        $tpl->assign("cmt_entry_time", _("Entry Time"));
        $tpl->assign("cmt_author", _("Author"));
        $tpl->assign("cmt_comment", _("Comments"));
        $tpl->assign("cmt_persistent", _("Persistent"));
        $tpl->assign("cmt_actions", _("Actions"));
        $tpl->assign("options", _("Options"));
        $tpl->assign("m_mon_tools_ping", _("Ping"));
        $tpl->assign("m_mon_tools_tracert", _("Tracert"));
        $tpl->assign("hosts_command", _("Host Commands"));
        $tpl->assign("m_mon_SCH_downtime", _("Schedule downtime for this host"));
        $tpl->assign("m_mon_add_comment", _("Add Comment for this host"));
        $tpl->assign("m_mon_disable_not_all_services", _("Disable all service notifications on this host"));
        $tpl->assign("m_mon_enable_not_all_services", _("Enable all service notifications on this host"));
        $tpl->assign("m_mon_SCH_immediate_check", _("Schedule an immediate check of all services on this host"));
        $tpl->assign(
            "m_mon_SCH_immediate_check_f",
            _("Schedule an immediate check of all services on this host (forced)")
        );
        $tpl->assign("m_mon_diable_check_all_svc", _("Disable all service checks on this host"));
        $tpl->assign("m_mon_enable_check_all_svc", _("Enable all service checks on this host"));
        $tpl->assign("m_mon_acknowledge", _("Acknowledge problem"));
        $tpl->assign("seconds", _("seconds"));
        $tpl->assign("links", _("Links"));
        $tpl->assign("notifications", _("Notifications"));
        $tpl->assign("notified", _("Notified"));
        $tpl->assign("m_mon_host_comment", _("Comments"));

        $tpl->assign("m_mon_obsess_over_host", _("Obsess Over Host"));
        $tpl->assign("m_mon_check_this_host", _("Active Checks"));
        $tpl->assign("m_mon_host_checks_active", _("Active Checks"));
        $tpl->assign("m_mon_host_checks_passive", _("Passive Checks"));
        $tpl->assign("m_mon_passive_check_this_host", _("Passive Checks"));
        $tpl->assign("m_mon_host_notification", _("Notifications"));
        $tpl->assign("m_mon_notify_this_host", _("Notifications"));
        $tpl->assign("m_mon_event_handler", _("Event Handler"));
        $tpl->assign("m_mon_ed_event_handler", _("Event Handler"));
        $tpl->assign("m_mon_ed_flapping_detect", _("Flap Detection"));
        $tpl->assign("m_mon_flap_detection", _("Flap Detection"));
        $tpl->assign("m_mon_services_en_acknowledge", _("Acknowledged"));
        $tpl->assign("m_mon_submit_passive", _("Submit result for this host"));

        /*
         * Strings are used by javascript command handler
         */
        $str_check_host_enable = _("Enable Active Checks");
        $str_check_host_disable = _("Disable Active Checks");
        $str_passive_check_host_enable = _("Enable Passive Checks");
        $str_passive_check_host_disable = _("Disable Passive Checks");
        $str_notif_host_enable = _("Enable Host Notifications");
        $str_notif_host_disable = _("Disable Host Notifications");
        $str_handler_host_enable = _("Enable Event Handler");
        $str_handler_host_disable = _("Disable Event Handler");
        $str_flap_host_enable = _("Enable Flap Detection");
        $str_flap_host_disable = _("Disable Flap Detection");
        $str_obsess_host_enable = _("Enable Obsess Over Host");
        $str_obsess_host_disable = _("Disable Obsess Over Host");

        /*
         * Add Tips
         */
        $tpl->assign("lnk_all_services", sprintf(_("View status of all services on host %s"), $host_name));
        $tpl->assign("lnk_host_graphs", sprintf(_("View graphs for host %s"), $host_name));
        $tpl->assign("lnk_host_config", sprintf(_("Configure host %s"), $host_name));
        $tpl->assign("lnk_host_reports", sprintf(_("View report for host %s"), $host_name));
        $tpl->assign("lnk_host_logs", sprintf(_("View logs for host %s"), $host_name));

        /*
         * if user is admin, allActions is true,
         * else we introduce all actions allowed for user
         */
        if (isset($authorized_actions)) {
            $tpl->assign("aclAct", $authorized_actions);
        }

        $tpl->assign("p", $p);
        $tpl->assign("en", $en);
        $tpl->assign("en_inv", $en_inv);
        $tpl->assign("en_inv_text", $en_inv_text);
        $tpl->assign("img_en", $img_en);
        $tpl->assign("color_onoff", $color_onoff);
        $tpl->assign("color_onoff_inv", $color_onoff_inv);
        $tpl->assign("en_disable", $en_disable);
        $tpl->assign("status", $status);
        $tpl->assign("en_acknowledge_text", $en_acknowledge_text);
        $tpl->assign("en_acknowledge", $en_acknowledge);
        $tpl->assign("admin", $is_admin);
        $tpl->assign("lcaTopo", $centreon->user->access->topology);
        $tpl->assign("h", CentreonUtils::escapeSecure($hostDB));
        $tpl->assign("url_id", $url_id);
        $tpl->assign("host_id", $host_id);
        $tpl->assign("graphs", $graphLists);
        $tpl->assign("m_mon_ticket", "Open Ticket");
        $tpl->assign('start', time() - 3600 * 12);
        $tpl->assign('end', time());

        /*
         * Hostgroups Display
         */
        $tpl->assign("hostgroups_label", _("Member of Host Groups"));
        if (isset($hostGroups)) {
            $tpl->assign("hostgroups", CentreonUtils::escapeSecure($hostGroups));
        }

        $tpl->assign("hostcategorie_label", _("Host Categories"));
        if (isset($hostCategorie)) {
            $tpl->assign("hostcategorie", $hostCategorie);
        }

        $tpl->assign("hosts_services", $services);

        /*
         * Contactgroups Display
         */
        $tpl->assign("contactgroups_label", _("Contact groups notified for this host"));
        if (isset($contactGroups)) {
            $tpl->assign("contactgroups", CentreonUtils::escapeSecure($contactGroups));
        }

        /*
         * Contacts Display
         */
        $tpl->assign("contacts_label", _("Contacts notified for this host"));
        if (isset($contacts)) {
            $tpl->assign("contacts", CentreonUtils::escapeSecure($contacts));
        }


        if (isset($tabCommentHosts)) {
            $tpl->assign("tab_comments_host", array_map(array("CentreonUtils", "escapeSecure"), $tabCommentHosts));
        }
        $tpl->assign("host_data", $host_status[$host_name]);

        /*
         * Ext informations
         */
        $notesurl = getMyHostExtendedInfoField($host_id, "ehi_notes_url");
        $notesurl = $hostObj->replaceMacroInString($host_id, $notesurl);
        if (isset($host_status[$host_name]['instance_name'])) {
            $notesurl = str_replace("\$INSTANCENAME\$", $host_status[$host_name]['instance_name'], $notesurl);
        }
        $notesurl = str_replace("\$HOSTSTATE\$", $host_status[$host_name]["current_state"], $notesurl);
        $notesurl = str_replace(
            "\$HOSTSTATEID\$",
            $tab_host_statusid[$host_status[$host_name]["current_state"]],
            $notesurl
        );

        $tpl->assign("h_ext_notes_url", CentreonUtils::escapeSecure($notesurl));
        $tpl->assign("h_ext_notes", CentreonUtils::escapeSecure(getMyHostExtendedInfoField($host_id, "ehi_notes")));
        $tpl->assign("h_ext_notes_url_lang", _("URL Notes"));
        $tpl->assign("h_ext_action_url_lang", _("Action URL"));

        $actionurl = getMyHostExtendedInfoField($host_id, "ehi_action_url");
        $actionurl = $hostObj->replaceMacroInString($host_id, $actionurl);
        if (isset($host_status[$host_name]['instance_name'])) {
            $actionurl = str_replace("\$INSTANCENAME\$", $host_status[$host_name]['instance_name'], $actionurl);
        }
        $actionurl = str_replace("\$HOSTSTATE\$", $host_status[$host_name]["current_state"], $actionurl);
        $actionurl = str_replace(
            "\$HOSTSTATEID\$",
            $tab_host_statusid[$host_status[$host_name]["current_state"]],
            $actionurl
        );
        $tpl->assign("h_ext_action_url", CentreonUtils::escapeSecure($actionurl));
        $tpl->assign("h_ext_icon_image", getMyHostExtendedInfoField($hostDB["host_id"], "ehi_icon_image"));
        $tpl->assign("h_ext_icon_image_alt", getMyHostExtendedInfoField($hostDB["host_id"], "ehi_icon_image_alt"));

        /*
         * Dynamics tools
         */
        $tools = array();
        $DBRESULT = $pearDB->query("SELECT * FROM modules_informations");
        while ($module = $DBRESULT->fetchrow()) {
            if (isset($module['host_tools']) && $module['host_tools'] == 1
                && file_exists('modules/' . $module['name'] . '/host_tools.php')
            ) {
                include('modules/' . $module['name'] . '/host_tools.php');
            }
        }
        $DBRESULT->free();

        foreach ($tools as $key => $tab) {
            $tools[$key]['url'] = str_replace("@host_id@", $host_id, $tools[$key]['url']);
            $tools[$key]['url'] = str_replace("@host_name@", $host_name, $tools[$key]['url']);
            $tools[$key]['url'] = str_replace(
                "@current_state@",
                $host_status[$host_name]["current_state"],
                $tools[$key]['url']
            );
            $tools[$key]['url'] = str_replace(
                "@plugin_output@",
                $host_status[$host_name]["plugin_output"],
                $tools[$key]['url']
            );
        }

        if (count($tools) > 0) {
            $tpl->assign("tools", $tools);
        }

        $tpl->display("hostDetails.ihtml");
    } else {
        echo "<div class='msg' align='center'>" .
            _("This host no longer exists in Centreon configuration. Please reload the configuration.") . "</div>";
    }
}
?>
    <script>
        <?php
        $tFM = 0;
        $time = time();
        require_once _CENTREON_PATH_ . 'www/include/monitoring/status/Common/commonJS.php';
        ?>
    </script>
<?php if (!is_null($host_id)) { ?>
    <script type="text/javascript">

        var glb_confirm = '<?php  echo _("Submit command?"); ?>';
        var command_sent = '<?php echo _("Command sent"); ?>';
        var command_failure = "<?php echo _("Failed to execute command");?>";
        var host_id = '<?php echo $hostObj->getHostId($host_name);?>';
        var labels = new Array();

        labels['host_checks'] = new Array(
            "<?php echo $str_check_host_enable;?>",
            "<?php echo $str_check_host_disable;?>",
            "<?php echo $img_en[0];?>",
            "<?php echo $img_en[1];?>"
        );

        labels['host_notifications'] = new Array(
            "<?php echo $str_notif_host_enable;?>",
            "<?php echo $str_notif_host_disable;?>",
            "<?php echo $img_en[0];?>",
            "<?php echo $img_en[1];?>"
        );

        labels['host_event_handler'] = new Array(
            "<?php echo $str_handler_host_enable;?>",
            "<?php echo $str_handler_host_disable;?>",
            "<?php echo $img_en[0];?>",
            "<?php echo $img_en[1];?>"
        );

        labels['host_flap_detection'] = new Array(
            "<?php echo $str_flap_host_enable;?>",
            "<?php echo $str_flap_host_disable;?>",
            "<?php echo $img_en[0];?>",
            "<?php echo $img_en[1];?>"
        );

        labels['host_obsess'] = new Array(
            "<?php echo $str_obsess_host_enable;?>",
            "<?php echo $str_obsess_host_disable;?>",
            "<?php echo $img_en[0];?>",
            "<?php echo $img_en[1];?>"
        );

        function send_command(cmd, actiontype) {
            if (!confirm(glb_confirm)) {
                return 0;
            }
            if (window.XMLHttpRequest) {
                xhr_cmd = new XMLHttpRequest();
            }
            else if (window.ActiveXObject) {
                xhr_cmd = new ActiveXObject("Microsoft.XMLHTTP");
            }
            xhr_cmd.onreadystatechange = function () {
                display_result(xhr_cmd, cmd);
            };
            xhr_cmd.open(
                "GET",
                "./include/monitoring/objectDetails/xml/hostSendCommand.php?cmd="
                + cmd + "&host_id=" + host_id + "&actiontype=" + actiontype,
                true
            );
            xhr_cmd.send(null);
        }

        function display_result(xhr_cmd, cmd) {
            if (xhr_cmd.readyState != 4 && xhr_cmd.readyState != "complete")
                return (0);
            var msg_result;
            var docXML = xhr_cmd.responseXML;
            var items_state = docXML.getElementsByTagName("result");
            var acttype = docXML.getElementsByTagName("actiontype");
            var actiontype = acttype.item(0).firstChild.data;
            var received_command = docXML.getElementsByTagName("cmd");
            var executed_command = received_command.item(0).firstChild.data;
            var commands = new Array(
                "host_checks",
                "host_notifications",
                "host_event_handler",
                "host_flap_detection",
                "host_obsess"
            );

            var state = items_state.item(0).firstChild.data;
            if (state == "0") {
                msg_result = command_sent;
                for (var i = 0; i < commands.length; i++)
                    mycmd = commands[i];
                if (cmd == mycmd) {
                    var tmp = atoi(actiontype) + 2;
                    img_src = labels[executed_command][tmp];
                    document.getElementById(cmd).innerHTML = "<a href='#' onClick='send_command(\""
                        + cmd + "\", \"" + actiontype + "\")'>"
                        + "<img src=" + img_src
                        + " alt=\"'" + labels[executed_command][actiontype] + "\"'"
                        + " onmouseover=\"Tip('" + labels[executed_command][actiontype] + "')\""
                        + " onmouseout='UnTip()' />"
                        + "</a>";
                }
            }
            else {
                msg_result = command_failure;
            }
            <?php
            require_once "./class/centreonMsg.class.php";
            ?>
            _clear("centreonMsg");
            _setTextStyle("centreonMsg", "bold");
            _setText("centreonMsg", msg_result);
            _nextLine("centreonMsg");
            _setTimeout("centreonMsg", 3);
        }
    </script>
    <?php
}

