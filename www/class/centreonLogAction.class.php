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

class CentreonLogAction
{

    protected $logUser;
    protected $uselessKey;

    /*
     * Initializes variables
     */

    public function __construct($usr)
    {
        $this->logUser = $usr;
        $this->uselessKey = array();
        $this->uselessKey['submitA'] = 1;
        $this->uselessKey['submitC'] = 1;
        $this->uselessKey['o'] = 1;
        $this->uselessKey['initialValues'] = 1;
        $this->uselessKey['centreon_token'] = 1;
        $this->uselessKey['resource'] = 1;
        $this->uselessKey['plugins'] = 1;
    }

    /*
     *  Inserts configuration into DB
     */

    public function insertFieldsNameValue($logId, $fields)
    {
        global $pearDBO;

        $query = "INSERT INTO `log_action_modification` (field_name, field_value, action_log_id) VALUES ";
        $append = "";
        foreach ($fields as $key => $value) {
            $query .= $append . "('" . CentreonDB::escape($key) . "', '" . CentreonDB::escape($value) . "', '" .
                CentreonDB::escape($logId) . "')";
            $append = ", ";
        }
        $DBRESULT = $pearDBO->query($query);
    }

    /*
     *  Inserts logs : add, delete or modification of an object
     */

    public function insertLog($object_type, $object_id, $object_name, $action_type, $fields = null)
    {
        global $pearDBO;

        // Check if audit log option is activated
        $optLogs = $pearDBO->query("SELECT audit_log_option FROM `config`");
        $auditLog = $optLogs->fetchRow();

        if (($auditLog) && ($auditLog['audit_log_option'] == '1')) {
            $str_query = "INSERT INTO `log_action`
                (action_log_date, object_type, object_id, object_name, action_type, log_contact_id)
                VALUES ('".time()."', '" . CentreonDB::escape($object_type) . "', '" .
                CentreonDB::escape($object_id) . "', '" . CentreonDB::escape($object_name) . "', '" .
                    CentreonDB::escape($action_type) . "', '" . CentreonDB::escape($this->logUser->user_id) . "')";
            $pearDBO->query($str_query);

            $DBRESULT2 = $pearDBO->query("SELECT MAX(action_log_id) FROM `log_action`");
            $logId = $DBRESULT2->fetchRow();
            if ($fields) {
                $this->insertFieldsNameValue($logId["MAX(action_log_id)"], $fields);
            }
        }
    }

    /*
     * returns the contact name
     */

    public function getContactname($id)
    {
        global $pearDB;

        $DBRESULT = $pearDB->query(
            "SELECT contact_name FROM `contact` WHERE contact_id = '" . CentreonDB::escape($id) . "' LIMIT 1"
        );
        while ($data = $DBRESULT->fetchRow()) {
            $name = $data["contact_name"];
        }
        $DBRESULT->free();
        return $name;
    }

    /*
     * returns the list of actions ("create","delete","change","massive change", "enable", "disable")
     */

    public function listAction($id, $object_type)
    {
        global $pearDBO;
        $list_actions = array();
        $i = 0;

        $DBRESULT = $pearDBO->query(
            "SELECT *
                FROM log_action
                WHERE object_id ='" . CentreonDB::escape($id) . "'
                AND object_type = '" . CentreonDB::escape($object_type) . "' ORDER BY action_log_date DESC"
        );
        while ($data = $DBRESULT->fetchRow()) {
            $list_actions[$i]["action_log_id"] = $data["action_log_id"];
            $list_actions[$i]["action_log_date"] = date("Y/m/d H:i", $data["action_log_date"]);
            $list_actions[$i]["object_type"] = $data["object_type"];
            $list_actions[$i]["object_id"] = $data["object_id"];
            $list_actions[$i]["object_name"] = $data["object_name"];
            $list_actions[$i]["action_type"] = $this->replaceActiontype($data["action_type"]);
            if ($data["log_contact_id"] != 0) {
                $list_actions[$i]["log_contact_id"] = $this->getContactname($data["log_contact_id"]);
            } else {
                $list_actions[$i]["log_contact_id"] = "System";
            }
            $i++;
        }
        $DBRESULT->free();
        unset($data);
        return $list_actions;
    }

    /*
     *  returns list of modifications
     */
    public function getHostId($service_id)
    {
        global $pearDBO;
        
        /* Get Hosts */
        $query = "SELECT a.action_log_id, field_value 
                    FROM log_action a, log_action_modification m 
                    WHERE m.action_log_id = a.action_log_id 
                    AND field_name LIKE 'service_hPars' 
                    AND object_id = $service_id 
                    AND object_type = 'service' 
                    AND field_value <> ''
                    ORDER BY action_log_date DESC 
                    LIMIT 1";
        $DBRESULT2 = $pearDBO->query($query);
        $info = $DBRESULT2->fetchRow();
        if (isset($info['field_value']) && $info['field_value'] != '') {
            return array('h' => $info['field_value']);
        }

        /* Get hostgroups */
        $query = "SELECT a.action_log_id, field_value 
                    FROM log_action a, log_action_modification m 
                    WHERE m.action_log_id = a.action_log_id 
                    AND field_name LIKE 'service_hgPars' 
                    AND object_id = $service_id 
                    AND object_type = 'service'
                    AND field_value <> '' 
                    ORDER BY action_log_date DESC 
                    LIMIT 1";
        $DBRESULT2 = $pearDBO->query($query);
        $info = $DBRESULT2->fetchRow();
        if (isset($info['field_value']) && $info['field_value'] != '') {
            return array('hg' => $info['field_value']);
        }
        return -1;
    }

    public function getHostName($host_id)
    {
        global $pearDB, $pearDBO;
        
        $query = "SELECT host_name FROM host WHERE host_register = '1' AND host_id = ".$host_id;
        $DBRESULT2 = $pearDB->query($query);
        $info = $DBRESULT2->fetchRow();
        if (isset($info['host_name'])) {
            return $info['host_name'];
        }

        $query = "SELECT object_id, object_name FROM log_action WHERE object_type = 'service' AND object_id = $host_id";
        $DBRESULT2 = $pearDBO->query($query);
        $info = $DBRESULT2->fetchRow();
        if (isset($info['object_name'])) {
            return $info['object_name'];
        }
        return -1;
    }

    public function getHostGroupName($hg_id)
    {
        global $pearDB, $pearDBO;
        
        $query = "SELECT hg_name FROM hostgroup WHERE hg_id = ".$hg_id;
        $DBRESULT2 = $pearDB->query($query);
        $info = $DBRESULT2->fetchRow();
        if (isset($info['hg_name'])) {
            return $info['hg_name'];
        }

        $query = "SELECT object_id, object_name FROM log_action WHERE object_type = 'service' AND object_id = $hg_id";
        $DBRESULT2 = $pearDBO->query($query);
        $info = $DBRESULT2->fetchRow();
        if (isset($info['object_name'])) {
            return $info['object_name'];
        }
        return -1;
    }

    /*
     *  returns list of modifications
     */
    public function listModification($id, $object_type)
    {
        global $pearDBO;
        $list_modifications = array();
        $ref = array();
        $i = 0;

        $DBRESULT = $pearDBO->query(
            "SELECT action_log_id, action_log_date, action_type
                FROM log_action
                WHERE object_id = '" . CentreonDB::escape($id) . "'
                    AND object_type = '" . CentreonDB::escape($object_type) . "' ORDER BY action_log_date ASC"
        );
        while ($row = $DBRESULT->fetchRow()) {
            $DBRESULT2 = $pearDBO->query(
                "SELECT action_log_id,field_name,field_value
                    FROM `log_action_modification`
                    WHERE action_log_id='" . CentreonDB::escape($row['action_log_id']) . "'"
            );
            while ($field = $DBRESULT2->fetchRow()) {
                if (!isset($ref[$field["field_name"]]) && $field["field_value"] != "") {
                    $list_modifications[$i]["action_log_id"] = $field["action_log_id"];
                    $list_modifications[$i]["field_name"] = $field["field_name"];
                    $list_modifications[$i]["field_value_before"] = "";
                    $list_modifications[$i]["field_value_after"] = $field["field_value"];
                } elseif (isset($ref[$field["field_name"]]) && $ref[$field["field_name"]] != $field["field_value"]) {
                    $list_modifications[$i]["action_log_id"] = $field["action_log_id"];
                    $list_modifications[$i]["field_name"] = $field["field_name"];
                    $list_modifications[$i]["field_value_before"] = $ref[$field["field_name"]];
                    $list_modifications[$i]["field_value_after"] = $field["field_value"];
                }
                $ref[$field["field_name"]] = $field["field_value"];
                $i++;
            }
        }
        return $list_modifications;
    }

    /*
     *  Display clear action labels
     */
    public function replaceActiontype($action)
    {
        $actionList = array();
        $actionList["d"] = "Delete";
        $actionList["c"] = "Change";
        $actionList["a"] = "Create";
        $actionList["disable"] = "Disable";
        $actionList["enable"] = "Enable";
        $actionList["mc"] = "Massive change";

        foreach ($actionList as $key => $value) {
            if ($action == $key) {
                $action = $value;
            }
        }
        return $action;
    }

    /*
     *  list object types
     */
    public function listObjecttype()
    {
        $object_type_tab = array();

        $object_type_tab[0] = _("All");
        $object_type_tab[1] = "command";
        $object_type_tab[2] = "timeperiod";
        $object_type_tab[3] = "contact";
        $object_type_tab[4] = "contactgroup";
        $object_type_tab[5] = "host";
        $object_type_tab[6] = "hostgroup";
        $object_type_tab[7] = "service";
        $object_type_tab[8] = "servicegroup";
        $object_type_tab[9] = "snmp traps";
        $object_type_tab[10] = "escalation";
        $object_type_tab[11] = "host dependency";
        $object_type_tab[12] = "hostgroup dependency";
        $object_type_tab[13] = "service dependency";
        $object_type_tab[14] = "servicegroup dependency";
        $object_type_tab[15] = "poller";
        $object_type_tab[16] = "engine";
        $object_type_tab[17] = "broker";
        $object_type_tab[18] = "resources";
        $object_type_tab[19] = "meta";

        return $object_type_tab;
    }

    public function prepareChanges($ret)
    {
        global $pearDB;

        $uselessKey = array();
        $uselessKey['submitA'] = 1;
        $uselessKey['o'] = 1;
        $uselessKey['initialValues'] = 1;
        $uselessKey['centreon_token'] = 1;

        if (!isset($ret)) {
            return array();
        } else {
            $info = array();
            foreach ($ret as $key => $value) {
                if (!isset($uselessKey[trim($key)])) {
                    if (is_array($value)) {
                        if (isset($value[$key])) {
                            $info[$key] = $value[$key];
                        } else {
                            $info[$key] = implode(",", $value);
                        }
                    } else {
                        $info[$key] = CentreonDB::escape($value);
                    }
                }
            }
        }
        return $info;
    }
}
