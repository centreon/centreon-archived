<?php

/**
 * Copyright 2005-2020 Centreon
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

/**
 *
 * Enter description here ...
 * @author jmathis
 *
 */
class CentreonTraps
{
    /**
     * @var CentreonDB
     */
    protected $db;
    /**
     *
     * @var HTML_QuickFormCustom
     */
    protected $form;
    /**
     *
     * @var Centreon
     */
    protected $centreon;

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
     *
     *  _setMatchingOptions takes the $_POST array and analyses it,
     *  then inserts data into the  traps_matching_properties
     * @param int $trapId
     */
    private function setMatchingOptions(int $trapId)
    {
        if ($trapId > 0) {
            $query = "DELETE FROM traps_matching_properties WHERE trap_id = :trapId";
            $statement = $this->db->prepare($query);
            $statement->bindValue(':trapId', $trapId, \PDO::PARAM_INT);
            $statement->execute();

            $insertStr = "";
            if (isset($_REQUEST['rule'])) {
                $rules = $_REQUEST['rule'];
                $regexp = $_REQUEST['regexp'];
                $status = $_REQUEST['rulestatus'];
                $severity = $_REQUEST['ruleseverity'];
                $i = 1;
                $queryValues = [];
                foreach ($rules as $key => $value) {
                    if (is_null($value) || $value == "" || filter_var($key, FILTER_VALIDATE_INT) === false) {
                        continue;
                    }
                    $value = filter_var($value, FILTER_SANITIZE_STRING);
                    $regexp[$key] = filter_var($regexp[$key], FILTER_SANITIZE_STRING) ? $regexp[$key] : "";
                    $status[$key] = filter_var($status[$key], FILTER_VALIDATE_INT) ? (int) $status[$key] : 0;
                    $severity[$key] = filter_var($severity[$key], FILTER_VALIDATE_INT);

                    if ($insertStr) {
                        $insertStr .= ", ";
                    }

                    $queryValues[':value' . $key] = [
                        \PDO::PARAM_STR => $value
                    ];
                    $queryValues[':regexp' . $key] = [
                        \PDO::PARAM_STR => $regexp[$key]
                    ];
                    $queryValues[':status' . $key] = [
                        \PDO::PARAM_INT => $status[$key]
                    ];

                    if ($severity[$key] !== false) {
                        $bindSeverity = ":severity" . $key;
                        $queryValues[":severity" . $key] = [
                            \PDO::PARAM_INT => $severity[$key]
                        ];
                    } else {
                        $bindSeverity = "NULL";
                    }
                    $insertStr .= "(:trapId,  :value" . $key . ", :regexp". $key .", :status" . $key . ", "
                        . $bindSeverity . ", " . $i . ")";
                }

            }
            if ($insertStr) {
                $query = "INSERT INTO traps_matching_properties
                    (trap_id, tmo_string, tmo_regexp, tmo_status, severity_id, tmo_order) VALUES $insertStr";
                $statement = $this->db->prepare($query);
                $statement->bindValue(':trapId', $trapId, \PDO::PARAM_INT);
                if (isset($queryValues)) {
                    foreach ($queryValues as $bindId => $bindData) {
                        foreach ($bindData as $bindType => $bindValue) {
                            $statement->bindValue($bindId, $bindValue, $bindType);
                        }
                    }
                }
                $statement->execute();
            }
        }
    }

    /**
     *
     * Sets form if not passed to constructor beforehands
     * @param $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * Check if the OID has the good Format
     *
     * @param null|string $oid
     * @return boolean
     */
    public function testOidFormat($oid = null)
    {
        if (preg_match('/^(\.([0-2]))|([0-2])((\.0)|(\.([1-9][0-9]*)))*$/', $oid) == true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * tests if trap already exists
     * @param $oid
     */
    public function testTrapExistence($oid = null)
    {
        if ($oid !== null && $this->testOidFormat($oid) === true) {
            $id = null;
            if (isset($this->form)) {
                $id = $this->form->getSubmitValue('traps_id');
            }
            $query = "SELECT traps_oid, traps_id FROM traps WHERE traps_oid = :oid ";

            $statement = $this->db->prepare($query);
            $statement->bindValue(':oid', $oid, \PDO::PARAM_STR);
            $statement->execute();

            $trap = $statement->fetch(\PDO::FETCH_ASSOC);

            /**
             * If the trap already existing return false to trigger an error with the form validation rule
             */
            if ($statement->rowCount() >= 1 && $trap["traps_id"] != $id) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     *
     * Delete Traps
     * @param $traps
     */
    public function delete($traps = [])
    {
        $querySelect = "SELECT traps_name FROM `traps` WHERE `traps_id` = :trapsId LIMIT 1";
        $queryDelete = "DELETE FROM traps WHERE traps_id = :trapsId ";

        $statementSelect = $this->db->prepare($querySelect);
        $statementDelete = $this->db->prepare($queryDelete);
        foreach (array_keys($traps) as $trapsId) {
            if (is_int($trapsId)) {
                $statementSelect->bindValue(':trapsId', $trapsId, \PDO::PARAM_INT);
                $statementSelect->execute();
                $row = $statementSelect->fetch(\PDO::FETCH_ASSOC);

                $statementDelete->bindValue(':trapsId', $trapsId, \PDO::PARAM_INT);
                $statementDelete->execute();
                if ($statementDelete->rowCount() > 0) {
                    $this->centreon->CentreonLogAction->insertLog("traps", $trapsId, $row['traps_name'], "d");
                }
            }
        }
    }

    /**
     * Indicates if the trap name already exists
     *
     * @param string $trapName Trap name to find
     * @return boolean
     */
    public function trapNameExists(string $trapName)
    {
        if (!empty($trapName)) {
            $statement = $this->db->prepare(
                "SELECT COUNT(*) AS total FROM traps WHERE traps_name = :trap_name"
            );
            $statement->bindValue(':trap_name', $trapName, \PDO::PARAM_STR);
            $statement->execute();
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return ((int)$result['total']) > 0;
        }
        return false;
    }

    /**
     *
     * duplicate traps
     * @param $traps
     * @param $nbrDup
     */
    public function duplicate($traps = [], $nbrDup = [])
    {
        $querySelectTrap = "SELECT * FROM traps WHERE traps_id = :trapsId LIMIT 1";
        $queryInsertTrapServiceRelation = "
            INSERT INTO traps_service_relation (traps_id, service_id)
            (SELECT :maxTrapsId, service_id
                FROM traps_service_relation
                WHERE traps_id = :trapsId)";
        $queryInsertPreexec = "
            INSERT INTO traps_preexec (trap_id, tpe_string, tpe_order)
            (SELECT :maxTrapsId, tpe_string, tpe_order
                FROM traps_preexec
                WHERE trap_id = :trapsId)";
        $querySelectMatching = "SELECT * FROM traps_matching_properties WHERE trap_id = :trapsId";
        $queryInsertMatching = "
            INSERT INTO traps_matching_properties
            (`trap_id`,`tmo_order`,`tmo_regexp`,`tmo_string`,`tmo_status`,`severity_id`)
            VALUES (:trap_id, :tmo_order, :tmo_regexp, :tmo_string, :tmo_status, :severity_id)";

        $stmtSelectTrap = $this->db->prepare($querySelectTrap);
        $stmtInsertTrapServiceRelation = $this->db->prepare($queryInsertTrapServiceRelation);
        $stmtInsertPreexec = $this->db->prepare($queryInsertPreexec);
        $stmtSelectMatching = $this->db->prepare($querySelectMatching);
        $stmtInsertMatching = $this->db->prepare($queryInsertMatching);
        foreach (array_keys($traps) as $trapsId) {
            if (is_int($trapsId)) {

                $stmtSelectTrap->bindValue(':trapsId', $trapsId, \PDO::PARAM_INT);
                $stmtSelectTrap->execute();

                $trapConfigurations = $stmtSelectTrap->fetch(\PDO::FETCH_ASSOC);
                $trapConfigurations["traps_id"] = '';
                for ($newIndex = 1; $newIndex <= $nbrDup[$trapsId]; $newIndex++) {
                    $val = null;
                    $trapName = null;
                    $fields = [];
                    foreach ($trapConfigurations as $cfgName => $cfgValue) {
                        if ($cfgName == 'traps_name') {
                            $cfgValue .= '_' . $newIndex;
                            $trapName = $cfgValue;
                            $fields['traps_name'] = $trapName;
                        } elseif ($cfgName != "traps_id") {
                            $fields[$cfgName] = $cfgValue;
                        }

                        if (is_null($val)) {
                            $val .= ($cfgValue == null)
                                ? 'NULL'
                                : "'" . $this->db->escape($cfgValue) . "'";
                        } else {
                            $val .= ($cfgValue == null)
                                ? ', NULL'
                                : ", '" . $this->db->escape($cfgValue) . "'";
                        }
                    }

                    if (!is_null($val)
                        && !empty($trapName)
                        && !$this->trapNameExists($trapName)
                    ) {
                        $this->db->query("INSERT INTO traps VALUES ($val)");
                        $res2 = $this->db->query("SELECT MAX(traps_id) FROM traps");
                        $maxId = $res2->fetch();

                        $stmtInsertTrapServiceRelation->bindValue(
                            ':maxTrapsId',
                            $maxId['MAX(traps_id)'],
                            \PDO::PARAM_INT
                        );
                        $stmtInsertTrapServiceRelation->bindValue(':trapsId', $trapsId, \PDO::PARAM_INT);
                        $stmtInsertTrapServiceRelation->execute();

                        $stmtInsertPreexec->bindValue(':maxTrapsId', $maxId['MAX(traps_id)'], \PDO::PARAM_INT);
                        $stmtInsertPreexec->bindValue(':trapsId', $trapsId, \PDO::PARAM_INT);
                        $stmtInsertPreexec->execute();

                        $stmtSelectMatching->bindValue(':trapsId', $trapsId, \PDO::PARAM_INT);
                        $stmtSelectMatching->execute();

                        while ($row = $stmtSelectMatching->fetch()) {
                            $severity = $row['severity_id'] ?? null;

                            $stmtInsertMatching->bindValue(':trap_id', $maxId['MAX(traps_id)'], \PDO::PARAM_INT);
                            $stmtInsertMatching->bindValue(':tmo_order', $row['tmo_order'], \PDO::PARAM_INT);
                            $stmtInsertMatching->bindValue(':tmo_regexp', $row['tmo_regexp'], \PDO::PARAM_STR);
                            $stmtInsertMatching->bindValue(':tmo_string', $row['tmo_string'], \PDO::PARAM_STR);
                            $stmtInsertMatching->bindValue(':tmo_status', $row['tmo_status'], \PDO::PARAM_INT);
                            $stmtInsertMatching->bindValue(':severity_id', $severity, \PDO::PARAM_INT);
                            $stmtInsertMatching->execute();
                        }

                        $this->centreon->CentreonLogAction->insertLog(
                            "traps",
                            $maxId["MAX(traps_id)"],
                            $trapName,
                            "a",
                            $fields
                        );
                    }
                }
            }
        }
    }

    /**
     * @param null $traps_id
     * @return null
     */
    public function update($traps_id = null)
    {
        if (!$traps_id) {
            return null;
        }

        $ret = $this->form->getSubmitValues();
        $retValue = [];

        $rq = "UPDATE traps SET ";
        $rq .= "`traps_name` = ";
        if (isset($ret["traps_name"]) && $ret["traps_name"] != null) {
            $rq .= ':traps_name, ';
            $retValue[':traps_name'] = $ret["traps_name"];
        } else {
            $rq .= "NULL, ";
        }

        $rq .= "`traps_oid` = ";
        if (isset($ret["traps_oid"]) && $ret["traps_oid"] != null) {
            $rq .= ':traps_oid, ';
            $retValue[':traps_oid'] = $ret["traps_oid"];
        } else {
            $rq .= "NULL, ";
        }

        $rq .= "`traps_args` = ";
        if (isset($ret["traps_args"]) && $ret["traps_args"] != null) {
            $rq .= ':traps_args, ';
            $retValue[':traps_args'] = $ret["traps_args"];
        } else {
            $rq .= "NULL, ";
        }

        $rq .= "`traps_status` = ";
        if (isset($ret["traps_status"]) && $ret["traps_status"] != null) {
            $rq .= ':traps_status, ';
            $retValue[':traps_status'] = $ret["traps_status"];
        } else {
            $rq .= "NULL, ";
        }

        $rq .= "`severity_id` = ";
        if (isset($ret["severity"]) && $ret["severity"] != null) {
            $rq .= ':severity, ';
            $retValue[':severity'] = $ret["severity"];
        } else {
            $rq .= "NULL, ";
        }

        $rq .= "`traps_submit_result_enable` = ";
        if (isset($ret["traps_submit_result_enable"]) && $ret["traps_submit_result_enable"] != null) {
            $rq .= ':traps_submit_result_enable, ';
            $retValue[':traps_submit_result_enable'] = $ret["traps_submit_result_enable"];
        } else {
            $rq .= "'0', ";
        }

        $rq .= "`traps_reschedule_svc_enable` = ";
        if (isset($ret["traps_reschedule_svc_enable"]) && $ret["traps_reschedule_svc_enable"] != null) {
            $rq .= ':traps_reschedule_svc_enable, ';
            $retValue[':traps_reschedule_svc_enable'] = $ret["traps_reschedule_svc_enable"];
        } else {
            $rq .= "'0', ";
        }

        $rq .= "`traps_execution_command` = ";
        if (isset($ret["traps_execution_command"]) && $ret["traps_execution_command"] != null) {
            $rq .= ':traps_execution_command, ';
            $retValue[':traps_execution_command'] = $ret["traps_execution_command"];
        } else {
            $rq .= "NULL, ";
        }

        $rq .= "`traps_execution_command_enable` = ";
        if (isset($ret["traps_execution_command_enable"]) && $ret["traps_execution_command_enable"] != null) {
            $rq .= ':traps_execution_command_enable, ';
            $retValue[':traps_execution_command_enable'] = $ret["traps_execution_command_enable"];
        } else {
            $rq .= "'0', ";
        }

        $rq .= "`traps_advanced_treatment` = ";
        if (isset($ret["traps_advanced_treatment"]) && $ret["traps_advanced_treatment"] != null) {
            $rq .= ':traps_advanced_treatment, ';
            $retValue[':traps_advanced_treatment'] = $ret["traps_advanced_treatment"];
        } else {
            $rq .= "'0', ";
        }

        $rq .= "`traps_comments` = ";
        if (isset($ret["traps_comments"]) && $ret["traps_comments"] != null) {
            $rq .= ':traps_comments, ';
            $retValue[':traps_comments'] = $ret["traps_comments"];
        } else {
            $rq .= "NULL, ";
        }

        $rq .= "`traps_routing_mode` = ";
        if (isset($ret["traps_routing_mode"]) && $ret["traps_routing_mode"] != null) {
            $rq .= ':traps_routing_mode, ';
            $retValue[':traps_routing_mode'] = $ret["traps_routing_mode"];
        } else {
            $rq .= "'0', ";
        }

        $rq .= "`traps_routing_value` = ";
        if (isset($ret["traps_routing_value"]) && $ret["traps_routing_value"] != null) {
            $rq .= ':traps_routing_value, ';
            $retValue[':traps_routing_value'] = $ret["traps_routing_value"];
        } else {
            $rq .= "NULL, ";
        }

        $rq .= "`traps_routing_filter_services` = ";
        if (isset($ret["traps_routing_filter_services"]) && $ret["traps_routing_filter_services"] != null) {
            $rq .= ':traps_routing_filter_services, ';
            $retValue[':traps_routing_filter_services'] = $ret["traps_routing_filter_services"];
        } else {
            $rq .= "NULL, ";
        }

        $rq .= "`manufacturer_id` = ";
        if (isset($ret["manufacturer_id"]) && $ret["manufacturer_id"] != null) {
            $rq .= ':manufacturer_id, ';
            $retValue[':manufacturer_id'] = $ret["manufacturer_id"];
        } else {
            $rq .= "NULL, ";
        }

        $rq .= "`traps_log` = ";
        if (isset($ret["traps_log"]) && $ret["traps_log"] != null) {
            $rq .= ':traps_log, ';
            $retValue[':traps_log'] = $ret["traps_log"];
        } else {
            $rq .= "'0', ";
        }

        $rq .= "`traps_exec_interval` = ";
        if (isset($ret["traps_exec_interval"]) && $ret["traps_exec_interval"] != null) {
            $rq .= ':traps_exec_interval, ';
            $retValue[':traps_exec_interval'] = $ret["traps_exec_interval"];
        } else {
            $rq .= "NULL, ";
        }

        $rq .= "`traps_exec_interval_type` = ";
        if (isset($ret["traps_exec_interval_type"]["traps_exec_interval_type"])
            && $ret["traps_exec_interval_type"]["traps_exec_interval_type"] != null) {
            $rq .= ':traps_exec_interval_type, ';
            $retValue[':traps_exec_interval_type'] = $ret["traps_exec_interval_type"]["traps_exec_interval_type"];
        } else {
            $rq .= "'0', ";
        }

        $rq .= "`traps_exec_method` = ";
        if (isset($ret["traps_exec_method"]["traps_exec_method"])
            && $ret["traps_exec_method"]["traps_exec_method"] != null) {
            $rq .= ':traps_exec_method, ';
            $retValue[':traps_exec_method'] = $ret["traps_exec_method"]["traps_exec_method"];
        } else {
            $rq .= "'0', ";
        }

        $rq .= "`traps_downtime` = ";
        if (isset($ret["traps_downtime"]["traps_downtime"])
            && $ret["traps_downtime"]["traps_downtime"] != null) {
            $rq .= ':traps_downtime, ';
            $retValue[':traps_downtime'] = $ret["traps_downtime"]["traps_downtime"];
        } else {
            $rq .= "'0', ";
        }

        $rq .= "`traps_output_transform` = ";
        if (isset($ret["traps_output_transform"]) && $ret["traps_output_transform"] != null) {
            $rq .= ':traps_output_transform, ';
            $retValue[':traps_output_transform'] = $ret["traps_output_transform"];
        } else {
            $rq .= "NULL, ";
        }

        $rq .= "`traps_advanced_treatment_default` = ";
        if (isset($ret["traps_advanced_treatment_default"]) && $ret["traps_advanced_treatment_default"] != null) {
            $rq .= ':traps_advanced_treatment_default, ';
            $retValue[':traps_advanced_treatment_default'] = $ret["traps_advanced_treatment_default"];
        } else {
            $rq .= "'0', ";
        }

        $rq .= "`traps_timeout` = ";
        if (isset($ret["traps_timeout"]) && $ret["traps_timeout"] != null) {
            $rq .= ':traps_timeout, ';
            $retValue[':traps_timeout'] = $ret["traps_timeout"];
        } else {
            $rq .= "NULL, ";
        }

        $rq .= "`traps_customcode` = ";
        if (isset($ret["traps_customcode"]) && $ret["traps_customcode"] != null) {
            $rq .= ':traps_customcode, ';
            $retValue[':traps_customcode'] = $ret["traps_customcode"];
        } else {
            $rq .= "NULL, ";
        }

        $rq .= "`traps_mode` = ";
        if (isset($ret['traps_mode']['traps_mode']) && $ret['traps_mode']['traps_mode'] != null) {
            $rq .= ':traps_mode ';
            $retValue[':traps_mode'] = $ret['traps_mode']['traps_mode'];
        } else {
            $rq .= "NULL ";
        }

        $rq .= 'WHERE `traps_id` = :traps_id ';
        $retValue[':traps_id'] = (int)$traps_id;

        $stmt = $this->db->prepare($rq);
        foreach ($retValue as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $this->setMatchingOptions($traps_id, $_POST);
        $this->setServiceRelations($traps_id);
        $this->setServiceTemplateRelations($traps_id);
        $this->setPreexec($traps_id);

        /* Prepare value for changelog */
        $fields = CentreonLogAction::prepareChanges($ret);
        $this->centreon->CentreonLogAction->insertLog("traps", $traps_id, $fields["traps_name"], "c", $fields);
    }

    /**
     * Set preexec commands
     *
     * @param int $trapId
     */
    protected function setPreexec(int $trapId)
    {
        if ($trapId > 0) {
            $query = "DELETE FROM traps_preexec WHERE trap_id = :trapId";
            $statement = $this->db->prepare($query);
            $statement->bindValue(':trapId', $trapId, \PDO::PARAM_INT);
            $statement->execute();

            $insertStr = "";
            if (isset($_REQUEST['preexec'])) {
                $preexec = $_REQUEST['preexec'];
                $i = 1;
                $queryValues = [];
                foreach ($preexec as $key => $value) {
                    if (is_null($value) || $value == "" || filter_var($key, FILTER_VALIDATE_INT) === false) {
                        continue;
                    }
                    $queryValues[':value'. $key] = [
                        \PDO::PARAM_STR => $value
                    ];
                    if ($insertStr) {
                        $insertStr .= ", ";
                    }
                    $insertStr .= "(:trapId, :value". $key . ", $i)";
                    $i++;
                }
            }
            if ($insertStr) {
                $query = "INSERT INTO traps_preexec (trap_id, tpe_string, tpe_order) VALUES $insertStr";
                $statement = $this->db->prepare($query);
                $statement->bindValue(':trapId', $trapId, \PDO::PARAM_INT);
                if (isset($queryValues)) {
                    foreach ($queryValues as $bindId => $bindData) {
                        foreach ($bindData as $bindType => $bindValue) {
                            $statement->bindValue($bindId, $bindValue, $bindType);
                        }
                    }
                }
                $statement->execute();
            }
        }
    }

    /**
     * Delete & insert service relations
     *
     * @param int $trapId
     */
    protected function setServiceRelations(int $trapId)
    {
        if ($trapId > 0) {
            $query = "
                DELETE FROM traps_service_relation
                    WHERE traps_id = :trapId
                    AND NOT EXISTS (
                        SELECT s.service_id
                        FROM service s
                        WHERE s.service_register = '0'
                        AND s.service_id = traps_service_relation.service_id)";

            $statement = $this->db->prepare($query);
            $statement->bindValue(':trapId', $trapId, \PDO::PARAM_INT);
            $statement->execute();

            $services = CentreonUtils::mergeWithInitialValues($this->form, 'services');
            $insertStr = "";
            $first = true;
            $already = [];
            foreach ($services as $id) {
                $t = preg_split("/\-/", $id);
                if (!isset($already[$t[1]])) {
                    if (!$first) {
                        $insertStr .= ",";
                    } else {
                        $first = false;
                    }
                    $insertStr .= "(:trapId, " . (int)$t[1] . ")";
                    $already[$t[1]] = true;
                }
            }
            if ($insertStr) {
                $query = "INSERT INTO traps_service_relation (traps_id, service_id) VALUES $insertStr";
                $statement = $this->db->prepare($query);
                $statement->bindValue(':trapId', $trapId, \PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }

    /**
     * Delete & insert service template relations
     *
     * @param int $trapId
     */
    protected function setServiceTemplateRelations(int $trapId)
    {
        if ($trapId > 0) {
            $query = "
                DELETE FROM traps_service_relation
                WHERE traps_id = :trapId
                AND NOT EXISTS (SELECT s.service_id
                    FROM service s
                    WHERE s.service_register = '1'
                    AND s.service_id = traps_service_relation.service_id)";

            $statement = $this->db->prepare($query);
            $statement->bindValue(':trapId', $trapId, \PDO::PARAM_INT);
            $statement->execute();

            $serviceTpl = (array)$this->form->getSubmitValue('service_templates');
            $insertStr = "";
            $first = true;
            foreach ($serviceTpl as $tpl) {
                if (!$first) {
                    $insertStr .= ",";
                } else {
                    $first = false;
                }
                $insertStr .= "(:trapId, $tpl)";
            }
            if ($insertStr) {
                $query = "INSERT INTO traps_service_relation (traps_id, service_id) VALUES $insertStr";
                $statement = $this->db->prepare($query);
                $statement->bindValue(':trapId',$trapId,\PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }

    /**
     * Insert Traps
     *
     * @param array $ret
     * @return mixed
     */
    public function insert($ret = [])
    {
        if (!count($ret)) {
            $ret = $this->form->getSubmitValues();
        }

        $sqlValue = '';
        $retValue = [];

        $rq = 'INSERT INTO traps (';
        $rq .= "`traps_name`, ";
        if (isset($ret["traps_name"]) && $ret["traps_name"] != null) {
            $sqlValue .= ':traps_name, ';
            $retValue[':traps_name'] = $ret["traps_name"];
        } else {
            $sqlValue .= "NULL, ";
        }

        $rq .= "`traps_oid`, ";
        if (isset($ret["traps_oid"]) && $ret["traps_oid"] != null) {
            $sqlValue .= ':traps_oid, ';
            $retValue[':traps_oid'] = $ret["traps_oid"];
        } else {
            $sqlValue .= "NULL, ";
        }

        $rq .= "`traps_args`, ";
        if (isset($ret["traps_args"]) && $ret["traps_args"] != null) {
            $sqlValue .= ':traps_args, ';
            $retValue[':traps_args'] = $ret["traps_args"];
        } else {
            $sqlValue .= "NULL, ";
        }

        $rq .= "`traps_status`, ";
        if (isset($ret["traps_status"]) && $ret["traps_status"] != null) {
            $sqlValue .= ':traps_status, ';
            $retValue[':traps_status'] = $ret["traps_status"];
        } else {
            $sqlValue .= "NULL, ";
        }

        $rq .= "`severity_id`, ";
        if (isset($ret["severity"]) && $ret["severity"] != null) {
            $sqlValue .= ':severity, ';
            $retValue[':severity'] = $ret["severity"];
        } else {
            $sqlValue .= "NULL, ";
        }

        $rq .= "`traps_submit_result_enable`, ";
        if (isset($ret["traps_submit_result_enable"]) && $ret["traps_submit_result_enable"] != null) {
            $sqlValue .= ':traps_submit_result_enable, ';
            $retValue[':traps_submit_result_enable'] = $ret["traps_submit_result_enable"];
        } else {
            $sqlValue .= "'0', ";
        }

        $rq .= "`traps_reschedule_svc_enable`, ";
        if (isset($ret["traps_reschedule_svc_enable"]) && $ret["traps_reschedule_svc_enable"] != null) {
            $sqlValue .= ':traps_reschedule_svc_enable, ';
            $retValue[':traps_reschedule_svc_enable'] = $ret["traps_reschedule_svc_enable"];
        } else {
            $sqlValue .= "'0', ";
        }

        $rq .= "`traps_execution_command`, ";
        if (isset($ret["traps_execution_command"]) && $ret["traps_execution_command"] != null) {
            $sqlValue .= ':traps_execution_command, ';
            $retValue[':traps_execution_command'] = $ret["traps_execution_command"];
        } else {
            $sqlValue .= "NULL, ";
        }

        $rq .= "`traps_execution_command_enable`, ";
        if (isset($ret["traps_execution_command_enable"]) && $ret["traps_execution_command_enable"] != null) {
            $sqlValue .= ':traps_execution_command_enable, ';
            $retValue[':traps_execution_command_enable'] = $ret["traps_execution_command_enable"];
        } else {
            $sqlValue .= "'0', ";
        }

        $rq .= "`traps_advanced_treatment`, ";
        if (isset($ret["traps_advanced_treatment"]) && $ret["traps_advanced_treatment"] != null) {
            $sqlValue .= ':traps_advanced_treatment, ';
            $retValue[':traps_advanced_treatment'] = $ret["traps_advanced_treatment"];
        } else {
            $sqlValue .= "'0', ";
        }

        $rq .= "`traps_comments`, ";
        if (isset($ret["traps_comments"]) && $ret["traps_comments"] != null) {
            $sqlValue .= ':traps_comments, ';
            $retValue[':traps_comments'] = $ret["traps_comments"];
        } else {
            $sqlValue .= "NULL, ";
        }

        $rq .= "`traps_routing_mode`, ";
        if (isset($ret["traps_routing_mode"]) && $ret["traps_routing_mode"] != null) {
            $sqlValue .= ':traps_routing_mode, ';
            $retValue[':traps_routing_mode'] = $ret["traps_routing_mode"];
        } else {
            $sqlValue .= "'0', ";
        }

        $rq .= "`traps_routing_value`, ";
        if (isset($ret["traps_routing_value"]) && $ret["traps_routing_value"] != null) {
            $sqlValue .= ':traps_routing_value, ';
            $retValue[':traps_routing_value'] = $ret["traps_routing_value"];
        } else {
            $sqlValue .= "NULL, ";
        }

        $rq .= "`traps_routing_filter_services`, ";
        if (isset($ret["traps_routing_filter_services"]) && $ret["traps_routing_filter_services"] != null) {
            $sqlValue .= ':traps_routing_filter_services, ';
            $retValue[':traps_routing_filter_services'] = $ret["traps_routing_filter_services"];
        } else {
            $sqlValue .= "NULL, ";
        }

        $rq .= "`manufacturer_id`, ";
        if (isset($ret["manufacturer_id"]) && $ret["manufacturer_id"] != null) {
            $sqlValue .= ':manufacturer_id, ';
            $retValue[':manufacturer_id'] = $ret["manufacturer_id"];
        } else {
            $sqlValue .= "NULL, ";
        }

        $rq .= "`traps_log`, ";
        if (isset($ret["traps_log"]) && $ret["traps_log"] != null) {
            $sqlValue .= ':traps_log, ';
            $retValue[':traps_log'] = $ret["traps_log"];
        } else {
            $sqlValue .= "'0', ";
        }

        $rq .= "`traps_exec_interval`, ";
        if (isset($ret["traps_exec_interval"]) && $ret["traps_exec_interval"] != null) {
            $sqlValue .= ':traps_exec_interval, ';
            $retValue[':traps_exec_interval'] = $ret["traps_exec_interval"];
        } else {
            $sqlValue .= "NULL, ";
        }

        $rq .= "`traps_exec_interval_type`, ";
        if (isset($ret["traps_exec_interval_type"]["traps_exec_interval_type"])
            && $ret["traps_exec_interval_type"]["traps_exec_interval_type"] != null) {
            $sqlValue .= ':traps_exec_interval_type, ';
            $retValue[':traps_exec_interval_type'] = $ret["traps_exec_interval_type"]["traps_exec_interval_type"];
        } else {
            $sqlValue .= "'0', ";
        }

        $rq .= "`traps_exec_method`, ";
        if (isset($ret["traps_exec_method"]["traps_exec_method"])
            && $ret["traps_exec_method"]["traps_exec_method"] != null) {
            $sqlValue .= ':traps_exec_method, ';
            $retValue[':traps_exec_method'] = $ret["traps_exec_method"]["traps_exec_method"];
        } else {
            $sqlValue .= "'0', ";
        }

        $rq .= "`traps_mode`, ";
        if (isset($ret["traps_mode"]["traps_mode"])
            && $ret["traps_mode"]["traps_mode"] != null) {
            $sqlValue .= ':traps_mode, ';
            $retValue[':traps_mode'] = $ret["traps_mode"]["traps_mode"];
        } else {
            $sqlValue .= "'0', ";
        }

        $rq .= "`traps_downtime`, ";
        if (isset($ret["traps_downtime"]["traps_downtime"])
            && $ret["traps_downtime"]["traps_downtime"] != null) {
            $sqlValue .= ':traps_downtime, ';
            $retValue[':traps_downtime'] = $ret["traps_downtime"]["traps_downtime"];
        } else {
            $sqlValue .= "'0', ";
        }

        $rq .= "`traps_output_transform`, ";
        if (isset($ret["traps_output_transform"]) && $ret["traps_output_transform"] != null) {
            $sqlValue .= ':traps_output_transform, ';
            $retValue[':traps_output_transform'] = $ret["traps_output_transform"];
        } else {
            $sqlValue .= "NULL, ";
        }

        $rq .= "`traps_advanced_treatment_default`, ";
        if (isset($ret["traps_advanced_treatment_default"]) && $ret["traps_advanced_treatment_default"] != null) {
            $sqlValue .= ':traps_advanced_treatment_default, ';
            $retValue[':traps_advanced_treatment_default'] = $ret["traps_advanced_treatment_default"];
        } else {
            $sqlValue .= "'0', ";
        }

        $rq .= "`traps_timeout`, ";
        if (isset($ret["traps_timeout"]) && $ret["traps_timeout"] != null) {
            $sqlValue .= ':traps_timeout, ';
            $retValue[':traps_timeout'] = $ret["traps_timeout"];
        } else {
            $sqlValue .= "NULL, ";
        }

        $rq .= "`traps_customcode` ";
        if (isset($ret["traps_customcode"]) && $ret["traps_customcode"] != null) {
            $sqlValue .= ':traps_customcode ';
            $retValue[':traps_customcode'] = $ret["traps_customcode"];
        } else {
            $sqlValue .= "NULL ";
        }
        $rq .= ') VALUES (' . $sqlValue . ')';

        $stmt = $this->db->prepare($rq);
        foreach ($retValue as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        $res = $this->db->query("SELECT MAX(traps_id) FROM traps");
        $traps_id = $res->fetch();

        $this->setMatchingOptions($traps_id['MAX(traps_id)'], $_POST);
        $this->setServiceRelations($traps_id['MAX(traps_id)']);
        $this->setServiceTemplateRelations($traps_id['MAX(traps_id)']);
        $this->setPreexec($traps_id['MAX(traps_id)']);

        /* Prepare value for changelog */
        $fields = CentreonLogAction::prepareChanges($ret);
        $this->centreon->CentreonLogAction->insertLog(
            "traps",
            $traps_id["MAX(traps_id)"],
            $fields["traps_name"],
            "a",
            $fields
        );

        return ($traps_id["MAX(traps_id)"]);
    }

    /**
     * Get pre exec commands from trap_id
     *
     * @param int $trapId
     * @return array
     */
    public function getPreexecFromTrapId(int $trapId)
    {
        if ($trapId > 0) {
            $query = "
                SELECT tpe_string
                FROM traps_preexec
                WHERE trap_id = :trapId
                ORDER BY tpe_order";

            $statement = $this->db->prepare($query);
            $statement->bindValue(':trapId', $trapId, \PDO::PARAM_INT);
            $statement->execute();

            $arr = [];
            $i = 0;
            while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $arr[$i] = array("preexec_#index#" => $row['tpe_string']);
                $i++;
            }
            return $arr;
        }
    }

    /**
     * Get matching rules from trap_id
     *
     * @param int $trapId
     * @return array
     */
    public function getMatchingRulesFromTrapId(int $trapId)
    {
        if ($trapId > 0) {
            $query = "
                SELECT tmo_string, tmo_regexp, tmo_status, severity_id
                FROM traps_matching_properties
                WHERE trap_id = :trapId
                ORDER BY tmo_order";

            $statement = $this->db->prepare($query);
            $statement->bindValue(':trapId', $trapId, \PDO::PARAM_INT);
            $statement->execute();

            $arr = [];
            $i = 0;
            while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
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
    }

    /**
     *
     * @param integer $field
     * @return array
     */
    public static function getDefaultValuesParameters($field)
    {
        $parameters = [];
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
     *
     * @param type $values
     * @return type
     */
    public function getObjectForSelect2($values = [], $options = [])
    {
        $items = [];
        $listValues = '';
        $queryValues = [];
        if (!empty($values)) {
            foreach ($values as $k => $v) {
                $listValues .= ':traps' . $v . ',';
                $queryValues['traps' . $v] = (int)$v;
            }
            $listValues = rtrim($listValues, ',');
        } else {
            $listValues .= '""';
        }

        # get list of selected traps
        $query = 'SELECT traps_id, traps_name FROM traps ' .
            'WHERE traps_id IN (' . $listValues . ') ORDER BY traps_name ';

        $stmt = $this->db->prepare($query);
        if (!empty($queryValues)) {
            foreach ($queryValues as $key => $id) {
                $stmt->bindValue(':' . $key, $id, PDO::PARAM_INT);
            }
        }
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $items[] = array(
                'id' => $row['traps_id'],
                'text' => $row['traps_name']
            );
        }

        return $items;
    }
}
