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
 *
 */

include_once __DIR__ . "/../../class/centreonLog.class.php";
$centreonLog = new CentreonLog();

/**
 * LDAP auto or manual synchronization feature
 */
try {
    $pearDB->query('SET SESSION innodb_strict_mode=OFF');

    // Adding two columns to check last user's LDAP sync timestamp
    if (!$pearDB->isColumnExist('contact', 'contact_ldap_last_sync')) {
        //$pearDB = "centreon"
        //$pearDBO = "realtime"
        $pearDB->query(
            "ALTER TABLE `contact` ADD COLUMN `contact_ldap_last_sync` INT(11) NOT NULL DEFAULT 0"
        );
    }
    if (!$pearDB->isColumnExist('contact', 'contact_ldap_required_sync')) {
        $pearDB->query(
            "ALTER TABLE `contact` ADD COLUMN `contact_ldap_required_sync` enum('0','1') NOT NULL DEFAULT '0'"
        );
    }

    // Adding a column to check last specific LDAP sync timestamp
    $needToUpdateValues = false;
    if (!$pearDB->isColumnExist('auth_ressource', 'ar_sync_base_date')) {
        $pearDB->query(
            "ALTER TABLE `auth_ressource` ADD COLUMN `ar_sync_base_date` INT(11) DEFAULT 0"
        );
        $needToUpdateValues = true;
    }
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : 19.10.0-beta.3 Unable to add LDAP new feature's tables in the database"
    );
} finally {
    $pearDB->query('SET SESSION innodb_strict_mode=ON');
}

// Initializing reference synchronization time for all LDAP configurations */
if ($needToUpdateValues) {
    try {
        $stmt = $pearDB->prepare(
            "UPDATE `auth_ressource` SET `ar_sync_base_date` = :minusTime"
        );
        $stmt->bindValue(':minusTime', time(), \PDO::PARAM_INT);
        $stmt->execute();
    } catch (\PDOException $e) {
        $centreonLog->insertLog(
            2,
            "UPGRADE : 19.10.0-beta.3 Unable to initialize LDAP reference date"
        );
    }

    /* Adding to each LDAP configuration two new fields */
    try {
        // field to enable the automatic sync at login
        $addSyncStateField = $pearDB->prepare(
            "INSERT IGNORE INTO auth_ressource_info
            (`ar_id`, `ari_name`, `ari_value`)
            VALUES (:arId, 'ldap_auto_sync', '1')"
        );
        // interval between two sync at login
        $addSyncIntervalField = $pearDB->prepare(
            "INSERT IGNORE INTO auth_ressource_info
            (`ar_id`, `ari_name`, `ari_value`)
            VALUES (:arId, 'ldap_sync_interval', '1')"
        );

        $pearDB->beginTransaction();
        $stmt = $pearDB->query("SELECT DISTINCT(ar_id) FROM auth_ressource");
        while ($row = $stmt->fetch()) {
            $addSyncIntervalField->bindValue(':arId', $row['ar_id'], \PDO::PARAM_INT);
            $addSyncIntervalField->execute();
            $addSyncStateField->bindValue(':arId', $row['ar_id'], \PDO::PARAM_INT);
            $addSyncStateField->execute();
        }
        $pearDB->commit();
    } catch (\PDOException $e) {
        $centreonLog->insertLog(
            1, // ldap.log
            "UPGRADE PROCESS : Error - Please open your LDAP configuration and save manually each LDAP form"
        );
        $centreonLog->insertLog(
            2, // sql-error.log
            "UPGRADE : 19.10.0-beta.3 Unable to add LDAP new fields"
        );
        $pearDB->rollBack();
    }
}

// update topology of poller wizard to display breadcrumb
$pearDB->query(
    'UPDATE topology
    SET topology_parent = 60901,
    topology_page = 60959,
    topology_group = 1,
    topology_show = "0"
    WHERE topology_url LIKE "/poller-wizard/%"'
);


try {
    // Add trap regexp matching
    if (!$pearDB->isColumnExist('traps', 'traps_mode')) {
        $pearDB->query('SET SESSION innodb_strict_mode=OFF');
        $pearDB->query(
            "ALTER TABLE `traps` ADD COLUMN `traps_mode` enum('0','1') DEFAULT '0' AFTER `traps_oid`"
        );
    }
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : 19.10.0-beta.3 Unable to modify regexp matching in the database"
    );
} finally {
    $pearDB->query('SET SESSION innodb_strict_mode=ON');
}


/**
 * Add columns to manage engine & broker restart/reload process
 */
try {
    $pearDB->query('SET SESSION innodb_strict_mode=OFF');
    $pearDB->query('
        ALTER TABLE `nagios_server`
        ADD COLUMN `engine_start_command` varchar(255) DEFAULT \'service centengine start\' AFTER `monitoring_engine`
    ');
    $pearDB->query('
        ALTER TABLE `nagios_server`
        ADD COLUMN `engine_stop_command` varchar(255) DEFAULT \'service centengine stop\' AFTER `engine_start_command`
    ');
    $pearDB->query('
        ALTER TABLE `nagios_server`
        ADD COLUMN `engine_restart_command` varchar(255)
        DEFAULT \'service centengine restart\' AFTER `engine_stop_command`
    ');
    $pearDB->query('
        ALTER TABLE `nagios_server`
        ADD COLUMN `engine_reload_command` varchar(255)
         DEFAULT \'service centengine reload\' AFTER `engine_restart_command`
    ');
    $pearDB->query('
        ALTER TABLE `nagios_server`
        ADD COLUMN `broker_reload_command` varchar(255) DEFAULT \'service cbd reload\' AFTER `nagios_perfdata`
    ');
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : 19.10.0-beta.3 Unable to manage engine & broker restart and reload processes"
    );
} finally {
    $pearDB->query('SET SESSION innodb_strict_mode=ON');
}

$stmt = $pearDB->prepare('
    UPDATE `nagios_server`
    SET engine_start_command = :engine_start_command,
    engine_stop_command = :engine_stop_command,
    engine_restart_command = :engine_restart_command,
    engine_reload_command = :engine_reload_command,
    broker_reload_command = :broker_reload_command
    WHERE id = :id
');

$result = $pearDB->query('SELECT value FROM `options` WHERE `key` = \'broker_correlator_script\'');
$brokerServiceName = 'cbd';
if ($row = $result->fetch()) {
    if (!empty($row['value'])) {
        $brokerServiceName = $row['value'];
    }
}
$stmt->bindValue(':broker_reload_command', 'service ' . $brokerServiceName . ' reload', \PDO::PARAM_STR);

$result = $pearDB->query('SELECT id, init_script FROM `nagios_server`');

while ($row = $result->fetch()) {
    $engineServiceName = 'centengine';
    if (!empty($row['init_script'])) {
        $engineServiceName = $row['init_script'];
    }
    $stmt->bindValue(':id', $row['id'], \PDO::PARAM_INT);
    $stmt->bindValue(':engine_start_command', 'service ' . $engineServiceName . ' start', \PDO::PARAM_STR);
    $stmt->bindValue(':engine_stop_command', 'service ' . $engineServiceName . ' stop', \PDO::PARAM_STR);
    $stmt->bindValue(':engine_restart_command', 'service ' . $engineServiceName . ' restart', \PDO::PARAM_STR);
    $stmt->bindValue(':engine_reload_command', 'service ' . $engineServiceName . ' reload', \PDO::PARAM_STR);
    $stmt->execute();
}

// Remove deprecated engine & broker init script paths
$pearDB->query('ALTER TABLE `nagios_server` DROP COLUMN `init_script`');
$pearDB->query('ALTER TABLE `nagios_server` DROP COLUMN `init_system`');
$pearDB->query('ALTER TABLE `nagios_server` DROP COLUMN `monitoring_engine`');
$pearDB->query('DELETE FROM `options` WHERE `key` = \'broker_correlator_script\'');
$pearDB->query('DELETE FROM `options` WHERE `key` = \'monitoring_engine\'');


/**
 * Manage upgrade of widget preferences
 */

// set cache for pollers
$pollers = [];
$result = $pearDB->query('SELECT id, name FROM nagios_server');
while ($row = $result->fetch()) {
    $pollerName = strtolower($row['name']);
    $pollers[$pollerName] = $row['id'];
}

// get poller preferences of engine-status widget
$result = $pearDB->query(
    'SELECT wpr.widget_view_id, wpr.parameter_id, wpr.preference_value, wpr.user_id
    FROM widget_preferences wpr
    INNER JOIN widget_parameters wpa ON wpa.parameter_id = wpr.parameter_id
    AND wpa.parameter_code_name = \'poller\'
    INNER JOIN widget_models wm ON wm.widget_model_id = wpa.widget_model_id
    AND wm.title = \'Engine-Status\''
);

$statement = $pearDB->prepare(
    'UPDATE widget_preferences
    SET preference_value= :value
    WHERE widget_view_id = :view_id
    AND parameter_id = :parameter_id
    AND user_id = :user_id'
);

// update poller preferences from name to id
while ($row = $result->fetch()) {
    $pollerName = strtolower($row['preference_value']);
    $pollerId = isset($pollers[$pollerName])
        ? $pollers[$pollerName]
        : '';

    $statement->bindValue(':value', $pollerId, \PDO::PARAM_STR);
    $statement->bindValue(':view_id', $row['widget_view_id'], \PDO::PARAM_INT);
    $statement->bindValue(':parameter_id', $row['parameter_id'], \PDO::PARAM_INT);
    $statement->bindValue(':user_id', $row['user_id'], \PDO::PARAM_INT);
    $statement->execute();
}

// set cache for severities
$severities = [];
$result = $pearDB->query('SELECT sc_id, sc_name FROM service_categories WHERE level IS NOT NULL');
while ($row = $result->fetch()) {
    $severityName = strtolower($row['sc_name']);
    $severities[$severityName] = $row['sc_id'];
}

// get poller preferences (criticality_filter) of service-monitoring widget
$result = $pearDB->query(
    'SELECT wpr.widget_view_id, wpr.parameter_id, wpr.preference_value, wpr.user_id
    FROM widget_preferences wpr
    INNER JOIN widget_parameters wpa ON wpa.parameter_id = wpr.parameter_id
    AND wpa.parameter_code_name = \'criticality_filter\'
    INNER JOIN widget_models wm ON wm.widget_model_id = wpa.widget_model_id
    AND wm.title = \'Service Monitoring\''
);

$statement = $pearDB->prepare(
    'UPDATE widget_preferences
    SET preference_value= :value
    WHERE widget_view_id = :view_id
    AND parameter_id = :parameter_id
    AND user_id = :user_id'
);

// update poller preferences from name to id
while ($row = $result->fetch()) {
    $severityIds = [];
    $severityNames = explode(',', $row['preference_value']);
    foreach ($severityNames as $severityName) {
        $severityName = strtolower($severityName);
        if (isset($severities[$severityName])) {
            $severityIds[] = $severities[$severityName];
        }
    }

    $severityIds = !empty($severityIds) ? implode(',', $severityIds) : '';

    $statement->bindValue(':value', $severityIds, \PDO::PARAM_STR);
    $statement->bindValue(':view_id', $row['widget_view_id'], \PDO::PARAM_INT);
    $statement->bindValue(':parameter_id', $row['parameter_id'], \PDO::PARAM_INT);
    $statement->bindValue(':user_id', $row['user_id'], \PDO::PARAM_INT);
    $statement->execute();
}

// manage rrdcached upgrade
$result = $pearDB->query("SELECT `value` FROM options WHERE `key` = 'rrdcached_enable' ");
$cache = $result->fetch();

if ($cache['value']) {
    try {
        $pearDB->beginTransaction();

        $res = $pearDB->query(
            "SELECT * FROM cfg_centreonbroker_info WHERE `config_key` = 'type' AND `config_value` = 'rrd'"
        );
        $result = $pearDB->query("SELECT `value` FROM options WHERE `key` = 'rrdcached_port' ");
        $port = $result->fetch();

        while ($row = $res->fetch()) {
            if ($port['value']) {
                $brokerInfoData = [
                    [
                        'config_id' => $row['config_id'],
                        'config_key' => 'rrd_cached_option',
                        'config_value' => 'tcp',
                        'config_group' => $row['config_group'],
                        'config_group_id' => $row['config_group_id']
                    ],
                    [
                        'config_id' => $row['config_id'],
                        'config_key' => 'rrd_cached',
                        'config_value' => $port['value'],
                        'config_group' => $row['config_group'],
                        'config_group_id' => $row['config_group_id']
                    ],
                ];
                $query = 'INSERT INTO cfg_centreonbroker_info (config_id, config_key, config_value, '
                    . 'config_group, config_group_id ) VALUES '
                    . '( :config_id, :config_key, :config_value, '
                    . ':config_group, :config_group_id)';
                $statement = $pearDB->prepare($query);
                foreach ($brokerInfoData as $dataRow) {
                    $statement->bindValue(":config_id", (int) $dataRow['config_id'], \PDO::PARAM_INT);
                    $statement->bindValue(":config_key", $dataRow['config_key']);
                    $statement->bindValue(":config_value", $dataRow['config_value']);
                    $statement->bindValue(":config_group", $dataRow['config_group']);
                    $statement->bindValue(":config_group_id", (int) $dataRow['config_group_id'], \PDO::PARAM_INT);
                    $statement->execute();
                }
            } else {
                $result = $pearDB->query("SELECT `value` FROM options WHERE `key` = 'rrdcached_unix_path' ");
                $path = $result->fetch();

                $brokerInfoData = [
                    [
                        'config_id' => $row['config_id'],
                        'config_key' => 'rrd_cached_option',
                        'config_value' => 'unix',
                        'config_group' => $row['config_group'],
                        'config_group_id' => $row['config_group_id']
                    ],
                    [
                        'config_id' => $row['config_id'],
                        'config_key' => 'rrd_cached',
                        'config_value' => $path['value'],
                        'config_group' => $row['config_group'],
                        'config_group_id' => $row['config_group_id']
                    ],
                ];
                $query = 'INSERT INTO cfg_centreonbroker_info (config_id, config_key, config_value, '
                    . 'config_group, config_group_id ) VALUES '
                    . '( :config_id, :config_key, :config_value, '
                    . ':config_group, :config_group_id)';
                $statement = $pearDB->prepare($query);
                foreach ($brokerInfoData as $rowData) {
                    $statement->bindValue(':config_id', (int) $rowData['config_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':config_key', $rowData['config_key']);
                    $statement->bindValue(':config_value', $rowData['config_value']);
                    $statement->bindValue(':config_group', $rowData['config_group']);
                    $statement->bindValue(':config_group_id', (int) $rowData['config_group_id'], \PDO::PARAM_INT);
                    $statement->execute();
                }
            }

            $statement = $pearDB->prepare(
                "DELETE FROM cfg_centreonbroker_info WHERE `config_id` = :config_id"
                . " AND config_group_id = :config_group_id"
                . " AND config_group = 'output' AND ( config_key = 'port' OR config_key = 'path') "
            );
            $statement->bindValue(':config_id', (int) $row['config_id'], \PDO::PARAM_INT);
            $statement->bindValue(':config_group_id', (int) $row['config_group_id'], \PDO::PARAM_INT);
            $statement->execute();
        }
        $pearDB->query(
            "DELETE FROM options WHERE `key` = 'rrdcached_enable'
                OR `key` = 'rrdcached_port' OR `key` = 'rrdcached_unix_path'"
        );
        $pearDB->commit();
    } catch (\PDOException $e) {
        $centreonLog->insertLog(
            2, // sql-error.log
            "UPGRADE : 19.10.0-beta.3 Unable to move rrd global cache option on broker form"
        );
        $pearDB->rollBack();
    }
}
