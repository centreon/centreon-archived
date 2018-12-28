<?php
/**
 * Copyright 2005-2018 Centreon
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

/**
 *
 * Enter description here ...
 * @author jmathis
 *
 */
class CentreonTraps
{
    /**
     * @var CentreonDB $db
     */
    protected $db;

    /**
     * @var Centreon
     */
    protected $centreon;

    /**
     * @var HTML_QuickForm
     */
    protected $form;

    /*
     * constructor
     */
    public function __construct($db, $centreon = null, $form = null)
    {
        if (!isset($db)) {
            throw new Exception('Db connector object is required');
        }
        $this->db = $db;
        $this->centreon = $centreon;
        $this->form = $form;
    }

    /**
     * Sets form if not passed to constructor beforehands
     *
     * @param $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * Inserts data into the traps_matching_properties table
     *
     * @param int $trapId Id of the trap
     * @param array $data Data to insert
     * @throws Exception
     */
    private function setMatchingOptions($trapId, array $data)
    {
        if (is_null($trapId)) {
            return;
        } elseif (!is_int($trapId)) {
            $trapId = (int) $trapId;
        }
        $this->db->query(
            'DELETE FROM traps_matching_properties WHERE trap_id = ' . $trapId
        );

        if (isset($data['rule'])) {
            $insertStr = "";
            $rules = $data['rule'];
            $regexp = $data['regexp'];
            $status = $data['rulestatus'];
            $severity = $data['ruleseverity'];
            $i = 1;
            foreach ($rules as $key => $value) {
                if (is_null($value) || $value == "") {
                    continue;
                }
                if ($insertStr) {
                    $insertStr .= ", ";
                }
                if ($severity[$key] == "") {
                    $severity[$key] = "NULL";
                }
                $insertStr .= "($trapId, '" . $this->db->escape($value) . "', '" .
                    $this->db->escape($regexp[$key]) . "', " . $this->db->escape($status[$key]) . ", " .
                    $this->db->escape($severity[$key]) . ", $i)";
                $i++;
            }

            if (!empty($insertStr)) {
                $this->db->query(
                    'INSERT INTO traps_matching_properties ' .
                    '(trap_id, tmo_string, tmo_regexp, tmo_status, severity_id, tmo_order) ' .
                    'VALUES ' . $insertStr
                );
            }
        }
    }

    /**
     * tests if the trap name already exists
     *
     * @param $oid
     * @return bool
     * @throws Exception
     */
    public function testTrapExistence($oid = null)
    {
        $id = null;
        if (isset($this->form)) {
            $id = $this->form->getSubmitValue('traps_id');
        }
        $query = "SELECT traps_oid, traps_id FROM traps WHERE traps_oid = '".$this->db->escape($oid)."'";
        $res = $this->db->query($query);
        $trap = $res->fetchRow();

        if ($res->numRows() >= 1 && $trap["traps_id"] == $id) {
            return true;
        } elseif ($res->numRows() >= 1 && $trap["traps_id"] != $id) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Retrieve the next available suffixes for this trap name from database
     *
     * @global CentreonDB $pearDB DB connector
     * @param string $trapName Trap name to process
     * @param int $numberOf Number of suffix requested
     * @param string $separator Character used to separate the trap name and suffix
     * @return array Return the next available suffixes
     * @throws Exception
     */
    function getAvailableSuffixIds($trapName, $numberOf, $separator = '_')
    {
        if ($numberOf < 0) {
            return array();
        }

        global $pearDB;

        /**
         * To avoid that this column value will be interpreted like regular
         * expression in the database query.
         */
        $trapName = preg_replace('/([<>.*?+\[\]{}^$|!\(\)])/','\\\\\1',$trapName);
        $trapName = mysql_real_escape_string($trapName);

        // Get list of suffix already used
        $query = "SELECT CAST(SUBSTRING_INDEX(traps_name,'_',-1) AS INT) AS suffix "
            . "FROM traps WHERE traps_name REGEXP '^" . $trapName . $separator . "[0-9]+$' "
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
        $nextAvailableSuffixes = array();
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
     * Delete traps
     *
     * @param array $trapIds List of trap id to delete
     * @throws Exception
     */
    public function delete(array $trapIds)
    {
        foreach (array_keys($trapIds) as $trapId) {
            $selectResult = $this->db->query(
                "SELECT traps_name FROM `traps` WHERE `traps_id` = " . (int) $trapId . " LIMIT 1"
            );
            $trapData = $selectResult->fetchRow();
            $deleteResult = $this->db->query("DELETE FROM traps WHERE traps_id = " . (int) $trapId);
            if ($deleteResult === 1) {
                $this->centreon->CentreonLogAction->insertLog("traps", $trapId, $trapData['traps_name'], "d");
            }
        }
    }

    /**
     * Duplicate traps
     *
     * @param array $trapIds List of trap id to duplicate
     * @param array $nbrDup Number of copy
     * @throws Exception
     */
    public function duplicate(array $trapIds, array $nbrDup)
    {
        foreach (array_keys($trapIds) as $trapId) {
            $res = $this->db->query(
                "SELECT * FROM traps WHERE traps_id = " . (int) $trapId . " LIMIT 1"
            );
            $row = $res->fetchRow();
            $row["traps_id"] = '';

            $availableSuffix = $this->getAvailableSuffixIds(
                $row["traps_name"],
                $nbrDup[$trapId]
            );

            foreach($availableSuffix as $suffix) {
                $queryValues = null;
                $trapsName = null;
                $fields = array();

                foreach ($row as $columnName => $columnValue) {
                    if ($columnName === 'traps_name') {
                        $trapsName = $columnValue = mysql_real_escape_string($columnValue . '_' . $suffix);
                    } else {
                        $columnValue = mysql_real_escape_string($columnValue);
                    }

                    if (is_null($queryValues)) {
                        $queryValues .= ($columnValue != null
                            ? ("'" . $columnValue . "'")
                            : "NULL");
                    } else {
                        $queryValues .= ($columnValue != null
                            ? (", '" . $columnValue . "'")
                            : ", NULL");
                    }

                    if ($columnName != "traps_id") {
                        $fields[$columnName] = $columnValue;
                    }
                }
                if (!is_null($queryValues)) {
                    $this->db->query('INSERT INTO traps VALUES (' . $queryValues . ')');
                    $resultMax = $this->db->query("SELECT MAX(traps_id) AS trapsId FROM traps");
                    $trapInfo = $resultMax->fetchRow();
                    $newTrapId = (int) $trapInfo['trapsId'];
                    $this->db->query(
                        'INSERT INTO traps_service_relation (traps_id, service_id) 
                          (SELECT ' . $newTrapId . ', service_id 
                            FROM traps_service_relation 
                            WHERE traps_id = ' . (int) $trapId . ')'
                    );
                    $this->db->query(
                        'INSERT INTO traps_preexec (trap_id, tpe_string, tpe_order) 
                          (SELECT ' . $newTrapId . ', tpe_string, tpe_order
                            FROM traps_preexec 
                            WHERE trap_id = ' . (int) $trapId . ')'
                    );

                    $this->db->query(
                        'INSERT INTO traps_matching_properties ' .
                        '(trap_id, tmo_order, tmo_regexp, tmo_string, tmo_status, severity_id) ' .
                        '(SELECT ' . $newTrapId . ' ,tmo_order, '.
                        'tmo_regexp, tmo_string, tmo_status, severity_id ' .
                        'FROM traps_matching_properties WHERE trap_id = ' . (int) $trapId . ')'
                    );

                    $this->centreon->CentreonLogAction->insertLog(
                        "traps",
                        $newTrapId,
                        $trapsName,
                        "a",
                        $fields
                    );
                }
            }
        }
    }

    /**
     * Update a trap
     *
     * @param int $traps_id Id of the trap
     * @param array $data Data to update
     * @throws Exception
     */
    public function update($traps_id, array $data)
    {
        if (is_null($traps_id)) {
            return;
        } elseif (!is_int($traps_id)) {
            $traps_id = (int) $traps_id;
        }

        if (!isset($data["traps_reschedule_svc_enable"]) || !$data["traps_reschedule_svc_enable"]) {
            $data["traps_reschedule_svc_enable"] = 0;
        }
        if (!isset($data["traps_submit_result_enable"]) || !$data["traps_submit_result_enable"]) {
            $data["traps_submit_result_enable"] = 0;
        }
        if (!isset($data["traps_execution_command_enable"])|| !$data["traps_execution_command_enable"]) {
            $data["traps_execution_command_enable"] = 0;
        }
        if (!isset($data["traps_advanced_treatment"]) || !$data["traps_advanced_treatment"]) {
            $data["traps_advanced_treatment"] = 0;
        }
        if (!isset($data["traps_routing_mode"]) || !$data["traps_routing_mode"]) {
            $data["traps_routing_mode"] = 0;
        }
        if (!isset($data["traps_log"]) || !$data["traps_log"]) {
            $data["traps_log"] = 0;
        }
        if (!isset($data['traps_advanced_treatment_default']) ||
                !$data['traps_advanced_treatment_default']) {
            $data['traps_advanced_treatment_default'] = 0;
        }
        if (isset($data['traps_exec_interval_type']['traps_exec_interval_type'])) {
            $data['traps_exec_interval_type'] = $data['traps_exec_interval_type']['traps_exec_interval_type'];
        }
        if (isset($data['traps_exec_method']['traps_exec_method'])) {
            $data['traps_exec_method'] = $data['traps_exec_method']['traps_exec_method'];
        }
        if (isset($data['traps_downtime']['traps_downtime'])) {
            $data['traps_downtime'] = $data['traps_downtime']['traps_downtime'];
        }
        if (!isset($data['severity']) || $data['severity'] == "") {
            $data['severity'] = "NULL";
        }

        $rq = "UPDATE traps ";
        $rq .= "SET `traps_name` = '" . $this->db->escape($data["traps_name"]) . "', ";
        $rq .= "`traps_oid` = '" . $this->db->escape($data["traps_oid"]) . "', ";
        $rq .= "`traps_args` = '" . $this->db->escape($data["traps_args"]) . "', ";
        $rq .= "`traps_status` = '" . $this->db->escape($data["traps_status"]) . "', ";
        $rq .= "`severity_id` = " . $this->db->escape($data["severity"]) . ", ";
        $rq .= "`traps_submit_result_enable` = '" . $this->db->escape($data["traps_submit_result_enable"]) . "', ";
        $rq .= "`traps_reschedule_svc_enable` = '" . $this->db->escape($data["traps_reschedule_svc_enable"]) . "', ";
        $rq .= "`traps_execution_command` = '" . $this->db->escape($data["traps_execution_command"]) . "', ";
        $rq .= "`traps_execution_command_enable` = '" . $this->db->escape($data["traps_execution_command_enable"]) .
            "', ";
        $rq .= "`traps_advanced_treatment` = '" . $this->db->escape($data["traps_advanced_treatment"]) . "', ";
        $rq .= "`traps_comments` = '" . $this->db->escape($data["traps_comments"]) . "', ";
        $rq .= "`traps_routing_mode` = '" . $this->db->escape($data["traps_routing_mode"]) . "', ";
        $rq .= "`traps_routing_value` = '" . $this->db->escape($data["traps_routing_value"]) . "', ";
        $rq .= "`traps_routing_filter_services` = '" . $this->db->escape($data["traps_routing_filter_services"]) .
            "', ";
        $rq .= "`manufacturer_id` = '" . $this->db->escape($data["manufacturer_id"]) . "', ";
        $rq .= "`traps_log` = '" . $this->db->escape($data["traps_log"]) . "', ";
        $rq .= "`traps_exec_interval` = '" . $this->db->escape($data["traps_exec_interval"]) . "', ";
        $rq .= "`traps_exec_interval_type` = '" . $this->db->escape($data["traps_exec_interval_type"]) . "', ";
        $rq .= "`traps_downtime` = '" . $this->db->escape($data["traps_downtime"]) . "', ";
        $rq .= "`traps_exec_method` = '" . $this->db->escape($data["traps_exec_method"]) . "', ";
        $rq .= "`traps_output_transform` = '" . $this->db->escape($data["traps_output_transform"]) . "', ";
        $rq .= "`traps_advanced_treatment_default` = '" .
            $this->db->escape($data['traps_advanced_treatment_default']) . "', ";
        $rq .= "`traps_customcode` = '" . $this->db->escape($data["traps_customcode"]) . "', ";
        $rq .= "`traps_timeout` = '" . $this->db->escape($data["traps_timeout"]) . "' ";
        $rq .= "WHERE `traps_id` = '" . $traps_id . "'";
        $this->db->query($rq);

        $this->setMatchingOptions($traps_id, $data);
        $this->setServiceRelations($traps_id);
        $this->setServiceTemplateRelations($traps_id);
        $this->setPreexec($traps_id);

        /* Prepare value for changelog */
        $fields = CentreonLogAction::prepareChanges($data);
        $this->centreon->CentreonLogAction->insertLog("traps", $traps_id, $fields["traps_name"], "c", $fields);
    }

    /**
     * Set preexec commands
     *
     * @param int $trapId Id of the trap
     * @throws Exception
     */
    protected function setPreexec($trapId)
    {
        if (is_null($trapId)) {
            return;
        } elseif (!is_int($trapId)) {
            $trapId = (int) $trapId;
        }

        $this->db->query(
            'DELETE FROM traps_preexec WHERE trap_id = ' . $trapId
        );

        $insertStr = "";
        if (isset($_REQUEST['preexec'])) {
            $preexec = $_REQUEST['preexec'];
            $i = 1;
            foreach ($preexec as $value) {
                if (is_null($value) || $value == "") {
                    continue;
                }
                if ($insertStr) {
                    $insertStr .= ", ";
                }
                $insertStr .= "($trapId, '" . $this->db->escape($value)."', $i)";
                $i++;
            }
        }
        if ($insertStr) {
            $this->db->query(
                'INSERT INTO traps_preexec (trap_id, tpe_string, tpe_order) VALUES ' .
                $insertStr
            );
        }
    }

    /**
     * Delete and insert service relations
     *
     * @param int $trapId Id of the trap
     * @throws Exception
     */
    protected function setServiceRelations($trapId)
    {
        if (is_null($trapId)) {
            return;
        } elseif (!is_int($trapId)) {
            $trapId = (int) $trapId;
        }

        $this->db->query(
            'DELETE FROM traps_service_relation 
             WHERE traps_id = ' . $trapId . '
             AND NOT EXISTS (
                SELECT s.service_id 
                FROM service s 
                WHERE s.service_register = \'0\'
                AND s.service_id = traps_service_relation.service_id)'
        );
        $services = CentreonUtils::mergeWithInitialValues($this->form, 'services');
        $insertStr = "";
        $first = true;
        $already = array();
        foreach ($services as $id) {
            $t = preg_split("/\-/", $id);
            if (!isset($already[$t[1]])) {
                if (!$first) {
                    $insertStr .= ",";
                } else {
                    $first = false;
                }
                $insertStr .= "($trapId, $t[1])";
                $already[$t[1]] = true;
            }
        }
        if ($insertStr) {
            $this->db->query(
                'INSERT INTO traps_service_relation (traps_id, service_id) VALUES '
                . $insertStr
            );
        }
    }

    /**
     * Delete and insert service template relations
     *
     * @param int $trapId Id of the trap
     * @throws Exception
     */
    protected function setServiceTemplateRelations($trapId)
    {
        if (is_null($trapId)) {
            return;
        } elseif (!is_int($trapId)) {
            $trapId = (int) $trapId;
        }

        $this->db->query(
            "DELETE FROM traps_service_relation 
             WHERE traps_id = " . $trapId . "
             AND NOT EXISTS (
                SELECT s.service_id 
                FROM service s 
                WHERE s.service_register = '1'
                AND s.service_id = traps_service_relation.service_id)"
        );
        $serviceTpl = (array)$this->form->getSubmitValue('service_templates');
        $insertStr = "";
        $first = true;
        foreach ($serviceTpl as $tpl) {
            if (!$first) {
                $insertStr .= ",";
            } else {
                $first = false;
            }
            $insertStr .= "($trapId, $tpl)";
        }
        if ($insertStr) {
            $this->db->query(
                'INSERT INTO traps_service_relation (traps_id, service_id) VALUES '
                . $insertStr
            );
        }
    }

    /**
     * Insert Traps
     *
     * @param array $ret Data to insert
     * @return int Return id of the new trap
     * @throws Exception
     */
    public function insert(array $ret)
    {
        if (!isset($ret["traps_reschedule_svc_enable"]) || !$ret["traps_reschedule_svc_enable"]) {
            $ret["traps_reschedule_svc_enable"] = 0;
        }
        if (!isset($ret["traps_submit_result_enable"]) || !$ret["traps_submit_result_enable"]) {
            $ret["traps_submit_result_enable"] = 0;
        }
        if (!isset($ret["traps_execution_command_enable"])|| !$ret["traps_execution_command_enable"]) {
            $ret["traps_execution_command_enable"] = 0;
        }
        if (!isset($ret["traps_advanced_treatment"]) || !$ret["traps_advanced_treatment"]) {
            $ret["traps_advanced_treatment"] = 0;
        }
        if (!isset($ret["traps_routing_mode"]) || !$ret["traps_routing_mode"]) {
            $ret["traps_routing_mode"] = 0;
        }
        if (!isset($ret["traps_log"]) || !$ret["traps_log"]) {
            $ret["traps_log"] = 0;
        }
        if (!isset($ret['traps_advanced_treatment_default']) ||
                !$ret['traps_advanced_treatment_default']) {
            $ret['traps_advanced_treatment_default'] = 0;
        }
        if (isset($ret['traps_exec_interval_type']['traps_exec_interval_type'])) {
            $ret['traps_exec_interval_type'] = $ret['traps_exec_interval_type']['traps_exec_interval_type'];
        }
        if (isset($ret['traps_exec_method']['traps_exec_method'])) {
            $ret['traps_exec_method'] = $ret['traps_exec_method']['traps_exec_method'];
        }
        if (isset($ret['traps_downtime']['traps_downtime'])) {
            $ret['traps_downtime'] = $ret['traps_downtime']['traps_downtime'];
        }
        if (!isset($ret['severity']) || $ret['severity'] == "") {
            $ret['severity'] = "NULL";
        }

        $rq = "INSERT INTO traps ";
        $rq .= "(traps_name, traps_oid, traps_args, 
            traps_status, severity_id, traps_submit_result_enable, 
            traps_reschedule_svc_enable, traps_execution_command, traps_execution_command_enable, 
            traps_advanced_treatment, traps_comments, traps_routing_mode, traps_routing_value,
            traps_routing_filter_services, manufacturer_id, traps_log, traps_exec_interval, traps_exec_interval_type,
            traps_exec_method, traps_downtime, traps_output_transform, traps_advanced_treatment_default,
            traps_timeout, traps_customcode) ";
        $rq .= "VALUES ";
        $rq .= "('" . $this->db->escape($ret["traps_name"])."',";
        $rq .= "'" . $this->db->escape($ret["traps_oid"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_args"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_status"])."', ";
        $rq .= "" . $this->db->escape($ret["severity"]).", ";
        $rq .= "'" . $this->db->escape($ret["traps_submit_result_enable"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_reschedule_svc_enable"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_execution_command"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_execution_command_enable"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_advanced_treatment"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_comments"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_routing_mode"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_routing_value"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_routing_filter_services"])."', ";
        $rq .= "'" . $this->db->escape($ret["manufacturer_id"])."',";
        $rq .= "'" . $this->db->escape($ret["traps_log"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_exec_interval"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_exec_interval_type"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_exec_method"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_downtime"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_output_transform"])."', ";
        $rq .= "'" . $this->db->escape($ret['traps_advanced_treatment_default'])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_timeout"])."', ";
        $rq .= "'" . $this->db->escape($ret["traps_customcode"])."') ";
        $this->db->query($rq);

        $res = $this->db->query("SELECT MAX(traps_id) as trap_id FROM traps");
        $trapInfo = $res->fetchRow();
        $traps_id = (int) $trapInfo['trap_id'];

        $this->setMatchingOptions($traps_id, $ret);
        $this->setServiceRelations($traps_id);
        $this->setServiceTemplateRelations($traps_id);
        $this->setPreexec($traps_id);

        /* Prepare value for changelog */
        $fields = CentreonLogAction::prepareChanges($ret);
        $this->centreon->CentreonLogAction->insertLog(
            "traps",
            $traps_id,
            $fields["traps_name"],
            "a",
            $fields
        );

        return (int) $traps_id;
    }

    /**
     * Get pre exec commands from trap_id
     *
     * @param int $trapId Id of the trap
     * @return array Return a pre exec commands list for this trap id
     * @throws Exception
     */
    public function getPreexecFromTrapId($trapId)
    {
        if (is_null($trapId)) {
            return null;
        } elseif (!is_int($trapId)) {
            $trapId = (int) $trapId;
        }

        $res = $this->db->query("SELECT tpe_string
                FROM traps_preexec
                WHERE trap_id = " . $trapId . "
                ORDER BY tpe_order");
        $arr = array();
        $i = 0;
        while ($row = $res->fetchRow()) {
            $arr[$i] = array("preexec_#index#" => $row['tpe_string']);
            $i++;
        }
        return $arr;
    }

    /**
     * Get matching rules from trap_id
     *
     * @param int $trapId Id of the trap
     * @return array Returns an advanced matching rules list for this trap id
     * @throws Exception
     */
    public function getMatchingRulesFromTrapId($trapId)
    {
        if (is_null($trapId)) {
            return null;
        } elseif (!is_int($trapId)) {
            $trapId = (int) $trapId;
        }

        $res = $this->db->query(
            'SELECT tmo_string, tmo_regexp, tmo_status, severity_id
             FROM traps_matching_properties
             WHERE trap_id = ' . $trapId .'
             ORDER BY tmo_order'
        );
        $arr = array();
        $i = 0;
        while ($row = $res->fetchRow()) {
            $arr[$i] = array(
                "rule_#index#" => $row['tmo_string'],
                "regexp_#index#" => $row['tmo_regexp'],
                "rulestatus_#index#" => $row['tmo_status'],
                "ruleseverity_#index#" => $row['severity_id']
            );
            $i++;
        }
        return $arr;
    }
    
    /**
     * @param string $field
     * @return array
     */
    public static function getDefaultValuesParameters($field)
    {
        $parameters = array();
        $parameters['currentObject']['table'] = 'traps';
        $parameters['currentObject']['id'] = 'traps_id';
        $parameters['currentObject']['name'] = 'traps_name';
        $parameters['currentObject']['comparator'] = 'traps_id';

        switch ($field) {
            case 'manufacturer_id':
                $parameters['type'] = 'simple';
                $parameters['externalObject']['table'] = 'traps_vendor';
                $parameters['externalObject']['id'] = 'id';
                $parameters['externalObject']['name'] = 'name';
                $parameters['externalObject']['comparator'] = 'id';
                break;
            case 'groups':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'traps';
                $parameters['externalObject']['id'] = 'traps_id';
                $parameters['externalObject']['name'] = 'traps_name';
                $parameters['externalObject']['comparator'] = 'traps_id';
                $parameters['relationObject']['table'] = 'traps_group_relation';
                $parameters['relationObject']['field'] = 'traps_id';
                $parameters['relationObject']['comparator'] = 'traps_group_id';
                break;
            case 'services':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonService';
                $parameters['relationObject']['table'] = 'traps_service_relation';
                $parameters['relationObject']['field'] = 'service_id';
                $parameters['relationObject']['comparator'] = 'traps_id';
                break;
            case 'service_templates':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonServicetemplates';
                $parameters['relationObject']['table'] = 'traps_service_relation';
                $parameters['relationObject']['field'] = 'service_id';
                $parameters['relationObject']['comparator'] = 'traps_id';
                break;
        }
        
        return $parameters;
    }

    /**
     * @param string[] $values
     * @param array $options Parameter not used
     * @return array Return array (('id' => traps_id, 'text' => traps_name), ...)
     * @throws Exception
     */
    public function getObjectForSelect2($values = array(), $options = array())
    {
        $items = array();

        $explodedValues = '';
        $queryValues = array();
        if (!empty($values)) {
            foreach ($values as $k => $v) {
                $explodedValues .= '?,';
                $queryValues[] = (int)$v;
            }
            $explodedValues = rtrim($explodedValues, ',');
        } else {
            $explodedValues .= '""';
        }

        # get list of selected traps
        $query = "SELECT traps_id, traps_name "
            . "FROM traps "
            . "WHERE traps_id IN (" . $explodedValues . ") "
            . "ORDER BY traps_name ";

        $stmt = $this->db->prepare($query);
        $resRetrieval = $this->db->execute($stmt, $queryValues);

        if (PEAR::isError($resRetrieval)) {
            throw new Exception('Bad traps query params');
        }

        while ($row = $resRetrieval->fetchRow()) {
            $items[] = array(
                'id' => $row['traps_id'],
                'text' => $row['traps_name']
            );
        }

        return $items;
    }
}
