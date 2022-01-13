<?php
/*
* Copyright 2005-2019 Centreon
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
*/

function testExistence($name = null)
{
    global $pearDB, $form;

    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('nagios_id');
    }

    $dbResult = $pearDB->query(
        "SELECT nagios_name, nagios_id FROM cfg_nagios WHERE nagios_name = '"
        . htmlentities($name, ENT_QUOTES, "UTF-8") . "'"
    );
    $nagios = $dbResult->fetch();
    if ($dbResult->rowCount() >= 1 && $nagios["nagios_id"] == $id) {
        return true;
    } elseif ($dbResult->rowCount() >= 1 && $nagios["nagios_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

/**
 * @param null $nagiosId
 * @throws Exception
 */
function enableNagiosInDB($nagiosId = null)
{
    global $pearDB, $centreon;
    if (!$nagiosId) {
        return;
    }

    $dbResult = $pearDB->query(
        "SELECT `nagios_server_id` FROM cfg_nagios WHERE nagios_id = '" . $nagiosId . "'"
    );
    $data = $dbResult->fetch();

    $pearDB->query(
        "UPDATE `cfg_nagios` SET `nagios_activate` = '0' WHERE `nagios_server_id` = '" . $data["nagios_server_id"] . "'"
    );

    $pearDB->query(
        "UPDATE cfg_nagios SET nagios_activate = '1' WHERE nagios_id = '" . $nagiosId . "'"
    );

    $query = "SELECT `id`, `name` FROM nagios_server WHERE `ns_activate` = '0' " .
        "AND `id` = '" . $data["nagios_server_id"] . "'";
    $dbResult = $pearDB->query($query);
    $activate = $dbResult->fetch();
    if ($activate["name"]) {
        $query = "UPDATE `nagios_server` SET `ns_activate` = '1' WHERE `id` = '" . $activate['id'] . "'";
        $pearDB->query($query);
        $centreon->CentreonLogAction->insertLog("poller", $activate['id'], $activate['name'], "enable");
    }
}

/**
 * @param null $nagiosId
 * @throws Exception
 */
function disableNagiosInDB($nagiosId = null)
{
    global $pearDB, $centreon;

    if (!$nagiosId) {
        return;
    }

    $dbResult = $pearDB->query(
        "SELECT `nagios_server_id` FROM cfg_nagios WHERE nagios_id = '" . $nagiosId . "'"
    );
    $data = $dbResult->fetch();

    $pearDB->query(
        "UPDATE cfg_nagios SET nagios_activate = '0' WHERE `nagios_id` = '" . $nagiosId . "'"
    );

    $query = "SELECT `nagios_id` FROM cfg_nagios WHERE `nagios_activate` = '1' " .
        "AND `nagios_server_id` = '" . $data["nagios_server_id"] . "'";
    $dbResult = $pearDB->query($query);
    $activate = $dbResult->fetch();

    if (!$activate["nagios_id"]) {
        $query = "UPDATE `nagios_server` SET `ns_activate` = '0' WHERE `id` = '" . $data["nagios_server_id"] . "'";
        $pearDB->query($query);

        $query = "SELECT `id`, `name` FROM nagios_server WHERE `id` = '" . $data["nagios_server_id"] . "'";
        $dbResult = $pearDB->query($query);
        $poller = $dbResult->fetch();

        $centreon->CentreonLogAction->insertLog("poller", $poller['id'], $poller['name'], "disable");
    }
}

function deleteNagiosInDB($nagios = array())
{
    global $pearDB;

    foreach ($nagios as $key => $value) {
        $pearDB->query(
            "DELETE FROM cfg_nagios WHERE nagios_id = '" . $key . "'"
        );
        $pearDB->query(
            "DELETE FROM cfg_nagios_broker_module WHERE cfg_nagios_id = '" . $key . "'"
        );
    }
    $dbResult = $pearDB->query(
        "SELECT nagios_id FROM cfg_nagios WHERE nagios_activate = '1'"
    );
    if (!$dbResult->rowCount()) {
        $dbResult2 = $pearDB->query(
            "SELECT MAX(nagios_id) FROM cfg_nagios"
        );
        $nagios_id = $dbResult2->fetch();
        $pearDB->query(
            "UPDATE cfg_nagios SET nagios_activate = '1' WHERE nagios_id = '" . $nagios_id["MAX(nagios_id)"] . "'"
        );
    }
    $dbResult->closeCursor();
}

function multipleNagiosInDB($nagios = array(), $nbrDup = array())
{
    foreach ($nagios as $key => $value) {
        global $pearDB;
        $dbResult = $pearDB->query(
            "SELECT * FROM cfg_nagios WHERE nagios_id = '" . $key . "' LIMIT 1"
        );
        $row = $dbResult->fetch();
        $row["nagios_id"] = '';
        $row["nagios_activate"] = '0';
        $dbResult->closeCursor();
        $rowBks = array();
        $dbResult = $pearDB->query(
            "SELECT * FROM cfg_nagios_broker_module WHERE cfg_nagios_id='" . $key . "'"
        );
        while ($rowBk = $dbResult->fetch()) {
            $rowBks[] = $rowBk;
        }
        $dbResult->closeCursor();
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $value2 = $pearDB->escape($value2);
                $key2 == "nagios_name" ? ($nagios_name = $value2 = $value2 . "_" . $i) : null;
                $val ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
            }
            if (testExistence($nagios_name)) {
                $val ? $rq = "INSERT INTO cfg_nagios VALUES (" . $val . ")" : $rq = null;
                $dbResult = $pearDB->query($rq);
                /* Find the new last nagios_id once */
                $dbResult = $pearDB->query("SELECT MAX(nagios_id) FROM cfg_nagios");
                $nagios_id = $dbResult->fetch();
                $dbResult->closeCursor();
                foreach ($rowBks as $keyBk => $valBk) {
                    if ($valBk["broker_module"]) {
                        $rqBk = "INSERT INTO cfg_nagios_broker_module (`cfg_nagios_id`, `broker_module`) VALUES ('"
                            . $nagios_id["MAX(nagios_id)"] . "', '" . $valBk["broker_module"] . "')";
                    }
                    $dbResult = $pearDB->query($rqBk);
                }
            }
        }
    }
}

function updateNagiosInDB($nagios_id = null)
{
    if (!$nagios_id) {
        return;
    }
    updateNagios($nagios_id);
}

function insertNagiosInDB()
{
    $nagios_id = insertNagios();
    return ($nagios_id);
}

/**
 * Calculate the sum of bitwise for a POST QuickForm array
 *
 * The array format
 *
 * array[key] => enable
 *  Key int the bit
 *  Enable 0|1 if the bit is activate
 *
 * if found the bit -1 (all) or 0 (none) activate return the value
 *
 * @param array $list The POST QuickForm table
 * @return int The bitwise
 */
function calculateBitwise($list)
{
    $bitwise = 0;
    foreach ($list as $bit => $value) {
        if ($value == 1) {
            if ($bit === -1 || $bit === 0) {
                return $bit;
            }
            $bitwise += $bit;
        }
    }
    return $bitwise;
}

function insertNagios($ret = array(), $brokerTab = array())
{
    global $form, $pearDB, $centreon;

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $rq = "INSERT INTO cfg_nagios ("
        . "`nagios_id` , `nagios_name` , `use_timezone`, `nagios_server_id`, `log_file` , `cfg_dir` , "
        . "`temp_file` , "
        . "`status_file` , `status_update_interval` , `nagios_user` , `nagios_group` , "
        . "`enable_notifications` , `execute_service_checks` , "
        . "`accept_passive_service_checks` , `execute_host_checks` , "
        . "`accept_passive_host_checks` , `enable_event_handlers` , `log_rotation_method` , `log_archive_path` , "
        . "`check_external_commands` , `command_check_interval` , `command_file` , `downtime_file` , `comment_file` , "
        . "`lock_file` , `retain_state_information` , `state_retention_file` , `retention_update_interval` , "
        . "`use_retained_program_state` , `use_retained_scheduling_info` , "
        . "`retained_contact_host_attribute_mask`, `retained_contact_service_attribute_mask` , "
        . "`retained_process_host_attribute_mask`, `retained_process_service_attribute_mask` , "
        . "`retained_host_attribute_mask`, `retained_service_attribute_mask`, "
        . "`use_syslog` , `log_notifications` , "
        . "`log_service_retries` , `log_host_retries` , `log_event_handlers` , "
        . "`log_external_commands` , `log_passive_checks` , `global_host_event_handler` , "
        . "`global_service_event_handler` , `sleep_time` , `service_inter_check_delay_method` , "
        . "`host_inter_check_delay_method` , `service_interleave_factor` ,"
        . " `max_concurrent_checks` , `max_service_check_spread` , "
        . "`max_host_check_spread` , `check_result_reaper_frequency` , `max_check_result_reaper_time`, "
        . "`auto_reschedule_checks` , `auto_rescheduling_interval` , "
        . "`auto_rescheduling_window` , "
        . "`enable_predictive_host_dependency_checks`, `enable_flap_detection` , `low_service_flap_threshold` , "
        . "`high_service_flap_threshold` , `low_host_flap_threshold` , `high_host_flap_threshold` ,"
        . " `soft_state_dependencies` ,`enable_predictive_service_dependency_checks` , "
        . "`service_check_timeout` , `host_check_timeout` , `event_handler_timeout` , "
        . "`notification_timeout` , `ocsp_timeout` , `ochp_timeout` , "
        . "`perfdata_timeout` , `obsess_over_services` , `ocsp_command` , "
        . "`obsess_over_hosts` , `ochp_command` , `process_performance_data` , "
        . "`host_perfdata_command` , `service_perfdata_command` , `host_perfdata_file` , "
        . "`service_perfdata_file` , `host_perfdata_file_template` , "
        . "`service_perfdata_file_template` , `host_perfdata_file_mode` , "
        . "`service_perfdata_file_mode` , `host_perfdata_file_processing_interval` , "
        . "`service_perfdata_file_processing_interval` , `host_perfdata_file_processing_command` , "
        . "`service_perfdata_file_processing_command` , "
        . "`check_for_orphaned_services` , `check_service_freshness` , "
        . "`service_freshness_check_interval` , `cached_host_check_horizon`, "
        . "`cached_service_check_horizon` , `additional_freshness_latency` , "
        . "`check_host_freshness` , `host_freshness_check_interval` , `instance_heartbeat_interval` , `date_format` , "
        . "`illegal_object_name_chars` , `illegal_macro_output_chars`, "
        . "`use_large_installation_tweaks` , `debug_file` , `debug_level` , "
        . "`debug_level_opt`, `debug_verbosity` , `max_debug_file_size` , `daemon_dumps_core`, "
        . "`enable_environment_macros` , `use_setpgid`, `use_regexp_matching` , `use_true_regexp_matching` , "
        . "`admin_email` , `admin_pager` , `nagios_comment` , `nagios_activate`, "
        . "`event_broker_options` , `translate_passive_host_checks`, "
        . "`passive_host_checks_are_soft`, `check_for_orphaned_hosts`, `external_command_buffer_slots`, "
        . "`cfg_file`, `log_pid`, `enable_macros_filter`, `macros_filter`) ";
    $rq .= "VALUES (";
    $rq .= "NULL, ";

    if (isset($ret["nagios_name"]) && $ret["nagios_name"] != null) {
        $rq .= "'" . htmlentities($ret["nagios_name"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["use_timezone"]) && $ret["use_timezone"] != null) {
        $rq .= "'" . htmlentities($ret["use_timezone"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["nagios_server_id"]) && $ret["nagios_server_id"] != null) {
        $rq .= "'" . htmlentities($ret["nagios_server_id"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["log_file"]) && $ret["log_file"] != null) {
        $rq .= "'" . htmlentities($ret["log_file"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["cfg_dir"]) && $ret["cfg_dir"] != null) {
        $rq .= "'" . htmlentities($ret["cfg_dir"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["temp_file"]) && $ret["temp_file"] != null) {
        $rq .= "'" . htmlentities($ret["temp_file"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["status_file"]) && $ret["status_file"] != null) {
        $rq .= "'" . htmlentities($ret["status_file"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["status_update_interval"]) && $ret["status_update_interval"] != null) {
        $rq .= "'" . htmlentities($ret["status_update_interval"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["nagios_user"]) && $ret["nagios_user"] != null) {
        $rq .= "'" . htmlentities($ret["nagios_user"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["nagios_group"]) && $ret["nagios_group"] != null) {
        $rq .= "'" . htmlentities($ret["nagios_group"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["enable_notifications"]["enable_notifications"])
        && $ret["enable_notifications"]["enable_notifications"] != 2
    ) {
        $rq .= "'" . $ret["enable_notifications"]["enable_notifications"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["execute_service_checks"]["execute_service_checks"])
        && $ret["execute_service_checks"]["execute_service_checks"] != 2
    ) {
        $rq .= "'" . $ret["execute_service_checks"]["execute_service_checks"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["accept_passive_service_checks"]["accept_passive_service_checks"])
        && $ret["accept_passive_service_checks"]["accept_passive_service_checks"] != 2
    ) {
        $rq .= "'" . $ret["accept_passive_service_checks"]["accept_passive_service_checks"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["execute_host_checks"]["execute_host_checks"])
        && $ret["execute_host_checks"]["execute_host_checks"] != 2
    ) {
        $rq .= "'" . $ret["execute_host_checks"]["execute_host_checks"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["accept_passive_host_checks"]["accept_passive_host_checks"])
        && $ret["accept_passive_host_checks"]["accept_passive_host_checks"] != 2
    ) {
        $rq .= "'" . $ret["accept_passive_host_checks"]["accept_passive_host_checks"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["enable_event_handlers"]["enable_event_handlers"])
        && $ret["enable_event_handlers"]["enable_event_handlers"] != 2
    ) {
        $rq .= "'" . $ret["enable_event_handlers"]["enable_event_handlers"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["log_rotation_method"]["log_rotation_method"])
        && $ret["log_rotation_method"]["log_rotation_method"] != 2
    ) {
        $rq .= "'" . $ret["log_rotation_method"]["log_rotation_method"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["log_archive_path"]) && $ret["log_archive_path"] != null) {
        $rq .= "'" . htmlentities($ret["log_archive_path"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["check_external_commands"]["check_external_commands"])
        && $ret["check_external_commands"]["check_external_commands"] != 2
    ) {
        $rq .= "'" . $ret["check_external_commands"]["check_external_commands"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["command_check_interval"]) && $ret["command_check_interval"] != null) {
        $rq .= "'" . htmlentities($ret["command_check_interval"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["command_file"]) && $ret["command_file"] != null) {
        $rq .= "'" . htmlentities($ret["command_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["downtime_file"]) && $ret["downtime_file"] != null) {
        $rq .= "'" . htmlentities($ret["downtime_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["comment_file"]) && $ret["comment_file"] != null) {
        $rq .= "'" . htmlentities($ret["comment_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["lock_file"]) && $ret["lock_file"] != null) {
        $rq .= "'" . htmlentities($ret["lock_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["retain_state_information"]["retain_state_information"])
        && $ret["retain_state_information"]["retain_state_information"] != 2
    ) {
        $rq .= "'" . $ret["retain_state_information"]["retain_state_information"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["state_retention_file"]) && $ret["state_retention_file"] != null) {
        $rq .= "'" . htmlentities($ret["state_retention_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["retention_update_interval"]) && $ret["retention_update_interval"] != null) {
        $rq .= "'" . htmlentities($ret["retention_update_interval"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["use_retained_program_state"]["use_retained_program_state"])
        && $ret["use_retained_program_state"]["use_retained_program_state"] != 2
    ) {
        $rq .= "'" . $ret["use_retained_program_state"]["use_retained_program_state"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["use_retained_scheduling_info"]["use_retained_scheduling_info"])
        && $ret["use_retained_scheduling_info"]["use_retained_scheduling_info"] != 2
    ) {
        $rq .= "'" . $ret["use_retained_scheduling_info"]["use_retained_scheduling_info"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["retained_contact_host_attribute_mask"]) && $ret["retained_contact_host_attribute_mask"] != null) {
        $rq .= "'" . htmlentities($ret["retained_contact_host_attribute_mask"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["retained_contact_service_attribute_mask"])
        && $ret["retained_contact_service_attribute_mask"] != null
    ) {
        $rq .= "'" . htmlentities($ret["retained_contact_service_attribute_mask"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["retained_process_host_attribute_mask"]) && $ret["retained_process_host_attribute_mask"] != null) {
        $rq .= "'" . htmlentities($ret["retained_process_host_attribute_mask"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["retained_process_service_attribute_mask"])
        && $ret["retained_process_service_attribute_mask"] != null
    ) {
        $rq .= "'" . htmlentities($ret["retained_process_service_attribute_mask"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["retained_host_attribute_mask"]) && $ret["retained_host_attribute_mask"] != null) {
        $rq .= "'" . htmlentities($ret["retained_host_attribute_mask"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["retained_service_attribute_mask"]) && $ret["retained_service_attribute_mask"] != null) {
        $rq .= "'" . htmlentities($ret["retained_service_attribute_mask"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["use_syslog"]["use_syslog"]) && $ret["use_syslog"]["use_syslog"] != 2) {
        $rq .= "'" . $ret["use_syslog"]["use_syslog"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["log_notifications"]["log_notifications"]) && $ret["log_notifications"]["log_notifications"] != 2) {
        $rq .= "'" . $ret["log_notifications"]["log_notifications"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["log_service_retries"]["log_service_retries"])
        && $ret["log_service_retries"]["log_service_retries"] != 2
    ) {
        $rq .= "'" . $ret["log_service_retries"]["log_service_retries"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["log_host_retries"]["log_host_retries"]) && $ret["log_host_retries"]["log_host_retries"] != 2) {
        $rq .= "'" . $ret["log_host_retries"]["log_host_retries"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["log_event_handlers"]["log_event_handlers"])
        && $ret["log_event_handlers"]["log_event_handlers"] != 2
    ) {
        $rq .= "'" . $ret["log_event_handlers"]["log_event_handlers"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["log_external_commands"]["log_external_commands"])
        && $ret["log_external_commands"]["log_external_commands"] != 2
    ) {
        $rq .= "'" . $ret["log_external_commands"]["log_external_commands"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["log_passive_checks"]["log_passive_checks"])
        && $ret["log_passive_checks"]["log_passive_checks"] != 2
    ) {
        $rq .= "'" . $ret["log_passive_checks"]["log_passive_checks"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["global_host_event_handler"]) && $ret["global_host_event_handler"] != null) {
        $rq .= "'" . $ret["global_host_event_handler"] . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["global_service_event_handler"]) && $ret["global_service_event_handler"] != null) {
        $rq .= "'" . $ret["global_service_event_handler"] . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["sleep_time"]) && $ret["sleep_time"] != null) {
        $rq .= "'" . htmlentities($ret["sleep_time"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["service_inter_check_delay_method"]) && $ret["service_inter_check_delay_method"] != null) {
        $rq .= "'" . $ret["service_inter_check_delay_method"] . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["host_inter_check_delay_method"]) && $ret["host_inter_check_delay_method"] != null) {
        $rq .= "'" . $ret["host_inter_check_delay_method"] . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["service_interleave_factor"]) && $ret["service_interleave_factor"] != null) {
        $rq .= "'" . htmlentities($ret["service_interleave_factor"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["max_concurrent_checks"]) && $ret["max_concurrent_checks"] != null) {
        $rq .= "'" . htmlentities($ret["max_concurrent_checks"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["max_service_check_spread"]) && $ret["max_service_check_spread"] != null) {
        $rq .= "'" . htmlentities($ret["max_service_check_spread"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["max_host_check_spread"]) && $ret["max_host_check_spread"] != null) {
        $rq .= "'" . htmlentities($ret["max_host_check_spread"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["check_result_reaper_frequency"]) && $ret["check_result_reaper_frequency"] != null) {
        $rq .= "'" . htmlentities($ret["check_result_reaper_frequency"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["max_check_result_reaper_time"]) && $ret["max_check_result_reaper_time"] != null) {
        $rq .= "'" . htmlentities($ret["max_check_result_reaper_time"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["auto_reschedule_checks"]["auto_reschedule_checks"])
        && $ret["auto_reschedule_checks"]["auto_reschedule_checks"] != 2
    ) {
        $rq .= "'" . $ret["auto_reschedule_checks"]["auto_reschedule_checks"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["auto_rescheduling_interval"]) && $ret["auto_rescheduling_interval"] != null) {
        $rq .= "'" . htmlentities($ret["auto_rescheduling_interval"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["auto_rescheduling_window"]) && $ret["auto_rescheduling_window"] != null) {
        $rq .= "'" . htmlentities($ret["auto_rescheduling_window"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["enable_predictive_host_dependency_checks"]["enable_predictive_host_dependency_checks"])
        && $ret["enable_predictive_host_dependency_checks"]["enable_predictive_host_dependency_checks"] != 2
    ) {
        $rq .= "'" .
        $ret["enable_predictive_host_dependency_checks"]["enable_predictive_host_dependency_checks"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["enable_flap_detection"]["enable_flap_detection"])
        && $ret["enable_flap_detection"]["enable_flap_detection"] != 2
    ) {
        $rq .= "'" . $ret["enable_flap_detection"]["enable_flap_detection"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["low_service_flap_threshold"]) && $ret["low_service_flap_threshold"] != null) {
        $rq .= "'" . htmlentities($ret["low_service_flap_threshold"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["high_service_flap_threshold"]) && $ret["high_service_flap_threshold"] != null) {
        $rq .= "'" . htmlentities($ret["high_service_flap_threshold"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["low_host_flap_threshold"]) && $ret["low_host_flap_threshold"] != null) {
        $rq .= "'" . htmlentities($ret["low_host_flap_threshold"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["high_host_flap_threshold"]) && $ret["high_host_flap_threshold"] != null) {
        $rq .= "'" . htmlentities($ret["high_host_flap_threshold"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["soft_state_dependencies"]["soft_state_dependencies"])
        && $ret["soft_state_dependencies"]["soft_state_dependencies"] != 2
    ) {
        $rq .= "'" . $ret["soft_state_dependencies"]["soft_state_dependencies"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["enable_predictive_service_dependency_checks"]["enable_predictive_service_dependency_checks"])
        && $ret["enable_predictive_service_dependency_checks"]["enable_predictive_service_dependency_checks"] != 2
    ) {
        $rq .= "'" .
        $ret["enable_predictive_service_dependency_checks"]["enable_predictive_service_dependency_checks"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["service_check_timeout"]) && $ret["service_check_timeout"] != null) {
        $rq .= "'" . htmlentities($ret["service_check_timeout"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["host_check_timeout"]) && $ret["host_check_timeout"] != null) {
        $rq .= "'" . htmlentities($ret["host_check_timeout"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["event_handler_timeout"]) && $ret["event_handler_timeout"] != null) {
        $rq .= "'" . htmlentities($ret["event_handler_timeout"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["notification_timeout"]) && $ret["notification_timeout"] != null) {
        $rq .= "'" . htmlentities($ret["notification_timeout"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["ocsp_timeout"]) && $ret["ocsp_timeout"] != null) {
        $rq .= "'" . htmlentities($ret["ocsp_timeout"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["ochp_timeout"]) && $ret["ochp_timeout"] != null) {
        $rq .= "'" . htmlentities($ret["ochp_timeout"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["perfdata_timeout"]) && $ret["perfdata_timeout"] != null) {
        $rq .= "'" . htmlentities($ret["perfdata_timeout"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["obsess_over_services"]["obsess_over_services"])
        && $ret["obsess_over_services"]["obsess_over_services"] != 2
    ) {
        $rq .= "'" . $ret["obsess_over_services"]["obsess_over_services"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["ocsp_command"]) && $ret["ocsp_command"] != null) {
        $rq .= "'" . htmlentities($ret["ocsp_command"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["obsess_over_hosts"]["obsess_over_hosts"])
        && $ret["obsess_over_hosts"]["obsess_over_hosts"] != 2
    ) {
        $rq .= "'" . $ret["obsess_over_hosts"]["obsess_over_hosts"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["ochp_command"]) && $ret["ochp_command"] != null) {
        $rq .= "'" . htmlentities($ret["ochp_command"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["process_performance_data"]["process_performance_data"])
        && $ret["process_performance_data"]["process_performance_data"] != 2
    ) {
        $rq .= "'" . $ret["process_performance_data"]["process_performance_data"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["host_perfdata_command"]) && $ret["host_perfdata_command"] != null) {
        $rq .= "'" . htmlentities($ret["host_perfdata_command"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["service_perfdata_command"]) && $ret["service_perfdata_command"] != null) {
        $rq .= "'" . htmlentities($ret["service_perfdata_command"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["host_perfdata_file"]) && $ret["host_perfdata_file"] != null) {
        $rq .= "'" . htmlentities($ret["host_perfdata_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["service_perfdata_file"]) && $ret["service_perfdata_file"] != null) {
        $rq .= "'" . htmlentities($ret["service_perfdata_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["host_perfdata_file_template"]) && $ret["host_perfdata_file_template"] != null) {
        $rq .= $pearDB->quote($ret["host_perfdata_file_template"]) . ",  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["service_perfdata_file_template"]) && $ret["service_perfdata_file_template"] != null) {
        $rq .= $pearDB->quote($ret["service_perfdata_file_template"]) . ",  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["host_perfdata_file_mode"]["host_perfdata_file_mode"])
        && $ret["host_perfdata_file_mode"]["host_perfdata_file_mode"] != null
    ) {
        $rq .= "'" . $ret["host_perfdata_file_mode"]["host_perfdata_file_mode"] . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["service_perfdata_file_mode"]["service_perfdata_file_mode"])
        && $ret["service_perfdata_file_mode"]["service_perfdata_file_mode"] != null
    ) {
        $rq .= "'" . $ret["service_perfdata_file_mode"]["service_perfdata_file_mode"] . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["host_perfdata_file_processing_interval"])
        && $ret["host_perfdata_file_processing_interval"] != null
    ) {
        $rq .= "'" . htmlentities($ret["host_perfdata_file_processing_interval"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["service_perfdata_file_processing_interval"])
        && $ret["service_perfdata_file_processing_interval"] != null
    ) {
        $rq .= "'" . htmlentities($ret["service_perfdata_file_processing_interval"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["host_perfdata_file_processing_command"])
        && $ret["host_perfdata_file_processing_command"] != null
    ) {
        $rq .= "'" . htmlentities($ret["host_perfdata_file_processing_command"]) . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["service_perfdata_file_processing_command"])
        && $ret["service_perfdata_file_processing_command"] != null
    ) {
        $rq .= "'" . htmlentities($ret["service_perfdata_file_processing_command"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["check_for_orphaned_services"]["check_for_orphaned_services"])
        && $ret["check_for_orphaned_services"]["check_for_orphaned_services"] != 2
    ) {
        $rq .= "'" . $ret["check_for_orphaned_services"]["check_for_orphaned_services"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["check_service_freshness"]["check_service_freshness"])
        && $ret["check_service_freshness"]["check_service_freshness"] != 2
    ) {
        $rq .= "'" . $ret["check_service_freshness"]["check_service_freshness"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["service_freshness_check_interval"]) && $ret["service_freshness_check_interval"] != null) {
        $rq .= "'" . htmlentities($ret["service_freshness_check_interval"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["cached_host_check_horizon"]) && $ret["cached_host_check_horizon"] != null) {
        $rq .= "'" . htmlentities($ret["cached_host_check_horizon"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["cached_service_check_horizon"]) && $ret["cached_service_check_horizon"] != null) {
        $rq .= "'" . htmlentities($ret["cached_service_check_horizon"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["additional_freshness_latency"]) && $ret["additional_freshness_latency"] != null) {
        $rq .= "'" . htmlentities($ret["additional_freshness_latency"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["check_host_freshness"]["check_host_freshness"])
        && $ret["check_host_freshness"]["check_host_freshness"] != 2
    ) {
        $rq .= "'" . $ret["check_host_freshness"]["check_host_freshness"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["host_freshness_check_interval"]) && $ret["host_freshness_check_interval"] != null) {
        $rq .= "'" . htmlentities($ret["host_freshness_check_interval"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["instance_heartbeat_interval"])
        && $ret["instance_heartbeat_interval"] != null
        && is_numeric($ret["instance_heartbeat_interval"])
    ) {
        $rq .= (int)$ret["instance_heartbeat_interval"] . ",  ";
    } else {
        $rq .= "30, ";
    }

    if (isset($ret["date_format"]) && $ret["date_format"] != null) {
        $rq .= "'" . htmlentities($ret["date_format"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["illegal_object_name_chars"]) && $ret["illegal_object_name_chars"] != null) {
        $rq .= $pearDB->quote($ret["illegal_object_name_chars"]) . ",  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["illegal_macro_output_chars"]) && $ret["illegal_macro_output_chars"] != null) {
        $rq .= $pearDB->quote($ret["illegal_macro_output_chars"]) . ",  ";
    } else {
        $rq .= "NULL, ";
    }

    if (
        isset($ret["use_large_installation_tweaks"]["use_large_installation_tweaks"])
        && $ret["use_large_installation_tweaks"]["use_large_installation_tweaks"] != 2
    ) {
        $rq .= "'" . $ret["use_large_installation_tweaks"]["use_large_installation_tweaks"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["debug_file"]) && $ret["debug_file"] != null) {
        $rq .= "'" . htmlentities($ret["debug_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    $level = 0;
    if (isset($ret["nagios_debug_level"]) && $ret["nagios_debug_level"] != null) {
        foreach ($ret["nagios_debug_level"] as $key => $value) {
            $level += $key;
        }
    }
    $rq .= "$level, ";

    if (isset($ret["nagios_debug_level"]) && $ret["nagios_debug_level"] != null) {
        $rq .= "'" . implode(",", array_keys($ret["nagios_debug_level"])) . "',  ";
    } else {
        $rq .= "'0', ";
    }

    if (isset($ret["debug_verbosity"]) && $ret["debug_verbosity"] != 2) {
        $rq .= "'" . $ret["debug_verbosity"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["max_debug_file_size"]) && $ret["max_debug_file_size"] != null) {
        $rq .= "'" . htmlentities($ret["max_debug_file_size"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["daemon_dumps_core"]["daemon_dumps_core"]) && $ret["daemon_dumps_core"]["daemon_dumps_core"]) {
        $rq .= "'1', ";
    } else {
        $rq .= "'0', ";
    }

    if (
        isset($ret["enable_environment_macros"]["enable_environment_macros"])
        && $ret["enable_environment_macros"]["enable_environment_macros"] != 2
    ) {
        $rq .= "'" . $ret["enable_environment_macros"]["enable_environment_macros"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["use_setpgid"]["use_setpgid"]) && $ret["use_setpgid"]["use_setpgid"] != 2) {
        $rq .= "'" . $ret["use_setpgid"]["use_setpgid"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["use_regexp_matching"]["use_regexp_matching"])
        && $ret["use_regexp_matching"]["use_regexp_matching"] != 2
    ) {
        $rq .= "'" . $ret["use_regexp_matching"]["use_regexp_matching"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["use_true_regexp_matching"]["use_true_regexp_matching"])
        && $ret["use_true_regexp_matching"]["use_true_regexp_matching"] != 2
    ) {
        $rq .= "'" . $ret["use_true_regexp_matching"]["use_true_regexp_matching"] . "',  ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["admin_email"]) && $ret["admin_email"] != null) {
        $rq .= "'" . htmlentities($ret["admin_email"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["admin_pager"]) && $ret["admin_pager"] != null) {
        $rq .= "'" . htmlentities($ret["admin_pager"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["nagios_comment"]) && $ret["nagios_comment"] != null) {
        $rq .= "'" . htmlentities($ret["nagios_comment"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["nagios_activate"]["nagios_activate"]) && $ret["nagios_activate"]["nagios_activate"] != null) {
        $rq .= "'" . $ret["nagios_activate"]["nagios_activate"] . "',";
    } else {
        $rq .= "'0',";
    }

    // Calculate the sum of bitwise
    if (isset($ret['event_broker_options']) && $ret['event_broker_options'] != null) {
        $rq .= "'" . calculateBitwise($ret["event_broker_options"]) . "', ";
    } else {
        $rq .= "'-1', ";
    }

    if (
        isset($ret["translate_passive_host_checks"]["translate_passive_host_checks"])
        && $ret["translate_passive_host_checks"]["translate_passive_host_checks"] != 2
    ) {
        $rq .= "'" . $ret["translate_passive_host_checks"]["translate_passive_host_checks"] . "', ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["passive_host_checks_are_soft"]["passive_host_checks_are_soft"])
        && $ret["passive_host_checks_are_soft"]["passive_host_checks_are_soft"] != 2
    ) {
        $rq .= "'" . $ret["passive_host_checks_are_soft"]["passive_host_checks_are_soft"] . "', ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["check_for_orphaned_hosts"]["check_for_orphaned_hosts"])
        && $ret["check_for_orphaned_hosts"]["check_for_orphaned_hosts"] != 2
    ) {
        $rq .= "'" . $ret["check_for_orphaned_hosts"]["check_for_orphaned_hosts"] . "', ";
    } else {
        $rq .= "'2', ";
    }

    if (
        isset($ret["external_command_buffer_slots"]["external_command_buffer_slots"])
        && $ret["external_command_buffer_slots"]["external_command_buffer_slots"] != 2
    ) {
        $rq .= "'" . $ret["external_command_buffer_slots"]["external_command_buffer_slots"] . "', ";
    } else {
        $rq .= "'2', ";
    }

    if (isset($ret["cfg_file"]) && $ret["cfg_file"] != null) {
        $rq .= "'" . htmlentities($ret["cfg_file"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "NULL, ";
    }

    if (isset($ret["log_pid"]["log_pid"]) && $ret["log_pid"]["log_pid"]) {
        $rq .= "'1', ";
    } else {
        $rq .= "'0', ";
    }

    if (
        isset($ret['enable_macros_filter']['enable_macros_filter'])
        && $ret['enable_macros_filter']['enable_macros_filter']
    ) {
        $rq .= "'1', ";
    } else {
        $rq .= "'0', ";
    }

    /* Add whitelist macros to send to Centreon Broker */
    if (isset($_REQUEST['macros_filter'])) {
        $macrosFilter = trim(
            join(
                ',',
                array_map(function ($value) {
                    return CentreonDB::escape($value);
                }, $_REQUEST['macros_filter'])
            )
        );
        $rq .= "'" . $macrosFilter . "')";
    } else {
        $rq .= "NULL)";
    }

    $dbResult = $pearDB->query($rq);
    $dbResult = $pearDB->query("SELECT MAX(nagios_id) FROM cfg_nagios");
    $nagios_id = $dbResult->fetch();
    $dbResult->closeCursor();

    if (isset($_REQUEST['in_broker'])) {
        $mainCfg = new CentreonConfigEngine($pearDB);
        $mainCfg->insertBrokerDirectives($nagios_id["MAX(nagios_id)"], $_REQUEST['in_broker']);
    }

    /* Manage the case where you have to main.cfg on the same poller */
    if (isset($ret["nagios_activate"]["nagios_activate"]) && $ret["nagios_activate"]["nagios_activate"]) {
        $dbResult = $pearDB->query(
            "UPDATE cfg_nagios SET nagios_activate = '0' WHERE nagios_id != '"
            . $nagios_id["MAX(nagios_id)"]
            . "' AND nagios_server_id = '" . $ret['nagios_server_id'] . "'"
        );
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "engine",
        $nagios_id["MAX(nagios_id)"],
        $pearDB->escape($ret["nagios_name"]),
        "a",
        $fields
    );

    return ($nagios_id["MAX(nagios_id)"]);
}

function updateNagios($nagios_id = null)
{
    global $form, $pearDB, $centreon;

    if (!$nagios_id) {
        return;
    }

    if (isset($ret["nagios_server_id"])) {
        $dbResult = $pearDB->query("UPDATE cfg_nagios SET `nagios_server_id` != '" . $ret["nagios_server_id"] . "'");
    }

    $ret = array();
    $ret = $form->getSubmitValues();
    $rq = "UPDATE cfg_nagios SET ";
    if (isset($ret["nagios_name"]) && $ret["nagios_name"] != null) {
        $rq .= "nagios_name = '" . htmlentities($ret["nagios_name"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "nagios_name = NULL, ";
    }

    if (isset($ret["nagios_server_id"]) && $ret["nagios_server_id"] != null) {
        $rq .= "nagios_server_id = '" . htmlentities($ret["nagios_server_id"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "nagios_server_id = NULL, ";
    }

    if (isset($ret["use_timezone"]) && $ret["use_timezone"] != null) {
        $rq .= "use_timezone = '" . htmlentities($ret["use_timezone"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "use_timezone = NULL, ";
    }

    if (isset($ret["log_file"]) && $ret["log_file"] != null) {
        $rq .= "log_file = '" . htmlentities($ret["log_file"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "log_file = NULL, ";
    }

    if (isset($ret["cfg_dir"]) && $ret["cfg_dir"] != null) {
        $rq .= "cfg_dir = '" . htmlentities($ret["cfg_dir"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "cfg_dir = NULL, ";
    }

    if (isset($ret["temp_file"]) && $ret["temp_file"] != null) {
        $rq .= "temp_file = '" . htmlentities($ret["temp_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "temp_file = NULL, ";
    }

    if (isset($ret["status_file"]) && $ret["status_file"] != null) {
        $rq .= "status_file = '" . htmlentities($ret["status_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "status_file = NULL, ";
    }

    if (isset($ret["status_update_interval"]) && $ret["status_update_interval"] != null) {
        $rq .= "status_update_interval = '" . (int)$ret["status_update_interval"] . "',  ";
    } else {
        $rq .= "status_update_interval = NULL, ";
    }

    if (isset($ret["nagios_user"]) && $ret["nagios_user"] != null) {
        $rq .= "nagios_user = '" . htmlentities($ret["nagios_user"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "nagios_user = NULL, ";
    }

    if (isset($ret["nagios_group"]) && $ret["nagios_group"] != null) {
        $rq .= "nagios_group = '" . htmlentities($ret["nagios_group"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "nagios_group = NULL, ";
    }

    if (
        isset($ret["enable_notifications"]["enable_notifications"])
        && $ret["enable_notifications"]["enable_notifications"] != 2
    ) {
        $rq .= "enable_notifications = '" . $ret["enable_notifications"]["enable_notifications"] . "',  ";
    } else {
        $rq .= "enable_notifications = '2', ";
    }

    if (
        isset($ret["execute_service_checks"]["execute_service_checks"])
        && $ret["execute_service_checks"]["execute_service_checks"] != 2
    ) {
        $rq .= "execute_service_checks = '" . $ret["execute_service_checks"]["execute_service_checks"] . "',  ";
    } else {
        $rq .= "execute_service_checks = '2', ";
    }

    if (
        isset($ret["accept_passive_service_checks"]["accept_passive_service_checks"])
        && $ret["accept_passive_service_checks"]["accept_passive_service_checks"] != 2
    ) {
        $rq .= "accept_passive_service_checks = '"
            . $ret["accept_passive_service_checks"]["accept_passive_service_checks"]
            . "',  ";
    } else {
        $rq .= "accept_passive_service_checks = '2', ";
    }

    if (
        isset($ret["execute_host_checks"]["execute_host_checks"])
        && $ret["execute_host_checks"]["execute_host_checks"] != 2
    ) {
        $rq .= "execute_host_checks = '" . $ret["execute_host_checks"]["execute_host_checks"] . "',  ";
    } else {
        $rq .= "execute_host_checks = '2', ";
    }

    if (
        isset($ret["accept_passive_host_checks"]["accept_passive_host_checks"])
        && $ret["accept_passive_host_checks"]["accept_passive_host_checks"] != 2
    ) {
        $rq .= "accept_passive_host_checks = '"
            . $ret["accept_passive_host_checks"]["accept_passive_host_checks"]
            . "',  ";
    } else {
        $rq .= "accept_passive_host_checks = '2', ";
    }

    if (
        isset($ret["enable_event_handlers"]["enable_event_handlers"])
        && $ret["enable_event_handlers"]["enable_event_handlers"] != 2
    ) {
        $rq .= "enable_event_handlers = '" . $ret["enable_event_handlers"]["enable_event_handlers"] . "',  ";
    } else {
        $rq .= "enable_event_handlers = '2', ";
    }

    if (
        isset($ret["log_rotation_method"]["log_rotation_method"])
        && $ret["log_rotation_method"]["log_rotation_method"] != 2
    ) {
        $rq .= "log_rotation_method = '" . $ret["log_rotation_method"]["log_rotation_method"] . "',  ";
    } else {
        $rq .= "log_rotation_method = '2', ";
    }

    if (isset($ret["log_archive_path"]) && $ret["log_archive_path"] != null) {
        $rq .= "log_archive_path = '" . htmlentities($ret["log_archive_path"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "log_archive_path = NULL, ";
    }

    if (
        isset($ret["check_external_commands"]["check_external_commands"])
        && $ret["check_external_commands"]["check_external_commands"] != 2
    ) {
        $rq .= "check_external_commands = '" . $ret["check_external_commands"]["check_external_commands"] . "',  ";
    } else {
        $rq .= "check_external_commands = '2', ";
    }

    if (isset($ret["command_check_interval"]) && $ret["command_check_interval"] != null) {
        $rq .= "command_check_interval = '"
            . htmlentities($ret["command_check_interval"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "command_check_interval = NULL, ";
    }

    if (isset($ret["command_file"]) && $ret["command_file"] != null) {
        $rq .= "command_file = '" . htmlentities($ret["command_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "command_file = NULL, ";
    }

    if (isset($ret["downtime_file"]) && $ret["downtime_file"] != null) {
        $rq .= "downtime_file = '" . htmlentities($ret["downtime_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "downtime_file = NULL, ";
    }

    if (isset($ret["comment_file"]) && $ret["comment_file"] != null) {
        $rq .= "comment_file = '" . htmlentities($ret["comment_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "comment_file = NULL, ";
    }

    if (isset($ret["lock_file"]) && $ret["lock_file"] != null) {
        $rq .= "lock_file = '" . htmlentities($ret["lock_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "lock_file = NULL, ";
    }

    if (
        isset($ret["retain_state_information"]["retain_state_information"])
        && $ret["retain_state_information"]["retain_state_information"] != 2
    ) {
        $rq .= "retain_state_information = '" . $ret["retain_state_information"]["retain_state_information"] . "',  ";
    } else {
        $rq .= "retain_state_information = '2', ";
    }

    if (isset($ret["state_retention_file"]) && $ret["state_retention_file"] != null) {
        $rq .= "state_retention_file = '" . htmlentities($ret["state_retention_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "state_retention_file = NULL, ";
    }

    if (isset($ret["retention_update_interval"]) && $ret["retention_update_interval"] != null) {
        $rq .= "retention_update_interval = '"
            . htmlentities($ret["retention_update_interval"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "retention_update_interval = NULL, ";
    }

    if (
        isset($ret["use_retained_program_state"]["use_retained_program_state"])
        && $ret["use_retained_program_state"]["use_retained_program_state"] != 2
    ) {
        $rq .= "use_retained_program_state = '"
            . $ret["use_retained_program_state"]["use_retained_program_state"]
            . "',  ";
    } else {
        $rq .= "use_retained_program_state = '2', ";
    }

    if (
        isset($ret["use_retained_scheduling_info"]["use_retained_scheduling_info"])
        && $ret["use_retained_scheduling_info"]["use_retained_scheduling_info"] != 2
    ) {
        $rq .= "use_retained_scheduling_info = '"
            . $ret["use_retained_scheduling_info"]["use_retained_scheduling_info"]
            . "',  ";
    } else {
        $rq .= "use_retained_scheduling_info = '2', ";
    }

    if (isset($ret["retained_contact_host_attribute_mask"]) && $ret["retained_contact_host_attribute_mask"] != null) {
        $rq .= "retained_contact_host_attribute_mask = '"
            . htmlentities($ret["retained_contact_host_attribute_mask"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "retained_contact_host_attribute_mask = NULL, ";
    }

    if (
        isset($ret["retained_contact_service_attribute_mask"])
        && $ret["retained_contact_service_attribute_mask"] != null
    ) {
        $rq .= "retained_contact_service_attribute_mask = '"
            . htmlentities($ret["retained_contact_service_attribute_mask"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "retained_contact_service_attribute_mask = NULL, ";
    }

    if (isset($ret["retained_process_host_attribute_mask"]) && $ret["retained_process_host_attribute_mask"] != null) {
        $rq .= "retained_process_host_attribute_mask = '"
            . htmlentities($ret["retained_process_host_attribute_mask"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "retained_process_host_attribute_mask = NULL, ";
    }

    if (
        isset($ret["retained_process_service_attribute_mask"])
        && $ret["retained_process_service_attribute_mask"] != null
    ) {
        $rq .= "retained_process_service_attribute_mask = '"
            . htmlentities($ret["retained_process_service_attribute_mask"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "retained_process_service_attribute_mask = NULL, ";
    }

    if (isset($ret["retained_host_attribute_mask"]) && $ret["retained_host_attribute_mask"] != null) {
        $rq .= "retained_host_attribute_mask = '"
            . htmlentities($ret["retained_host_attribute_mask"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "retained_host_attribute_mask = NULL, ";
    }

    if (isset($ret["retained_service_attribute_mask"]) && $ret["retained_service_attribute_mask"] != null) {
        $rq .= "retained_service_attribute_mask = '"
            . htmlentities($ret["retained_service_attribute_mask"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "retained_service_attribute_mask = NULL, ";
    }

    if (isset($ret["use_syslog"]["use_syslog"]) && $ret["use_syslog"]["use_syslog"] != 2) {
        $rq .= "use_syslog = '" . $ret["use_syslog"]["use_syslog"] . "',  ";
    } else {
        $rq .= "use_syslog = '2', ";
    }

    if (isset($ret["log_notifications"]["log_notifications"]) && $ret["log_notifications"]["log_notifications"] != 2) {
        $rq .= "log_notifications = '" . $ret["log_notifications"]["log_notifications"] . "',  ";
    } else {
        $rq .= "log_notifications = '2', ";
    }

    if (
        isset($ret["log_service_retries"]["log_service_retries"])
        && $ret["log_service_retries"]["log_service_retries"] != 2
    ) {
        $rq .= "log_service_retries = '" . $ret["log_service_retries"]["log_service_retries"] . "',  ";
    } else {
        $rq .= "log_service_retries = '2', ";
    }

    if (isset($ret["log_host_retries"]["log_host_retries"]) && $ret["log_host_retries"]["log_host_retries"] != 2) {
        $rq .= "log_host_retries = '" . $ret["log_host_retries"]["log_host_retries"] . "',  ";
    } else {
        $rq .= "log_host_retries = '2', ";
    }

    if (
        isset($ret["log_event_handlers"]["log_event_handlers"])
        && $ret["log_event_handlers"]["log_event_handlers"] != 2
    ) {
        $rq .= "log_event_handlers = '" . $ret["log_event_handlers"]["log_event_handlers"] . "',  ";
    } else {
        $rq .= "log_event_handlers = '2', ";
    }

    if (
        isset($ret["log_external_commands"]["log_external_commands"])
        && $ret["log_external_commands"]["log_external_commands"] != 2
    ) {
        $rq .= "log_external_commands = '" . $ret["log_external_commands"]["log_external_commands"] . "',  ";
    } else {
        $rq .= "log_external_commands = '2', ";
    }

    if (
        isset($ret["log_passive_checks"]["log_passive_checks"])
        && $ret["log_passive_checks"]["log_passive_checks"] != 2
    ) {
        $rq .= "log_passive_checks = '" . $ret["log_passive_checks"]["log_passive_checks"] . "',  ";
    } else {
        $rq .= "log_passive_checks = '2', ";
    }

    if (isset($ret["global_host_event_handler"]) && $ret["global_host_event_handler"] != null) {
        $rq .= "global_host_event_handler = '" . $ret["global_host_event_handler"] . "',  ";
    } else {
        $rq .= "global_host_event_handler = NULL, ";
    }

    if (isset($ret["global_service_event_handler"]) && $ret["global_service_event_handler"] != null) {
        $rq .= "global_service_event_handler = '" . $ret["global_service_event_handler"] . "',  ";
    } else {
        $rq .= "global_service_event_handler = NULL, ";
    }

    if (isset($ret["sleep_time"]) && $ret["sleep_time"] != null) {
        $rq .= "sleep_time = '" . htmlentities($ret["sleep_time"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "sleep_time = NULL, ";
    }

    if (isset($ret["service_inter_check_delay_method"]) && $ret["service_inter_check_delay_method"] != null) {
        $rq .= "service_inter_check_delay_method = '" . $ret["service_inter_check_delay_method"] . "',  ";
    } else {
        $rq .= "service_inter_check_delay_method = NULL, ";
    }

    if (isset($ret["max_service_check_spread"]) && $ret["max_service_check_spread"] != null) {
        $rq .= "max_service_check_spread = '"
            . htmlentities($ret["max_service_check_spread"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "max_service_check_spread = NULL, ";
    }

    if (isset($ret["service_interleave_factor"]) && $ret["service_interleave_factor"] != null) {
        $rq .= "service_interleave_factor = '"
            . htmlentities($ret["service_interleave_factor"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "service_interleave_factor = '2', ";
    }

    if (isset($ret["max_concurrent_checks"]) && $ret["max_concurrent_checks"] != null) {
        $rq .= "max_concurrent_checks = '" . htmlentities($ret["max_concurrent_checks"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "max_concurrent_checks = NULL, ";
    }

    if (isset($ret["check_result_reaper_frequency"]) && $ret["check_result_reaper_frequency"] != null) {
        $rq .= "check_result_reaper_frequency = '"
            . htmlentities($ret["check_result_reaper_frequency"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "check_result_reaper_frequency = NULL, ";
    }

    if (isset($ret["max_check_result_reaper_time"]) && $ret["max_check_result_reaper_time"] != null) {
        $rq .= "max_check_result_reaper_time = '"
            . htmlentities($ret["max_check_result_reaper_time"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "max_check_result_reaper_time = NULL, ";
    }

    if (isset($ret["host_inter_check_delay_method"]) && $ret["host_inter_check_delay_method"] != null) {
        $rq .= "host_inter_check_delay_method  = '" . $ret["host_inter_check_delay_method"] . "',  ";
    } else {
        $rq .= "host_inter_check_delay_method  = NULL, ";
    }

    if (isset($ret["max_host_check_spread"]) && $ret["max_host_check_spread"] != null) {
        $rq .= "max_host_check_spread = '"
            . htmlentities($ret["max_host_check_spread"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "max_host_check_spread = NULL, ";
    }

    if (
        isset($ret["auto_reschedule_checks"]["auto_reschedule_checks"])
        && $ret["auto_reschedule_checks"]["auto_reschedule_checks"] != 2
    ) {
        $rq .= "auto_reschedule_checks = '" . $ret["auto_reschedule_checks"]["auto_reschedule_checks"] . "',  ";
    } else {
        $rq .= "auto_reschedule_checks = '2', ";
    }

    if (isset($ret["auto_rescheduling_interval"]) && $ret["auto_rescheduling_interval"] != null) {
        $rq .= "auto_rescheduling_interval = '"
            . htmlentities($ret["auto_rescheduling_interval"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "auto_rescheduling_interval = NULL, ";
    }

    if (isset($ret["auto_rescheduling_window"]) && $ret["auto_rescheduling_window"] != null) {
        $rq .= "auto_rescheduling_window = '"
            . htmlentities($ret["auto_rescheduling_window"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "auto_rescheduling_window = NULL, ";
    }

    if (
        isset($ret["enable_predictive_host_dependency_checks"]["enable_predictive_host_dependency_checks"])
        && $ret["enable_predictive_host_dependency_checks"]["enable_predictive_host_dependency_checks"] != 2
    ) {
        $rq .= "enable_predictive_host_dependency_checks = '"
            . $ret["enable_predictive_host_dependency_checks"]["enable_predictive_host_dependency_checks"]
            . "',  ";
    } else {
        $rq .= "enable_predictive_host_dependency_checks = '2', ";
    }

    if (
        isset($ret["enable_flap_detection"]["enable_flap_detection"])
        && $ret["enable_flap_detection"]["enable_flap_detection"] != 2
    ) {
        $rq .= "enable_flap_detection = '" . $ret["enable_flap_detection"]["enable_flap_detection"] . "',  ";
    } else {
        $rq .= "enable_flap_detection = '2', ";
    }

    if (isset($ret["low_service_flap_threshold"]) && $ret["low_service_flap_threshold"] != null) {
        $rq .= "low_service_flap_threshold = '"
            . htmlentities($ret["low_service_flap_threshold"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "low_service_flap_threshold = NULL, ";
    }

    if (isset($ret["high_service_flap_threshold"]) && $ret["high_service_flap_threshold"] != null) {
        $rq .= "high_service_flap_threshold = '"
            . htmlentities($ret["high_service_flap_threshold"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "high_service_flap_threshold = NULL, ";
    }

    if (isset($ret["low_host_flap_threshold"]) && $ret["low_host_flap_threshold"] != null) {
        $rq .= "low_host_flap_threshold = '"
            . htmlentities($ret["low_host_flap_threshold"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "low_host_flap_threshold = NULL, ";
    }

    if (isset($ret["high_host_flap_threshold"]) && $ret["high_host_flap_threshold"] != null) {
        $rq .= "high_host_flap_threshold = '"
            . htmlentities($ret["high_host_flap_threshold"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "high_host_flap_threshold = NULL, ";
    }

    if (
        isset($ret["soft_state_dependencies"]["soft_state_dependencies"])
        && $ret["soft_state_dependencies"]["soft_state_dependencies"] != 2
    ) {
        $rq .= "soft_state_dependencies = '" . $ret["soft_state_dependencies"]["soft_state_dependencies"] . "',  ";
    } else {
        $rq .= "soft_state_dependencies = '2', ";
    }

    if (
        isset($ret["enable_predictive_service_dependency_checks"]["enable_predictive_service_dependency_checks"])
        && $ret["enable_predictive_service_dependency_checks"]["enable_predictive_service_dependency_checks"] != 2
    ) {
        $rq .= "enable_predictive_service_dependency_checks = '"
            . $ret["enable_predictive_service_dependency_checks"]["enable_predictive_service_dependency_checks"]
            . "',  ";
    } else {
        $rq .= "enable_predictive_service_dependency_checks = '2', ";
    }

    if (isset($ret["service_check_timeout"]) && $ret["service_check_timeout"] != null) {
        $rq .= "service_check_timeout = '" . htmlentities($ret["service_check_timeout"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "service_check_timeout = NULL, ";
    }

    if (isset($ret["host_check_timeout"]) && $ret["host_check_timeout"] != null) {
        $rq .= "host_check_timeout = '" . htmlentities($ret["host_check_timeout"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "host_check_timeout = NULL, ";
    }

    if (isset($ret["event_handler_timeout"]) && $ret["event_handler_timeout"] != null) {
        $rq .= "event_handler_timeout = '" . htmlentities($ret["event_handler_timeout"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "event_handler_timeout = NULL, ";
    }

    if (isset($ret["notification_timeout"]) && $ret["notification_timeout"] != null) {
        $rq .= "notification_timeout = '" . htmlentities($ret["notification_timeout"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "notification_timeout = NULL, ";
    }

    if (isset($ret["ocsp_timeout"]) && $ret["ocsp_timeout"] != null) {
        $rq .= "ocsp_timeout = '" . htmlentities($ret["ocsp_timeout"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "ocsp_timeout = NULL, ";
    }

    if (isset($ret["ochp_timeout"]) && $ret["ochp_timeout"] != null) {
        $rq .= "ochp_timeout = '" . htmlentities($ret["ochp_timeout"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "ochp_timeout = NULL, ";
    }

    if (isset($ret["perfdata_timeout"]) && $ret["perfdata_timeout"] != null) {
        $rq .= "perfdata_timeout = '" . htmlentities($ret["perfdata_timeout"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "perfdata_timeout = NULL, ";
    }

    if (
        isset($ret["obsess_over_services"]["obsess_over_services"])
        && $ret["obsess_over_services"]["obsess_over_services"] != 2
    ) {
        $rq .= "obsess_over_services = '" . $ret["obsess_over_services"]["obsess_over_services"] . "',  ";
    } else {
        $rq .= "obsess_over_services = '2', ";
    }

    if (isset($ret["ocsp_command"]) && $ret["ocsp_command"] != null) {
        $rq .= "ocsp_command = '" . htmlentities($ret["ocsp_command"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "ocsp_command = NULL, ";
    }

    if (
        isset($ret["obsess_over_hosts"]["obsess_over_hosts"])
        && $ret["obsess_over_hosts"]["obsess_over_hosts"] != 2
    ) {
        $rq .= "obsess_over_hosts = '" . $ret["obsess_over_hosts"]["obsess_over_hosts"] . "',  ";
    } else {
        $rq .= "obsess_over_hosts = '2', ";
    }

    if (isset($ret["ochp_command"]) && $ret["ochp_command"] != null) {
        $rq .= "ochp_command = '" . htmlentities($ret["ochp_command"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "ochp_command = NULL, ";
    }

    if (
        isset($ret["process_performance_data"]["process_performance_data"])
        && $ret["process_performance_data"]["process_performance_data"] != 2
    ) {
        $rq .= "process_performance_data = '" . $ret["process_performance_data"]["process_performance_data"] . "',  ";
    } else {
        $rq .= "process_performance_data = '2', ";
    }

    if (isset($ret["host_perfdata_command"]) && $ret["host_perfdata_command"] != null) {
        $rq .= "host_perfdata_command = '" . htmlentities($ret["host_perfdata_command"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "host_perfdata_command = NULL, ";
    }

    if (isset($ret["service_perfdata_command"]) && $ret["service_perfdata_command"] != null) {
        $rq .= "service_perfdata_command = '"
            . htmlentities($ret["service_perfdata_command"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "service_perfdata_command = NULL, ";
    }

    if (isset($ret["host_perfdata_file"]) && $ret["host_perfdata_file"] != null) {
        $rq .= "host_perfdata_file = '" . htmlentities($ret["host_perfdata_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "host_perfdata_file = NULL, ";
    }

    if (isset($ret["service_perfdata_file"]) && $ret["service_perfdata_file"] != null) {
        $rq .= "service_perfdata_file = '" . htmlentities($ret["service_perfdata_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "service_perfdata_file = NULL, ";
    }

    if (isset($ret["host_perfdata_file_template"]) && $ret["host_perfdata_file_template"] != null) {
        $rq .= "host_perfdata_file_template = " . $pearDB->quote($ret["host_perfdata_file_template"]) . ",  ";
    } else {
        $rq .= "host_perfdata_file_template = NULL, ";
    }

    if (isset($ret["service_perfdata_file_template"]) && $ret["service_perfdata_file_template"] != null) {
        $rq .= "service_perfdata_file_template = " . $pearDB->quote($ret["service_perfdata_file_template"]) . ",  ";
    } else {
        $rq .= "service_perfdata_file_template = NULL, ";
    }

    if (
        isset($ret["host_perfdata_file_mode"]["host_perfdata_file_mode"])
        && $ret["host_perfdata_file_mode"]["host_perfdata_file_mode"] != null
    ) {
        $rq .= "host_perfdata_file_mode  = '" . $ret["host_perfdata_file_mode"]["host_perfdata_file_mode"] . "',  ";
    } else {
        $rq .= "host_perfdata_file_mode  = NULL, ";
    }

    if (
        isset($ret["service_perfdata_file_mode"]["service_perfdata_file_mode"])
        && $ret["service_perfdata_file_mode"]["service_perfdata_file_mode"] != null
    ) {
        $rq .= "service_perfdata_file_mode  = '"
            . $ret["service_perfdata_file_mode"]["service_perfdata_file_mode"]
            . "',  ";
    } else {
        $rq .= "service_perfdata_file_mode  = NULL, ";
    }

    if (
        isset($ret["host_perfdata_file_processing_interval"])
        && $ret["host_perfdata_file_processing_interval"] != null
    ) {
        $rq .= "host_perfdata_file_processing_interval  = '"
            . htmlentities($ret["host_perfdata_file_processing_interval"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "host_perfdata_file_processing_interval = NULL, ";
    }

    if (
        isset($ret["service_perfdata_file_processing_interval"])
        && $ret["service_perfdata_file_processing_interval"] != null
    ) {
        $rq .= "service_perfdata_file_processing_interval  = '"
            . htmlentities($ret["service_perfdata_file_processing_interval"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "service_perfdata_file_processing_interval = NULL, ";
    }

    if (
        isset($ret["host_perfdata_file_processing_command"])
        && $ret["host_perfdata_file_processing_command"] != null
    ) {
        $rq .= "host_perfdata_file_processing_command  = '"
            . htmlentities($ret["host_perfdata_file_processing_command"])
            . "',  ";
    } else {
        $rq .= "host_perfdata_file_processing_command  = NULL, ";
    }

    if (
        isset($ret["service_perfdata_file_processing_command"])
        && $ret["service_perfdata_file_processing_command"] != null
    ) {
        $rq .= "service_perfdata_file_processing_command  = '"
            . htmlentities($ret["service_perfdata_file_processing_command"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "service_perfdata_file_processing_command  = NULL, ";
    }

    if (
        isset($ret["check_for_orphaned_services"]["check_for_orphaned_services"])
        && $ret["check_for_orphaned_services"]["check_for_orphaned_services"] != 2
    ) {
        $rq .= "check_for_orphaned_services = '"
            . $ret["check_for_orphaned_services"]["check_for_orphaned_services"]
            . "',  ";
    } else {
        $rq .= "check_for_orphaned_services = '2', ";
    }

    if (
        isset($ret["check_service_freshness"]["check_service_freshness"])
        && $ret["check_service_freshness"]["check_service_freshness"] != 2
    ) {
        $rq .= "check_service_freshness = '"
            . $ret["check_service_freshness"]["check_service_freshness"]
            . "',  ";
    } else {
        $rq .= "check_service_freshness = '2', ";
    }

    if (isset($ret["service_freshness_check_interval"]) && $ret["service_freshness_check_interval"] != null) {
        $rq .= "service_freshness_check_interval  = '"
            . htmlentities($ret["service_freshness_check_interval"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "service_freshness_check_interval  = NULL, ";
    }

    if (isset($ret["cached_host_check_horizon"]) && $ret["cached_host_check_horizon"] != null) {
        $rq .= "cached_host_check_horizon  = '"
            . htmlentities($ret["cached_host_check_horizon"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "cached_host_check_horizon  = NULL, ";
    }

    if (isset($ret["cached_service_check_horizon"]) && $ret["cached_service_check_horizon"] != null) {
        $rq .= "cached_service_check_horizon  = '"
            . htmlentities($ret["cached_service_check_horizon"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "cached_service_check_horizon  = NULL, ";
    }

    if (isset($ret["additional_freshness_latency"]) && $ret["additional_freshness_latency"] != null) {
        $rq .= "additional_freshness_latency  = '"
            . htmlentities($ret["additional_freshness_latency"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "additional_freshness_latency  = NULL, ";
    }

    if (
        isset($ret["check_host_freshness"]["check_host_freshness"])
        && $ret["check_host_freshness"]["check_host_freshness"] != 2
    ) {
        $rq .= "check_host_freshness = '" . $ret["check_host_freshness"]["check_host_freshness"] . "',  ";
    } else {
        $rq .= "check_host_freshness = '2', ";
    }

    if (isset($ret["host_freshness_check_interval"]) && $ret["host_freshness_check_interval"] != null) {
        $rq .= "host_freshness_check_interval  = '"
            . htmlentities($ret["host_freshness_check_interval"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "host_freshness_check_interval  = NULL, ";
    }

    if (
        isset($ret["instance_heartbeat_interval"])
        && $ret["instance_heartbeat_interval"] != null
        && is_numeric($ret["instance_heartbeat_interval"])
    ) {
        $rq .= "instance_heartbeat_interval = " . (int)$ret["instance_heartbeat_interval"] . ",  ";
    } else {
        $rq .= "instance_heartbeat_interval = 30, ";
    }

    if (isset($ret["date_format"]) && $ret["date_format"] != null) {
        $rq .= "date_format  = '" . htmlentities($ret["date_format"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "date_format  = NULL, ";
    }

    if (isset($ret["illegal_object_name_chars"]) && $ret["illegal_object_name_chars"] != null) {
        $rq .= "illegal_object_name_chars = " . $pearDB->quote($ret["illegal_object_name_chars"]) . ",  ";
    } else {
        $rq .= "illegal_object_name_chars = NULL, ";
    }

    if (isset($ret["illegal_macro_output_chars"]) && $ret["illegal_macro_output_chars"] != null) {
        $rq .= "illegal_macro_output_chars = " . $pearDB->quote($ret["illegal_macro_output_chars"]) . ",  ";
    } else {
        $rq .= "illegal_macro_output_chars = NULL, ";
    }

    if (
        isset($ret["use_large_installation_tweaks"]["use_large_installation_tweaks"])
        && $ret["use_large_installation_tweaks"]["use_large_installation_tweaks"] != 2
    ) {
        $rq .= "use_large_installation_tweaks = '"
            . $ret["use_large_installation_tweaks"]["use_large_installation_tweaks"]
            . "',  ";
    } else {
        $rq .= "use_large_installation_tweaks = '2', ";
    }

    if (
        isset($ret["enable_environment_macros"]["enable_environment_macros"])
        && $ret["enable_environment_macros"]["enable_environment_macros"] != 2
    ) {
        $rq .= "enable_environment_macros = '"
            . $ret["enable_environment_macros"]["enable_environment_macros"]
            . "',  ";
    } else {
        $rq .= "use_large_installation_tweaks = '2', ";
    }

    if (isset($ret["use_setpgid"]["use_setpgid"]) && $ret["use_setpgid"]["use_setpgid"] != 2) {
        $rq .= "use_setpgid = '" . $ret["use_setpgid"]["use_setpgid"] . "',  ";
    } else {
        $rq .= "use_setpgid = '2', ";
    }

    if (
        isset($ret["use_regexp_matching"]["use_regexp_matching"])
        && $ret["use_regexp_matching"]["use_regexp_matching"] != 2
    ) {
        $rq .= "use_regexp_matching = '" . $ret["use_regexp_matching"]["use_regexp_matching"] . "',  ";
    } else {
        $rq .= "use_regexp_matching = '2', ";
    }

    if (
        isset($ret["use_true_regexp_matching"]["use_true_regexp_matching"])
        && $ret["use_true_regexp_matching"]["use_true_regexp_matching"] != 2
    ) {
        $rq .= "use_true_regexp_matching = '" . $ret["use_true_regexp_matching"]["use_true_regexp_matching"] . "',  ";
    } else {
        $rq .= "use_true_regexp_matching = '2', ";
    }

    if (isset($ret["admin_email"]) && $ret["admin_email"] != null) {
        $rq .= "admin_email = '" . htmlentities($ret["admin_email"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "admin_email = NULL, ";
    }

    if (isset($ret["admin_pager"]) && $ret["admin_pager"] != null) {
        $rq .= "admin_pager = '" . htmlentities($ret["admin_pager"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "admin_pager = NULL, ";
    }

    if (isset($ret["nagios_comment"]) && $ret["nagios_comment"] != null) {
        $rq .= "nagios_comment = '" . htmlentities($ret["nagios_comment"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "nagios_comment = NULL, ";
    }

    /* Calculate the sum of bitwise */
    if (isset($ret["event_broker_options"]) && $ret["event_broker_options"] != null) {
        $rq .= "event_broker_options = '" . calculateBitwise($ret['event_broker_options']) . "', ";
    } else {
        $rq .= "event_broker_options = '-1', ";
    }

    if (isset($ret["debug_file"]) && $ret["debug_file"] != null) {
        $rq .= "debug_file = '" . htmlentities($ret["debug_file"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "debug_file = NULL, ";
    }

    $level = 0;
    if (isset($ret["nagios_debug_level"]) && $ret["nagios_debug_level"] != null) {
        foreach ($ret["nagios_debug_level"] as $key => $value) {
            $level += $key;
        }
    }

    $rq .= "debug_level = '" . $level . "', ";
    if (isset($ret["nagios_debug_level"]) && $ret["nagios_debug_level"] != null) {
        $rq .= "debug_level_opt = '" . implode(",", array_keys($ret["nagios_debug_level"])) . "',  ";
    } else {
        $rq .= "debug_level = NULL, ";
    }

    if (isset($ret["debug_verbosity"]) && $ret["debug_verbosity"] != 2) {
        $rq .= "debug_verbosity = '" . $ret["debug_verbosity"] . "',  ";
    } else {
        $rq .= "debug_verbosity = '2', ";
    }

    if (isset($ret["max_debug_file_size"]) && $ret["max_debug_file_size"] != null) {
        $rq .= "max_debug_file_size = '" . htmlentities($ret["max_debug_file_size"], ENT_QUOTES, "UTF-8") . "',  ";
    } else {
        $rq .= "max_debug_file_size = NULL, ";
    }

    if (isset($ret["daemon_dumps_core"]["daemon_dumps_core"]) && $ret["daemon_dumps_core"]["daemon_dumps_core"]) {
        $rq .= "daemon_dumps_core = '1',  ";
    } else {
        $rq .= "daemon_dumps_core = '0', ";
    }

    if (
        isset($ret["translate_passive_host_checks"]["translate_passive_host_checks"])
        && $ret["translate_passive_host_checks"]["translate_passive_host_checks"] != null
    ) {
        $rq .= "translate_passive_host_checks = '"
            . htmlentities($ret["translate_passive_host_checks"]["translate_passive_host_checks"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "translate_passive_host_checks = NULL, ";
    }

    if (
        isset($ret["passive_host_checks_are_soft"]["passive_host_checks_are_soft"])
        && $ret["passive_host_checks_are_soft"]["passive_host_checks_are_soft"] != null
    ) {
        $rq .= "passive_host_checks_are_soft = '"
            . htmlentities($ret["passive_host_checks_are_soft"]["passive_host_checks_are_soft"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "passive_host_checks_are_soft = NULL, ";
    }

    if (
        isset($ret["check_for_orphaned_hosts"]["check_for_orphaned_hosts"])
        && $ret["check_for_orphaned_hosts"]["check_for_orphaned_hosts"] != null
    ) {
        $rq .= "check_for_orphaned_hosts = '"
            . htmlentities($ret["check_for_orphaned_hosts"]["check_for_orphaned_hosts"], ENT_QUOTES, "UTF-8")
            . "',  ";
    } else {
        $rq .= "check_for_orphaned_hosts = NULL, ";
    }

    if (isset($ret["external_command_buffer_slots"]) && $ret["external_command_buffer_slots"] != null) {
        $rq .= "external_command_buffer_slots = '"
            . htmlentities($ret["external_command_buffer_slots"], ENT_QUOTES, "UTF-8")
            . "', ";
    } else {
        $rq .= "external_command_buffer_slots = NULL, ";
    }

    if (isset($ret["cfg_file"]) && $ret["cfg_file"] != null) {
        $rq .= "cfg_file = '" . htmlentities($ret["cfg_file"], ENT_QUOTES, "UTF-8") . "', ";
    } else {
        $rq .= "cfg_file = NULL, ";
    }

    isset($ret["log_pid"]["log_pid"]) && $ret["log_pid"]["log_pid"]
        ? $rq .= "log_pid = '1', "
        : $rq .= "log_pid = '0', ";

    if (isset($ret["log_pid"]["log_pid"]) && $ret["log_pid"]["log_pid"]) {
        $rq .= "log_pid = '1',  ";
    } else {
        $rq .= "log_pid = '0', ";
    }

    if (
        isset($ret['enable_macros_filter']['enable_macros_filter'])
        && $ret['enable_macros_filter']['enable_macros_filter']
    ) {
        $rq .= "enable_macros_filter = '1', ";
    } else {
        $rq .= "enable_macros_filter = '0', ";
    }

    /* Add whitelist macros to send to Centreon Broker */
    if (isset($_REQUEST['macros_filter'])) {
        $macrosFilter = trim(
            join(
                ',',
                array_map(function ($value) {
                    return CentreonDB::escape($value);
                }, $_REQUEST['macros_filter'])
            )
        );
        $rq .= "macros_filter = '" . $macrosFilter . "', ";
    } else {
        $rq .= "macros_filter = NULL, ";
    }

    $rq .= "nagios_activate = '" . $ret["nagios_activate"]["nagios_activate"] . "' ";
    $rq .= "WHERE nagios_id = '" . $nagios_id . "'";
    $dbResult = $pearDB->query($rq);

    $mainCfg = new CentreonConfigEngine($pearDB);
    if (isset($_REQUEST['in_broker'])) {
        $mainCfg->insertBrokerDirectives($nagios_id, $_REQUEST['in_broker']);
    } else {
        $mainCfg->insertBrokerDirectives($nagios_id);
    }

    if ($ret["nagios_activate"]["nagios_activate"]) {
        enableNagiosInDB($nagios_id);
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "engine",
        $nagios_id,
        $pearDB->escape($ret["nagios_name"]),
        "c",
        $fields
    );
}
