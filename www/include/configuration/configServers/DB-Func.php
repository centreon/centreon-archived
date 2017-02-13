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
 *
 * Test poller existance
 * @param $name
 */
function testExistence($name = null)
{
    global $pearDB, $form;

    $id = null;
    
    if (isset($form)) {
        $id = $form->getSubmitValue('id');
    }

    $DBRESULT = $pearDB->query("SELECT name, id FROM `nagios_server` WHERE `name` = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
    $row = $DBRESULT->fetchRow();
    
    if ($DBRESULT->numRows() >= 1 && $row["id"] == $id) {
        return true;
    } elseif ($DBRESULT->numRows() >= 1 && $row["id"] != $id) {
        return false;
    } else {
        return true;
    }
}

function enableServerInDB($id = null)
{
    global $pearDB, $centreon;

    if (!$id) {
        return;
    }
    
    $DBRESULT2 = $pearDB->query("SELECT name FROM `nagios_server` WHERE `id` = '" . intval($id) . "' LIMIT 1");
    $row = $DBRESULT2->fetchRow();

    $DBRESULT = $pearDB->query("UPDATE `nagios_server` SET `ns_activate` = '1' WHERE id = '".$id."'");

    $centreon->CentreonLogAction->insertLog("poller", $id, $row['name'], "enable");
}

function disableServerInDB($id = null)
{
    global $pearDB, $centreon;
    
    if (!$id) {
        return;
    }

    $DBRESULT2 = $pearDB->query("SELECT name FROM `nagios_server` WHERE `id` = '" . intval($id) . "' LIMIT 1");
    $row = $DBRESULT2->fetchRow();

    $DBRESULT = $pearDB->query("UPDATE `nagios_server` SET `ns_activate` = '0' WHERE id = '".$id."'");

    $centreon->CentreonLogAction->insertLog("poller", $id, $row['name'], "disable");
}

function deleteServerInDB($server = array())
{
    global $pearDB, $pearDBO, $centreon;

    foreach ($server as $key => $value) {
        $DBRESULT2 = $pearDB->query("SELECT name FROM `nagios_server` WHERE `id` = '" . intval($id) . "' LIMIT 1");
        $row = $DBRESULT2->fetchRow();

        $pearDB->query("DELETE FROM `nagios_server` WHERE id = '".$key."'");
        $pearDBO->query("UPDATE `instances` SET deleted = '1' WHERE instance_id = '".$key."'");
        deleteCentreonBrokerByPollerId($key);
    
        $centreon->CentreonLogAction->insertLog("poller", $id, $row['name'], "d");
    }
}

/**
 * Delete Centreon Broker configurations
 *
 * @param int $id The Id poller
 */
function deleteCentreonBrokerByPollerId($id)
{
    if (empty($id)) {
        return;
    }
    
    global $pearDB;
    $pearDB->query("DELETE FROM cfg_centreonbroker WHERE ns_nagios_server = ".$id);
}

function multipleServerInDB($server = array(), $nbrDup = array())
{
    global $pearDB;
    
    $obj = new CentreonMainCfg();

    foreach ($server as $key => $value) {
        $DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE id = '".$key."' LIMIT 1");
        $rowServer = $DBRESULT->fetchRow();
        $rowServer["id"] = '';
        $rowServer["ns_activate"] = '0';
        $rowServer["is_default"] = '0';
        $rowServer["localhost"] = '0';
        $DBRESULT->free();
        
        $rowBks = $obj->getBrokerModules($key);

        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($rowServer as $key2 => $value2) {
                $key2 == "name" ? ($server_name = $value2 = $value2."_".$i) : null;
                $val ? $val .= ($value2 != null ? (", '".$value2."'"):", NULL") : $val .= ($value2 != null ? ("'".$value2."'") : "NULL");
            }
            if (testExistence($server_name)) {
                $val ? $rq = "INSERT INTO `nagios_server` VALUES (".$val.")" : $rq = null;
                $DBRESULT = $pearDB->query($rq);
                
                $queryGetId = 'SELECT id FROM nagios_server WHERE name = "' . $server_name . '"';
                $res = $pearDB->query($queryGetId);
                if (false === PEAR::isError($res)) {
                    if ($res->numRows() > 0) {
                        $row = $res->fetchRow();
                        
                        $iId = $obj->insertServerInCfgNagios($key, $row['id'], $server_name);

                        if (isset($rowBks)) {
                            foreach ($rowBks as $keyBk => $valBk) {
                                if ($valBk["broker_module"]) {
                                    $rqBk = "INSERT INTO cfg_nagios_broker_module (`cfg_nagios_id`, `broker_module`) VALUES ('".$iId."', '".$valBk["broker_module"]."')";
                                }
                                $DBRESULT = $pearDB->query($rqBk);
                            }
                        }

                        $queryRel = 'INSERT INTO cfg_resource_instance_relations (resource_id, instance_id) SELECT b.resource_id, ' . $row['id'] . ' FROM cfg_resource_instance_relations as b WHERE b.instance_id = ' . $key;
                        $pearDB->query($queryRel);
                        
                        $queryCmd = 'INSERT INTO poller_command_relations (poller_id, command_id, command_order) SELECT ' . $row['id'] . ', b.command_id, b.command_order FROM poller_command_relations as b WHERE b.poller_id = ' . $key;
                        $pearDB->query($queryCmd);
                    }
                }
            }
        }
    }
}

function updateServerInDB($id = null)
{
    if (!$id) {
        return;
    }
    updateServer($id);
}

function insertServerInDB()
{
    global $form;

    $srvObj = new CentreonMainCfg();

    $sName = '';
    $id = insertServer();
    $ret = $form->getSubmitValues();
    if (isset($ret['name'])) {
        $sName = $ret['name'];
    }
    $iIdNagios = $srvObj->insertServerInCfgNagios(-1, $id, $sName);

    if (!empty($iIdNagios)) {
        $srvObj->insertBrokerDefaultDirectives($iIdNagios, 'ui');
    }
    addUserRessource($id);
    return ($id);
}


function insertServer($ret = array())
{
    global $form, $pearDB, $centreon;
    
    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $rq = "INSERT INTO `nagios_server` (`name` , `localhost`, `ns_ip_address`, `ssh_port`, `nagios_bin`, `nagiostats_bin`, `init_system`, `init_script`, `init_script_centreontrapd`, `snmp_trapd_path_conf`, `nagios_perfdata` , `centreonbroker_cfg_path`, `centreonbroker_module_path`, `centreonconnector_path`, `ssh_private_key`, `is_default`, `ns_activate`, `centreonbroker_logs_path`) ";
    $rq .= "VALUES (";
    isset($ret["name"]) && $ret["name"] != null ? $rq .= "'".htmlentities(trim($ret["name"]), ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
    isset($ret["localhost"]["localhost"]) && $ret["localhost"]["localhost"] != null ? $rq .= "'".htmlentities($ret["localhost"]["localhost"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["ns_ip_address"]) && $ret["ns_ip_address"] != null ? $rq .= "'".htmlentities(trim($ret["ns_ip_address"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["ssh_port"]) && $ret["ssh_port"] != null ? $rq .= "'".htmlentities(trim($ret["ssh_port"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "'22', ";
    isset($ret["nagios_bin"]) && $ret["nagios_bin"] != null ? $rq .= "'".htmlentities(trim($ret["nagios_bin"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["nagiostats_bin"]) && $ret["nagiostats_bin"] != null ? $rq .= "'".htmlentities(trim($ret["nagiostats_bin"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["init_system"]) && $ret["init_system"] != null ? $rq .= "'".htmlentities(trim($ret["init_system"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["init_script"]) && $ret["init_script"] != null ? $rq .= "'".htmlentities(trim($ret["init_script"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["init_script_centreontrapd"]) && $ret["init_script_centreontrapd"] != null ? $rq .= "'".htmlentities(trim($ret["init_script_centreontrapd"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["snmp_trapd_path_conf"]) && $ret["snmp_trapd_path_conf"] != null ? $rq .= "'".htmlentities(trim($ret["snmp_trapd_path_conf"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["nagios_perfdata"]) && $ret["nagios_perfdata"] != null ? $rq .= "'".htmlentities(trim($ret["nagios_perfdata"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["centreonbroker_cfg_path"]) && $ret["centreonbroker_cfg_path"] != null ? $rq .= "'".htmlentities(trim($ret["centreonbroker_cfg_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["centreonbroker_module_path"]) && $ret["centreonbroker_module_path"] != null ? $rq .= "'".htmlentities(trim($ret["centreonbroker_module_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["centreonconnector_path"]) && $ret["centreonconnector_path"] != null ? $rq .= "'".htmlentities(trim($ret["centreonconnector_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["ssh_private_key"]) && $ret["ssh_private_key"] != null ? $rq .= "'".htmlentities(trim($ret["ssh_private_key"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["is_default"]["is_default"]) && $ret["is_default"]["is_default"] != null ? $rq .= "'".htmlentities(trim($ret["is_default"]["is_default"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
    isset($ret["ns_activate"]["ns_activate"]) && $ret["ns_activate"]["ns_activate"] != 2 ? $rq .= "'".$ret["ns_activate"]["ns_activate"]."',  "  : $rq .= "NULL, ";
    isset($ret["centreonbroker_logs_path"]) && $ret["centreonbroker_logs_path"] != null ?
        $rq .= "'".htmlentities(trim($ret["centreonbroker_logs_path"]), ENT_QUOTES, "UTF-8")."' " : $rq .= "NULL";
    $rq .= ")";

    $DBRESULT = $pearDB->query($rq);
    $DBRESULT = $pearDB->query("SELECT MAX(id) as last_id FROM `nagios_server`");
    $poller = $DBRESULT->fetchRow();
    $DBRESULT->free();

    if (isset($_REQUEST['pollercmd'])) {
        $instanceObj = new CentreonInstance($pearDB);
        $instanceObj->setCommands($poller['last_id'], $_REQUEST['pollercmd']);
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "poller",
        $poller["MAX(id)"],
        CentreonDB::escape($ret["name"]),
        "a",
        $fields
    );

    return ($poller["last_id"]);
}

function addUserRessource($serverId)
{
    global $pearDB, $centreon;

    $queryInsert = "INSERT INTO cfg_resource_instance_relations (resource_id, instance_id) VALUES (%s, %s)";
    $queryGetResources = "SELECT resource_id, resource_name FROM cfg_resource ORDER BY resource_id";
    
    $res = $pearDB->query($queryGetResources);
    if (PEAR::isError($res)) {
        return false;
    }
    $isInsert = array();
    while ($row = $res->fetchRow()) {
        if (!in_array($row['resource_name'], $isInsert)) {
            $isInsert[] = $row['resource_name'];
            $query = sprintf($queryInsert, $row['resource_id'], $serverId);
            $pearDB->query($query);
            
            /* Prepare value for changelog */
            $fields = CentreonLogAction::prepareChanges($row);
            $centreon->CentreonLogAction->insertLog("resource", $serverId, CentreonDB::escape($row["resource_name"]), "a", $fields);
        }
    }
    return true;
}

function updateServer($id = null)
{
    global $form, $pearDB, $centreon;
    
    if (!$id) {
        return;
    }

    $ret = array();
    $ret = $form->getSubmitValues();

    if ($ret["localhost"]["localhost"] == 1) {
        $DBRESULT = $pearDB->query("UPDATE `nagios_server` SET `localhost` = '0'");
    }
    if ($ret["is_default"]["is_default"] == 1) {
        $DBRESULT = $pearDB->query("UPDATE `nagios_server` SET `is_default` = '0'");
    }

    $rq = "UPDATE `nagios_server` SET ";
    isset($ret["name"]) && $ret["name"] != null ? $rq .= "name = '".htmlentities($ret["name"], ENT_QUOTES, "UTF-8")."', " : $rq .= "name = NULL, ";
    isset($ret["localhost"]["localhost"]) && $ret["localhost"]["localhost"] != null ? $rq .= "localhost = '".htmlentities($ret["localhost"]["localhost"], ENT_QUOTES, "UTF-8")."', " : $rq .= "localhost = NULL, ";
    isset($ret["ns_ip_address"]) && $ret["ns_ip_address"] != null ? $rq .= "ns_ip_address = '".htmlentities(trim($ret["ns_ip_address"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "ns_ip_address = NULL, ";
    isset($ret["ssh_port"]) && $ret["ssh_port"] != null ? $rq .= "ssh_port = '".htmlentities(trim($ret["ssh_port"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "ssh_port = '22', ";
    isset($ret["init_system"]) && $ret["init_system"] != null ? $rq .= "init_system = '".htmlentities(trim($ret["init_system"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "init_system = NULL, ";
    isset($ret["init_script"]) && $ret["init_script"] != null ? $rq .= "init_script = '".htmlentities(trim($ret["init_script"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "init_script = NULL, ";
    isset($ret["init_script_centreontrapd"]) && $ret["init_script_centreontrapd"] != null ? $rq .= "init_script_centreontrapd = '".htmlentities(trim($ret["init_script_centreontrapd"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "init_script_centreontrapd = NULL, ";
    isset($ret["snmp_trapd_path_conf"]) && $ret["snmp_trapd_path_conf"] != null ? $rq .= "snmp_trapd_path_conf = '".htmlentities(trim($ret["snmp_trapd_path_conf"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "snmp_trapd_path_conf = NULL, ";
    isset($ret["nagios_bin"]) && $ret["nagios_bin"] != null ? $rq .= "nagios_bin = '".htmlentities(trim($ret["nagios_bin"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "nagios_bin = NULL, ";
    isset($ret["nagiostats_bin"]) && $ret["nagiostats_bin"] != null ? $rq .= "nagiostats_bin = '".htmlentities(trim($ret["nagiostats_bin"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "nagiostats_bin = NULL, ";
    isset($ret["nagios_perfdata"]) && $ret["nagios_perfdata"] != null ? $rq .= "nagios_perfdata = '".htmlentities(trim($ret["nagios_perfdata"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "nagios_perfdata = NULL, ";
    isset($ret["centreonbroker_cfg_path"]) && $ret["centreonbroker_cfg_path"] != null ?
        $rq .= "centreonbroker_cfg_path = '" . htmlentities(
                trim($ret["centreonbroker_cfg_path"]),
                ENT_QUOTES,
                "UTF-8"
            )
            . "',  " : $rq .= "centreonbroker_cfg_path = NULL, ";
    isset($ret["centreonbroker_module_path"]) && $ret["centreonbroker_module_path"] != null ?
        $rq .= "centreonbroker_module_path = '" . htmlentities(
                trim($ret["centreonbroker_module_path"]),
                ENT_QUOTES,
                "UTF-8"
            )."',  " : $rq .= "centreonbroker_module_path = NULL, ";
    isset($ret["centreonconnector_path"]) && $ret["centreonconnector_path"] != null ? $rq .= "centreonconnector_path = '".htmlentities(trim($ret["centreonconnector_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "centreonconnector_path = NULL, ";
    isset($ret["ssh_private_key"]) && $ret["ssh_private_key"] != null ? $rq .= "ssh_private_key = '".htmlentities(trim($ret["ssh_private_key"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "ssh_private_key = NULL, ";
    isset($ret["is_default"]) && $ret["is_default"] != null ? $rq .= "is_default = '".htmlentities(trim($ret["is_default"]["is_default"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "is_default = NULL, ";
    isset($ret["centreonbroker_logs_path"]) && $ret["centreonbroker_logs_path"] != null ? $rq .= "centreonbroker_logs_path = '".htmlentities(trim($ret["centreonbroker_logs_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "centreonbroker_logs_path = NULL, ";
    $rq .= "ns_activate = '".$ret["ns_activate"]["ns_activate"]."' ";
    $rq .= "WHERE id = '".$id."'";
    $pearDB->query($rq);

    if (isset($_REQUEST['pollercmd'])) {
        $instanceObj = new CentreonInstance($pearDB);
        $instanceObj->setCommands($id, $_REQUEST['pollercmd']);
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("poller", $id, CentreonDB::escape($ret["name"]), "c", $fields);
}

/**
 *
 * Check if a service or an host has been
 * changed for a specific poller.
 * @param unknown_type $poller_id
 * @param unknown_type $last_restart
 * @return number
 */
function checkChangeState($poller_id, $last_restart)
{
    global $pearDB, $pearDBO, $conf_centreon;

    if (!isset($last_restart) || $last_restart == "") {
        return 0;
    }

    $request = "SELECT *
                    FROM log_action
                    WHERE
                    action_log_date > $last_restart AND
                    (
                    (object_type = 'host' AND (action_type = 'd' OR object_id IN (SELECT host_host_id FROM ".$conf_centreon['db'].".ns_host_relation WHERE nagios_server_id = '$poller_id')))
                    OR
                    (object_type = 'service' AND (action_type = 'd' OR object_id IN (SELECT service_service_id FROM ".$conf_centreon['db'].".ns_host_relation nhr, ".$conf_centreon['db'].".host_service_relation hsr WHERE nagios_server_id = '$poller_id' AND hsr.host_host_id = nhr.host_host_id)))
                    )";
    $DBRESULT = $pearDBO->query($request);
    if ($DBRESULT->numRows()) {
        return 1;
    }
    return 0;
}
