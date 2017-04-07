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
include_once("./class/centreonDB.class.php");
include_once("./class/centreonHost.class.php");
include_once("./class/centreonService.class.php");
include_once("./class/centreonMeta.class.php");
include_once( $centreon_path . "www/include/monitoring/objectDetails/common-func.php");

/*
 * Create Object env
 */
$hostObj    = new CentreonHost($pearDB);
$svcObj     = new CentreonService($pearDB);
$metaObj = new CentreonMeta($pearDB);

/*
 * ACL Actions
 */
$GroupListofUser = array();
$GroupListofUser =  $centreon->user->access->getAccessGroups();

$allActions = false;
/*
 * Get list of actions allowed for user
	 */
if (count($GroupListofUser) > 0 && $is_admin == 0) {
    $authorized_actions = array();
    $authorized_actions = $centreon->user->access->getActions();
}

if (isset($_GET["host_name"]) && $_GET["host_name"] != "" && isset($_GET["service_description"]) && $_GET["service_description"] != "") {
    $host_name = $_GET["host_name"];
    $svc_description = $_GET["service_description"];
} else {
    foreach ($_GET["select"] as $key => $value) {
        $tab_data = preg_split("/\;/", $key);
    }
    $host_name = $tab_data[0];
    $svc_description = $tab_data[1];
}

/*
 * Check if host is found
 */
$host_id = getMyHostID($host_name);

if (!is_null($host_id)) {
    $can_display = 1;
    $service_id = getMyServiceID($svc_description, $host_id);
    if (!isset($service_id)) {
        $service_id = getMyServiceIDStorage($svc_description, $host_id);
    }

    /* Define if the service is a metaservice */
    $isMetaservice = 'false';
    $meta_id = $service_id;
    if ($host_name == '_Module_Meta') {
        $isMetaservice = 'true';
        if (preg_match('/meta_(\d+)/', $svc_description, $matches)) {
            $meta_id = $matches[1];
        }
    }

    if (!$is_admin) {
        $lcaHost["LcaHost"] = $centreon->user->access->getHostServicesName($pearDBO, $host_name);
        if (!isset($lcaHost["LcaHost"][$service_id])) {
            $can_display = 0;
        }
    }
    if ($can_display == 0) {
        include_once("../errors/alt_error.php");
    } else {
        // Get Hostgroup List
        $DBRESULT = $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM hostgroup_relation WHERE host_host_id = '" . $host_id . "' " .
            $centreon->user->access->queryBuilder("AND", "host_host_id", $centreon->user->access->getHostsString("ID", $pearDBO)));
        for ($i = 0; $hg = $DBRESULT->fetchRow(); $i++) {
            $hostGroups[] = getMyHostGroupName($hg["hostgroup_hg_id"]);
        }
        $DBRESULT->free();

        if (isset($service_id) && $service_id) {
            $proc_warning = getMyServiceMacro($service_id, "PROC_WARNING");
            $proc_critical = getMyServiceMacro($service_id, "PROC_CRITICAL");
        }

        // Get notifications contacts
        $retrievedNotificationsInfos = get_notified_infos_for_service($service_id, $host_id);
        $contacts = $retrievedNotificationsInfos['contacts'];
        $contactGroups = $retrievedNotificationsInfos['contactGroups'];

        /*
         * Get servicegroups list
         */
        if (isset($service_id) && isset($host_id)) {
            $query = "SELECT DISTINCT sg.sg_name
                    FROM servicegroup sg, servicegroup_relation sgr
                    WHERE sgr.servicegroup_sg_id = sg.sg_id AND sgr.host_host_id = " . $host_id . " AND sgr.service_service_id = " . $service_id . " " .
                $centreon->user->access->queryBuilder("AND", "sgr.host_host_id", $centreon->user->access->getHostsString("ID", $pearDBO));
            $DBRESULT = $pearDB->query($query);
            while ($row = $DBRESULT->fetchRow()) {
                $serviceGroups[] = $row['sg_name'];
            }
            $DBRESULT->free();
        }

        /*
         * Get service category
         */
        $tab_sc = getMyServiceCategories($service_id);
        if (is_array($tab_sc)) {
            foreach ($tab_sc as $sc_id) {
                $serviceCategories[] = getMyCategorieName($sc_id);
            }
        }

        $tab_status = array();

        /*
         * Get all service information 
         */
        $rq = "SELECT s.service_id, " .
            " s.state AS current_state," .
            " s.output as plugin_output, " .
            " s.output as plugin_output2," .
            " s.check_attempt as current_attempt," .
            " s.last_update as status_update_time," .
            " s.last_state_change," .
            " s.last_check," .
            " s.last_time_ok," .
            " s.last_time_warning," .
            " s.last_time_critical," .
            " s.last_time_unknown," .
            " s.notify AS notifications_enabled," .
            " s.next_check," .
            " s.acknowledged AS problem_has_been_acknowledged," .
            " s.passive_checks AS passive_checks_enabled," .
            " s.active_checks AS active_checks_enabled," .
            " s.event_handler_enabled," .
            " s.perfdata as performance_data," .
            " s.flapping AS is_flapping," .
            " s.scheduled_downtime_depth," .
            " s.percent_state_change," .
            " s.notification_number AS current_notification_number," .
            " s.obsess_over_service," .
            " s.check_type," .
            " s.check_command," .
            " s.state_type," .
            " s.latency as check_latency," .
            " s.execution_time as check_execution_time," .
            " s.flap_detection AS flap_detection_enabled," .
            " s.last_notification as last_notification," .
            " h.name AS host_name," .
            " s.description as service_description, " .
            " s.display_name, " .
            " s.notes_url, " .
            " s.notes, " .
            " s.action_url, " .
            " i.name as instance_name " .
            " FROM services s, hosts h, instances i " .
            " WHERE h.host_id = s.host_id " . 
            " AND h.host_id LIKE '" . $pearDB->escape($host_id) . "'" .
            " AND s.service_id LIKE '" . $pearDB->escape($service_id) . "'".
            " AND h.instance_id = i.instance_id " .
            " AND h.enabled = 1 " .
            " AND s.enabled = 1 ";
        $DBRESULT = $pearDBO->query($rq);

        $tab_status_service = array(0 => "OK", 1 => "WARNING", 2 => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
        $tab_class_service = array("ok" => 'service_ok', "warning" => 'service_warning', "critical" => 'service_critical', "unknown" => 'service_unknown', 'pending' => 'pending');
        while ($data = $DBRESULT->fetchRow()) {
            if (isset($data['performance_data'])) {
                $data['performance_data'] = $data['performance_data'];
            }
            if ($data["service_description"] == $svc_description) {
                $service_status = $data;
            }
            if (!isset($tab_status[$data["current_state"]])) {
                $tab_status[$tab_status_service[$data["current_state"]]] = 0;
            }
            $tab_status[$tab_status_service[$data["current_state"]]]++;
        }
        $DBRESULT->free();

        if ($is_admin || isset($authorized_actions['service_display_command'])) {
            $service_status["command_line"] = hidePasswordInCommand($service_status["check_command"], $host_id, $service_status["service_id"]);
        }

        $service_status["current_stateid"] = $service_status["current_state"];
        $service_status["current_state"] = $tab_status_service[$service_status["current_state"]];

        /*
         * start ndo host detail
         */
        $tab_host_status[0] = "UP";
        $tab_host_status[1] = "DOWN";
        $tab_host_status[2] = "UNREACHABLE";

        $rq2 = "SELECT state AS current_state FROM hosts WHERE name LIKE '" . $pearDBO->escape($host_name) . "'";
        $DBRESULT = $pearDBO->query($rq2);

        $ndo2 = $DBRESULT->fetchRow();
        $host_status[$host_name] = $tab_host_status[$ndo2["current_state"]];

        // Get Host informations
        $DBRESULT = $pearDB->query("SELECT * FROM host WHERE host_id = " . $pearDB->escape($host_id) . "");
        $host = $DBRESULT->fetchrow();
        $DBRESULT->free();

        if ($isMetaservice == 'true') {
            $metaParameters = $metaObj->getParameters($meta_id, array('max_check_attempts'));
            $total_current_attempts = $metaParameters['max_check_attempts'];
        } else {
            $total_current_attempts = getMyServiceField($service_id, "service_max_check_attempts");
        }

        $path = "./include/monitoring/objectDetails/";

        /*
         * Smarty template Init
         */
        $tpl = new Smarty();
        $tpl = initSmartyTpl($path, $tpl, "./template/");

        $en = array("0" => _("No"), "1" => _("Yes"));

        /*
         * Get comments for service
         */
        $tabCommentServices = array();
        if (isset($host_id) && isset($service_id)) {
            $rq2 = " SELECT DISTINCT FROM_UNIXTIME(cmt.entry_time) as entry_time, cmt.comment_id, cmt.author AS author_name, cmt.data AS comment_data, cmt.persistent AS is_persistent, h.name AS host_name, s.description AS service_description " .
                " FROM comments cmt, hosts h, services s " .
                " WHERE h.host_id = " . $pearDBO->escape($host_id) . " 
                                      AND h.host_id = s.host_id
                                      AND s.service_id = " . $pearDBO->escape($service_id) . " 
                                      AND h.host_id = cmt.host_id 
                                      AND s.service_id = cmt.service_id 
                                      AND cmt.expires = 0 
                                      AND (cmt.deletion_time IS NULL OR cmt.deletion_time = 0)
                                      ORDER BY cmt.entry_time DESC";
            $DBRESULT = $pearDBO->query($rq2);
            for ($i = 0; $data = $DBRESULT->fetchRow(); $i++) {
                $tabCommentServices[$i] = $data;
                $tabCommentServices[$i]['host_name'] = $data['host_name'];
                $tabCommentServices[$i]['service_description'] = $data['service_description'];
                $tabCommentServices[$i]['comment_data'] = $data['comment_data'];
                $tabCommentServices[$i]["is_persistent"] = $en[$tabCommentServices[$i]["is_persistent"]];
            }
            $DBRESULT->free();
            unset($data);
        }

        $en_acknowledge_text = array("1" => _("Delete Problem Acknowledgement"), "0" => _("Acknowledge Service Problem"));
        $en_acknowledge = array("1" => "0", "0" => "1");
        $en_disable = array("1" => _("Enabled"), "0" => _("Disabled"));
        $en_inv = array("1" => "1", "0" => "0");
        $en_inv_text = array("1" => _("Disable"), "0" => _("Enable"));
        $color_onoff = array("1" => "#88b917", "0" => "#e00b3d");
        $color_onoff_inv = array("0" => "#F7FAFF", "1" => "#E7C9FF");
        $img_en = array("0" => "'./img/icons/enabled.png'", "1" => "'./img/icons/disabled.png'");

        /*
         * Ajust data for beeing displayed in template
         */
        $centreon->CentreonGMT->getMyGMTFromSession(session_id(), $pearDB);
        $service_status['command_line'] = str_replace(' -', "\n\t-", $service_status['command_line']);
        $service_status['performance_data'] = str_replace(' \'', "\n'", $service_status['performance_data']);
        $service_status["status_color"] = $centreon->optGen["color_" . strtolower($service_status["current_state"])];

        $service_status["status_class"] = $tab_class_service[strtolower($service_status["current_state"])];


        $service_status["last_check"] = $centreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $service_status["last_check"]);
        $service_status["next_check"] = $centreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $service_status["next_check"]);
        !$service_status["check_latency"] ? $service_status["check_latency"] = "< 1 second" : $service_status["check_latency"] = $service_status["check_latency"] . " seconds";
        !$service_status["check_execution_time"] ? $service_status["check_execution_time"] = "< 1 second" : $service_status["check_execution_time"] = $service_status["check_execution_time"] . " seconds";

        !$service_status["last_notification"] ? $service_status["notification"] = "" : $service_status["last_notification"] = $centreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $service_status["last_notification"]);

        if (isset($service_status["next_notification"]) && !$service_status["next_notification"]) {
            $service_status["next_notification"] = "";
        } elseif (!isset($service_status["next_notification"])) {
            $service_status["next_notification"] = "N/A";
        } else {
            $service_status["next_notification"] = $centreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $service_status["next_notification"]);
        }

        $service_status["long_plugin_output"] = "";
        $service_status["plugin_output2"] = str_replace("\n", '\n', $service_status["plugin_output2"]);
        $outputTmp = explode('\n', $service_status["plugin_output2"]);
        if (count($outputTmp)) {
            $i = 0;
            while (isset($outputTmp[$i])) {
                if (!$i) {
                    $service_status["plugin_output"] = htmlentities($outputTmp[$i], ENT_QUOTES, 'UTF-8') . "<br />";
                } else {
                    $service_status["long_plugin_output"] .= htmlentities($outputTmp[$i], ENT_QUOTES, 'UTF-8') . "<br />";
                }
                $i++;
            }
        }
        $service_status["plugin_output"] = utf8_encode($service_status["plugin_output"]);
        $service_status["plugin_output"] = str_replace("'", "", $service_status["plugin_output"]);
        $service_status["plugin_output"] = str_replace("\"", "", $service_status["plugin_output"]);
        $service_status["plugin_output"] = str_replace("\\n", "<br>", $service_status["plugin_output"]);
        $service_status["plugin_output"] = str_replace('\n', "<br>", $service_status["plugin_output"]);

        /*
         * Added for long_plugin_output <gavinw>
         */
        if (isset($service_status["long_plugin_output"])) {
            $service_status["long_plugin_output"] = str_replace("<b>", "", $service_status["long_plugin_output"]);
            $service_status["long_plugin_output"] = str_replace("</b>", "", $service_status["long_plugin_output"]);
            $service_status["long_plugin_output"] = str_replace("<br>", "", $service_status["long_plugin_output"]);
            $service_status["long_plugin_output"] = str_replace("'", "", $service_status["long_plugin_output"]);
            $service_status["long_plugin_output"] = str_replace("\"", "", $service_status["long_plugin_output"]);
            $service_status["long_plugin_output"] = str_replace('\n', '<br />', $service_status["long_plugin_output"]);
        }
        if (isset($service_status["notes_url"]) && $service_status["notes_url"]) {
            $service_status["notes_url"] = str_replace("\$HOSTNAME\$", $host_name, $service_status["notes_url"]);
            $service_status["notes_url"] = str_replace("\$SERVICEDESC\$", $svc_description, $service_status["notes_url"]);
            $service_status["notes_url"] = str_replace("\$SERVICESTATE\$", $service_status["current_state"], $service_status["notes_url"]);
            $service_status["notes_url"] = str_replace("\$SERVICESTATEID\$", $service_status["current_stateid"], $service_status["notes_url"]);
            if ($host_id) {
                $service_status["notes_url"] = str_replace("\$HOSTALIAS\$", $hostObj->getHostAlias($host_id), $service_status["notes_url"]);
                $service_status["notes_url"] = str_replace("\$HOSTADDRESS\$", $hostObj->getHostAddress($host_id), $service_status["notes_url"]);
            }
        }
        if (isset($service_status["action_url"]) && $service_status["action_url"]) {
            $service_status["action_url"] = str_replace("\$HOSTNAME\$", $host_name, $service_status["action_url"]);
            $service_status["action_url"] = str_replace("\$SERVICEDESC\$", $svc_description, $service_status["action_url"]);
            $service_status["action_url"] = str_replace("\$SERVICESTATE\$", $service_status["current_state"], $service_status["action_url"]);
            $service_status["action_url"] = str_replace("\$SERVICESTATEID\$", $service_status["current_stateid"], $service_status["action_url"]);
            if ($host_id) {
                $service_status["action_url"] = str_replace("\$HOSTALIAS\$", $hostObj->getHostAlias($host_id), $service_status["action_url"]);
                $service_status["action_url"] = str_replace("\$HOSTADDRESS\$", $hostObj->getHostAddress($host_id), $service_status["action_url"]);
            }
        }

        $service_status["plugin_output"] = $service_status["plugin_output"];
        $service_status["plugin_output"] = str_replace("'", "", $service_status["plugin_output"]);
        $service_status["plugin_output"] = str_replace("\"", "", $service_status["plugin_output"]);
        $service_status["plugin_output"] = str_replace("\\n", "<br>", $service_status["plugin_output"]);
        $service_status["plugin_output"] = str_replace('\n', "<br>", $service_status["plugin_output"]);

        /*
         * Added for long_plugin_output <gavinw>
         */
        if (isset($service_status["long_plugin_output"])) {
            $service_status["long_plugin_output"] = str_replace("<b>", "", $service_status["long_plugin_output"]);
            $service_status["long_plugin_output"] = str_replace("</b>", "", $service_status["long_plugin_output"]);
            $service_status["long_plugin_output"] = str_replace("<br>", "", $service_status["long_plugin_output"]);
            $service_status["long_plugin_output"] = str_replace("'", "", $service_status["long_plugin_output"]);
            $service_status["long_plugin_output"] = str_replace("\"", "", $service_status["long_plugin_output"]);
            $service_status["long_plugin_output"] = str_replace('\n', '<br />', $service_status["long_plugin_output"]);
        }
        if (isset($service_status["notes_url"]) && $service_status["notes_url"]) {
            $service_status["notes_url"] = str_replace("\$HOSTNAME\$", $host_name, $service_status["notes_url"]);
            $service_status["notes_url"] = str_replace("\$SERVICEDESC\$", $svc_description, $service_status["notes_url"]);
            if ($host_id) {
                $service_status["notes_url"] = str_replace("\$HOSTALIAS\$", $hostObj->getHostAlias($host_id), $service_status["notes_url"]);
                $service_status["notes_url"] = str_replace("\$HOSTADDRESS\$", $hostObj->getHostAddress($host_id), $service_status["notes_url"]);
            }
        }
        if (isset($service_status["action_url"]) && $service_status["action_url"]) {
            $service_status["action_url"] = str_replace("\$HOSTNAME\$", $host_name, $service_status["action_url"]);
            $service_status["action_url"] = str_replace("\$SERVICEDESC\$", $svc_description, $service_status["action_url"]);
            if ($host_id) {
                $service_status["action_url"] = str_replace("\$HOSTALIAS\$", $hostObj->getHostAlias($host_id), $service_status["action_url"]);
                $service_status["action_url"] = str_replace("\$HOSTADDRESS\$", $hostObj->getHostAddress($host_id), $service_status["action_url"]);
            }
        }

        if (isset($service_status["last_time_" . strtolower($service_status["current_state"])])) {
            !$service_status["last_state_change"] ? $service_status["duration"] = CentreonDuration::toString($service_status["last_time_" . strtolower($service_status["current_state"])]) : $service_status["duration"] = centreonDuration::toString(time() - $service_status["last_state_change"]);
        }
        !$service_status["last_state_change"] ? $service_status["last_state_change"] = "" : $service_status["last_state_change"] = $centreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $service_status["last_state_change"]);
        $service_status["last_update"] = $centreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), time());
        $service_status["is_flapping"] ? $service_status["is_flapping"] = $en[$service_status["is_flapping"]] : $service_status["is_flapping"] = "N/A";

        if ($service_status["problem_has_been_acknowledged"]) {
            $service_status["current_state"] .= "&nbsp;&nbsp;<b>(" . _("ACKNOWLEDGED") . ")</b>";
        }

        if (isset($service_status["scheduled_downtime_depth"]) &&
            $service_status["scheduled_downtime_depth"]
        ) {
            $service_status["scheduled_downtime_depth"] = 1;
        }

        $status = null;
        foreach ($tab_status as $key => $value) {
            $status .= "&value[" . $key . "]=" . $value;
        }

        $optionsURL = "host_name=" . urlencode($host_name) . "&service_description=" . urlencode($svc_description);

        $DBRES = $pearDBO->query("SELECT id FROM `index_data`, metrics WHERE metrics.index_id = index_data.id AND host_name LIKE '" . $pearDBO->escape($host_name) . "' AND service_description LIKE '" . $pearDBO->escape($svc_description) . "' LIMIT 1");
        $index_data = 0;
        if ($DBRES->numRows()) {
            $row = $DBRES->fetchRow();
            $index_data = $row['id'];
        }
        $optionsURL2 = "index=" . $index_data;

        /*
         * Assign translations
         */
        if ($isMetaservice == 'false') {
            $tpl->assign("m_mon_services", _("Service"));
        } else { 
            $tpl->assign("m_mon_services", _("Meta Service"));
        }
        
        $tpl->assign("m_mon_status_info", _("Status Details"));
        $tpl->assign("m_mon_on_host", _("on host"));
        $tpl->assign("m_mon_services_status", _("Service Status"));
        $tpl->assign("m_mon_host_status_info", _("Status information"));
        $tpl->assign("m_mon_host_long_info", _("Extended status information"));
        $tpl->assign("m_mon_performance_data", _("Performance Data"));
        $tpl->assign("m_mon_services_attempt", _("Current Attempt"));
        $tpl->assign("m_mon_services_state", _("State Type"));
        $tpl->assign("m_mon_last_check_type", _("Last Check Type"));
        $tpl->assign("m_mon_host_last_check", _("Last Check"));
        $tpl->assign("m_mon_services_active_check", _("Next Scheduled Active Check"));
        $tpl->assign("m_mon_services_latency", _("Latency"));
        $tpl->assign("m_mon_services_duration", _("Check Duration"));
        $tpl->assign("m_mon_last_change", _("Last State Change"));
        $tpl->assign("m_mon_current_state_duration", _("Current State Duration"));
        $tpl->assign("m_mon_last_notification_serv", _("Last Service Notification"));
        $tpl->assign("m_mon_notification_nb", _("Current Notification Number"));
        $tpl->assign("m_mon_services_flapping", _("Is This Service Flapping?"));
        $tpl->assign("m_mon_percent_state_change", _("Percent State Change"));
        $tpl->assign("m_mon_downtime_sc", _("In Scheduled Downtime?"));
        $tpl->assign("m_mon_last_update", _("Last Update"));
        $tpl->assign("m_mon_tools", _("Tools"));
        $tpl->assign("m_mon_service_command", _("Service Commands"));
        $tpl->assign("m_mon_check_this_service", _("Checks for this service"));
        $tpl->assign("m_mon_schedule", _("Re-schedule the next check for this service"));
        $tpl->assign("m_mon_schedule_force", _("Re-schedule the next check for this service (forced)"));
        $tpl->assign("m_mon_submit_passive", _("Submit result for this service"));
        $tpl->assign("m_mon_schedule_downtime", _("Schedule downtime for this service"));
        $tpl->assign("m_mon_schedule_comment", _("Add a comment for this service"));
        $tpl->assign("m_mon_obsessing", _("Obsess Over Service"));
        $tpl->assign("m_comment_for_service", _("All Comments for this service"));
        $tpl->assign("cmt_host_name", _("Host Name"));
        $tpl->assign("cmt_service_descr", _("Services"));
        $tpl->assign("cmt_entry_time", _("Entry Time"));
        $tpl->assign("cmt_author", _("Author"));
        $tpl->assign("cmt_comment", _("Comments"));
        $tpl->assign("cmt_persistent", _("Persistent"));
        $tpl->assign("secondes", _("seconds"));
        $tpl->assign("m_mon_ticket", "Open Ticket");
        $tpl->assign("links", _("Links"));
        $tpl->assign("notifications", _("Notifications"));
        $tpl->assign("m_mon_service_command_line", _("Executed Check Command Line"));
        $tpl->assign("m_mon_services_en_check_active", _("Active Checks"));
        $tpl->assign("m_mon_services_en_check_passif", _("Passive Checks"));
        $tpl->assign("m_mon_accept_passive", _("Passive Checks"));
        $tpl->assign("m_mon_notification_service", _("Service Notifications"));
        $tpl->assign("m_mon_services_en_notification", _("Service Notifications"));
        $tpl->assign("m_mon_services_en_acknowledge", _("Acknowledged"));
        $tpl->assign("m_mon_event_handler", _("Event Handler"));
        $tpl->assign("m_mon_flap_detection", _("Flap Detection"));
        $tpl->assign("m_mon_services_en_flap", _("Flap Detection"));

        $str_check_svc_enable = _("Enable Active Checks");
        $str_check_svc_disable = _("Disable Active Checks");
        $str_passive_svc_enable = _("Enable Passive Checks");
        $str_passive_svc_disable = _("Disable Passive Checks");
        $str_notif_svc_enable = _("Enable Service Notifications");
        $str_notif_svc_disable = _("Disable Service Notifications");
        $str_handler_svc_enable = _("Enable Event Handler");
        $str_handler_svc_disable = _("Disable Event Handler");
        $str_flap_svc_enable = _("Enable Flap Detection");
        $str_flap_svc_disable = _("Disable Flap Detection");
        $str_obsess_svc_enable = _("Enable Obsess Over Service");
        $str_obsess_svc_disable = _("Disable Obsess Over Service");

        /*
         * if user is admin, allActions is true,
         * else we introduce all actions allowed for user
         */
        if (isset($authorized_actions)) {
            $tpl->assign("aclAct", $authorized_actions);
        }

        $serviceDescriptionDisplay = $svc_description;
        $hostNameDisplay = $host_name;
        if ($isMetaservice == 'true') {
            $tpl->assign("meta_id", $meta_id);
            $hostNameDisplay = '';
            $serviceDescriptionDisplay = $service_status['display_name'];
        }

        $tpl->assign("is_meta", $isMetaservice);

        $tpl->assign("p", $p);
        $tpl->assign("o", $o);
        $tpl->assign("en", $en);
        $tpl->assign("en_inv", $en_inv);
        $tpl->assign("en_inv_text", $en_inv_text);
        $tpl->assign("img_en", $img_en);
        $tpl->assign("color_onoff", $color_onoff);
        $tpl->assign("color_onoff_inv", $color_onoff_inv);
        $tpl->assign("en_disable", $en_disable);
        $tpl->assign("total_current_attempt", $total_current_attempts);
        $tpl->assign("en_acknowledge_text", $en_acknowledge_text);
        $tpl->assign("en_acknowledge", $en_acknowledge);
        $tpl->assign("actpass", array("0"=>_("Active"), "1"=>_("Passive")));
        $tpl->assign("harsof", array("0"=>_("SOFT"), "1"=>_("HARD")));
        $tpl->assign("status", $status);
        $tpl->assign("h", CentreonUtils::escapeSecure($host));
        $tpl->assign("admin", $is_admin);
        $tpl->assign("lcaTopo", $centreon->user->access->topology);
        $tpl->assign("count_comments_svc", count($tabCommentServices));
        $tpl->assign("tab_comments_svc", array_map(array("CentreonUtils","escapeSecure"), $tabCommentServices));
        $tpl->assign("host_id", $host_id);
        $tpl->assign("service_id", $service_id);
        $centreonGraph = new CentreonGraph($centreon->user->user_id, null, 0, null);
        if (isset($host_id) && isset($service_id)) {
            $tpl->assign("flag_graph", $centreonGraph->statusGraphExists($host_id, $service_id));
        }
        $tpl->assign("host_data", $host_status[$host_name]);
        $tpl->assign("service_data", $service_status);
        $tpl->assign("host_display_name", CentreonUtils::escapeSecure($hostNameDisplay));
        $tpl->assign("host_name", CentreonUtils::escapeSecure($host_name));
        $tpl->assign("svc_display_name", CentreonUtils::escapeSecure($serviceDescriptionDisplay));
        $tpl->assign("svc_description", CentreonUtils::escapeSecure($svc_description));
        $tpl->assign("url_svc_id", urlencode(CentreonUtils::escapeSecure($host_name)).';'.urlencode(CentreonUtils::escapeSecure($svc_description)));
        $tpl->assign("status_str", _("Status Graph"));
        $tpl->assign("detailed_graph", _("Detailed Graph"));

        /*
         * Contactgroups Display
         */
        $tpl->assign("contactgroups_label", _("Contact groups notified for this service"));
        if (isset($contactGroups)) {
            $tpl->assign("contactgroups", CentreonUtils::escapeSecure($contactGroups));
        }

        /*
         * Contacts Display
         */
        $tpl->assign("contacts_label", _("Contacts notified for this service"));
        if (isset($contacts)) {
            $tpl->assign("contacts", CentreonUtils::escapeSecure($contacts));
        }
            
        /*
         * Hostgroups Display
         */
        $tpl->assign("hostgroups_label", _("Host Groups"));
        if (isset($hostGroups)) {
            $tpl->assign("hostgroups", CentreonUtils::escapeSecure($hostGroups));
        }

        /*
         * Servicegroup Display
         */
        $tpl->assign("servicegroups_label", _("Service groups"));
        if (isset($serviceGroups)) {
            $tpl->assign("servicegroups", CentreonUtils::escapeSecure($serviceGroups));
        }

        /*
         * Service Categories
         */
        $tpl->assign("sg_label", _("Service Categories"));
        if (isset($serviceCategories)) {
            $tpl->assign("service_categories", CentreonUtils::escapeSecure($serviceCategories));
        }

        /*
         * Macros
         */
        if (isset($proc_warning) && $proc_warning) {
            $tpl->assign("proc_warning", $proc_warning);
        }
        if (isset($proc_critical) && $proc_critical) {
            $tpl->assign("proc_critical", $proc_critical);
        }

        /*
         * Tips translations
         */
        $tpl->assign("host_shortcut", _("Host Shortcuts"));
        $tpl->assign("serv_shortcut", _("Service Shortcuts"));
        $tpl->assign("all_serv", _("All Services"));
        $tpl->assign("lnk_host_config", _("Configure host"));
        $tpl->assign("lnk_serv_config", _("Configure service"));
        $tpl->assign("lnk_host_graphs", sprintf(_("View graphs for host %s"), CentreonUtils::escapeSecure($host_name)));
        $tpl->assign("lnk_host_reports", sprintf(_("View report for host %s"), CentreonUtils::escapeSecure($host_name)));
        $tpl->assign("lnk_serv_reports", sprintf(_("View report for service %s"), CentreonUtils::escapeSecure($svc_description)));
        $tpl->assign("lnk_host_status", _("View host status page"));
        $tpl->assign("lnk_serv_status", sprintf(_("View status of all services on host %s"), CentreonUtils::escapeSecure($host_name)));
        $tpl->assign("lnk_host_logs", sprintf(_("View logs for host %s"), CentreonUtils::escapeSecure($host_name)));
        $tpl->assign("lnk_serv_logs", sprintf(_("View logs for service %s"), CentreonUtils::escapeSecure($serviceDescriptionDisplay)));

        /*
         * Ext informations
         */
        $notesurl = $hostObj->replaceMacroInString($host_id, $service_status["notes_url"]);
        $notesurl = $svcObj->replaceMacroInString($service_id, $notesurl);
        if (isset($service_status["instance_name"])) {
            $notesurl = str_replace("\$INSTANCENAME\$", $service_status["instance_name"], $notesurl);
        }

        $actionurl = $hostObj->replaceMacroInString($host_id, $service_status["action_url"]);
        $actionurl = $svcObj->replaceMacroInString($service_id, $actionurl);
        if (isset($service_status["instance_name"])) {
            $actionurl = str_replace("\$INSTANCENAME\$", $service_status["instance_name"], $actionurl);
        }

        $tpl->assign("sv_ext_notes", CentreonUtils::escapeSecure(getMyServiceExtendedInfoField($service_id, "esi_notes")));
        $tpl->assign("sv_ext_notes_url", CentreonUtils::escapeSecure($notesurl));
        $tpl->assign("sv_ext_action_url_lang", _("Action URL"));
        $tpl->assign("sv_ext_action_url", CentreonUtils::escapeSecure($actionurl));
        $tpl->assign("sv_ext_icon_image_alt", getMyServiceExtendedInfoField($service_id, "esi_icon_image_alt"));
        $tpl->assign("options", $optionsURL);
        $tpl->assign("index_data", $index_data);
        $tpl->assign("options2", CentreonUtils::escapeSecure($optionsURL2));

        /*
         * Dynamics tools
         */
        $tools = array();
        $DBRESULT = $pearDB->query("SELECT * FROM modules_informations");
        while ($module = $DBRESULT->fetchrow()) {
            if (isset($module['svc_tools']) && $module['svc_tools'] == 1 && file_exists('modules/'.$module['name'].'/svc_tools.php')) {
                include('modules/'.$module['name'].'/svc_tools.php');
            }
        }
        $DBRESULT->free();

        foreach ($tools as $key => $tab) {
            $tools[$key]['url'] = str_replace("@host_id@", $host_id, $tools[$key]['url']);
            $tools[$key]['url'] = str_replace("@host_name@", $host_name, $tools[$key]['url']);
            $tools[$key]['url'] = str_replace("@svc_description@", $svc_description, $tools[$key]['url']);
            $tools[$key]['url'] = str_replace("@svc_id@", $service_id, $tools[$key]['url']);
            $tools[$key]['url'] = str_replace("@current_state@", $service_status["current_state"], $tools[$key]['url']);
            $tools[$key]['url'] = str_replace("@plugin_output@", $service_status["plugin_output"], $tools[$key]['url']);
        }

        if (count($tools) > 0) {
            $tpl->assign("tools", CentreonUtils::escapeSecure($tools));
        }

        $tpl->display("serviceDetails.ihtml");
    }
} else {
    echo "<div class='msg' align='center'>"._("The related host no longer exists in Centreon configuration. Please reload the configuration.")."</div>";
}
?>
<?php if (!is_null($host_id)) { ?>
<script type="text/javascript">
    var glb_confirm = "<?php  echo _("Submit command?"); ?>";
    var command_sent = "<?php echo _("Command sent"); ?>";
    var command_failure = "<?php echo _("Failed to execute command");?>";
    var host_id = '<?php echo $host_id;?>';
    var svc_id = '<?php echo $service_id;?>';
    var labels = new Array();

    labels['service_checks'] = new Array(
        "<?php echo $str_check_svc_enable;?>",
        "<?php echo $str_check_svc_disable;?>",
        "<?php echo $img_en[0];?>",
        "<?php echo $img_en[1];?>"
    );

    labels['service_notifications'] = new Array(
        "<?php echo $str_notif_svc_enable;?>",
        "<?php echo $str_notif_svc_disable;?>",
        "<?php echo $img_en[0];?>",
        "<?php echo $img_en[1];?>"
    );

    labels['service_event_handler'] = new Array(
        "<?php echo $str_handler_svc_enable;?>",
        "<?php echo $str_handler_svc_disable;?>",
        "<?php echo $img_en[0];?>",
        "<?php echo $img_en[1];?>"
    );

    labels['service_flap_detection'] = new Array(
        "<?php echo $str_flap_svc_enable;?>",
        "<?php echo $str_flap_svc_disable;?>",
        "<?php echo $img_en[0];?>",
        "<?php echo $img_en[1];?>"
    );

    labels['service_passive_checks'] = new Array(
        "<?php echo $str_passive_svc_enable;?>",
        "<?php echo $str_passive_svc_disable;?>",
        "<?php echo $img_en[0];?>",
        "<?php echo $img_en[1];?>"
    );

    labels['service_obsess'] = new Array(
        "<?php echo $str_obsess_svc_enable;?>",
        "<?php echo $str_obsess_svc_disable;?>",
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
        else if (window.ActiveXObject)
        {
            xhr_cmd = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xhr_cmd.onreadystatechange = function() { display_result(xhr_cmd, cmd); };
        xhr_cmd.open("GET", "./include/monitoring/objectDetails/xml/serviceSendCommand.php?cmd=" + cmd + "&host_id=" + host_id + "&service_id=" + svc_id + "&actiontype=" + actiontype, true);
        xhr_cmd.send(null);
    }

    function display_result(xhr_cmd, cmd) {
        if (xhr_cmd.readyState != 4 && xhr_cmd.readyState != "complete") {
            return(0);
        }
        var msg_result;
        var docXML= xhr_cmd.responseXML;
        var items_state = docXML.getElementsByTagName("result");
        var received_command = docXML.getElementsByTagName("cmd");
        var acttype = docXML.getElementsByTagName("actiontype");
        var state = items_state.item(0).firstChild.data;
        var actiontype = acttype.item(0).firstChild.data;
        var executed_command = received_command.item(0).firstChild.data;
        var commands = new Array("service_checks", "service_notifications", "service_event_handler", "service_flap_detection", "service_passive_checks", "service_obsess");

        if (state == "0") {
            msg_result = command_sent;
            for (var i = 0;i < commands.length; i++)
                mycmd = commands[i];
                if (cmd == mycmd) {
                var tmp = atoi(actiontype) + 2;
                img_src= labels[executed_command][tmp];
                document.getElementById(cmd).innerHTML = "<a href='#' onClick='send_command(\"" + cmd + "\", \""+ actiontype +"\")'>"
                + "<img src=" + img_src
                + " alt=\"'" + labels[executed_command][actiontype] + "\"'"
                + " onmouseover=\"Tip('" + labels[executed_command][actiontype] + "')\""
                + " onmouseout='UnTip()'>"
                + "</img></a>";
                }
        } else {
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
<?php } ?>
