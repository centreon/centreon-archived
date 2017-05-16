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

function testExistence($name = null)
{
    global $pearDB, $form;

    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('nagios_id');
    }

    $DBRESULT = $pearDB->query(
        "SELECT nagios_name, nagios_id FROM cfg_nagios WHERE nagios_name = '"
        . htmlentities($name, ENT_QUOTES, "UTF-8") . "'"
    );
    $nagios = $DBRESULT->fetchRow();
    if ($DBRESULT->numRows() >= 1 && $nagios["nagios_id"] == $id) {
        return true;
    } elseif ($DBRESULT->numRows() >= 1 && $nagios["nagios_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

function enableNagiosInDB($nagios_id = null)
{
    global $pearDB, $centreon;
    if (!$nagios_id) {
        return;
    }

    $DBRESULT = $pearDB->query(
        "SELECT `nagios_server_id` FROM cfg_nagios WHERE nagios_id = '".$nagios_id."'"
    );
    $data = $DBRESULT->fetchRow();

    $DBRESULT = $pearDB->query(
        "UPDATE `cfg_nagios` SET `nagios_activate` = '0' WHERE `nagios_server_id` = '".$data["nagios_server_id"]."'"
    );

    $DBRESULT = $pearDB->query(
        "UPDATE cfg_nagios SET nagios_activate = '1' WHERE nagios_id = '".$nagios_id."'"
    );
    $centreon->Nagioscfg = array();
}

function disableNagiosInDB($nagios_id = null)
{
    global $pearDB, $centreon;

    if (!$nagios_id) {
        return;
    }

    $DBRESULT = $pearDB->query(
        "SELECT `nagios_server_id` FROM cfg_nagios WHERE nagios_id = '".$nagios_id."'"
    );
    $data = $DBRESULT->fetchRow();

    $DBRESULT = $pearDB->query(
        "UPDATE cfg_nagios SET nagios_activate = '0' WHERE `nagios_server_id` = '".$data["nagios_server_id"]."'"
    );

    $DBRESULT = $pearDB->query(
        "SELECT MAX(nagios_id) FROM cfg_nagios WHERE nagios_id != '".$nagios_id."'"
    );
    $maxId = $DBRESULT->fetchRow();
    if (isset($maxId["MAX(nagios_id)"])) {
        $DBRESULT2 = $pearDB->query(
            "UPDATE cfg_nagios SET nagios_activate = '1' WHERE nagios_id = '".$maxId["MAX(nagios_id)"]."'"
        );
        $centreon->Nagioscfg = array();
        $DBRESULT2 = $pearDB->query(
            "SELECT * FROM `cfg_nagios` WHERE `nagios_activate` = '1' LIMIT 1"
        );
        $centreon->Nagioscfg = $DBRESULT->fetchRow();
        $DBRESULT2->free();
    }
}

function deleteNagiosInDB($nagios = array())
{
    global $pearDB;

    foreach ($nagios as $key => $value) {
        $DBRESULT = $pearDB->query(
            "DELETE FROM cfg_nagios WHERE nagios_id = '".$key."'"
        );
        $DBRESULT = $pearDB->query(
            "DELETE FROM cfg_nagios_broker_module WHERE cfg_nagios_id = '".$key."'"
        );
    }
    $DBRESULT = $pearDB->query(
        "SELECT nagios_id FROM cfg_nagios WHERE nagios_activate = '1'"
    );
    if (!$DBRESULT->numRows()) {
        $DBRESULT2 = $pearDB->query(
            "SELECT MAX(nagios_id) FROM cfg_nagios"
        );
        $nagios_id = $DBRESULT2->fetchRow();
        $DBRESULT2 = $pearDB->query(
            "UPDATE cfg_nagios SET nagios_activate = '1' WHERE nagios_id = '".$nagios_id["MAX(nagios_id)"]."'"
        );
    }
    $DBRESULT->free();
}

function multipleNagiosInDB($nagios = array(), $nbrDup = array())
{
    foreach ($nagios as $key => $value) {
        global $pearDB;
        $DBRESULT = $pearDB->query(
            "SELECT * FROM cfg_nagios WHERE nagios_id = '".$key."' LIMIT 1"
        );
        $row = $DBRESULT->fetchRow();
        $row["nagios_id"] = '';
        $row["nagios_activate"] = '0';
        $DBRESULT->free();
        $rowBks = array();
        $DBRESULT = $pearDB->query(
            "SELECT * FROM cfg_nagios_broker_module WHERE cfg_nagios_id='".$key."'"
        );
        while ($rowBk = $DBRESULT->fetchRow()) {
            $rowBks[] = $rowBk;
        }
        $DBRESULT->free();
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "nagios_name" ? ($nagios_name = $value2 = $value2."_".$i) : null;
                $val ? $val .= ($value2!=null?(", '". $pearDB->escape($value2) ."'"):", NULL")
                    : $val .= ($value2!=null?("'". $pearDB->escape($value2) ."'"):"NULL");
            }
            if (testExistence($nagios_name)) {
                $val ? $rq = "INSERT INTO cfg_nagios VALUES (".$val.")" : $rq = null;
                $DBRESULT = $pearDB->query($rq);
                /* Find the new last nagios_id once */
                $DBRESULT = $pearDB->query("SELECT MAX(nagios_id) FROM cfg_nagios");
                $nagios_id = $DBRESULT->fetchRow();
                $DBRESULT->free();
                foreach ($rowBks as $keyBk => $valBk) {
                    if ($valBk["broker_module"]) {
                        $rqBk = "INSERT INTO cfg_nagios_broker_module (`cfg_nagios_id`, `broker_module`) VALUES ('"
                        . $nagios_id["MAX(nagios_id)"] . "', '" . $valBk["broker_module"] . "')";
                    }
                    $DBRESULT = $pearDB->query($rqBk);
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

function insertNagios($ret = array(), $brokerTab = array())
{
    global $form, $pearDB, $centreon;

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    
    $rq = "INSERT INTO cfg_nagios ("
        . "`nagios_id` , `nagios_name` , `nagios_server_id`, `log_file` , `cfg_dir` , "
        . "`temp_file` , "
        . "`check_result_path`, `max_check_result_file_age`, "
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
        . "`log_service_retries` , `log_host_retries` , `log_event_handlers` , `log_initial_states` , "
        . "`log_external_commands` , `log_passive_checks` , `global_host_event_handler` , "
        . "`global_service_event_handler` , `sleep_time` , `service_inter_check_delay_method` , "
        . "`host_inter_check_delay_method` , `service_interleave_factor` ,"
        . " `max_concurrent_checks` , `max_service_check_spread` , "
        . "`max_host_check_spread` , `check_result_reaper_frequency` , `max_check_result_reaper_time`, "
        . "`auto_reschedule_checks` , `auto_rescheduling_interval` , "
        . "`auto_rescheduling_window` , `use_aggressive_host_checking` , "
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
        . "`check_host_freshness` , `host_freshness_check_interval` , `date_format` , "
        . "`illegal_object_name_chars` , `illegal_macro_output_chars`, "
        . "`use_large_installation_tweaks` , `debug_file` , `debug_level` , "
        . "`debug_level_opt`, `debug_verbosity` , `max_debug_file_size` , `daemon_dumps_core`, "
        . "`enable_environment_macros` , `use_setpgid`, `use_regexp_matching` , `use_true_regexp_matching` , "
        . "`admin_email` , `admin_pager` , `nagios_comment` , `nagios_activate`, "
        . "`event_broker_options` , `translate_passive_host_checks`, "
        . "`passive_host_checks_are_soft`, `check_for_orphaned_hosts`, `external_command_buffer_slots`, "
        . "`cfg_file`, `log_pid`, `use_check_result_path`) ";
    $rq .= "VALUES (";
    $rq .= "NULL, ";
    isset($ret["nagios_name"]) && $ret["nagios_name"] != null ?
        $rq .= "'".htmlentities($ret["nagios_name"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
    isset($ret["nagios_server_id"]) && $ret["nagios_server_id"] != null ?
        $rq .= "'".htmlentities($ret["nagios_server_id"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
    isset($ret["log_file"]) && $ret["log_file"] != null ?
        $rq .= "'".htmlentities($ret["log_file"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
    isset($ret["cfg_dir"]) && $ret["cfg_dir"] != null ?
        $rq .= "'".htmlentities($ret["cfg_dir"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["temp_file"]) && $ret["temp_file"] != null ?
        $rq .= "'".htmlentities($ret["temp_file"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["check_result_path"]) && $ret["check_result_path"] != null ?
        $rq .= "'".htmlentities($ret["check_result_path"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["max_check_result_file_age"]) && $ret["max_check_result_file_age"] != null ?
        $rq .= "'".htmlentities($ret["max_check_result_file_age"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["status_file"]) && $ret["status_file"] != null ?
        $rq .= "'".htmlentities($ret["status_file"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["status_update_interval"]) && $ret["status_update_interval"] != null ?
        $rq .= "'".(int)$ret["status_update_interval"]."',  " : $rq .= "NULL, ";
    isset($ret["nagios_user"]) && $ret["nagios_user"] != null ?
        $rq .= "'".htmlentities($ret["nagios_user"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["nagios_group"]) && $ret["nagios_group"] != null ?
        $rq .= "'".htmlentities($ret["nagios_group"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["enable_notifications"]["enable_notifications"])
        && $ret["enable_notifications"]["enable_notifications"] != 2 ?
        $rq .= "'".$ret["enable_notifications"]["enable_notifications"]."',  " : $rq .= "'2', ";
    isset($ret["execute_service_checks"]["execute_service_checks"])
        && $ret["execute_service_checks"]["execute_service_checks"] != 2 ?
        $rq .= "'".$ret["execute_service_checks"]["execute_service_checks"]."',  " : $rq .= "'2', ";
    isset($ret["accept_passive_service_checks"]["accept_passive_service_checks"])
        && $ret["accept_passive_service_checks"]["accept_passive_service_checks"] != 2 ?
        $rq .= "'".$ret["accept_passive_service_checks"]["accept_passive_service_checks"]."',  " : $rq .= "'2', ";
    isset($ret["execute_host_checks"]["execute_host_checks"])
        && $ret["execute_host_checks"]["execute_host_checks"] != 2 ?
        $rq .= "'".$ret["execute_host_checks"]["execute_host_checks"]."',  " : $rq .= "'2', ";
    isset($ret["accept_passive_host_checks"]["accept_passive_host_checks"])
        && $ret["accept_passive_host_checks"]["accept_passive_host_checks"] != 2 ?
        $rq .= "'".$ret["accept_passive_host_checks"]["accept_passive_host_checks"]."',  " : $rq .= "'2', ";
    isset($ret["enable_event_handlers"]["enable_event_handlers"])
        && $ret["enable_event_handlers"]["enable_event_handlers"] != 2 ?
        $rq .= "'".$ret["enable_event_handlers"]["enable_event_handlers"]."',  " : $rq .= "'2', ";
    isset($ret["log_rotation_method"]["log_rotation_method"])
        && $ret["log_rotation_method"]["log_rotation_method"] != 2 ?
        $rq .= "'".$ret["log_rotation_method"]["log_rotation_method"]."',  " : $rq .= "'2', ";
    isset($ret["log_archive_path"]) && $ret["log_archive_path"] != null ?
        $rq .= "'".htmlentities($ret["log_archive_path"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["check_external_commands"]["check_external_commands"])
        && $ret["check_external_commands"]["check_external_commands"] != 2 ?
        $rq .= "'".$ret["check_external_commands"]["check_external_commands"]."',  " : $rq .= "'2', ";
    isset($ret["command_check_interval"]) && $ret["command_check_interval"] != null ?
        $rq .= "'".htmlentities($ret["command_check_interval"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["command_file"]) && $ret["command_file"] != null ?
        $rq .= "'".htmlentities($ret["command_file"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["downtime_file"]) && $ret["downtime_file"] != null ?
        $rq .= "'".htmlentities($ret["downtime_file"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["comment_file"]) && $ret["comment_file"] != null ?
        $rq .= "'".htmlentities($ret["comment_file"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["lock_file"]) && $ret["lock_file"] != null ?
        $rq .= "'".htmlentities($ret["lock_file"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["retain_state_information"]["retain_state_information"])
        && $ret["retain_state_information"]["retain_state_information"] != 2 ?
        $rq .= "'".$ret["retain_state_information"]["retain_state_information"]."',  " : $rq .= "'2', ";
    isset($ret["state_retention_file"]) && $ret["state_retention_file"] != null ?
        $rq .= "'".htmlentities($ret["state_retention_file"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["retention_update_interval"]) && $ret["retention_update_interval"] != null ?
        $rq .= "'".htmlentities($ret["retention_update_interval"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["use_retained_program_state"]["use_retained_program_state"])
        && $ret["use_retained_program_state"]["use_retained_program_state"] != 2 ?
        $rq .= "'".$ret["use_retained_program_state"]["use_retained_program_state"]."',  " : $rq .= "'2', ";
    isset($ret["use_retained_scheduling_info"]["use_retained_scheduling_info"])
        && $ret["use_retained_scheduling_info"]["use_retained_scheduling_info"] != 2 ?
        $rq .= "'".$ret["use_retained_scheduling_info"]["use_retained_scheduling_info"]."',  " : $rq .= "'2', ";
    isset($ret["retained_contact_host_attribute_mask"]) && $ret["retained_contact_host_attribute_mask"] != null ?
        $rq .= "'".htmlentities($ret["retained_contact_host_attribute_mask"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "NULL, ";
    isset($ret["retained_contact_service_attribute_mask"]) && $ret["retained_contact_service_attribute_mask"] != null ?
        $rq .= "'".htmlentities($ret["retained_contact_service_attribute_mask"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "NULL, ";
    isset($ret["retained_process_host_attribute_mask"]) && $ret["retained_process_host_attribute_mask"] != null ?
        $rq .= "'".htmlentities($ret["retained_process_host_attribute_mask"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "NULL, ";
    isset($ret["retained_process_service_attribute_mask"]) && $ret["retained_process_service_attribute_mask"] != null ?
        $rq .= "'".htmlentities($ret["retained_process_service_attribute_mask"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "NULL, ";
    isset($ret["retained_host_attribute_mask"]) && $ret["retained_host_attribute_mask"] != null ?
        $rq .= "'".htmlentities($ret["retained_host_attribute_mask"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["retained_service_attribute_mask"]) && $ret["retained_service_attribute_mask"] != null ?
        $rq .= "'".htmlentities($ret["retained_service_attribute_mask"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["use_syslog"]["use_syslog"]) && $ret["use_syslog"]["use_syslog"] != 2 ?
        $rq .= "'".$ret["use_syslog"]["use_syslog"]."',  " : $rq .= "'2', ";
    isset($ret["log_notifications"]["log_notifications"]) && $ret["log_notifications"]["log_notifications"] != 2 ?
        $rq .= "'".$ret["log_notifications"]["log_notifications"]."',  " : $rq .= "'2', ";
    isset($ret["log_service_retries"]["log_service_retries"])
        && $ret["log_service_retries"]["log_service_retries"] != 2 ?
        $rq .= "'".$ret["log_service_retries"]["log_service_retries"]."',  " : $rq .= "'2', ";
    isset($ret["log_host_retries"]["log_host_retries"])
        && $ret["log_host_retries"]["log_host_retries"] != 2 ?
        $rq .= "'".$ret["log_host_retries"]["log_host_retries"]."',  " : $rq .= "'2', ";
    isset($ret["log_event_handlers"]["log_event_handlers"])
        && $ret["log_event_handlers"]["log_event_handlers"] != 2 ?
        $rq .= "'".$ret["log_event_handlers"]["log_event_handlers"]."',  " : $rq .= "'2', ";
    isset($ret["log_initial_states"]["log_initial_states"])
        && $ret["log_initial_states"]["log_initial_states"] != 2 ?
        $rq .= "'".$ret["log_initial_states"]["log_initial_states"]."',  " : $rq .= "'2', ";
    isset($ret["log_external_commands"]["log_external_commands"])
        && $ret["log_external_commands"]["log_external_commands"] != 2 ?
        $rq .= "'".$ret["log_external_commands"]["log_external_commands"]."',  " : $rq .= "'2', ";
    isset($ret["log_passive_checks"]["log_passive_checks"])
        && $ret["log_passive_checks"]["log_passive_checks"] != 2 ?
        $rq .= "'".$ret["log_passive_checks"]["log_passive_checks"]."',  " : $rq .= "'2', ";
    isset($ret["global_host_event_handler"]) && $ret["global_host_event_handler"] != null ?
        $rq .= "'".$ret["global_host_event_handler"]."',  " : $rq .= "NULL, ";
    isset($ret["global_service_event_handler"]) && $ret["global_service_event_handler"] != null ?
        $rq .= "'".$ret["global_service_event_handler"]."',  " : $rq .= "NULL, ";
    isset($ret["sleep_time"]) && $ret["sleep_time"] != null ?
        $rq .= "'".htmlentities($ret["sleep_time"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["service_inter_check_delay_method"]) && $ret["service_inter_check_delay_method"] != null ?
        $rq .= "'".$ret["service_inter_check_delay_method"]."',  " : $rq .= "NULL, ";
    isset($ret["host_inter_check_delay_method"]) && $ret["host_inter_check_delay_method"] != null ?
        $rq .= "'".$ret["host_inter_check_delay_method"]."',  " : $rq .= "NULL, ";
    isset($ret["service_interleave_factor"]["service_interleave_factor"])
        && $ret["service_interleave_factor"]["service_interleave_factor"] != 2 ?
        $rq .= "'".$ret["service_interleave_factor"]["service_interleave_factor"]."',  " : $rq .= "'2', ";
    isset($ret["max_concurrent_checks"]) && $ret["max_concurrent_checks"] != null ?
        $rq .= "'".htmlentities($ret["max_concurrent_checks"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["max_service_check_spread"]) && $ret["max_service_check_spread"] != null ?
        $rq .= "'".htmlentities($ret["max_service_check_spread"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["max_host_check_spread"]) && $ret["max_host_check_spread"] != null ?
        $rq .= "'".htmlentities($ret["max_host_check_spread"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["check_result_reaper_frequency"]) && $ret["check_result_reaper_frequency"] != null ?
        $rq .= "'".htmlentities($ret["check_result_reaper_frequency"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["max_check_result_reaper_time"]) && $ret["max_check_result_reaper_time"] != null ?
        $rq .= "'".htmlentities($ret["max_check_result_reaper_time"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["auto_reschedule_checks"]["auto_reschedule_checks"])
        && $ret["auto_reschedule_checks"]["auto_reschedule_checks"] != 2 ?
        $rq .= "'".$ret["auto_reschedule_checks"]["auto_reschedule_checks"]."', " : $rq .= "'2', ";
    isset($ret["auto_rescheduling_interval"]) && $ret["auto_rescheduling_interval"] != null ?
        $rq .= "'".htmlentities($ret["auto_rescheduling_interval"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
    isset($ret["auto_rescheduling_window"]) && $ret["auto_rescheduling_window"] != null ?
        $rq .= "'".htmlentities($ret["auto_rescheduling_window"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
    isset($ret["use_aggressive_host_checking"]["use_aggressive_host_checking"])
        && $ret["use_aggressive_host_checking"]["use_aggressive_host_checking"] != 0 ?
        $rq .= "'".$ret["use_aggressive_host_checking"]["use_aggressive_host_checking"]."',  " : $rq .= "'0', ";
    isset($ret["enable_predictive_host_dependency_checks"]["enable_predictive_host_dependency_checks"])
        && $ret["enable_predictive_host_dependency_checks"]["enable_predictive_host_dependency_checks"] != 2 ?
        $rq .= "'".$ret["enable_predictive_host_dependency_checks"]["enable_predictive_host_dependency_checks"]."',  "
        : $rq .= "'2', ";
    isset($ret["enable_flap_detection"]["enable_flap_detection"])
        && $ret["enable_flap_detection"]["enable_flap_detection"] != 2 ?
        $rq .= "'".$ret["enable_flap_detection"]["enable_flap_detection"]."',  " : $rq .= "'2', ";
    isset($ret["low_service_flap_threshold"]) && $ret["low_service_flap_threshold"] != null ?
        $rq .= "'".htmlentities($ret["low_service_flap_threshold"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["high_service_flap_threshold"]) && $ret["high_service_flap_threshold"] != null ?
        $rq .= "'".htmlentities($ret["high_service_flap_threshold"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["low_host_flap_threshold"]) && $ret["low_host_flap_threshold"] != null ?
        $rq .= "'".htmlentities($ret["low_host_flap_threshold"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["high_host_flap_threshold"]) && $ret["high_host_flap_threshold"] != null ?
        $rq .= "'".htmlentities($ret["high_host_flap_threshold"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["soft_state_dependencies"]["soft_state_dependencies"])
        && $ret["soft_state_dependencies"]["soft_state_dependencies"] != 2 ?
        $rq .= "'".$ret["soft_state_dependencies"]["soft_state_dependencies"]."',  " : $rq .= "'2', ";
    isset($ret["enable_predictive_service_dependency_checks"]["enable_predictive_service_dependency_checks"])
        && $ret["enable_predictive_service_dependency_checks"]["enable_predictive_service_dependency_checks"] != 2 ?
        $rq .= "'".$ret["enable_predictive_service_dependency_checks"]["enable_predictive_service_dependency_checks"]
            ."',  "
        : $rq .= "'2', ";
    isset($ret["service_check_timeout"]) && $ret["service_check_timeout"] != null ?
        $rq .= "'".htmlentities($ret["service_check_timeout"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["host_check_timeout"]) && $ret["host_check_timeout"] != null ?
        $rq .= "'".htmlentities($ret["host_check_timeout"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["event_handler_timeout"]) && $ret["event_handler_timeout"] != null ?
        $rq .= "'".htmlentities($ret["event_handler_timeout"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["notification_timeout"]) && $ret["notification_timeout"] != null ?
        $rq .= "'".htmlentities($ret["notification_timeout"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["ocsp_timeout"]) && $ret["ocsp_timeout"] != null ?
        $rq .= "'".htmlentities($ret["ocsp_timeout"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["ochp_timeout"]) && $ret["ochp_timeout"] != null ?
        $rq .= "'".htmlentities($ret["ochp_timeout"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["perfdata_timeout"]) && $ret["perfdata_timeout"] != null ?
        $rq .= "'".htmlentities($ret["perfdata_timeout"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["obsess_over_services"]["obsess_over_services"])
        && $ret["obsess_over_services"]["obsess_over_services"] != 2 ?
        $rq .= "'".$ret["obsess_over_services"]["obsess_over_services"]."',  " : $rq .= "'2', ";
    isset($ret["ocsp_command"]) && $ret["ocsp_command"] != null ?
        $rq .= "'".htmlentities($ret["ocsp_command"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["obsess_over_hosts"]["obsess_over_hosts"])
        && $ret["obsess_over_hosts"]["obsess_over_hosts"] != 2 ?
        $rq .= "'".$ret["obsess_over_hosts"]["obsess_over_hosts"]."',  " : $rq .= "'2', ";
    isset($ret["ochp_command"]) && $ret["ochp_command"] != null ?
        $rq .= "'".htmlentities($ret["ochp_command"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["process_performance_data"]["process_performance_data"])
        && $ret["process_performance_data"]["process_performance_data"] != 2 ?
        $rq .= "'".$ret["process_performance_data"]["process_performance_data"]."',  " : $rq .= "'2', ";
    isset($ret["host_perfdata_command"]) && $ret["host_perfdata_command"] != null ?
        $rq .= "'".htmlentities($ret["host_perfdata_command"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["service_perfdata_command"]) && $ret["service_perfdata_command"] != null ?
        $rq .= "'".htmlentities($ret["service_perfdata_command"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["host_perfdata_file"]) && $ret["host_perfdata_file"] != null ?
        $rq .= "'".htmlentities($ret["host_perfdata_file"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["service_perfdata_file"]) && $ret["service_perfdata_file"] != null ?
        $rq .= "'".htmlentities($ret["service_perfdata_file"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["host_perfdata_file_template"]) && $ret["host_perfdata_file_template"] != null ?
        $rq .= "'".  mysql_real_escape_string($ret["host_perfdata_file_template"])."',  " : $rq .= "NULL, ";
    isset($ret["service_perfdata_file_template"]) && $ret["service_perfdata_file_template"] != null ?
        $rq .= "'".mysql_real_escape_string($ret["service_perfdata_file_template"])."',  " : $rq .= "NULL, ";
    isset($ret["host_perfdata_file_mode"]["host_perfdata_file_mode"])
        && $ret["host_perfdata_file_mode"]["host_perfdata_file_mode"] != null ?
        $rq .= "'".$ret["host_perfdata_file_mode"]["host_perfdata_file_mode"]."',  " : $rq .= "NULL, ";
    isset($ret["service_perfdata_file_mode"]["service_perfdata_file_mode"])
        && $ret["service_perfdata_file_mode"]["service_perfdata_file_mode"] != null ?
        $rq .= "'".$ret["service_perfdata_file_mode"]["service_perfdata_file_mode"]."',  " : $rq .= "NULL, ";
    isset($ret["host_perfdata_file_processing_interval"])
        && $ret["host_perfdata_file_processing_interval"] != null ?
        $rq .= "'".htmlentities($ret["host_perfdata_file_processing_interval"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "NULL, ";
    isset($ret["service_perfdata_file_processing_interval"])
        && $ret["service_perfdata_file_processing_interval"] != null ?
        $rq .= "'".htmlentities($ret["service_perfdata_file_processing_interval"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "NULL, ";
    isset($ret["host_perfdata_file_processing_command"])
        && $ret["host_perfdata_file_processing_command"] != null ?
        $rq .= "'".htmlentities($ret["host_perfdata_file_processing_command"])."',  " : $rq .= "NULL, ";
    isset($ret["service_perfdata_file_processing_command"])
        && $ret["service_perfdata_file_processing_command"] != null ?
        $rq .= "'".htmlentities($ret["service_perfdata_file_processing_command"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "NULL, ";
    isset($ret["check_for_orphaned_services"]["check_for_orphaned_services"])
        && $ret["check_for_orphaned_services"]["check_for_orphaned_services"] != 2 ?
        $rq .= "'".$ret["check_for_orphaned_services"]["check_for_orphaned_services"]."',  " : $rq .= "'2', ";
    isset($ret["check_service_freshness"]["check_service_freshness"])
        && $ret["check_service_freshness"]["check_service_freshness"] != 2 ?
        $rq .= "'".$ret["check_service_freshness"]["check_service_freshness"]."',  " : $rq .= "'2', ";
    isset($ret["service_freshness_check_interval"]) && $ret["service_freshness_check_interval"] != null ?
        $rq .= "'".htmlentities($ret["service_freshness_check_interval"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "NULL, ";
    isset($ret["cached_host_check_horizon"]) && $ret["cached_host_check_horizon"] != null ?
        $rq .= "'".htmlentities($ret["cached_host_check_horizon"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["cached_service_check_horizon"]) && $ret["cached_service_check_horizon"] != null ?
        $rq .= "'".htmlentities($ret["cached_service_check_horizon"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["additional_freshness_latency"]) && $ret["additional_freshness_latency"] != null ?
        $rq .= "'".htmlentities($ret["additional_freshness_latency"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["check_host_freshness"]["check_host_freshness"])
        && $ret["check_host_freshness"]["check_host_freshness"] != 2 ?
        $rq .= "'".$ret["check_host_freshness"]["check_host_freshness"]."',  " : $rq .= "'2', ";
    isset($ret["host_freshness_check_interval"]) && $ret["host_freshness_check_interval"] != null ?
        $rq .= "'".htmlentities($ret["host_freshness_check_interval"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["date_format"]) && $ret["date_format"] != null ?
        $rq .= "'".htmlentities($ret["date_format"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["illegal_object_name_chars"]) && $ret["illegal_object_name_chars"] != null ?
        $rq .= "'". $pearDB->escape($ret["illegal_object_name_chars"]) ."',  " : $rq .= "NULL, ";
    isset($ret["illegal_macro_output_chars"]) && $ret["illegal_macro_output_chars"] != null ?
        $rq .= "'". $pearDB->escape($ret["illegal_macro_output_chars"])."',  " : $rq .= "NULL, ";
    isset($ret["use_large_installation_tweaks"]["use_large_installation_tweaks"])
        && $ret["use_large_installation_tweaks"]["use_large_installation_tweaks"] != 2 ?
        $rq .= "'".$ret["use_large_installation_tweaks"]["use_large_installation_tweaks"]."',  " : $rq .= "'2', ";
    isset($ret["debug_file"]) && $ret["debug_file"] != null ?
        $rq .= "'".htmlentities($ret["debug_file"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    $level = 0;
    if (isset($ret["nagios_debug_level"]) && $ret["nagios_debug_level"] != null) {
        foreach ($ret["nagios_debug_level"] as $key => $value) {
            $level += $key;
        }
    }
    $rq .= "'.$level.', ";
    isset($ret["nagios_debug_level"]) && $ret["nagios_debug_level"] != null ?
        $rq .= "'".implode(",", array_keys($ret["nagios_debug_level"]))."',  " : $rq .= "'0', ";
    isset($ret["debug_verbosity"]["debug_verbosity"])
        && $ret["debug_verbosity"]["debug_verbosity"] != 2 ?
        $rq .= "'".$ret["debug_verbosity"]["debug_verbosity"]."',  " : $rq .= "'2', ";
    isset($ret["max_debug_file_size"]) && $ret["max_debug_file_size"] != null ?
        $rq .= "'".htmlentities($ret["max_debug_file_size"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["daemon_dumps_core"]["daemon_dumps_core"])
        && $ret["daemon_dumps_core"]["daemon_dumps_core"] ?
        $rq .= "'1', " : $rq .= "'0', ";
    isset($ret["enable_environment_macros"]["enable_environment_macros"])
        && $ret["enable_environment_macros"]["enable_environment_macros"] != 2 ?
        $rq .= "'".$ret["enable_environment_macros"]["enable_environment_macros"]."',  " : $rq .= "'2', ";
    isset($ret["use_setpgid"]["use_setpgid"]) && $ret["use_setpgid"]["use_setpgid"] != 2 ?
        $rq .= "'".$ret["use_setpgid"]["use_setpgid"]."',  " : $rq .= "'2', ";
    isset($ret["use_regexp_matching"]["use_regexp_matching"])
        && $ret["use_regexp_matching"]["use_regexp_matching"] != 2 ?
        $rq .= "'".$ret["use_regexp_matching"]["use_regexp_matching"]."',  " : $rq .= "'2', ";
    isset($ret["use_true_regexp_matching"]["use_true_regexp_matching"])
        && $ret["use_true_regexp_matching"]["use_true_regexp_matching"] != 2 ?
        $rq .= "'".$ret["use_true_regexp_matching"]["use_true_regexp_matching"]."',  " : $rq .= "'2', ";
    isset($ret["admin_email"]) && $ret["admin_email"] != null ?
        $rq .= "'".htmlentities($ret["admin_email"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
    isset($ret["admin_pager"]) && $ret["admin_pager"] != null ?
        $rq .= "'".htmlentities($ret["admin_pager"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
    isset($ret["nagios_comment"]) && $ret["nagios_comment"] != null ?
        $rq .= "'".htmlentities($ret["nagios_comment"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
    isset($ret["nagios_activate"]["nagios_activate"])
        && $ret["nagios_activate"]["nagios_activate"] != null ?
        $rq .= "'".$ret["nagios_activate"]["nagios_activate"]."'," : $rq .= "'0',";
    isset($ret["event_broker_options"]) && $ret["event_broker_options"] != null ?
        $rq .= "'".htmlentities($ret["event_broker_options"], ENT_QUOTES, "UTF-8")."', " : $rq .= "'-1', ";
    isset($ret["translate_passive_host_checks"]["translate_passive_host_checks"])
        && $ret["translate_passive_host_checks"]["translate_passive_host_checks"] != 2 ?
        $rq .= "'".$ret["translate_passive_host_checks"]["translate_passive_host_checks"]."', " : $rq .= "'2', ";
    isset($ret["passive_host_checks_are_soft"]["passive_host_checks_are_soft"])
        && $ret["passive_host_checks_are_soft"]["passive_host_checks_are_soft"] != 2 ?
        $rq .= "'".$ret["passive_host_checks_are_soft"]["passive_host_checks_are_soft"]."', " : $rq .= "'2', ";
    isset($ret["check_for_orphaned_hosts"]["check_for_orphaned_hosts"])
        && $ret["check_for_orphaned_hosts"]["check_for_orphaned_hosts"] != 2 ?
        $rq .= "'".$ret["check_for_orphaned_hosts"]["check_for_orphaned_hosts"]."', " : $rq .= "'2', ";
    isset($ret["external_command_buffer_slots"]["external_command_buffer_slots"])
        && $ret["external_command_buffer_slots"]["external_command_buffer_slots"] != 2 ?
        $rq .= "'".$ret["external_command_buffer_slots"]["external_command_buffer_slots"]."', " : $rq .= "'2', ";
    isset($ret["cfg_file"]) && $ret["cfg_file"] != null ?
        $rq .= "'".htmlentities($ret["cfg_file"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
    isset($ret["log_pid"]["log_pid"]) && $ret["log_pid"]["log_pid"] ? $rq .= "'1', " : $rq .= "'0', ";
    isset($ret['use_check_result_path']['use_check_result_path'])
        && $ret['use_check_result_path']['use_check_result_path'] ?
        $rq .= "'1')" : $rq .= "'0')";
    
    $DBRESULT = $pearDB->query($rq);
    $DBRESULT = $pearDB->query("SELECT MAX(nagios_id) FROM cfg_nagios");
    $nagios_id = $DBRESULT->fetchRow();
    $DBRESULT->free();

    if (isset($_REQUEST['in_broker'])) {
        $mainCfg = new CentreonConfigEngine($pearDB);
        $mainCfg->insertBrokerDirectives($nagios_id["MAX(nagios_id)"], $_REQUEST['in_broker']);
    }
    
    /* Manage the case where you have to main.cfg on the same poller */
    if (isset($ret["nagios_activate"]["nagios_activate"]) && $ret["nagios_activate"]["nagios_activate"]) {
        $DBRESULT = $pearDB->query(
            "UPDATE cfg_nagios SET nagios_activate = '0' WHERE nagios_id != '"
            . $nagios_id["MAX(nagios_id)"]
            . "' AND nagios_server_id = '".$ret['nagios_server_id']."'"
        );
        $centreon->Nagioscfg = array();
        $DBRESULT = $pearDB->query("SELECT * FROM `cfg_nagios` WHERE `nagios_activate` = '1' LIMIT 1");
        $centreon->Nagioscfg = $DBRESULT->fetchRow();
        $DBRESULT->free();
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
        $DBRESULT = $pearDB->query("UPDATE cfg_nagios SET `nagios_server_id` != '".$ret["nagios_server_id"]."'");
    }

    $ret = array();
    $ret = $form->getSubmitValues();
    $rq = "UPDATE cfg_nagios SET ";
    isset($ret["nagios_name"]) && $ret["nagios_name"] != null ?
        $rq .= "nagios_name = '".htmlentities($ret["nagios_name"], ENT_QUOTES, "UTF-8")."', "
        : $rq .= "nagios_name = NULL, ";
    isset($ret["nagios_server_id"]) && $ret["nagios_server_id"] != null ?
        $rq .= "nagios_server_id = '".htmlentities($ret["nagios_server_id"], ENT_QUOTES, "UTF-8")."', "
        : $rq .= "nagios_server_id = NULL, ";
    isset($ret["log_file"]) && $ret["log_file"] != null ?
        $rq .= "log_file = '".htmlentities($ret["log_file"], ENT_QUOTES, "UTF-8")."', "
        : $rq .= "log_file = NULL, ";
    isset($ret["cfg_dir"]) && $ret["cfg_dir"] != null ?
        $rq .= "cfg_dir = '".htmlentities($ret["cfg_dir"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "cfg_dir = NULL, ";
    isset($ret["temp_file"]) && $ret["temp_file"] != null ?
        $rq .= "temp_file = '".htmlentities($ret["temp_file"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "temp_file = NULL, ";
    isset($ret["check_result_path"]) && $ret["check_result_path"] != null ?
        $rq .= "check_result_path = '".htmlentities($ret["check_result_path"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "check_result_path = NULL, ";
    isset($ret["max_check_result_file_age"]) && $ret["max_check_result_file_age"] != null ?
        $rq .= "max_check_result_file_age = '"
        . htmlentities($ret["max_check_result_file_age"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "max_check_result_file_age = NULL, ";
    isset($ret["status_file"]) && $ret["status_file"] != null ?
        $rq .= "status_file = '".htmlentities($ret["status_file"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "status_file = NULL, ";
    isset($ret["status_update_interval"]) && $ret["status_update_interval"] != null ?
        $rq .= "status_update_interval = '".(int)$ret["status_update_interval"]."',  "
        : $rq .= "status_update_interval = NULL, ";
    isset($ret["nagios_user"]) && $ret["nagios_user"] != null ?
        $rq .= "nagios_user = '".htmlentities($ret["nagios_user"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "nagios_user = NULL, ";
    isset($ret["nagios_group"]) && $ret["nagios_group"] != null ?
        $rq .= "nagios_group = '".htmlentities($ret["nagios_group"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "nagios_group = NULL, ";
    isset($ret["enable_notifications"]["enable_notifications"])
        && $ret["enable_notifications"]["enable_notifications"] != 2 ?
        $rq .= "enable_notifications = '"
        . $ret["enable_notifications"]["enable_notifications"]."',  "
        : $rq .= "enable_notifications = '2', ";
    isset($ret["execute_service_checks"]["execute_service_checks"])
        && $ret["execute_service_checks"]["execute_service_checks"] != 2 ?
        $rq .= "execute_service_checks = '"
        . $ret["execute_service_checks"]["execute_service_checks"]."',  "
        : $rq .= "execute_service_checks = '2', ";
    isset($ret["accept_passive_service_checks"]["accept_passive_service_checks"])
        && $ret["accept_passive_service_checks"]["accept_passive_service_checks"] != 2 ?
        $rq .= "accept_passive_service_checks = '"
        . $ret["accept_passive_service_checks"]["accept_passive_service_checks"]."',  "
        : $rq .= "accept_passive_service_checks = '2', ";
    isset($ret["execute_host_checks"]["execute_host_checks"])
        && $ret["execute_host_checks"]["execute_host_checks"] != 2 ?
        $rq .= "execute_host_checks = '".$ret["execute_host_checks"]["execute_host_checks"]."',  "
        : $rq .= "execute_host_checks = '2', ";
    isset($ret["accept_passive_host_checks"]["accept_passive_host_checks"])
        && $ret["accept_passive_host_checks"]["accept_passive_host_checks"] != 2 ?
        $rq .= "accept_passive_host_checks = '"
        . $ret["accept_passive_host_checks"]["accept_passive_host_checks"]."',  "
        : $rq .= "accept_passive_host_checks = '2', ";
    isset($ret["enable_event_handlers"]["enable_event_handlers"])
        && $ret["enable_event_handlers"]["enable_event_handlers"] != 2 ?
        $rq .= "enable_event_handlers = '"
        . $ret["enable_event_handlers"]["enable_event_handlers"]."',  "
        : $rq .= "enable_event_handlers = '2', ";
    isset($ret["log_rotation_method"]["log_rotation_method"])
        && $ret["log_rotation_method"]["log_rotation_method"] != 2 ?
        $rq .= "log_rotation_method = '"
        . $ret["log_rotation_method"]["log_rotation_method"]."',  "
        : $rq .= "log_rotation_method = '2', ";
    isset($ret["log_archive_path"]) && $ret["log_archive_path"] != null ?
        $rq .= "log_archive_path = '"
        . htmlentities($ret["log_archive_path"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "log_archive_path = NULL, ";
    isset($ret["check_external_commands"]["check_external_commands"])
        && $ret["check_external_commands"]["check_external_commands"] != 2 ?
        $rq .= "check_external_commands = '"
        . $ret["check_external_commands"]["check_external_commands"]."',  "
        : $rq .= "check_external_commands = '2', ";
    isset($ret["command_check_interval"]) && $ret["command_check_interval"] != null ?
        $rq .= "command_check_interval = '"
        . htmlentities($ret["command_check_interval"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "command_check_interval = NULL, ";
    isset($ret["command_file"]) && $ret["command_file"] != null ?
        $rq .= "command_file = '".htmlentities($ret["command_file"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "command_file = NULL, ";
    isset($ret["downtime_file"]) && $ret["downtime_file"] != null ?
        $rq .= "downtime_file = '".htmlentities($ret["downtime_file"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "downtime_file = NULL, ";
    isset($ret["comment_file"]) && $ret["comment_file"] != null ?
        $rq .= "comment_file = '".htmlentities($ret["comment_file"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "comment_file = NULL, ";
    isset($ret["lock_file"]) && $ret["lock_file"] != null ?
        $rq .= "lock_file = '".htmlentities($ret["lock_file"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "lock_file = NULL, ";
    isset($ret["retain_state_information"]["retain_state_information"])
        && $ret["retain_state_information"]["retain_state_information"] != 2 ?
        $rq .= "retain_state_information = '"
        . $ret["retain_state_information"]["retain_state_information"]."',  "
        : $rq .= "retain_state_information = '2', ";
    isset($ret["state_retention_file"]) && $ret["state_retention_file"] != null ?
        $rq .= "state_retention_file = '"
        . htmlentities($ret["state_retention_file"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "state_retention_file = NULL, ";
    isset($ret["retention_update_interval"]) && $ret["retention_update_interval"] != null ?
        $rq .= "retention_update_interval = '"
        . htmlentities($ret["retention_update_interval"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "retention_update_interval = NULL, ";
    isset($ret["use_retained_program_state"]["use_retained_program_state"])
        && $ret["use_retained_program_state"]["use_retained_program_state"] != 2 ?
        $rq .= "use_retained_program_state = '"
        . $ret["use_retained_program_state"]["use_retained_program_state"]."',  "
        : $rq .= "use_retained_program_state = '2', ";
    isset($ret["use_retained_scheduling_info"]["use_retained_scheduling_info"])
        && $ret["use_retained_scheduling_info"]["use_retained_scheduling_info"] != 2 ?
        $rq .= "use_retained_scheduling_info = '"
        . $ret["use_retained_scheduling_info"]["use_retained_scheduling_info"]."',  "
        : $rq .= "use_retained_scheduling_info = '2', ";
    isset($ret["retained_contact_host_attribute_mask"])
        && $ret["retained_contact_host_attribute_mask"] != null ?
        $rq .= "retained_contact_host_attribute_mask = '"
        . htmlentities($ret["retained_contact_host_attribute_mask"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "retained_contact_host_attribute_mask = NULL, ";
    isset($ret["retained_contact_service_attribute_mask"])
        && $ret["retained_contact_service_attribute_mask"] != null ?
        $rq .= "retained_contact_service_attribute_mask = '"
        . htmlentities($ret["retained_contact_service_attribute_mask"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "retained_contact_service_attribute_mask = NULL, ";
    isset($ret["retained_process_host_attribute_mask"])
        && $ret["retained_process_host_attribute_mask"] != null ?
        $rq .= "retained_process_host_attribute_mask = '"
        . htmlentities($ret["retained_process_host_attribute_mask"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "retained_process_host_attribute_mask = NULL, ";
    isset($ret["retained_process_service_attribute_mask"])
        && $ret["retained_process_service_attribute_mask"] != null ?
        $rq .= "retained_process_service_attribute_mask = '"
        . htmlentities($ret["retained_process_service_attribute_mask"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "retained_process_service_attribute_mask = NULL, ";
    isset($ret["retained_host_attribute_mask"]) && $ret["retained_host_attribute_mask"] != null ?
        $rq .= "retained_host_attribute_mask = '"
        . htmlentities($ret["retained_host_attribute_mask"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "retained_host_attribute_mask = NULL, ";
    isset($ret["retained_service_attribute_mask"])
        && $ret["retained_service_attribute_mask"] != null ?
        $rq .= "retained_service_attribute_mask = '"
        . htmlentities($ret["retained_service_attribute_mask"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "retained_service_attribute_mask = NULL, ";
    isset($ret["use_syslog"]["use_syslog"]) && $ret["use_syslog"]["use_syslog"] != 2 ?
        $rq .= "use_syslog = '".$ret["use_syslog"]["use_syslog"]."',  "
        : $rq .= "use_syslog = '2', ";
    isset($ret["log_notifications"]["log_notifications"])
        && $ret["log_notifications"]["log_notifications"] != 2 ?
        $rq .= "log_notifications = '".$ret["log_notifications"]["log_notifications"]."',  "
        : $rq .= "log_notifications = '2', ";
    isset($ret["log_service_retries"]["log_service_retries"])
        && $ret["log_service_retries"]["log_service_retries"] != 2 ?
        $rq .= "log_service_retries = '"
        . $ret["log_service_retries"]["log_service_retries"]."',  "
        : $rq .= "log_service_retries = '2', ";
    isset($ret["log_host_retries"]["log_host_retries"])
        && $ret["log_host_retries"]["log_host_retries"] != 2 ?
        $rq .= "log_host_retries = '".$ret["log_host_retries"]["log_host_retries"]."',  "
        : $rq .= "log_host_retries = '2', ";
    isset($ret["log_event_handlers"]["log_event_handlers"])
        && $ret["log_event_handlers"]["log_event_handlers"] != 2 ?
        $rq .= "log_event_handlers = '".$ret["log_event_handlers"]["log_event_handlers"]."',  "
        : $rq .= "log_event_handlers = '2', ";
    isset($ret["log_initial_states"]["log_initial_states"])
        && $ret["log_initial_states"]["log_initial_states"] != 2 ?
        $rq .= "log_initial_states = '".$ret["log_initial_states"]["log_initial_states"]."',  "
        : $rq .= "log_initial_states = '2', ";
    isset($ret["log_external_commands"]["log_external_commands"])
        && $ret["log_external_commands"]["log_external_commands"] != 2 ?
        $rq .= "log_external_commands = '".$ret["log_external_commands"]["log_external_commands"]."',  "
        : $rq .= "log_external_commands = '2', ";
    isset($ret["log_passive_checks"]["log_passive_checks"])
        && $ret["log_passive_checks"]["log_passive_checks"] != 2 ?
        $rq .= "log_passive_checks = '".$ret["log_passive_checks"]["log_passive_checks"]."',  "
        : $rq .= "log_passive_checks = '2', ";
    isset($ret["global_host_event_handler"]) && $ret["global_host_event_handler"] != null ?
        $rq .= "global_host_event_handler = '".$ret["global_host_event_handler"]."',  "
        : $rq .= "global_host_event_handler = NULL, ";
    isset($ret["global_service_event_handler"]) && $ret["global_service_event_handler"] != null ?
        $rq .= "global_service_event_handler = '".$ret["global_service_event_handler"]."',  "
        : $rq .= "global_service_event_handler = NULL, ";
    isset($ret["sleep_time"]) && $ret["sleep_time"] != null ?
        $rq .= "sleep_time = '".htmlentities($ret["sleep_time"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "sleep_time = NULL, ";
    isset($ret["service_inter_check_delay_method"])
        && $ret["service_inter_check_delay_method"] != null ?
        $rq .= "service_inter_check_delay_method = '".$ret["service_inter_check_delay_method"]."',  "
        : $rq .= "service_inter_check_delay_method = NULL, ";
    isset($ret["max_service_check_spread"]) && $ret["max_service_check_spread"] != null ?
        $rq .= "max_service_check_spread = '"
        . htmlentities($ret["max_service_check_spread"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "max_service_check_spread = NULL, ";
    isset($ret["service_interleave_factor"]["service_interleave_factor"])
        && $ret["service_interleave_factor"]["service_interleave_factor"] != 2 ?
        $rq .= "service_interleave_factor = '"
        . $ret["service_interleave_factor"]["service_interleave_factor"]."',  "
        : $rq .= "service_interleave_factor = '2', ";
    isset($ret["max_concurrent_checks"]) && $ret["max_concurrent_checks"] != null ?
        $rq .= "max_concurrent_checks = '"
        . htmlentities($ret["max_concurrent_checks"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "max_concurrent_checks = NULL, ";
    isset($ret["check_result_reaper_frequency"]) && $ret["check_result_reaper_frequency"] != null ?
        $rq .= "check_result_reaper_frequency = '"
        . htmlentities($ret["check_result_reaper_frequency"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "check_result_reaper_frequency = NULL, ";
    isset($ret["max_check_result_reaper_time"]) && $ret["max_check_result_reaper_time"] != null ?
        $rq .= "max_check_result_reaper_time = '"
        . htmlentities($ret["max_check_result_reaper_time"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "max_check_result_reaper_time = NULL, ";
    isset($ret["host_inter_check_delay_method"]) && $ret["host_inter_check_delay_method"] != null ?
        $rq .= "host_inter_check_delay_method  = '" . $ret["host_inter_check_delay_method"]."',  "
        : $rq .= "host_inter_check_delay_method  = NULL, ";
    isset($ret["max_host_check_spread"]) && $ret["max_host_check_spread"] != null ?
        $rq .= "max_host_check_spread = '"
        . htmlentities($ret["max_host_check_spread"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "max_host_check_spread = NULL, ";
    isset($ret["auto_reschedule_checks"]["auto_reschedule_checks"])
        && $ret["auto_reschedule_checks"]["auto_reschedule_checks"] != 2 ?
        $rq .= "auto_reschedule_checks = '".$ret["auto_reschedule_checks"]["auto_reschedule_checks"]."', "
        : $rq .= "auto_reschedule_checks = '2', ";
    isset($ret["auto_rescheduling_interval"]) && $ret["auto_rescheduling_interval"] != null ?
        $rq .= "auto_rescheduling_interval = '"
        . htmlentities($ret["auto_rescheduling_interval"], ENT_QUOTES, "UTF-8")."', "
        : $rq .= "auto_rescheduling_interval = NULL, ";
    isset($ret["auto_rescheduling_window"]) && $ret["auto_rescheduling_window"] != null ?
        $rq .= "auto_rescheduling_window = '"
        . htmlentities($ret["auto_rescheduling_window"], ENT_QUOTES, "UTF-8")."', "
        : $rq .= "auto_rescheduling_window = NULL, ";
    isset($ret["use_aggressive_host_checking"]["use_aggressive_host_checking"])
        && $ret["use_aggressive_host_checking"]["use_aggressive_host_checking"] != 2 ?
        $rq .= "use_aggressive_host_checking   = '"
        . $ret["use_aggressive_host_checking"]["use_aggressive_host_checking"]."',  "
        : $rq .= "use_aggressive_host_checking   = '2', ";
    isset($ret["enable_predictive_host_dependency_checks"]["enable_predictive_host_dependency_checks"])
        && $ret["enable_predictive_host_dependency_checks"]["enable_predictive_host_dependency_checks"] != 2 ?
        $rq .= "enable_predictive_host_dependency_checks   = '"
        . $ret["enable_predictive_host_dependency_checks"]["enable_predictive_host_dependency_checks"]."',  "
        : $rq .= "enable_predictive_host_dependency_checks   = '2', ";
    isset($ret["enable_flap_detection"]["enable_flap_detection"])
        && $ret["enable_flap_detection"]["enable_flap_detection"] != 2 ?
        $rq .= "enable_flap_detection = '".$ret["enable_flap_detection"]["enable_flap_detection"]."',  "
        : $rq .= "enable_flap_detection = '2', ";
    isset($ret["low_service_flap_threshold"]) && $ret["low_service_flap_threshold"] != null ?
        $rq .= "low_service_flap_threshold = '"
        . htmlentities($ret["low_service_flap_threshold"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "low_service_flap_threshold = NULL, ";
    isset($ret["high_service_flap_threshold"]) && $ret["high_service_flap_threshold"] != null ?
        $rq .= "high_service_flap_threshold = '"
        . htmlentities($ret["high_service_flap_threshold"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "high_service_flap_threshold = NULL, ";
    isset($ret["low_host_flap_threshold"]) && $ret["low_host_flap_threshold"] != null ?
        $rq .= "low_host_flap_threshold = '"
        . htmlentities($ret["low_host_flap_threshold"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "low_host_flap_threshold = NULL, ";
    isset($ret["high_host_flap_threshold"]) && $ret["high_host_flap_threshold"] != null ?
        $rq .= "high_host_flap_threshold = '"
        . htmlentities($ret["high_host_flap_threshold"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "high_host_flap_threshold = NULL, ";
    isset($ret["soft_state_dependencies"]["soft_state_dependencies"])
        && $ret["soft_state_dependencies"]["soft_state_dependencies"] != 2 ?
        $rq .= "soft_state_dependencies   = '".$ret["soft_state_dependencies"]["soft_state_dependencies"]."',  "
        : $rq .= "soft_state_dependencies   = '2', ";
    isset($ret["enable_predictive_service_dependency_checks"]["enable_predictive_service_dependency_checks"])
        && $ret["enable_predictive_service_dependency_checks"]["enable_predictive_service_dependency_checks"] != 2 ?
        $rq .= "enable_predictive_service_dependency_checks   = '"
        . $ret["enable_predictive_service_dependency_checks"]["enable_predictive_service_dependency_checks"]."',  "
        : $rq .= "enable_predictive_service_dependency_checks   = '2', ";
    isset($ret["service_check_timeout"]) && $ret["service_check_timeout"] != null ?
        $rq .= "service_check_timeout = '"
        . htmlentities($ret["service_check_timeout"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "service_check_timeout = NULL, ";
    isset($ret["host_check_timeout"]) && $ret["host_check_timeout"] != null ?
        $rq .= "host_check_timeout = '"
        . htmlentities($ret["host_check_timeout"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "host_check_timeout = NULL, ";
    isset($ret["event_handler_timeout"]) && $ret["event_handler_timeout"] != null ?
        $rq .= "event_handler_timeout = '"
        . htmlentities($ret["event_handler_timeout"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "event_handler_timeout = NULL, ";
    isset($ret["notification_timeout"]) && $ret["notification_timeout"] != null ?
        $rq .= "notification_timeout = '"
        . htmlentities($ret["notification_timeout"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "notification_timeout = NULL, ";
    isset($ret["ocsp_timeout"]) && $ret["ocsp_timeout"] != null ?
        $rq .= "ocsp_timeout = '"
        . htmlentities($ret["ocsp_timeout"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "ocsp_timeout = NULL, ";
    isset($ret["ochp_timeout"]) && $ret["ochp_timeout"] != null ?
        $rq .= "ochp_timeout = '"
        . htmlentities($ret["ochp_timeout"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "ochp_timeout = NULL, ";
    isset($ret["perfdata_timeout"]) && $ret["perfdata_timeout"] != null ?
        $rq .= "perfdata_timeout = '"
        . htmlentities($ret["perfdata_timeout"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "perfdata_timeout = NULL, ";
    isset($ret["obsess_over_services"]["obsess_over_services"])
        && $ret["obsess_over_services"]["obsess_over_services"] != 2 ?
        $rq .= "obsess_over_services  = '"
        . $ret["obsess_over_services"]["obsess_over_services"]."',  "
        : $rq .= "obsess_over_services  = '2', ";
    isset($ret["ocsp_command"]) && $ret["ocsp_command"] != null ?
        $rq .= "ocsp_command = '"
        . htmlentities($ret["ocsp_command"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "ocsp_command = NULL, ";
    isset($ret["obsess_over_hosts"]["obsess_over_hosts"])
        && $ret["obsess_over_hosts"]["obsess_over_hosts"] != 2 ?
        $rq .= "obsess_over_hosts = '".$ret["obsess_over_hosts"]["obsess_over_hosts"]."',  "
        : $rq .= "obsess_over_hosts = '2', ";
    isset($ret["ochp_command"]) && $ret["ochp_command"] != null ?
        $rq .= "ochp_command  = '"
        . htmlentities($ret["ochp_command"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "ochp_command  = NULL, ";
    isset($ret["process_performance_data"]["process_performance_data"])
        && $ret["process_performance_data"]["process_performance_data"] != 2 ?
        $rq .= "process_performance_data   = '"
        . $ret["process_performance_data"]["process_performance_data"]."',  "
        : $rq .= "process_performance_data   = '2', ";
    isset($ret["host_perfdata_command"]) && $ret["host_perfdata_command"] != null ?
        $rq .= "host_perfdata_command = '"
        . htmlentities($ret["host_perfdata_command"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "host_perfdata_command = NULL, ";
    isset($ret["service_perfdata_command"]) && $ret["service_perfdata_command"] != null ?
        $rq .= "service_perfdata_command = '"
        . htmlentities($ret["service_perfdata_command"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "service_perfdata_command = NULL, ";
    isset($ret["host_perfdata_file"]) && $ret["host_perfdata_file"] != null ?
        $rq .= "host_perfdata_file = '"
        . htmlentities($ret["host_perfdata_file"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "host_perfdata_file = NULL, ";
    isset($ret["service_perfdata_file"]) && $ret["service_perfdata_file"] != null ?
        $rq .= "service_perfdata_file = '"
        . htmlentities($ret["service_perfdata_file"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "service_perfdata_file = NULL, ";
    isset($ret["host_perfdata_file_template"]) && $ret["host_perfdata_file_template"] != null ?
        $rq .= "host_perfdata_file_template = '"
        . mysql_real_escape_string($ret["host_perfdata_file_template"])."',  "
        : $rq .= "host_perfdata_file_template = NULL, ";
    isset($ret["service_perfdata_file_template"]) && $ret["service_perfdata_file_template"] != null ?
        $rq .= "service_perfdata_file_template = '"
        . mysql_real_escape_string($ret["service_perfdata_file_template"])."',  "
        : $rq .= "service_perfdata_file_template = NULL, ";
    isset($ret["host_perfdata_file_mode"]["host_perfdata_file_mode"])
        && $ret["host_perfdata_file_mode"]["host_perfdata_file_mode"] != null ?
        $rq .= "host_perfdata_file_mode  = '"
        . $ret["host_perfdata_file_mode"]["host_perfdata_file_mode"]."',  "
        : $rq .= "host_perfdata_file_mode  = NULL, ";
    isset($ret["service_perfdata_file_mode"]["service_perfdata_file_mode"])
        && $ret["service_perfdata_file_mode"]["service_perfdata_file_mode"] != null ?
        $rq .= "service_perfdata_file_mode  = '"
        . $ret["service_perfdata_file_mode"]["service_perfdata_file_mode"]."',  "
        : $rq .= "service_perfdata_file_mode  = NULL, ";
    isset($ret["host_perfdata_file_processing_interval"])
        && $ret["host_perfdata_file_processing_interval"] != null ?
        $rq .= "host_perfdata_file_processing_interval  = '"
        . htmlentities($ret["host_perfdata_file_processing_interval"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "host_perfdata_file_processing_interval  = NULL, ";
    isset($ret["service_perfdata_file_processing_interval"])
        && $ret["service_perfdata_file_processing_interval"] != null ?
        $rq .= "service_perfdata_file_processing_interval  = '"
        . htmlentities($ret["service_perfdata_file_processing_interval"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "service_perfdata_file_processing_interval  = NULL, ";
    isset($ret["host_perfdata_file_processing_command"])
        && $ret["host_perfdata_file_processing_command"] != null ?
        $rq .= "host_perfdata_file_processing_command  = '"
        . htmlentities($ret["host_perfdata_file_processing_command"])."',  "
        : $rq .= "host_perfdata_file_processing_command  = NULL, ";
    isset($ret["service_perfdata_file_processing_command"])
        && $ret["service_perfdata_file_processing_command"] != null ?
        $rq .= "service_perfdata_file_processing_command  = '"
        . htmlentities($ret["service_perfdata_file_processing_command"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "service_perfdata_file_processing_command  = NULL, ";
    isset($ret["check_for_orphaned_services"]["check_for_orphaned_services"])
        && $ret["check_for_orphaned_services"]["check_for_orphaned_services"] != 2 ?
        $rq .= "check_for_orphaned_services  = '"
        . $ret["check_for_orphaned_services"]["check_for_orphaned_services"]."',  "
        : $rq .= "check_for_orphaned_services  = '2', ";
    isset($ret["check_service_freshness"]["check_service_freshness"])
        && $ret["check_service_freshness"]["check_service_freshness"] != 2 ?
        $rq .= "check_service_freshness  = '"
        . $ret["check_service_freshness"]["check_service_freshness"]."',  "
        : $rq .= "check_service_freshness   = '2', ";
    isset($ret["service_freshness_check_interval"])
        && $ret["service_freshness_check_interval"] != null ?
        $rq .= "service_freshness_check_interval   = '"
        . htmlentities($ret["service_freshness_check_interval"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "service_freshness_check_interval   = NULL, ";
    isset($ret["cached_host_check_horizon"]) && $ret["cached_host_check_horizon"] != null ?
        $rq .= "cached_host_check_horizon   = '"
        . htmlentities($ret["cached_host_check_horizon"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "cached_host_check_horizon   = NULL, ";
    isset($ret["cached_service_check_horizon"]) && $ret["cached_service_check_horizon"] != null ?
        $rq .= "cached_service_check_horizon   = '"
        . htmlentities($ret["cached_service_check_horizon"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "cached_service_check_horizon   = NULL, ";
    isset($ret["additional_freshness_latency"]) && $ret["additional_freshness_latency"] != null ?
        $rq .= "additional_freshness_latency = '"
        . htmlentities($ret["additional_freshness_latency"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "additional_freshness_latency = NULL, ";
    isset($ret["check_host_freshness"]["check_host_freshness"])
        && $ret["check_host_freshness"]["check_host_freshness"] != 2 ?
        $rq .= "check_host_freshness = '"
        . $ret["check_host_freshness"]["check_host_freshness"]."',  "
        : $rq .= "check_host_freshness = '2', ";
    isset($ret["host_freshness_check_interval"])
        && $ret["host_freshness_check_interval"] != null ?
        $rq .= "host_freshness_check_interval = '"
        . htmlentities($ret["host_freshness_check_interval"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "host_freshness_check_interval = NULL, ";
    isset($ret["date_format"]) && $ret["date_format"] != null ?
        $rq .= "date_format = '"
        . htmlentities($ret["date_format"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "date_format = NULL, ";
    isset($ret["illegal_object_name_chars"]) && $ret["illegal_object_name_chars"] != null ?
        $rq .= "illegal_object_name_chars  = '" . $pearDB->escape($ret["illegal_object_name_chars"]) . "',  "
        : $rq .= "illegal_object_name_chars  = NULL, ";
    isset($ret["illegal_macro_output_chars"]) && $ret["illegal_macro_output_chars"] != null ?
        $rq .= "illegal_macro_output_chars  = '" . $pearDB->escape($ret["illegal_macro_output_chars"]) . "',  "
        : $rq .= "illegal_macro_output_chars  = NULL, ";
    isset($ret["use_large_installation_tweaks"]["use_large_installation_tweaks"])
        && $ret["use_large_installation_tweaks"]["use_large_installation_tweaks"] != 2 ?
        $rq .= "use_large_installation_tweaks = '"
        . $ret["use_large_installation_tweaks"]["use_large_installation_tweaks"]."',  "
        : $rq .= "use_large_installation_tweaks = '2', ";
    isset($ret["enable_environment_macros"]["enable_environment_macros"])
        && $ret["enable_environment_macros"]["enable_environment_macros"] != 2 ?
        $rq .= "enable_environment_macros = '"
        . $ret["enable_environment_macros"]["enable_environment_macros"]."',  "
        : $rq .= "enable_environment_macros = '2', ";
    isset($ret["use_setpgid"]["use_setpgid"]) && $ret["use_setpgid"]["use_setpgid"] != 2 ?
        $rq .= "use_setpgid = '".$ret["use_setpgid"]["use_setpgid"]."',  "
        : $rq .= "use_setpgid = '2', ";
    isset($ret["use_regexp_matching"]["use_regexp_matching"])
        && $ret["use_regexp_matching"]["use_regexp_matching"] != 2 ?
        $rq .= "use_regexp_matching = '".$ret["use_regexp_matching"]["use_regexp_matching"]."',  "
        : $rq .= "use_regexp_matching = '2', ";
    isset($ret["use_true_regexp_matching"]["use_true_regexp_matching"])
        && $ret["use_true_regexp_matching"]["use_true_regexp_matching"] != 2 ?
        $rq .= "use_true_regexp_matching = '"
        . $ret["use_true_regexp_matching"]["use_true_regexp_matching"]."',  "
        : $rq .= "use_true_regexp_matching = '2', ";
    isset($ret["admin_email"]) && $ret["admin_email"] != null ?
        $rq .= "admin_email = '"
        . htmlentities($ret["admin_email"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "admin_email = NULL, ";
    isset($ret["admin_pager"]) && $ret["admin_pager"] != null ?
        $rq .= "admin_pager = '"
        . htmlentities($ret["admin_pager"], ENT_QUOTES, "UTF-8")."', "
        : $rq .= "admin_pager = NULL, ";
    isset($ret["nagios_comment"]) && $ret["nagios_comment"] != null ?
        $rq .= "nagios_comment = '"
        . htmlentities($ret["nagios_comment"], ENT_QUOTES, "UTF-8")."', "
        : $rq .= "nagios_comment = NULL, ";
    isset($ret["event_broker_options"]) && $ret["event_broker_options"] != null ?
        $rq .= "event_broker_options = '"
        . htmlentities($ret["event_broker_options"], ENT_QUOTES, "UTF-8")."', "
        : $rq .= "event_broker_options = '-1', ";
    isset($ret["debug_file"]) && $ret["debug_file"] != null ?
        $rq .= "debug_file = '"
        . htmlentities($ret["debug_file"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "debug_file = NULL, ";
    $level = 0;
    if (isset($ret["nagios_debug_level"]) && $ret["nagios_debug_level"] != null) {
        foreach ($ret["nagios_debug_level"] as $key => $value) {
            $level += $key;
        }
    }
    $rq .= "debug_level = '".$level."', ";
    isset($ret["nagios_debug_level"]) && $ret["nagios_debug_level"] != null ?
        $rq .= "debug_level_opt = '".implode(",", array_keys($ret["nagios_debug_level"]))."',  "
        : $rq .= "debug_level = NULL, ";
    isset($ret["debug_verbosity"]["debug_verbosity"])
        && $ret["debug_verbosity"]["debug_verbosity"] != 2 ?
        $rq .= "debug_verbosity   = '".$ret["debug_verbosity"]["debug_verbosity"]."',  "
        : $rq .= "debug_verbosity   = '2', ";
    isset($ret["max_debug_file_size"]) && $ret["max_debug_file_size"] != null ?
        $rq .= "max_debug_file_size = '"
        . htmlentities($ret["max_debug_file_size"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "max_debug_file_size = NULL, ";
    isset($ret["daemon_dumps_core"]["daemon_dumps_core"])
        && $ret["daemon_dumps_core"]["daemon_dumps_core"] ?
        $rq .= "daemon_dumps_core = '1',  "
        : $rq .= "daemon_dumps_core = '0', ";

    isset($ret["translate_passive_host_checks"]["translate_passive_host_checks"])
        && $ret["translate_passive_host_checks"]["translate_passive_host_checks"] != null ?
        $rq .= "translate_passive_host_checks = '"
        . htmlentities($ret["translate_passive_host_checks"]["translate_passive_host_checks"], ENT_QUOTES, "UTF-8")
        . "',  "
        : $rq .= "translate_passive_host_checks = NULL, ";
    isset($ret["passive_host_checks_are_soft"]["passive_host_checks_are_soft"])
        && $ret["passive_host_checks_are_soft"]["passive_host_checks_are_soft"] != null ?
        $rq .= "passive_host_checks_are_soft = '"
        . htmlentities($ret["passive_host_checks_are_soft"]["passive_host_checks_are_soft"], ENT_QUOTES, "UTF-8")
        . "',  "
        : $rq .= "passive_host_checks_are_soft = NULL, ";
    isset($ret["check_for_orphaned_hosts"]["check_for_orphaned_hosts"])
        && $ret["check_for_orphaned_hosts"]["check_for_orphaned_hosts"] != null ?
        $rq .= "check_for_orphaned_hosts = '"
        . htmlentities($ret["check_for_orphaned_hosts"]["check_for_orphaned_hosts"], ENT_QUOTES, "UTF-8")
        . "',  "
        : $rq .= "check_for_orphaned_hosts = NULL, ";
    isset($ret["external_command_buffer_slots"]) && $ret["external_command_buffer_slots"]!= null ?
        $rq .= "external_command_buffer_slots = '"
        . htmlentities($ret["external_command_buffer_slots"], ENT_QUOTES, "UTF-8")."', "
        : $rq .= " external_command_buffer_slots = NULL, ";
    
    isset($ret["cfg_file"]) && $ret["cfg_file"] != null ?
        $rq .= "cfg_file = '"
        . htmlentities($ret["cfg_file"], ENT_QUOTES, "UTF-8")."',  "
        : $rq .= "cfg_file = NULL, ";
    isset($ret["log_pid"]["log_pid"]) && $ret["log_pid"]["log_pid"] ?
        $rq .= "log_pid = '1',  " : $rq .= "log_pid = '0', ";
    isset($ret['use_check_result_path']['use_check_result_path'])
        && $ret['use_check_result_path']['use_check_result_path'] ?
        $rq .= "use_check_result_path = '1', " : $rq .= "use_check_result_path = '0', ";
    
    $rq .= "nagios_activate = '".$ret["nagios_activate"]["nagios_activate"]."' ";
    $rq .= "WHERE nagios_id = '".$nagios_id."'";
    $DBRESULT = $pearDB->query($rq);

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
