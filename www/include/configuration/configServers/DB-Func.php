<?php

/*
 * Copyright 2005-2021 Centreon
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

use Centreon\Domain\PlatformTopology\Model\PlatformRegistered;

if (!isset($centreon)) {
    exit();
}

require_once _CENTREON_PATH_ . "/www/class/centreon-config/centreonMainCfg.class.php";

const ZMQ = 1;
const SSH = 2;

/**
 * Retrieve the next available suffixes for this server name from database
 *
 * @param string $serverName Server name to process
 * @param int $numberOf Number of suffix requested
 * @param string $separator Character used to separate the server name and suffix
 *
 * @return array Return the next available suffixes
 * @throws Exception
 * @global CentreonDB $pearDB DB connector
 */
function getAvailableSuffixIds(
    string $serverName,
    int $numberOf,
    string $separator = '_'
): array {
    if ($numberOf < 0) {
        return [];
    }

    global $pearDB;
    /**
     * To avoid that this column value will be interpreted like regular
     * expression in the database query
     */
    $serverName = preg_quote($serverName);

    // Get list of suffix already used
    $serverName = CentreonDB::escape($serverName);
    $query = "SELECT CAST(SUBSTRING_INDEX(name,'_',-1) AS INT) AS suffix "
        . "FROM nagios_server WHERE name REGEXP '^" . $serverName . $separator . "[0-9]+$' "
        . "ORDER BY suffix";
    $results = $pearDB->query($query);

    $notAvailableSuffixes = [];

    while ($result = $results->fetch()) {
        $suffix = (int)$result['suffix'];
        if (!in_array($suffix, $notAvailableSuffixes)) {
            $notAvailableSuffixes[] = $suffix;
        }
    }

    /**
     * Search for available suffixes taking into account those found in the database
     */
    $nextAvailableSuffixes = [];
    $counter = 1;
    while (count($nextAvailableSuffixes) < $numberOf) {
        if (!in_array($counter, $notAvailableSuffixes)) {
            $nextAvailableSuffixes[] = $counter;
        }
        $counter++;
    }

    return $nextAvailableSuffixes;
}

/**
 * Check if Master Remote is selected to use additional Remote Server
 *
 * @param array $values the values of Remote Servers selectboxes
 *
 * @return false only if additional Remote Server selectbox is not empty and Master selectbox is empty
 */
function testAdditionalRemoteServer(array $values)
{
    # If remote_additional_id select2 is not empty
    if (
        isset($values[0])
        && is_array($values[0])
        && count($values[0]) >= 1
    ) {
        # If Master Remote Server is not empty
        if (isset($values[1]) && trim($values[1]) != '') {
            return true;
        } else {
            return false;
        }
    }

    return true;
}

/**
 * Check if the name already exist in database
 *
 * @param string $name Name to check
 *
 * @return bool Return true if the name does not exist in database
 */
function testExistence($name = null): bool
{
    global $pearDB, $form;

    $id = null;

    if (isset($form)) {
        $id = $form->getSubmitValue('id');
    }

    $query = "SELECT name, id FROM `nagios_server` WHERE `name` = '" . htmlentities($name, ENT_QUOTES, "UTF-8") . "'";
    $dbResult = $pearDB->query($query);
    $row = $dbResult->fetch();

    if ($dbResult->rowCount() >= 1 && $row["id"] == $id) {
        return true;
    } elseif ($dbResult->rowCount() >= 1 && $row["id"] != $id) {
        return false;
    } else {
        return true;
    }
}

/**
 * Test is the IP address is a valid IPv4/IPv6 or FQDN
 *
 * @param string $ipAddress The IP address to test
 * @return bool
 */
function isValidIpAddress(string $ipAddress): bool
{
    // Check IPv6, IPv4 and FQDN format
    if (
        !filter_var($ipAddress, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
        && !filter_var($ipAddress, FILTER_VALIDATE_IP)
    ) {
        return false;
    } else {
        return true;
    }
}

/**
 * Enable a server
 *
 * @param int $id Id of the server
 *
 * @throws Exception
 * @global CentreonDB $pearDB DB connector
 * @global Centreon $centreon
 */
function enableServerInDB(int $id): void
{
    global $pearDB, $centreon;

    $dbResult = $pearDB->query("SELECT name FROM `nagios_server` WHERE `id` = " . $id . " LIMIT 1");
    $row = $dbResult->fetch();

    $pearDB->query("UPDATE `nagios_server` SET `ns_activate` = '1' WHERE id = " . $id);
    $centreon->CentreonLogAction->insertLog("poller", $id, $row['name'], "enable");

    $query = 'SELECT MIN(`nagios_id`) AS idEngine FROM cfg_nagios WHERE `nagios_server_id` = ' . $id;
    $dbResult = $pearDB->query($query);
    $idEngine = $dbResult->fetch();

    if ($idEngine['idEngine']) {
        $pearDB->query(
            "UPDATE `cfg_nagios` SET `nagios_activate` = '0' WHERE `nagios_server_id` = " . $id
        );
        $pearDB->query(
            "UPDATE cfg_nagios SET nagios_activate = '1' WHERE nagios_id = " . (int)$idEngine['idEngine']
        );
    }
}

/**
 * Disable a server
 *
 * @param int $id Id of the server
 *
 * @throws Exception
 * @global CentreonDB $pearDB DB connector
 * @global Centreon $centreon
 */
function disableServerInDB(int $id): void
{
    global $pearDB, $centreon;

    $dbResult = $pearDB->query("SELECT name FROM `nagios_server` WHERE `id` = " . $id . " LIMIT 1");
    $row = $dbResult->fetch();

    $pearDB->query("UPDATE `nagios_server` SET `ns_activate` = '0' WHERE id = " . $id);

    $centreon->CentreonLogAction->insertLog(
        'poller',
        $id,
        $row['name'],
        'disable'
    );

    $pearDB->query(
        "UPDATE `cfg_nagios` SET `nagios_activate` = '0' WHERE `nagios_server_id` = " . $id
    );
}

/**
 * Delete a server
 *
 * @param array $serverIds
 *
 * @global CentreonDB $pearDB DB connector
 * @global Centreon $centreon
 */
function deleteServerInDB(array $serverIds): void
{
    global $pearDB, $pearDBO, $centreon;

    foreach (array_keys($serverIds) as $serverId) {
        $statement = $pearDB->prepare('SELECT `id`, `type` FROM `platform_topology` WHERE server_id = :serverId ');
        $statement->bindValue(':serverId', (int)$serverId, \PDO::PARAM_INT);
        $statement->execute();

        //If the deleted platform is a remote, reassign the parent_id of its children to the top level platform
        if (
            ($platformInTopology = $statement->fetch(\PDO::FETCH_ASSOC))
            && $platformInTopology['type'] === PlatformRegistered::TYPE_REMOTE
        ) {
            $statement = $pearDB->query('SELECT id FROM `platform_topology` WHERE parent_id IS NULL');
            if ($topPlatform = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $statement2 = $pearDB->prepare('
                    UPDATE `platform_topology`
                    SET `parent_id` = :topPlatformId
                    WHERE `parent_id` = :remoteId
                ');
                $statement2->bindValue(':topPlatformId', (int)$topPlatform['id'], \PDO::PARAM_INT);
                $statement2->bindValue(':remoteId', (int)$platformInTopology['id'], \PDO::PARAM_INT);
                $statement2->execute();
            }
        }

        $result = $pearDB->query(
            "SELECT name, ns_ip_address AS ip FROM `nagios_server` WHERE `id` = " . $serverId . " LIMIT 1"
        );
        $row = $result->fetch();

        // Is a Remote Server?
        $result = $pearDB->query(
            "SELECT * FROM remote_servers WHERE ip = '" . $row['ip'] . "'"
        );

        if ($result->numRows() > 0) {
            // Delete entry from remote_servers
            $pearDB->query(
                "DELETE FROM remote_servers WHERE ip = '" . $row['ip'] . "'"
            );
            // Delete all relation between this Remote Server and pollers
            $pearDB->query(
                "DELETE FROM rs_poller_relation WHERE remote_server_id = '" . $serverId . "'"
            );
        } else {
            // Delete all relation between this poller and Remote Servers
            $pearDB->query(
                "DELETE FROM rs_poller_relation WHERE poller_server_id = '" . $serverId . "'"
            );
        }

        $pearDB->query('DELETE FROM `nagios_server` WHERE id = ' . $serverId);
        $pearDBO->query(
            "UPDATE `instances` SET deleted = '1' WHERE instance_id = " . $serverId
        );
        deleteCentreonBrokerByPollerId($serverId);

        $centreon->CentreonLogAction->insertLog(
            'poller',
            $serverId,
            $row['name'],
            'd'
        );
    }
}

/**
 * Delete Centreon Broker configurations
 *
 * @param int $id The Id poller
 *
 * @global CentreonDB $pearDB DB connector
 */
function deleteCentreonBrokerByPollerId(int $id)
{
    global $pearDB;

    $pearDB->query(
        'DELETE FROM cfg_centreonbroker WHERE ns_nagios_server = ' . $id
    );
}

/**
 * Duplicate server
 *
 * @param array $server List of server id to duplicate
 * @param array $nbrDup Number of duplications per server id
 *
 * @throws Exception
 * @global CentreonDB $pearDB DB connector
 */
function duplicateServer(array $server, array $nbrDup): void
{
    global $pearDB;

    $obj = new CentreonMainCfg();

    foreach (array_keys($server) as $serverId) {
        $result = $pearDB->query(
            'SELECT * FROM `nagios_server` WHERE id = ' . (int)$serverId . ' LIMIT 1'
        );
        $rowServer = $result->fetch();
        $rowServer["id"] = null;
        $rowServer["ns_activate"] = '0';
        $rowServer["is_default"] = '0';
        $rowServer["localhost"] = '0';
        $result->closeCursor();

        if (!isset($rowServer['name'])) {
            continue;
        }

        $rowBks = $obj->getBrokerModules($serverId);

        $availableSuffix = getAvailableSuffixIds(
            $rowServer['name'],
            $nbrDup[$serverId]
        );

        foreach ($availableSuffix as $suffix) {
            $queryValues = null;
            $serverName = null;
            foreach ($rowServer as $columnName => $columnValue) {
                if ($columnName == 'name') {
                    $columnValue .= "_" . $suffix;
                    $serverName = $columnValue;
                }

                if (is_null($queryValues)) {
                    $queryValues .= $columnValue != null
                        ? ("'" . $columnValue . "'")
                        : "NULL";
                } else {
                    $queryValues .= $columnValue != null
                        ? (", '" . $columnValue . "'")
                        : ", NULL";
                }
            }
            if (!is_null($queryValues) && testExistence($serverName)) {
                if ($queryValues) {
                    $pearDB->query('INSERT INTO `nagios_server` VALUES (' . $queryValues . ')');
                }

                $queryGetId = 'SELECT id FROM nagios_server WHERE name = "' . $serverName . '"';
                try {
                    $res = $pearDB->query($queryGetId);
                    if ($res->rowCount() > 0) {
                        $row = $res->fetch();
                        $iId = $obj->insertServerInCfgNagios($serverId, $row['id'], $serverName);
                        $obj->insertCfgNagiosLogger($iId, $serverId);

                        if (isset($rowBks)) {
                            foreach ($rowBks as $keyBk => $valBk) {
                                if ($valBk["broker_module"]) {
                                    $rqBk = "INSERT INTO cfg_nagios_broker_module (`cfg_nagios_id`, `broker_module`)" .
                                        " VALUES ('" . $iId . "', '" . $valBk["broker_module"] . "')";
                                }
                                $pearDB->query($rqBk);
                            }
                        }

                        $queryRel = 'INSERT INTO cfg_resource_instance_relations (resource_id, instance_id) ' .
                            'SELECT b.resource_id, ' . $row['id'] . ' FROM ' .
                            'cfg_resource_instance_relations as b WHERE b.instance_id = ' . $serverId;
                        $pearDB->query($queryRel);
                        $queryCmd = 'INSERT INTO poller_command_relations (poller_id, command_id, command_order) ' .
                            'SELECT ' . $row['id'] . ', b.command_id, b.command_order FROM ' .
                            'poller_command_relations as b WHERE b.poller_id = ' . $serverId;
                        $pearDB->query($queryCmd);
                    }
                } catch (\PDOException $e) {
                    // Nothing to do
                }
            }
        }
    }
}

/**
 * Insert additionnal Remote Servers relation
 *
 * @param int $id Id of the server
 * @param array $remotes Id of the additionnal Remote Servers
 *
 * @return void
 * @throws Exception
 *
 * @global CentreonDB $pearDB DB connector
 */
function additionnalRemoteServersByPollerId(int $id, array $remotes = null): void
{
    global $pearDB;

    $statement = $pearDB->prepare("DELETE FROM rs_poller_relation WHERE poller_server_id = :id");
    $statement->bindParam(':id', $id, \PDO::PARAM_INT);
    $statement->execute();

    if (!is_null($remotes)) {
        $statement = $pearDB->prepare("INSERT INTO rs_poller_relation VALUES (:remote_id,:poller_id)");
        foreach ($remotes as $remote) {
            $statement->bindParam(':remote_id', $remote, \PDO::PARAM_INT);
            $statement->bindParam(':poller_id', $id, \PDO::PARAM_INT);
            $statement->execute();
        }
    }
}

/**
 * Insert a new server
 *
 * @param array $data Data of the new server
 *
 * @return int Id of the new server
 */
function insertServerInDB(array $data): int
{
    $srvObj = new CentreonMainCfg();

    $sName = '';
    $id = insertServer($data);

    if (isset($data['name'])) {
        $sName = $data['name'];
    }
    $iIdNagios = $srvObj->insertServerInCfgNagios(-1, $id, $sName);

    additionnalRemoteServersByPollerId($id, $data["remote_additional_id"]);

    if (!empty($iIdNagios)) {
        $srvObj->insertBrokerDefaultDirectives($iIdNagios, 'ui');
        $srvObj->insertDefaultCfgNagiosLogger($iIdNagios);
    }
    addUserRessource($id);

    return $id;
}

/**
 * Create a server in database
 *
 * @param array $data Data of the new server
 *
 * @return int Id of the new server
 * @global Centreon $centreon
 * @global CentreonDB $pearDB DB connector
 */
function insertServer(array $data): int
{
    global $pearDB, $centreon;

    $retValue = [];
    $rq = "INSERT INTO `nagios_server` (`name` , `localhost`, `ns_ip_address`, `ssh_port`, " .
        "`gorgone_communication_type`, `gorgone_port`, `nagios_bin`, `nagiostats_bin`, " .
        "`engine_start_command`, `engine_stop_command`, `engine_restart_command`, `engine_reload_command`, " .
        "`init_script_centreontrapd`, `snmp_trapd_path_conf`, " .
        "`nagios_perfdata` , `broker_reload_command`, " .
        "`centreonbroker_cfg_path`, `centreonbroker_module_path`, `centreonconnector_path`, " .
        "`is_default`, `ns_activate`, `centreonbroker_logs_path`, `remote_id`, `remote_server_use_as_proxy`) ";
    $rq .= "VALUES (";

    if (isset($data["name"]) && $data["name"] != null) {
        $rq .= ':name, ';
        $retValue[':name'] = htmlentities(trim($data["name"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["localhost"]["localhost"]) && $data["localhost"]["localhost"] != null) {
        $rq .= ':localhost, ';
        $retValue[':localhost'] = htmlentities($data["localhost"]["localhost"], ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["ns_ip_address"]) && $data["ns_ip_address"] != null) {
        $rq .= ':ns_ip_address, ';
        $retValue[':ns_ip_address'] = htmlentities(trim($data["ns_ip_address"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["ssh_port"]) && $data["ssh_port"] != null) {
        $rq .= ':ssh_port, ';
        $retValue[':ssh_port'] = (int)$data["ssh_port"];
    } else {
        $rq .= "22, ";
    }
    if (
        isset($data["gorgone_communication_type"]['gorgone_communication_type'])
        && $data["gorgone_communication_type"]['gorgone_communication_type'] != null
    ) {
        $rq .= ':gorgone_communication_type, ';
        $retValue[':gorgone_communication_type'] =
            htmlentities(trim($data["gorgone_communication_type"]['gorgone_communication_type']), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "'1', ";
    }
    if (isset($data["gorgone_port"]) && $data["gorgone_port"] != null) {
        $rq .= ':gorgone_port, ';
        $retValue[':gorgone_port'] = (int)$data["gorgone_port"];
    } else {
        $rq .= "5556, ";
    }
    if (isset($data["nagios_bin"]) && $data["nagios_bin"] != null) {
        $rq .= ':nagios_bin, ';
        $retValue[':nagios_bin'] = htmlentities(trim($data["nagios_bin"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["nagiostats_bin"]) && $data["nagiostats_bin"] != null) {
        $rq .= ':nagiostats_bin, ';
        $retValue[':nagiostats_bin'] = htmlentities(trim($data["nagiostats_bin"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["engine_start_command"]) && $data["engine_start_command"] != null) {
        $rq .= ':engine_start_command, ';
        $retValue[':engine_start_command'] = htmlentities(trim($data["engine_start_command"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["engine_stop_command"]) && $data["engine_stop_command"] != null) {
        $rq .= ':engine_stop_command, ';
        $retValue[':engine_stop_command'] = htmlentities(trim($data["engine_stop_command"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["engine_restart_command"]) && $data["engine_restart_command"] != null) {
        $rq .= ':engine_restart_command, ';
        $retValue[':engine_restart_command'] = htmlentities(trim($data["engine_restart_command"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["engine_reload_command"]) && $data["engine_reload_command"] != null) {
        $rq .= ':engine_reload_command, ';
        $retValue[':engine_reload_command'] = htmlentities(trim($data["engine_reload_command"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["init_script_centreontrapd"]) && $data["init_script_centreontrapd"] != null) {
        $rq .= ':init_script_centreontrapd, ';
        $retValue[':init_script_centreontrapd'] =
            htmlentities(trim($data["init_script_centreontrapd"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["snmp_trapd_path_conf"]) && $data["snmp_trapd_path_conf"] != null) {
        $rq .= ':snmp_trapd_path_conf, ';
        $retValue[':snmp_trapd_path_conf'] = htmlentities(trim($data["snmp_trapd_path_conf"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["nagios_perfdata"]) && $data["nagios_perfdata"] != null) {
        $rq .= ':nagios_perfdata, ';
        $retValue[':nagios_perfdata'] = htmlentities(trim($data["nagios_perfdata"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["broker_reload_command"]) && $data["broker_reload_command"] != null) {
        $rq .= ':broker_reload_command, ';
        $retValue[':broker_reload_command'] = htmlentities(trim($data["broker_reload_command"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["centreonbroker_cfg_path"]) && $data["centreonbroker_cfg_path"] != null) {
        $rq .= ':centreonbroker_cfg_path, ';
        $retValue[':centreonbroker_cfg_path'] =
            htmlentities(trim($data["centreonbroker_cfg_path"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["centreonbroker_module_path"]) && $data["centreonbroker_module_path"] != null) {
        $rq .= ':centreonbroker_module_path, ';
        $retValue[':centreonbroker_module_path'] =
            htmlentities(trim($data["centreonbroker_module_path"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["centreonconnector_path"]) && $data["centreonconnector_path"] != null) {
        $rq .= ':centreonconnector_path, ';
        $retValue[':centreonconnector_path'] = htmlentities(trim($data["centreonconnector_path"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["is_default"]["is_default"]) && $data["is_default"]["is_default"] != null) {
        $rq .= ':is_default, ';
        $retValue[':is_default'] = htmlentities(trim($data["is_default"]["is_default"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["ns_activate"]["ns_activate"]) && $data["ns_activate"]["ns_activate"] != 2) {
        $rq .= ':ns_activate, ';
        $retValue[':ns_activate'] = $data["ns_activate"]["ns_activate"];
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["centreonbroker_logs_path"]) && $data["centreonbroker_logs_path"] != null) {
        $rq .= ':centreonbroker_logs_path, ';
        $retValue[':centreonbroker_logs_path'] =
            htmlentities(trim($data["centreonbroker_logs_path"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (isset($data["remote_id"]) && $data["remote_id"] != null) {
        $rq .= ':remote_id, ';
        $retValue[':remote_id'] = htmlentities(trim($data["remote_id"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    if (
        isset($data["remote_server_use_as_proxy"]["remote_server_use_as_proxy"])
        && $data["remote_server_use_as_proxy"]["remote_server_use_as_proxy"] != null
    ) {
        $rq .= ':remote_server_use_as_proxy ';
        $retValue[':remote_server_use_as_proxy'] =
            htmlentities($data["remote_server_use_as_proxy"]["remote_server_use_as_proxy"], ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL ";
    }
    $rq .= ")";
    $stmt = $pearDB->prepare($rq);
    foreach ($retValue as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();

    $result = $pearDB->query("SELECT MAX(id) as last_id FROM `nagios_server`");
    $poller = $result->fetch();
    $result->closeCursor();

    try {
        insertServerIntoPlatformTopology($retValue, (int)$poller['last_id']);
    } catch (Exception $e) {
        // catch exception but don't return anything to avoid blank pages on form
    }

    if (isset($_REQUEST['pollercmd'])) {
        $instanceObj = new CentreonInstance($pearDB);
        $instanceObj->setCommands($poller['last_id'], $_REQUEST['pollercmd']);
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($data);
    $centreon->CentreonLogAction->insertLog(
        "poller",
        $poller['last_id'] ?? null,
        CentreonDB::escape($data["name"]),
        "a",
        $fields
    );

    return (int)$poller["last_id"];
}

/**
 * @param int $serverId Id of the server
 *
 * @return bool Return true if ok
 * @global CentreonDB $pearDB DB connector
 *                              global Centreon $centreon
 */
function addUserRessource(int $serverId): bool
{
    global $pearDB, $centreon;

    $queryInsert = "INSERT INTO cfg_resource_instance_relations (resource_id, instance_id) VALUES (%d, %d)";
    $queryGetResources = "SELECT resource_id, resource_name FROM cfg_resource ORDER BY resource_id";

    try {
        $res = $pearDB->query($queryGetResources);
    } catch (\PDOException $e) {
        return false;
    }
    $isInsert = [];
    while ($resource = $res->fetch()) {
        if (!in_array($resource['resource_name'], $isInsert)) {
            $isInsert[] = $resource['resource_name'];
            $query = sprintf(
                $queryInsert,
                (int)$resource['resource_id'],
                $serverId
            );
            $pearDB->query($query);

            /* Prepare value for changelog */
            $fields = CentreonLogAction::prepareChanges($resource);
            $centreon->CentreonLogAction->insertLog(
                "resource",
                $serverId,
                CentreonDB::escape($resource["resource_name"]),
                "a",
                $fields
            );
        }
    }

    return true;
}

/**
 * Update Remote Server information
 *
 * @param array $data
 * @param string|null $oldIpAddress Old IP address of the server before the upgrade
 */
function updateRemoteServerInformation(array $data, string $oldIpAddress = null)
{
    global $pearDB;

    $statement = $pearDB->prepare("SELECT COUNT(*) AS total FROM remote_servers WHERE ip = :ip");
    $statement->bindValue(':ip', $oldIpAddress ?? $data["ns_ip_address"]);
    $statement->execute();
    $total = (int) $statement->fetch(\PDO::FETCH_ASSOC)['total'];

    if ($total === 1) {
        $statement = $pearDB->prepare("
            UPDATE remote_servers
            SET http_method = :http_method, http_port = :http_port,
                no_check_certificate = :no_check_certificate, no_proxy = :no_proxy, ip = :new_ip
            WHERE ip = :ip
        ");
        $statement->bindValue(':http_method', $data["http_method"]);
        $statement->bindValue(':http_port', $data["http_port"] ?? null, \PDO::PARAM_INT);
        $statement->bindValue(':no_proxy', $data["no_proxy"]["no_proxy"]);
        $statement->bindValue(':no_check_certificate', $data["no_check_certificate"]["no_check_certificate"]);
        $statement->bindValue(':new_ip', $data["ns_ip_address"]);
        $statement->bindValue(':ip', $oldIpAddress ?? $data["ns_ip_address"]);
        $statement->execute();
    }
}

/**
 * Update a server
 *
 * @param int $id
 * @param array $data
 *
 * @throws Exception
 */
function updateServer(int $id, array $data): void
{
    global $pearDB, $centreon;

    if ($data["localhost"]["localhost"] == 1) {
        $pearDB->query("UPDATE `nagios_server` SET `localhost` = '0'");
    }
    if ($data["is_default"]["is_default"] == 1) {
        $pearDB->query("UPDATE `nagios_server` SET `is_default` = '0'");
    }
    $retValue = [];

    // We retrieve IP address that was defined before the update request
    $statement = $pearDB->prepare('SELECT ns_ip_address FROM nagios_server WHERE id = :id');
    $statement->bindValue(':id', $id, \PDO::PARAM_INT);
    $statement->execute();
    $ipAddressBeforeChanges = ($result = $statement->fetch(\PDO::FETCH_ASSOC))
        ? $result['ns_ip_address']
        : null;

    $rq = "UPDATE `nagios_server` SET ";
    $rq .= "`name` = ";
    if (isset($data["name"]) && $data["name"] != null) {
        $rq .= ':name, ';
        $retValue[':name'] = $data["name"];
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`localhost` = ";
    if (isset($data["localhost"]["localhost"]) && $data["localhost"]["localhost"] != null) {
        $rq .= ':localhost, ';
        $retValue[':localhost'] = htmlentities($data["localhost"]["localhost"], ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`ns_ip_address` = ";
    if (isset($data["ns_ip_address"]) && $data["ns_ip_address"] != null) {
        $rq .= ':ns_ip_address, ';
        $retValue[':ns_ip_address'] = htmlentities(trim($data["ns_ip_address"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`ssh_port` = ";
    if (isset($data["ssh_port"]) && $data["ssh_port"] != null) {
        $rq .= ':ssh_port, ';
        $retValue[':ssh_port'] = (int)$data["ssh_port"];
    } else {
        $rq .= "22, ";
    }
    $rq .= "`gorgone_communication_type` = ";
    if (
        isset($data["gorgone_communication_type"]['gorgone_communication_type'])
        && $data["gorgone_communication_type"]['gorgone_communication_type'] != null
    ) {
        $rq .= ':gorgone_communication_type, ';
        $retValue[':gorgone_communication_type'] =
            htmlentities(trim($data["gorgone_communication_type"]['gorgone_communication_type']), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "'1', ";
    }
    $rq .= "`gorgone_port` = ";
    if (isset($data["gorgone_port"]) && $data["gorgone_port"] != null) {
        $rq .= ':gorgone_port, ';
        $retValue[':gorgone_port'] = (int)$data["gorgone_port"];
    } else {
        $rq .= "5556, ";
    }
    $rq .= "`engine_start_command` = ";
    if (isset($data["engine_start_command"]) && $data["engine_start_command"] != null) {
        $rq .= ':engine_start_command, ';
        $retValue[':engine_start_command'] = htmlentities(trim($data["engine_start_command"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`engine_stop_command` = ";
    if (isset($data["engine_stop_command"]) && $data["engine_stop_command"] != null) {
        $rq .= ':engine_stop_command, ';
        $retValue[':engine_stop_command'] = htmlentities(trim($data["engine_stop_command"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`engine_restart_command` = ";
    if (isset($data["engine_restart_command"]) && $data["engine_restart_command"] != null) {
        $rq .= ':engine_restart_command, ';
        $retValue[':engine_restart_command'] = htmlentities(trim($data["engine_restart_command"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`engine_reload_command` = ";
    if (isset($data["engine_reload_command"]) && $data["engine_reload_command"] != null) {
        $rq .= ':engine_reload_command, ';
        $retValue[':engine_reload_command'] = htmlentities(trim($data["engine_reload_command"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`init_script_centreontrapd` = ";
    if (isset($data["init_script_centreontrapd"]) && $data["init_script_centreontrapd"] != null) {
        $rq .= ':init_script_centreontrapd, ';
        $retValue[':init_script_centreontrapd'] =
            htmlentities(trim($data["init_script_centreontrapd"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`snmp_trapd_path_conf` = ";
    if (isset($data["snmp_trapd_path_conf"]) && $data["snmp_trapd_path_conf"] != null) {
        $rq .= ':snmp_trapd_path_conf, ';
        $retValue[':snmp_trapd_path_conf'] = htmlentities(trim($data["snmp_trapd_path_conf"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`nagios_bin` = ";
    if (isset($data["nagios_bin"]) && $data["nagios_bin"] != null) {
        $rq .= ':nagios_bin, ';
        $retValue[':nagios_bin'] = htmlentities(trim($data["nagios_bin"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`nagiostats_bin` = ";
    if (isset($data["nagiostats_bin"]) && $data["nagiostats_bin"] != null) {
        $rq .= ':nagiostats_bin, ';
        $retValue[':nagiostats_bin'] = htmlentities(trim($data["nagiostats_bin"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`nagios_perfdata` = ";
    if (isset($data["nagios_perfdata"]) && $data["nagios_perfdata"] != null) {
        $rq .= ':nagios_perfdata, ';
        $retValue[':nagios_perfdata'] = htmlentities(trim($data["nagios_perfdata"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`broker_reload_command` = ";
    if (isset($data["broker_reload_command"]) && $data["broker_reload_command"] != null) {
        $rq .= ':broker_reload_command, ';
        $retValue[':broker_reload_command'] = htmlentities(trim($data["broker_reload_command"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`centreonbroker_cfg_path` = ";
    if (isset($data["centreonbroker_cfg_path"]) && $data["centreonbroker_cfg_path"] != null) {
        $rq .= ':centreonbroker_cfg_path, ';
        $retValue[':centreonbroker_cfg_path'] =
            htmlentities(trim($data["centreonbroker_cfg_path"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`centreonbroker_module_path` = ";
    if (isset($data["centreonbroker_module_path"]) && $data["centreonbroker_module_path"] != null) {
        $rq .= ':centreonbroker_module_path, ';
        $retValue[':centreonbroker_module_path'] =
            htmlentities(trim($data["centreonbroker_module_path"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`centreonconnector_path` = ";
    if (isset($data["centreonconnector_path"]) && $data["centreonconnector_path"] != null) {
        $rq .= ':centreonconnector_path, ';
        $retValue[':centreonconnector_path'] = htmlentities(trim($data["centreonconnector_path"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`is_default` = ";
    if (isset($data["is_default"]['is_default']) && $data["is_default"]['is_default'] != null) {
        $rq .= ':isDefault, ';
        $retValue[':isDefault'] = (int)$data["is_default"]['is_default'];
    } else {
        $rq .= "0, ";
    }
    $rq .= "`centreonbroker_logs_path` = ";
    if (isset($data["centreonbroker_logs_path"]) && $data["centreonbroker_logs_path"] != null) {
        $rq .= ':centreonbroker_logs_path, ';
        $retValue[':centreonbroker_logs_path'] =
            htmlentities(trim($data["centreonbroker_logs_path"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`remote_id` = ";
    if (isset($data["remote_id"]) && $data["remote_id"] != null) {
        $rq .= ':remote_id, ';
        $retValue[':remote_id'] = htmlentities(trim($data["remote_id"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "`ns_activate` = ";
    if (isset($data["ns_activate"]["ns_activate"]) && $data["ns_activate"]["ns_activate"] != null) {
        $rq .= ':ns_activate, ';
        $retValue[':ns_activate'] = htmlentities(trim($data["ns_activate"]["ns_activate"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "'1', ";
    }
    $rq .= "`remote_server_use_as_proxy` = ";
    if (
        isset($data["remote_server_use_as_proxy"]["remote_server_use_as_proxy"])
        && $data["remote_server_use_as_proxy"]["remote_server_use_as_proxy"] != null
    ) {
        $rq .= ':remote_server_use_as_proxy ';
        $retValue[':remote_server_use_as_proxy'] =
            htmlentities(trim($data["remote_server_use_as_proxy"]["remote_server_use_as_proxy"]), ENT_QUOTES, "UTF-8");
    } else {
        $rq .= "'1' ";
    }
    $rq .= "WHERE id = " . (int)$id;
    $stmt = $pearDB->prepare($rq);
    foreach ($retValue as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    try {
        updateServerIntoPlatformTopology($retValue, $id);
    } catch (\Exception $e) {
        // catch exception but don't return anything to avoid blank pages on form
    }

    updateRemoteServerInformation($data, $ipAddressBeforeChanges);
    additionnalRemoteServersByPollerId(
        $id,
        $data["remote_additional_id"] ?? null
    );

    if (isset($_REQUEST['pollercmd'])) {
        $instanceObj = new CentreonInstance($pearDB);
        $instanceObj->setCommands($id, $_REQUEST['pollercmd']);
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($data);
    $centreon->CentreonLogAction->insertLog("poller", $id, CentreonDB::escape($data["name"]), "c", $fields);
}

/**
 * Get poller state if a service or an host has changed.
 *
 * @param array $pollers Listing of pollers
 *
 * @return an array of id => boolean. True if the configuration has changed
 * @global array $conf_centreon Database configuration
 * @global CentreonDB $pearDBO DB connector for centreon_storage database
 */
function getChangeState(array $pollers): array
{
    global $pearDBO, $conf_centreon, $pearDB;

    $results = [];
    $lastRestart = -1;
    foreach ($pollers as $id => $pollerRestart) {
        $results[$id] = false;
        if (is_numeric($pollerRestart) && ($lastRestart === -1 || $lastRestart > $pollerRestart)) {
            $lastRestart = $pollerRestart;
        }
    }
    if ($lastRestart === -1) {
        return $results;
    }
    $pollersSearch = implode(',', array_keys($pollers));

    $query = <<<REQUEST
SELECT instance_id, COUNT(*) as num_logs, MAX(action_log_date) as action_log_date FROM log_action
    INNER JOIN (
        SELECT instance_id, host_id FROM hosts where instance_id IN ($pollersSearch)
    ) AS subtable ON log_action.action_type = 'd' AND log_action.object_id = subtable.host_id
WHERE log_action.object_type = 'host' AND action_log_date > $lastRestart GROUP BY subtable.instance_id
UNION
SELECT instance_id, COUNT(*) as num_logs, MAX(action_log_date) as action_log_date FROM log_action
    INNER JOIN (
        SELECT nagios_server_id as instance_id, host_host_id as host_id FROM {$conf_centreon['db']}.ns_host_relation
         WHERE nagios_server_id IN ($pollersSearch)
    ) AS subtable ON log_action.object_id = subtable.host_id
WHERE log_action.object_type = 'host' AND action_log_date > $lastRestart GROUP BY subtable.instance_id
UNION

SELECT instance_id, COUNT(*) as num_logs, MAX(action_log_date) as action_log_date FROM log_action
    INNER JOIN (
        SELECT h.instance_id, s.service_id FROM hosts h, services s
        WHERE h.host_id = s.host_id AND h.instance_id IN ($pollersSearch)
    ) AS subtable ON log_action.action_type = 'd' AND log_action.object_id = subtable.service_id
WHERE log_action.object_type = 'service' AND action_log_date > $lastRestart GROUP BY subtable.instance_id
UNION
SELECT instance_id, COUNT(*) as num_logs, MAX(action_log_date) as action_log_date FROM log_action
    INNER JOIN (
        SELECT nagios_server_id as instance_id, service_service_id as service_id
        FROM {$conf_centreon['db']}.ns_host_relation nhr, {$conf_centreon['db']}.host_service_relation hsr
        WHERE nagios_server_id IN ($pollersSearch)
            AND hsr.host_host_id = nhr.host_host_id
    ) AS subtable ON log_action.object_id = subtable.service_id
WHERE log_action.object_type = 'service' AND action_log_date > $lastRestart GROUP BY subtable.instance_id
UNION

SELECT instance_id, COUNT(*) as num_logs, MAX(action_log_date) as action_log_date FROM log_action
    INNER JOIN (
        SELECT h.instance_id, servicegroup_id FROM services_servicegroups sg
        INNER JOIN hosts h ON h.host_id = sg.host_id AND h.instance_id IN ($pollersSearch)
    ) AS subtable ON log_action.action_type = 'd' AND log_action.object_id = subtable.servicegroup_id
WHERE log_action.object_type = 'servicegroup' AND action_log_date > $lastRestart GROUP BY subtable.instance_id
UNION
SELECT instance_id, COUNT(*) as num_logs, MAX(action_log_date) as action_log_date FROM log_action
    INNER JOIN (
        SELECT nhr.nagios_server_id as instance_id, servicegroup_sg_id as servicegroup_id
        FROM {$conf_centreon['db']}.servicegroup_relation sgr, {$conf_centreon['db']}.ns_host_relation nhr
        WHERE nhr.nagios_server_id IN ($pollersSearch)
            AND sgr.host_host_id = nhr.host_host_id
    ) AS subtable ON log_action.object_id = subtable.servicegroup_id
WHERE log_action.object_type = 'servicegroup' AND action_log_date > $lastRestart GROUP BY subtable.instance_id
UNION

SELECT instance_id, COUNT(*) as num_logs, MAX(action_log_date) as action_log_date FROM log_action
    INNER JOIN (
        SELECT h.instance_id, hostgroup_id FROM hosts_hostgroups hg
        INNER JOIN hosts h ON h.host_id = hg.host_id AND h.instance_id IN ($pollersSearch)
    ) AS subtable ON log_action.action_type = 'd' AND log_action.object_id = subtable.hostgroup_id
WHERE log_action.object_type = 'hostgroup' AND action_log_date > $lastRestart GROUP BY subtable.instance_id
UNION
SELECT instance_id, COUNT(*) as num_logs, MAX(action_log_date) as action_log_date FROM log_action
    INNER JOIN (
        SELECT nhr.nagios_server_id as instance_id, hostgroup_hg_id as hostgroup_id
        FROM {$conf_centreon['db']}.hostgroup_relation hr, {$conf_centreon['db']}.ns_host_relation nhr
        WHERE nhr.nagios_server_id IN ($pollersSearch)
            AND hr.host_host_id = nhr.host_host_id
    ) AS subtable ON log_action.object_id = subtable.hostgroup_id
WHERE log_action.object_type = 'hostgroup' AND action_log_date > $lastRestart GROUP BY subtable.instance_id
REQUEST;

    $dbResult = $pearDBO->query($query);
    while (($row = $dbResult->fetch())) {
        if (
            $row['num_logs'] == 0 ||
            $row['action_log_date'] < $pollers[$row['instance_id']]
        ) {
            continue;
        }

        $results[$row['instance_id']] = true;
    }

    // also requires restart if flag updated is set to true
    $dbResult = $pearDB->query("SELECT id, updated FROM nagios_server WHERE id IN (" . $pollersSearch . ")");
    while (($row = $dbResult->fetch())) {
        if ($row['updated']) {
            $results[$row['id']] = true;
        }
    }

    return $results;
}

/**
 * Check if a service or an host has been changed for a specific poller.
 *
 * @param int $poller_id Id of the poller
 * @param int $last_restart Timestamp of the last restart
 *
 * @return bool Return true if the configuration has changed
 * @global array $conf_centreon Database configuration
 * @global CentreonDB $pearDBO DB connector for centreon_storage database
 */
function checkChangeState(int $poller_id, int $last_restart): bool
{
    global $pearDBO, $conf_centreon, $pearDB;

    if (!isset($last_restart) || $last_restart === "") {
        return false;
    }

    $query = <<<REQUEST
SELECT * FROM log_action
WHERE action_log_date > $last_restart
AND (
  (
    object_type = 'host'
    AND (
      (action_type = 'd'
        AND object_id IN (SELECT host_id FROM hosts where instance_id = $poller_id)
      )
      OR object_id IN (
        SELECT host_host_id
        FROM {$conf_centreon['db']}.ns_host_relation
        WHERE nagios_server_id = $poller_id
      )
    )
  )
  OR (
    object_type = 'service'
    AND (
      (
        action_type = 'd'
        AND object_id IN (
          SELECT service_id FROM services s
          INNER JOIN hosts h ON h.host_id = s.host_id
          WHERE h.instance_id = $poller_id
        )
      )
      OR object_id IN (
        SELECT service_service_id
        FROM {$conf_centreon['db']}.ns_host_relation nhr, {$conf_centreon['db']}.host_service_relation hsr
        WHERE nagios_server_id = $poller_id
        AND hsr.host_host_id = nhr.host_host_id
      )
    )
  )
  OR (
    object_type = 'servicegroup'
    AND (
      (
        action_type = 'd'
        AND object_id IN (
          SELECT DISTINCT servicegroup_id
          FROM services_servicegroups sg
          INNER JOIN hosts h ON h.host_id = sg.host_id
          WHERE h.instance_id = $poller_id
        )
      )
      OR object_id IN (
        SELECT DISTINCT servicegroup_sg_id
        FROM {$conf_centreon['db']}.servicegroup_relation sgr, {$conf_centreon['db']}.ns_host_relation nhr
        WHERE sgr.host_host_id = nhr.host_host_id
        AND nhr.nagios_server_id = $poller_id
      )
    )
  )
  OR (
    object_type = 'hostgroup'
    AND (
      (
        action_type = 'd'
        AND object_id IN (
          SELECT DISTINCT hostgroup_id
          FROM hosts_hostgroups hg
          INNER JOIN hosts h ON h.host_id = hg.host_id
          WHERE h.instance_id = $poller_id
        )
      )
      OR object_id IN (
        SELECT DISTINCT hr.hostgroup_hg_id
        FROM {$conf_centreon['db']}.hostgroup_relation hr, {$conf_centreon['db']}.ns_host_relation nhr
        WHERE hr.host_host_id = nhr.host_host_id
        AND nhr.nagios_server_id = $poller_id
      )
    )
  )
)
REQUEST;

    $dbResult = $pearDBO->query($query);
    if ($dbResult->rowCount()) {
        // requires restart if storage db has log information about changes
        return true;
    } else {
        // also requires restart if flag updated is set to true
        $configStmt = $pearDB->prepare("SELECT updated FROM nagios_server WHERE id = :pollerID LIMIT 1");
        $configStmt->bindValue(':pollerID', $poller_id, \PDO::PARAM_INT);
        $configStmt->execute();
        $row = $configStmt->fetch(\PDO::FETCH_ASSOC);
        if ($row['updated']) {
            return true;
        }
    }

    return false;
}

/**
 * Define LocalPoller as Default Poller if there is no Default Poller
 *
 * @return void
 */
function defineLocalPollerToDefault()
{
    global $pearDB;
    $query = "SELECT COUNT(*) AS `nb_of_default_poller` FROM `nagios_server` WHERE `is_default` = '1'";
    $statement = $pearDB->query($query);
    $result = $statement->fetch(\PDO::FETCH_ASSOC);

    if ($result !== false && ((int)$result['nb_of_default_poller'] === 0)) {
        $query = "UPDATE `nagios_server` SET `is_default` = '1' WHERE `localhost` = '1'";
        $pearDB->query($query);
    }
}

/**
 * Add Poller into platform_topology table
 *
 * @param array $pollerInformations
 * @param integer $pollerId
 */
function insertServerIntoPlatformTopology(array $pollerInformations, int $pollerId)
{
    global $pearDB;

    $serverIp = $pollerInformations[':ns_ip_address'];
    $serverName = $pollerInformations[':name'];
    $type = (int)$pollerInformations[':localhost'] == true ? 'central' : 'poller';

    /**
     * Prepare statement to get the Parent depending on Remote attachment or not.
     */
    if (isset($pollerInformations[':remote_id'])) {
        $statement = $pearDB->prepare("SELECT id FROM `platform_topology` WHERE `server_id` = :remoteId");
        $statement->bindValue(':remoteId', (int)$pollerInformations[':remote_id'], \PDO::PARAM_INT);
        $statement->execute();
    } else {
        $statement = $pearDB->query("SELECT id FROM `platform_topology` WHERE `type` = 'central'");
    }
    $parent = $statement->fetch(\PDO::FETCH_ASSOC);
    $statement->closeCursor();

    /**
     * If no Parent, Poller isn't attached to any remote server or Central
     */
    if (!empty($parent['id'])) {
        $parentId = (int)$parent['id'];
    } else {
        throw new \Exception(
            'Missing parent platform topology. Please register the parent first using the endpoint
            or the available script. For more details check the documentation'
        );
    }

    $statement = $pearDB->prepare("
        INSERT INTO `platform_topology` (`address`, `name`, `type`, `parent_id`, `server_id`, `pending`)
        VALUES (:address, :name, :type, :parent_id, :server_id, '0')
    ");
    $statement->bindValue(':address', $serverIp, \PDO::PARAM_STR);
    $statement->bindValue(':name', $serverName, \PDO::PARAM_STR);
    $statement->bindValue(':type', $type, \PDO::PARAM_STR);
    $statement->bindValue(':parent_id', $parentId, \PDO::PARAM_INT);
    $statement->bindValue(':server_id', $pollerId, \PDO::PARAM_INT);
    $statement->execute();
}

/**
 * Update Server information into platform_topology table
 *
 * @param array $pollerInformations
 * @param integer $serverId
 */
function updateServerIntoPlatformTopology(array $pollerInformations, int $serverId)
{
    global $pearDB;

    $pollerIp = $pollerInformations[':ns_ip_address'];
    $name = $pollerInformations[':name'];

    /**
     * Check if we are updating a Remote Server
     */
    $statement = $pearDB->prepare("SELECT * FROM remote_servers WHERE ip = :address");
    $statement->bindValue(':address', $pollerIp, \PDO::PARAM_STR);
    $statement->execute();
    $isRemote = $statement->fetch(\PDO::FETCH_ASSOC);
    if ($isRemote) {
        $type = 'remote';
    } else {
        /**
         * Otherwise we define type with the localhost key
         */
        $type = (int)$pollerInformations[':localhost'] == true ? 'central' : 'poller';
    }

    if ($type === 'central') {
        $parentId = null;
    } else {
        /**
         * Prepare statement to get the Parent depending on Remote attachment or not.
         */
        if (!empty($pollerInformations[':remote_id'])) {
            $statement = $pearDB->prepare("SELECT id FROM `platform_topology` WHERE `server_id` = :remoteId");
            $statement->bindValue(':remoteId', (int)$pollerInformations[':remote_id'], \PDO::PARAM_INT);
            $statement->execute();
        } else {
            $statement = $pearDB->query("SELECT id FROM `platform_topology` WHERE `type` = 'central'");
        }
        $parent = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        /**
         * If no Parent, Poller isn't attached to any remote server or Central
         */
        if (!empty($parent['id'])) {
            $parentId = (int)$parent['id'];
        } else {
            throw new \Exception(
                'Missing parent platform topology. Please register the parent first using the endpoint
                or the available script. For more details check the documentation'
            );
        }
    }

    $statement = $pearDB->prepare("SELECT * FROM platform_topology WHERE server_id = :serverId");
    $statement->bindValue(':serverId', $serverId, \PDO::PARAM_INT);
    $statement->execute();
    $platform = $statement->fetch(\PDO::FETCH_ASSOC);

    // Updating platform
    if ($platform) {
        $statement = $pearDB->prepare("
            UPDATE platform_topology SET
            address = :address,
            name = :name,
            type = :type,
            parent_id = :parent
            WHERE server_id = :serverId
        ");
        // do not override platform IP with localhost value
        if ($type === 'central' && $pollerIp === '127.0.0.1') {
            $pollerIp = $platform['address'];
        }
    } else {
        /**
         * In the case of editing a poller freshly duplicated, it doesn't exist in platform_topology,
         * so we need to create it instead of editing
         */
        $statement = $pearDB->prepare("
            INSERT INTO `platform_topology` (`address`, `name`, `type`, `parent_id`, `server_id`, `pending`)
            VALUES (:address, :name, :type, :parent, :serverId, '0')
        ");
    }

    $statement->bindValue(':address', $pollerIp, \PDO::PARAM_STR);
    $statement->bindValue(':name', $name, \PDO::PARAM_STR);
    $statement->bindValue(':type', $type, \PDO::PARAM_STR);
    $statement->bindValue(':parent', $parentId, \PDO::PARAM_INT);
    $statement->bindValue(':serverId', $serverId, \PDO::PARAM_INT);
    $statement->execute();
}

/**
 * Check if a poller IP can be registered and display an error in form if it can't
 * This ruleset avoid IP duplication in Poller form
 *
 * @param array $formParameters
 * @return boolean
 */
function ipCanBeRegistered(string $serverIp): bool
{
    global $pearDB;

    $pollerIp = $serverIp;
    $statement = $pearDB->prepare("SELECT * FROM `platform_topology` WHERE address = :address");
    $statement->bindValue(':address', $pollerIp, \PDO::PARAM_STR);
    $statement->execute();
    $isAlreadyInTopology = $statement->fetch(\PDO::FETCH_ASSOC);
    if ($isAlreadyInTopology) {
        return false;
    }

    return true;
}

/**
 * Check if a poller IP can be updated and display an error in form if it can't
 * This ruleset avoid IP duplication in Poller form
 */
function ipCanBeUpdated(array $options): bool
{
    global $pearDB;

    $serverIp = $options[0];
    $serverId = (int)$options[1];

    /**
     * Check if the IP address is already existing in Nagios Server
     */
    $statement = $pearDB->prepare("
        SELECT `id`, `ns_ip_address` AS `address` FROM `nagios_server`
        WHERE `ns_ip_address` = :address
    ");
    $statement->bindValue(':address', $serverIp, \PDO::PARAM_STR);
    $statement->execute();
    $platform = $statement->fetch(\PDO::FETCH_ASSOC);

    /**
     * check if previously found platform is the platform we're editing
     */
    if ($platform) {
        if ((int)$platform['id'] === $serverId) {
            return true;
        }
        return false;
    }

    /**
     * If nothing was found in nagios server check if it exists in platform topology
     * e.g: a Central is 127.0.0.1 in NS but is displayed with its true IP in platform_topology
     */
    $statement = $pearDB->prepare("SELECT * FROM `platform_topology` WHERE `address` = :address");
    $statement->bindValue(':address', $serverIp, \PDO::PARAM_STR);
    $statement->execute();
    $platformInTopology = $statement->fetch(\PDO::FETCH_ASSOC);
    if ($platformInTopology && (int)$platformInTopology['server_id'] !== $serverId) {
        return false;
    }
    return true;
}
