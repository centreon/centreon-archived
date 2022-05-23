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
    if ($activate && $activate["name"]) {
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

/*
 * Duplicate Engine Configuration file in DB
 */
function multipleNagiosInDB($nagios = array(), $nbrDup = array())
{
    foreach ($nagios as $originalNagiosId => $value) {
        global $pearDB;

        $stmt = $pearDB->prepare("SELECT * FROM cfg_nagios WHERE nagios_id = :nagiosId LIMIT 1");
        $stmt->bindValue('nagiosId', (int) $originalNagiosId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $row["nagios_id"] = '';
        $row["nagios_activate"] = '0';
        $stmt->closeCursor();

        $rowBks = array();
        $stmt = $pearDB->prepare("SELECT * FROM cfg_nagios_broker_module WHERE cfg_nagios_id = :nagiosId");
        $stmt->bindValue('nagiosId', (int) $originalNagiosId, \PDO::PARAM_INT);
        $stmt->execute();
        while ($rowBk = $stmt->fetch()) {
            $rowBks[] = $rowBk;
        }
        $stmt->closeCursor();

        for ($i = 1; $i <= $nbrDup[$originalNagiosId]; $i++) {
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
                $nagiosId = $dbResult->fetch();
                $dbResult->closeCursor();
                foreach ($rowBks as $keyBk => $valBk) {
                    if ($valBk["broker_module"]) {
                        $stmt = $pearDB->prepare(
                            "INSERT INTO cfg_nagios_broker_module (`cfg_nagios_id`, `broker_module`)
                            VALUES (:nagiosId, :brokerModule)"
                        );
                        $stmt->bindValue('nagiosId', (int) $nagiosId["MAX(nagios_id)"], \PDO::PARAM_INT);
                        $stmt->bindValue('brokerModule', $valBk["broker_module"], \PDO::PARAM_STR);
                        $stmt->execute();
                    }
                }
                duplicateLoggerV2Cfg($pearDB, $originalNagiosId, $nagiosId["MAX(nagios_id)"]);
            }
        }
    }
}

/**
 * @param CentreonDB $pearDB
 * @param int $originalNagiosId
 * @param int $duplicatedNagiosId
 */
function duplicateLoggerV2Cfg(CentreonDB $pearDB, int $originalNagiosId, int $duplicatedNagiosId): void
{
    $statement = $pearDB->prepare("SELECT * FROM cfg_nagios_logger WHERE cfg_nagios_id=:nagiosId");
    $statement->bindValue('nagiosId', $originalNagiosId, \PDO::PARAM_INT);
    $statement->execute();
    $loggerCfg = $statement->fetch(\PDO::FETCH_ASSOC);

    if (! empty($loggerCfg)) {
        unset($loggerCfg['id']);
        $loggerCfg['cfg_nagios_id'] = $duplicatedNagiosId;
        $columnNames = array_keys($loggerCfg);

        $statement = $pearDB->prepare(
            'INSERT INTO cfg_nagios_logger ( `' . implode('`, `', $columnNames) . '`) VALUES
            ( :' . implode(', :', $columnNames) . ' )'
        );
        foreach ($loggerCfg as $columnName => $value) {
            if ($columnName === 'cfg_nagios_id') {
                $statement->bindValue(":{$columnName}", $value, \PDO::PARAM_INT);
            } else {
                $statement->bindValue(":{$columnName}", $value, \PDO::PARAM_STR);
            }
        }
        $statement->execute();
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

/**
 * @param array $levels
 * @return int
 */
function calculateDebugLevel(array $levels): int
{
    $level = 0;
    foreach ($levels as $key => $value) {
        $level += (int) $key;
    }
    return $level;
}

/**
 * @param array $levels
 * @return string
 */
function implodeDebugLevel(array $levels): string
{
    return implode(",", array_keys($levels));
}

/**
 * @param array $macros
 * @return string
 */
function concatMacrosWhitelist(array $macros): string
{
    return trim(
        implode(
            ',',
            array_map(function ($macro) {
                return CentreonDB::escape($macro);
            }, $macros)
        )
    );
}

/**
 * @return array<string,mixed>
 */
function getNagiosCfgColumnsDetails(): array
{
    return [
        'additional_freshness_latency' => ['default' => null],
        'admin_email' => ['default' => null],
        'admin_pager' => ['default' => null],
        'auto_rescheduling_interval' => ['default' => null],
        'auto_rescheduling_window' => ['default' => null],
        'cached_host_check_horizon' => ['default' => null],
        'cached_service_check_horizon' => ['default' => null],
        'cfg_dir' => ['default' => null],
        'cfg_file' => ['default' => null],
        'command_check_interval' => ['default' => null],
        'command_file' => ['default' => null],
        'comment_file' => ['default' => null],
        'check_result_reaper_frequency' => ['default' => null],
        'date_format' => ['default' => null],
        'debug_level' => ['callback' => 'calculateDebugLevel', 'default' => '0'],
        'debug_log_opt' => ['callback' => 'implodeDebugLevel', 'default' => '0'],
        'debug_file' => ['default' => null],
        'debug_verbosity' => ['default' => '2'],
        'downtime_file' => ['default' => null],
        'event_broker_options' => ['callback' => 'calculateBitwise', 'default' => '-1'],
        'event_handler_timeout' => ['default' => null],
        'external_command_buffer_slots' => ['default' => null],
        'global_host_event_handler' => ['default' => null],
        'global_service_event_handler' => ['default' => null],
        'high_host_flap_threshold' => ['default' => null],
        'high_service_flap_threshold' => ['default' => null],
        'host_check_timeout' => ['default' => null],
        'host_freshness_check_interval' => ['default' => null],
        'host_inter_check_delay_method' => ['default' => null],
        'host_perfdata_command' => ['default' => null],
        'host_perfdata_file' => ['default' => null],
        'host_perfdata_file_processing_command' => ['default' => null],
        'host_perfdata_file_processing_interval' => ['default' => null],
        'host_perfdata_file_template' => ['default' => null],
        'lock_file' => ['default' => null],
        'log_file' => ['default' => null],
        'low_host_flap_threshold' => ['default' => null],
        'low_service_flap_threshold' => ['default' => null],
        'macros_filter' => ['callback' => 'concatMacrosWhitelist', 'default' => null],
        'max_debug_file_size' => ['default' => null],
        'max_concurrent_checks' => ['default' => null],
        'max_check_result_reaper_time' => ['default' => null],
        'max_host_check_spread' => ['default' => null],
        'max_service_check_spread' => ['default' => null],
        'nagios_comment' => ['default' => null],
        'nagios_group' => ['default' => null],
        'nagios_name' => ['default' => null],
        'nagios_server_id' => ['default' => null],
        'nagios_user' => ['default' => null],
        'notification_timeout' => ['default' => null],
        'ochp_command' => ['default' => null],
        'ochp_timeout' => ['default' => null],
        'ocsp_command' => ['default' => null],
        'ocsp_timeout' => ['default' => null],
        'perfdata_timeout' => ['default' => null],
        'use_timezone' => ['default' => null],
        'retained_contact_host_attribute_mask' => ['default' => null],
        'retained_contact_service_attribute_mask' => ['default' => null],
        'retained_host_attribute_mask' => ['default' => null],
        'retained_process_host_attribute_mask' => ['default' => null],
        'retained_process_service_attribute_mask' => ['default' => null],
        'retained_service_attribute_mask' => ['default' => null],
        'retention_update_interval' => ['default' => null],
        'service_check_timeout' => ['default' => null],
        'service_freshness_check_interval' => ['default' => null],
        'service_inter_check_delay_method' => ['default' => null],
        'service_interleave_factor' => ['default' => '2'],
        'service_perfdata_command' => ['default' => null],
        'service_perfdata_file' => ['default' => null],
        'service_perfdata_file_processing_command' => ['default' => null],
        'service_perfdata_file_processing_interval' => ['default' => null],
        'service_perfdata_file_template' => ['default' => null],
        'status_file' => ['default' => null],
        'status_update_interval' => ['default' => null],
        'state_retention_file' => ['default' => null],
        'sleep_time' => ['default' => null],
        'temp_file' => ['default' => null],
        'illegal_macro_output_chars' => ['default' => null],
        'illegal_object_name_chars' => ['default' => null],
        'instance_heartbeat_interval' => ['default' => '30'],
        // Radio inputs
        'accept_passive_host_checks' => ['isRadio' => true, 'default' => '2'],
        'accept_passive_service_checks' => ['isRadio' => true, 'default' => '2'],
        'auto_reschedule_checks' => ['isRadio' => true, 'default' => '2'],
        'check_external_commands' => ['isRadio' => true, 'default' => '2'],
        'check_for_orphaned_hosts' => ['isRadio' => true, 'default' => '2'],
        'check_for_orphaned_services' => ['isRadio' => true, 'default' => '2'],
        'check_host_freshness' => ['isRadio' => true, 'default' => '2'],
        'check_service_freshness' => ['isRadio' => true, 'default' => '2'],
        'enable_environment_macros' => ['isRadio' => true, 'default' => '2'],
        'enable_event_handlers' => ['isRadio' => true, 'default' => '2'],
        'enable_flap_detection' => ['isRadio' => true, 'default' => '2'],
        'enable_macros_filter' => ['isRadio' => true, 'default' => '0'],
        'enable_notifications' => ['isRadio' => true, 'default' => '2'],
        'enable_predictive_host_dependency_checks' => ['isRadio' => true, 'default' => '2'],
        'enable_predictive_service_dependency_checks' => ['isRadio' => true, 'default' => '2'],
        'execute_host_checks' => ['isRadio' => true, 'default' => '2'],
        'execute_service_checks' => ['isRadio' => true, 'default' => '2'],
        'host_perfdata_file_mode' => ['isRadio' => true, 'default' => '2'],
        'log_event_handlers' => ['isRadio' => true, 'default' => '2'],
        'log_external_commands' => ['isRadio' => true, 'default' => '2'],
        'log_host_retries' => ['isRadio' => true, 'default' => '2'],
        'log_notifications' => ['isRadio' => true, 'default' => '2'],
        'log_passive_checks' => ['isRadio' => true, 'default' => '2'],
        'log_pid' => ['isRadio' => true, 'default' => '0'],
        'log_service_retries' => ['isRadio' => true, 'default' => '2'],
        'nagios_activate' => ['isRadio' => true, 'default' => '0'],
        'obsess_over_hosts' => ['isRadio' => true, 'default' => '2'],
        'obsess_over_services' => ['isRadio' => true, 'default' => '2'],
        'passive_host_checks_are_soft' => ['isRadio' => true, 'default' => '2'],
        'process_performance_data' => ['isRadio' => true, 'default' => '2'],
        'retain_state_information' => ['isRadio' => true, 'default' => '2'],
        'service_perfdata_file_mode' => ['isRadio' => true, 'default' => '2'],
        'soft_state_dependencies' => ['isRadio' => true, 'default' => '2'],
        'translate_passive_host_checks' => ['isRadio' => true, 'default' => '2'],
        'use_large_installation_tweaks' => ['isRadio' => true, 'default' => '2'],
        'use_setpgid' => ['isRadio' => true, 'default' => '2'],
        'use_regexp_matching' => ['isRadio' => true, 'default' => '2'],
        'use_retained_program_state' => ['isRadio' => true, 'default' => '2'],
        'use_retained_scheduling_info' => ['isRadio' => true, 'default' => '2'],
        'use_syslog' => ['isRadio' => true, 'default' => '2'],
        'use_true_regexp_matching' => ['isRadio' => true, 'default' => '2'],
        'logger_version' => ['isRadio' => true, 'default' => 'log_v2_enabled'],
    ];
}

/**
 * @return string[]
 */
function getLoggerV2Columns(): array
{
    return [
        'log_v2_logger',
        'log_level_functions',
        'log_level_config',
        'log_level_events',
        'log_level_checks',
        'log_level_notifications',
        'log_level_eventbroker',
        'log_level_external_command',
        'log_level_commands',
        'log_level_downtimes',
        'log_level_comments',
        'log_level_macros',
        'log_level_process',
        'log_level_runtime',
    ];
}

/**
 * @param CentreonDB $pearDB
 * @param array $data
 * @param int $nagiosId
 */
function insertLoggerV2Cfg(CentreonDB $pearDB, array $data, int $nagiosId): void
{
    $loggerCfg = getLoggerV2Columns();

    $statement = $pearDB->prepare(
        'INSERT INTO cfg_nagios_logger (`cfg_nagios_id`, `' . implode('`, `', $loggerCfg) . '`)
        VALUES (:cfg_nagios_id, :' . implode(', :', $loggerCfg) . ')'
    );

    $statement->bindValue(':cfg_nagios_id', $nagiosId, \PDO::PARAM_INT);
    foreach ($loggerCfg as $columnName) {
        $statement->bindValue(':' . $columnName, $data[$columnName] ?? null, \PDO::PARAM_STR);
    }
    $statement->execute();
}

/**
 * @param CentreonDB $pearDB
 * @param array<string,mixed> $data
 * @param int $nagiosId
 */
function updateLoggerV2Cfg(CentreonDB $pearDB, array $data, int $nagiosId): void
{
    $loggerCfg = getLoggerV2Columns();

    $queryPieces = array_map(fn($columnName) => "`{$columnName}` = :{$columnName}", $loggerCfg);
    $statement = $pearDB->prepare(
        'UPDATE cfg_nagios_logger SET ' . implode(', ', $queryPieces) . ' WHERE cfg_nagios_id = :cfg_nagios_id'
    );

    $statement->bindValue(':cfg_nagios_id', $nagiosId, \PDO::PARAM_INT);
    foreach ($loggerCfg as $columnName) {
        $statement->bindValue(':' . $columnName, $data[$columnName] ?? null, \PDO::PARAM_STR);
    }
    $statement->execute();
}

/**
 * Insert logger V2 config if doesn't exist, otherwise update it
 *
 * @param CentreonDB $pearDB
 * @param array<string,mixed> $data
 * @param int $nagiosId
 */
function insertOrUpdateLogger(CentreonDB $pearDB, array $data, int $nagiosId): void
{
    $statement = $pearDB->prepare('SELECT id FROM cfg_nagios_logger WHERE cfg_nagios_id = :cfg_nagios_id');
    $statement->bindValue(':cfg_nagios_id', $nagiosId, \PDO::PARAM_INT);
    $statement->execute();

    if ($statement->fetch()) {
        updateLoggerV2Cfg($pearDB, $data, $nagiosId);
    } else {
        insertLoggerV2Cfg($pearDB, $data, $nagiosId);
    }
}

function insertNagios($data = array(), $brokerTab = array())
{
    global $form, $pearDB, $centreon;

    if (! count($data)) {
        $data = $form->getSubmitValues();
    }

    $nagiosColumns = getNagiosCfgColumnsDetails();

    $nagiosCfg = [];
    foreach ($data as $columnName => $rawValue) {
        if (! array_key_exists($columnName, $nagiosColumns)) {
            continue;
        }

        if (! empty($nagiosColumns[$columnName]['callback'])) {
            $value = isset($rawValue)
                ? ($nagiosColumns[$columnName]['callback'])($rawValue)
                : $nagiosColumns[$columnName]['default'] ;
        } elseif (! empty($nagiosColumns[$columnName]['isRadio'])) {
            $value = isset($rawValue[$columnName])
                ? htmlentities($rawValue[$columnName], ENT_QUOTES, "UTF-8")
                : $nagiosColumns[$columnName]['default'];
        } else {
            $value = isset($rawValue) && $rawValue !== ''
                ? htmlentities($rawValue, ENT_QUOTES, "UTF-8")
                : $nagiosColumns[$columnName]['default'];
        }
        $nagiosCfg[$columnName] = $value;
    }

    $statement = $pearDB->prepare(
        'INSERT INTO cfg_nagios (`' . implode('`, `', array_keys($nagiosCfg)) . '`)
        VALUES (:' . implode(', :', array_keys($nagiosCfg)) . ')'
    );

    array_walk(
        $nagiosCfg,
        fn($value, $param, $statement) => $statement->bindValue(':' . $param, $value, \PDO::PARAM_STR),
        $statement
    );

    $statement->execute();

    $dbResult = $pearDB->query("SELECT MAX(nagios_id) FROM cfg_nagios");
    $nagios_id = $dbResult->fetch();
    $dbResult->closeCursor();

    if (isset($nagiosCfg['logger_version']) && $nagiosCfg['logger_version'] === 'log_v2_enabled') {
        insertLoggerV2Cfg($pearDB, $data, $nagios_id["MAX(nagios_id)"]);
    }

    if (isset($_REQUEST['in_broker'])) {
        $mainCfg = new CentreonConfigEngine($pearDB);
        $mainCfg->insertBrokerDirectives($nagios_id["MAX(nagios_id)"], $_REQUEST['in_broker']);
    }

    /* Manage the case where you have to main.cfg on the same poller */
    if (isset($data["nagios_activate"]["nagios_activate"]) && $data["nagios_activate"]["nagios_activate"]) {
        $dbResult = $pearDB->query(
            "UPDATE cfg_nagios SET nagios_activate = '0' WHERE nagios_id != '"
            . $nagios_id["MAX(nagios_id)"]
            . "' AND nagios_server_id = '" . $data['nagios_server_id'] . "'"
        );
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($data);
    $centreon->CentreonLogAction->insertLog(
        "engine",
        $nagios_id["MAX(nagios_id)"],
        $pearDB->escape($data["nagios_name"]),
        "a",
        $fields
    );

    return ($nagios_id["MAX(nagios_id)"]);
}

function updateNagios($nagiosId = null)
{
    global $form, $pearDB, $centreon;

    if (!$nagiosId) {
        return;
    }

    $data = [];
    $data = $form->getSubmitValues();
    $nagiosColumns = getNagiosCfgColumnsDetails();

    $nagiosCfg = [];
    foreach ($data as $columnName => $rawValue) {
        if (! array_key_exists($columnName, $nagiosColumns)) {
            continue;
        }

        if (! empty($nagiosColumns[$columnName]['callback'])) {
            $value = isset($rawValue)
                ? ($nagiosColumns[$columnName]['callback'])($rawValue)
                : $nagiosColumns[$columnName]['default'] ;
        } elseif (! empty($nagiosColumns[$columnName]['isRadio'])) {
            $value = isset($rawValue[$columnName])
                ? htmlentities($rawValue[$columnName], ENT_QUOTES, "UTF-8")
                : $nagiosColumns[$columnName]['default'];
        } else {
            $value = isset($rawValue) && $rawValue !== ''
                ? htmlentities($rawValue, ENT_QUOTES, "UTF-8")
                : $nagiosColumns[$columnName]['default'];
        }
        $nagiosCfg[$columnName] = $value;
    }

    $queryPieces = array_map(fn($columnName) => "`{$columnName}` = :{$columnName}", array_keys($nagiosCfg));

    $statement = $pearDB->prepare(
        'UPDATE cfg_nagios SET ' . implode(', ', $queryPieces) . " WHERE nagios_id = {$nagiosId}"
    );

    array_walk(
        $nagiosCfg,
        fn($value, $param, $statement) => $statement->bindValue(':' . $param, $value, \PDO::PARAM_STR),
        $statement
    );

    $statement->execute();

    if (isset($nagiosCfg['logger_version']) && $nagiosCfg['logger_version'] === 'log_v2_enabled') {
        insertOrUpdateLogger($pearDB, $data, $nagiosId);
    }

    $mainCfg = new CentreonConfigEngine($pearDB);
    if (isset($_REQUEST['in_broker'])) {
        $mainCfg->insertBrokerDirectives($nagiosId, $_REQUEST['in_broker']);
    } else {
        $mainCfg->insertBrokerDirectives($nagiosId);
    }

    if ($data["nagios_activate"]["nagios_activate"]) {
        enableNagiosInDB($nagiosId);
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($data);
    $centreon->CentreonLogAction->insertLog(
        "engine",
        $nagiosId,
        $pearDB->escape($data["nagios_name"]),
        "c",
        $fields
    );
}
