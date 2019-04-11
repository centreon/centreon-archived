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

require_once _CENTREON_PATH_ . "www/class/centreon-config/centreonMainCfg.class.php";

/**
 * Retrieve the next available suffixes for this server name from database
 *
 * @global CentreonDB $pearDB DB connector
 * @param string $serverName Server name to process
 * @param int $numberOf Number of suffix requested
 * @param string $separator Character used to separate the server name and suffix
 * @return array Return the next available suffixes
 * @throws Exception
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

    $notAvailableSuffixes = array();

    while ($result = $results->fetchRow()) {
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
 * Check if the name already exist in database
 *
 * @param string $name Name to check
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
    $DBRESULT = $pearDB->query($query);
    $row = $DBRESULT->fetchRow();

    if ($DBRESULT->rowCount() >= 1 && $row["id"] == $id) {
        return true;
    } elseif ($DBRESULT->rowCount() >= 1 && $row["id"] != $id) {
        return false;
    } else {
        return true;
    }
}

/**
 * Enable a server
 *
 * @global CentreonDB $pearDB DB connector
 * @global Centreon $centreon
 * @param int $id Id of the server
 * @throws Exception
 */
function enableServerInDB(int $id): void
{
    global $pearDB, $centreon;

    $dbResult = $pearDB->query("SELECT name FROM `nagios_server` WHERE `id` = " . $id . " LIMIT 1");
    $row = $dbResult->fetchRow();

    $pearDB->query("UPDATE `nagios_server` SET `ns_activate` = '1' WHERE id = " . $id);
    $centreon->CentreonLogAction->insertLog("poller", $id, $row['name'], "enable");

    $query = 'SELECT MIN(`nagios_id`) AS idEngine FROM cfg_nagios WHERE `nagios_server_id` = ' . $id;
    $dbResult = $pearDB->query($query);
    $idEngine = $dbResult->fetchRow();

    if ($idEngine['idEngine']) {
        $pearDB->query(
            "UPDATE `cfg_nagios` SET `nagios_activate` = '0' WHERE `nagios_server_id` = " . $id
        );
        $pearDB->query(
            "UPDATE cfg_nagios SET nagios_activate = '1' WHERE nagios_id = " . (int) $idEngine['idEngine']
        );
    }
}

/**
 * Disable a server
 *
 * @global CentreonDB $pearDB DB connector
 * @global Centreon $centreon
 * @param int $id Id of the server
 * @throws Exception
 */
function disableServerInDB(int $id): void
{
    global $pearDB, $centreon;

    $dbResult = $pearDB->query("SELECT name FROM `nagios_server` WHERE `id` = " . $id . " LIMIT 1");
    $row = $dbResult->fetchRow();

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
 * @global CentreonDB $pearDB DB connector
 * @global Centreon $centreon
 */
function deleteServerInDB(array $serverIds): void
{
    global $pearDB, $pearDBO, $centreon;

    foreach (array_keys($serverIds) as $serverId) {
        $result = $pearDB->query(
            "SELECT name FROM `nagios_server` WHERE `id` = " . $serverId . " LIMIT 1"
        );
        $row = $result->fetchRow();

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
 * @global CentreonDB $pearDB DB connector
 * @throws Exception
 */
function duplicateServer(array $server, array $nbrDup): void
{
    global $pearDB;

    $obj = new CentreonMainCfg();

    foreach (array_keys($server) as $serverId) {
        $result = $pearDB->query(
            'SELECT * FROM `nagios_server` WHERE id = ' . (int) $serverId . ' LIMIT 1'
        );
        $rowServer = $result->fetchRow();
        $rowServer["id"] = '';
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
                        $row = $res->fetchRow();
                        $iId = $obj->insertServerInCfgNagios($serverId, $row['id'], $serverName);

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
 * Insert a new server
 *
 * @param array $data Data of the new server
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

    if (!empty($iIdNagios)) {
        $srvObj->insertBrokerDefaultDirectives($iIdNagios, 'ui');
    }
    addUserRessource($id);
    return $id;
}

/**
 * Create a server in database
 *
 * @param array $data Data of the new server
 * @global CentreonDB $pearDB DB connector
 * @global Centreon $centreon
 * @return int Id of the new server
 */
function insertServer(array $data): int
{
    global $pearDB, $centreon;

    $rq = "INSERT INTO `nagios_server` (`name` , `localhost`, `ns_ip_address`, `ssh_port`, `nagios_bin`," .
        " `nagiostats_bin`, `init_system`, `init_script`, `init_script_centreontrapd`, `snmp_trapd_path_conf`, " .
        "`nagios_perfdata` , `centreonbroker_cfg_path`, `centreonbroker_module_path`, `centreonconnector_path`, " .
        "`ssh_private_key`, `is_default`, `ns_activate`, `centreonbroker_logs_path`, `remote_id`) ";
    $rq .= "VALUES (";
    isset($data["name"]) && $data["name"] != null
        ? $rq .= "'" . htmlentities(trim($data["name"]), ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($data["localhost"]["localhost"]) && $data["localhost"]["localhost"] != null
        ? $rq .= "'" . htmlentities($data["localhost"]["localhost"], ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "NULL, ";
    isset($data["ns_ip_address"]) && $data["ns_ip_address"] != null
        ? $rq .= "'" . htmlentities(trim($data["ns_ip_address"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "NULL, ";
    isset($data["ssh_port"]) && $data["ssh_port"] != null
        ? $rq .= "'" . htmlentities(trim($data["ssh_port"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "'22', ";
    isset($data["nagios_bin"]) && $data["nagios_bin"] != null
        ? $rq .= "'" . htmlentities(trim($data["nagios_bin"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "NULL, ";
    isset($data["nagiostats_bin"]) && $data["nagiostats_bin"] != null
        ? $rq .= "'" . htmlentities(trim($data["nagiostats_bin"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "NULL, ";
    isset($data["init_system"]) && $data["init_system"] != null
        ? $rq .= "'" . htmlentities(trim($data["init_system"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "NULL, ";
    isset($data["init_script"]) && $data["init_script"] != null
        ? $rq .= "'" . htmlentities(trim($data["init_script"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "NULL, ";
    isset($data["init_script_centreontrapd"]) && $data["init_script_centreontrapd"] != null
        ? $rq .= "'" . htmlentities(trim($data["init_script_centreontrapd"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "NULL, ";
    isset($data["snmp_trapd_path_conf"]) && $data["snmp_trapd_path_conf"] != null
        ? $rq .= "'" . htmlentities(trim($data["snmp_trapd_path_conf"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "NULL, ";
    isset($data["nagios_perfdata"]) && $data["nagios_perfdata"] != null
        ? $rq .= "'" . htmlentities(trim($data["nagios_perfdata"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "NULL, ";
    isset($data["centreonbroker_cfg_path"]) && $data["centreonbroker_cfg_path"] != null
        ? $rq .= "'" . htmlentities(trim($data["centreonbroker_cfg_path"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "NULL, ";
    isset($data["centreonbroker_module_path"]) && $data["centreonbroker_module_path"] != null
        ? $rq .= "'" . htmlentities(trim($data["centreonbroker_module_path"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "NULL, ";
    isset($data["centreonconnector_path"]) && $data["centreonconnector_path"] != null
        ? $rq .= "'" . htmlentities(trim($data["centreonconnector_path"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "NULL, ";
    isset($data["ssh_private_key"]) && $data["ssh_private_key"] != null
        ? $rq .= "'" . htmlentities(trim($data["ssh_private_key"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "NULL, ";
    isset($data["is_default"]["is_default"]) && $data["is_default"]["is_default"] != null
        ? $rq .= "'" . htmlentities(trim($data["is_default"]["is_default"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "NULL, ";
    isset($data["ns_activate"]["ns_activate"]) && $data["ns_activate"]["ns_activate"] != 2
        ? $rq .= "'" . $data["ns_activate"]["ns_activate"] . "',  "
        : $rq .= "NULL, ";
    isset($data["centreonbroker_logs_path"]) && $data["centreonbroker_logs_path"] != null
        ? $rq .= "'" . htmlentities(trim($data["centreonbroker_logs_path"]), ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL,";
    isset($data["remote_id"]) && $data["remote_id"] != null
        ? $rq .= "'" . htmlentities(trim($data["remote_id"]), ENT_QUOTES, "UTF-8") . "' "
        : $rq .= "NULL";
    $rq .= ")";

    $pearDB->query($rq);
    $result = $pearDB->query("SELECT MAX(id) as last_id FROM `nagios_server`");
    $poller = $result->fetchRow();
    $result->closeCursor();

    if (isset($_REQUEST['pollercmd'])) {
        $instanceObj = new CentreonInstance($pearDB);
        $instanceObj->setCommands($poller['last_id'], $_REQUEST['pollercmd']);
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($data);
    $centreon->CentreonLogAction->insertLog(
        "poller",
        isset($poller["MAX(id)"]) ? $poller["MAX(id)"] : null,
        CentreonDB::escape($data["name"]),
        "a",
        $fields
    );

    return (int) $poller["last_id"];
}

/**
 * @param int $serverId Id of the server
 * @global CentreonDB $pearDB DB connector
 * global Centreon $centreon
 * @return bool Return true if ok
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
    $isInsert = array();
    while ($resource = $res->fetchRow()) {
        if (!in_array($resource['resource_name'], $isInsert)) {
            $isInsert[] = $resource['resource_name'];
            $query = sprintf(
                $queryInsert,
                (int) $resource['resource_id'],
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
 * Update a server
 *
 * @param int $id Id of the server
 * @param array $data Data of server
 * @global CentreonDB $pearDB DB connector
 * @global Centreon $centreon
 */
function updateServer(int $id, $data): void
{
    global $pearDB, $centreon;

    if ($data["localhost"]["localhost"] == 1) {
        $pearDB->query("UPDATE `nagios_server` SET `localhost` = '0'");
    }
    if ($data["is_default"]["is_default"] == 1) {
        $pearDB->query("UPDATE `nagios_server` SET `is_default` = '0'");
    }

    $rq = "UPDATE `nagios_server` SET ";
    isset($data["name"]) && $data["name"] != null
        ? $rq .= "name = '" . htmlentities($data["name"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "name = NULL, ";
    isset($data["localhost"]["localhost"]) && $data["localhost"]["localhost"] != null
        ? $rq .= "localhost = '" . htmlentities($data["localhost"]["localhost"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "localhost = NULL, ";
    isset($data["ns_ip_address"]) && $data["ns_ip_address"] != null
        ? $rq .= "ns_ip_address = '" . htmlentities(trim($data["ns_ip_address"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "ns_ip_address = NULL, ";
    isset($data["ssh_port"]) && $data["ssh_port"] != null
        ? $rq .= "ssh_port = '" . htmlentities(trim($data["ssh_port"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "ssh_port = '22', ";
    isset($data["init_system"]) && $data["init_system"] != null
        ? $rq .= "init_system = '" . htmlentities(trim($data["init_system"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "init_system = NULL, ";
    isset($data["init_script"]) && $data["init_script"] != null
        ? $rq .= "init_script = '" . htmlentities(trim($data["init_script"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "init_script = NULL, ";
    isset($data["init_script_centreontrapd"]) && $data["init_script_centreontrapd"] != null
        ? $rq .= "init_script_centreontrapd = '" . htmlentities(
            trim($data["init_script_centreontrapd"]),
            ENT_QUOTES,
            "UTF-8"
        ) . "',  "
        : $rq .= "init_script_centreontrapd = NULL, ";
    isset($data["snmp_trapd_path_conf"]) && $data["snmp_trapd_path_conf"] != null
        ? $rq .= "snmp_trapd_path_conf = '" . htmlentities(
            trim($data["snmp_trapd_path_conf"]),
            ENT_QUOTES,
            "UTF-8"
        ) . "',  "
        : $rq .= "snmp_trapd_path_conf = NULL, ";
    isset($data["nagios_bin"]) && $data["nagios_bin"] != null
        ? $rq .= "nagios_bin = '" . htmlentities(trim($data["nagios_bin"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "nagios_bin = NULL, ";
    isset($data["nagiostats_bin"]) && $data["nagiostats_bin"] != null
        ? $rq .= "nagiostats_bin = '" . htmlentities(trim($data["nagiostats_bin"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "nagiostats_bin = NULL, ";
    isset($data["nagios_perfdata"]) && $data["nagios_perfdata"] != null
        ? $rq .= "nagios_perfdata = '" . htmlentities(trim($data["nagios_perfdata"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "nagios_perfdata = NULL, ";
    isset($data["centreonbroker_cfg_path"]) && $data["centreonbroker_cfg_path"] != null
        ? $rq .= "centreonbroker_cfg_path = '" . htmlentities(
            trim($data["centreonbroker_cfg_path"]),
            ENT_QUOTES,
            "UTF-8"
        ) . "',  "
        : $rq .= "centreonbroker_cfg_path = NULL, ";
    isset($data["centreonbroker_module_path"]) && $data["centreonbroker_module_path"] != null
        ? $rq .= "centreonbroker_module_path = '" . htmlentities(
            trim($data["centreonbroker_module_path"]),
            ENT_QUOTES,
            "UTF-8"
        ) . "',  "
        : $rq .= "centreonbroker_module_path = NULL, ";
    isset($data["centreonconnector_path"]) && $data["centreonconnector_path"] != null
        ? $rq .= "centreonconnector_path = '" . htmlentities(
            trim($data["centreonconnector_path"]),
            ENT_QUOTES,
            "UTF-8"
        ) . "',  "
        : $rq .= "centreonconnector_path = NULL, ";
    isset($data["ssh_private_key"]) && $data["ssh_private_key"] != null
        ? $rq .= "ssh_private_key = '" . htmlentities(trim($data["ssh_private_key"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "ssh_private_key = NULL, ";
    isset($data["is_default"]) && $data["is_default"] != null
        ? $rq .= "is_default = '" . htmlentities(trim($data["is_default"]["is_default"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "is_default = NULL, ";
    isset($data["centreonbroker_logs_path"]) && $data["centreonbroker_logs_path"] != null
        ? $rq .= "centreonbroker_logs_path = '" . htmlentities(
            trim($data["centreonbroker_logs_path"]),
            ENT_QUOTES,
            "UTF-8"
        ) . "',  "
        : $rq .= "centreonbroker_logs_path = NULL, ";
    isset($data["remote_id"]) && $data["remote_id"] != null
        ? $rq .= "remote_id = '" . htmlentities(trim($data["remote_id"]), ENT_QUOTES, "UTF-8") . "',  "
        : $rq .= "remote_id = NULL, ";
    $rq .= "ns_activate = '" . $data["ns_activate"]["ns_activate"] . "' ";
    $rq .= "WHERE id = '" . $id . "'";
    $pearDB->query($rq);

    updateRemoteServerInformation($data);

    if (isset($_REQUEST['pollercmd'])) {
        $instanceObj = new CentreonInstance($pearDB);
        $instanceObj->setCommands($id, $_REQUEST['pollercmd']);
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($data);
    $centreon->CentreonLogAction->insertLog("poller", $id, CentreonDB::escape($data["name"]), "c", $fields);
}

/**
 * Update Remote Server informations
 *
 * @param array $data
 *
 */
function updateRemoteServerInformation(array $data)
{
    global $pearDB, $centreon;

    $req = "SELECT * FROM `remote_servers` WHERE ip = '" . $data["ns_ip_address"]  . "'";
    $result = $pearDB->query($req);

    if ($result->rowCount()) {
        $rq = "UPDATE `remote_servers` SET ";
        $rq .= "http_method = '" . $data["http_method"] . "', ";
        isset($data["http_port"]) && $data["http_port"] != null
            ? $rq .= "http_port = '" . $data["http_port"]  . "', "
            : $rq .= "http_port = NULL, ";
        $rq .= "no_check_certificate = '" . $data["no_check_certificate"]["no_check_certificate"] . "', ";
        $rq .= "no_proxy = '" . $data["no_proxy"]["no_proxy"] . "' ";
		$rq .= "ip = '" . $data["ns_ip_address"]  . "'";
        $pearDB->query($rq);
    }
    $result->closeCursor();
}

/**
 * Check if a service or an host has been changed for a specific poller.
 *
 * @param int $poller_id Id of the poller
 * @param int $last_restart Timestamp of the last restart
 * @global CentreonDB $pearDBO DB connector for centreon_storage database
 * @global array $conf_centreon Database configuration
 * @return bool Return true if the configuration has changed
 */
function checkChangeState(int $poller_id, int $last_restart): bool
{
    global $pearDBO, $conf_centreon;

    if (!isset($last_restart) || $last_restart == "") {
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
    return $dbResult->rowCount() ? true : false;
}
