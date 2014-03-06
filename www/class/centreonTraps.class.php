<?php
/**
 * Copyright 2005-2011 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

/**
 *
 * Enter description here ...
 * @author jmathis
 *
 */
class Centreon_Traps {
    protected $_db;
    protected $_form;
    protected $_centreon;

    /*
     * constructor
     */
    public function __construct($centreon, $db, $form = null) {
        if (!isset($centreon)) {
            throw new Exception('Centreon object is required');
        }
        if (!isset($db)) {
            throw new Exception('Db connector object is required');
        }
        $this->_centreon = $centreon;
        $this->_db = $db;
        $this->_form = $form;
    }

    /**
     *
     *  _setMatchingOptions takes the $_POST array and analyses it,
     *  then inserts data into the  traps_matching_properties
     * @param int $trapId
     */
    private function _setMatchingOptions($trapId) {
    	$this->_db->query("DELETE FROM traps_matching_properties WHERE trap_id = '" . $trapId ."'");

        $insertStr = "";
        if (isset($_REQUEST['rule'])) {
            $rules = $_REQUEST['rule'];
            $regexp = $_REQUEST['regexp'];
            $status = $_REQUEST['rulestatus'];            
            $severity = $_REQUEST['ruleseverity'];
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
                $insertStr .= "($trapId, '".$this->_db->escape($value)."', '".$this->_db->escape($regexp[$key])."', ".$this->_db->escape($status[$key]).", ".$this->_db->escape($severity[$key]).", $i)";
                $i++;
            }
        }
        if ($insertStr) {
            $this->_db->query("INSERT INTO traps_matching_properties (trap_id, tmo_string, tmo_regexp, tmo_status, severity_id, tmo_order) VALUES $insertStr");
        }
    }

	/**
	 *
	 * Sets form if not passed to constructor beforehands
	 * @param $form
	 */
    public function setForm($form) {
        $this->_form = $form;
    }

   	/**
   	 *
   	 * tests if trap already exists
   	 * @param $oid
   	 */
    public function testTrapExistence($oid = NULL)	{
		$id = NULL;
		if (isset($this->_form)) {
			$id = $this->_form->getSubmitValue('traps_id');
        }
		$query = "SELECT traps_oid, traps_id FROM traps WHERE traps_oid = '".$this->_db->escape($oid)."'";
        $res = $this->_db->query($query);
		$trap = $res->fetchRow();

		if ($res->numRows() >= 1 && $trap["traps_id"] == $id) {
			return true;
        } else if ($res->numRows() >= 1 && $trap["traps_id"] != $id) {
			return false;
        } else {
            return true;
        }
	}

    /**
     *
     * Delete Traps
     * @param $traps
     */
	public function delete($traps = array()) {
		foreach($traps as $key=>$value) {
			$res2 = $this->_db->query("SELECT traps_name FROM `traps` WHERE `traps_id` = '".$this->_db->escape($key)."' LIMIT 1");
			$row = $res2->fetchRow();
			$res = $this->_db->query("DELETE FROM traps WHERE traps_id = '".$this->_db->escape($key)."'");
			$this->_centreon->CentreonLogAction->insertLog("traps", $key, $row['traps_name'], "d");
		}
	}

        /**
         *
         * duplicate traps
         * @param $traps
         * @param $nbrDup
         */
	public function duplicate($traps = array(), $nbrDup = array()) {
            foreach ($traps as $key => $value) {
                $res = $this->_db->query("SELECT * FROM traps WHERE traps_id = '".$key."' LIMIT 1");
                $row = $res->fetchRow();
		$row["traps_id"] = '';
		for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
                    $val = null;
                    foreach ($row as $key2 => $value2) {
                        $key2 == "traps_name" ? ($traps_name = $value2 = $value2."_".$i) : null;
			$val ? $val .= ($value2!=NULL?(", '".$this->_db->escape($value2)."'"):", NULL") : $val .= ($value2!=NULL?("'".$this->_db->escape($value2)."'"):"NULL");
                        if ($key2 != "traps_id") {
                            $fields[$key2] = $value2;
                        }
                        if (isset($traps_name)) {
                            $fields["traps_name"] = $traps_name;
                        }
                    }
                    $val ? $rq = "INSERT INTO traps VALUES (".$val.")" : $rq = null;
                    $res = $this->_db->query($rq);
                    $res2 = $this->_db->query("SELECT MAX(traps_id) FROM traps");
                    $maxId = $res2->fetchRow();
                    $this->_db->query("INSERT INTO traps_service_relation (traps_id, service_id) 
                                    (SELECT ".$maxId['MAX(traps_id)'].", service_id 
                                        FROM traps_service_relation 
                                        WHERE traps_id = ".$this->_db->escape($key).")");
                    $this->_db->query("INSERT INTO traps_preexec (trap_id, tpe_string, tpe_order) 
                                    (SELECT ".$maxId['MAX(traps_id)'].", tpe_string, tpe_order
                                        FROM traps_preexec 
                                        WHERE trap_id = ".$this->_db->escape($key).")");
                    $this->_centreon->CentreonLogAction->insertLog("traps", $maxId["MAX(traps_id)"], $traps_name, "a", $fields);
		}
            }
	}

    /**
     *
     * Update
     * @param $traps_id
     */
	public function update($traps_id = null) {
            if (!$traps_id) {
                return null;
            }

            $ret = array();
            $ret = $this->_form->getSubmitValues();

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

            $rq = "UPDATE traps ";
            $rq .= "SET `traps_name` = '".$this->_db->escape($ret["traps_name"])."', ";
            $rq .= "`traps_oid` = '".$this->_db->escape($ret["traps_oid"])."', ";
            $rq .= "`traps_args` = '".$this->_db->escape($ret["traps_args"])."', ";
            $rq .= "`traps_status` = '".$this->_db->escape($ret["traps_status"])."', ";
            $rq .= "`severity_id` = ".$this->_db->escape($ret["severity"]).", ";
            $rq .= "`traps_submit_result_enable` = '".$this->_db->escape($ret["traps_submit_result_enable"])."', ";
            $rq .= "`traps_reschedule_svc_enable` = '".$this->_db->escape($ret["traps_reschedule_svc_enable"])."', ";
            $rq .= "`traps_execution_command` = '".$this->_db->escape($ret["traps_execution_command"])."', ";
            $rq .= "`traps_execution_command_enable` = '".$this->_db->escape($ret["traps_execution_command_enable"])."', ";
            $rq .= "`traps_advanced_treatment` = '".$this->_db->escape($ret["traps_advanced_treatment"])."', ";
            $rq .= "`traps_comments` = '".$this->_db->escape($ret["traps_comments"])."', ";
            $rq .= "`traps_routing_mode` = '".$this->_db->escape($ret["traps_routing_mode"])."', ";
            $rq .= "`traps_routing_value` = '".$this->_db->escape($ret["traps_routing_value"])."', ";
            $rq .= "`traps_routing_filter_services` = '".$this->_db->escape($ret["traps_routing_filter_services"])."', ";
            $rq .= "`manufacturer_id` = '".$this->_db->escape($ret["manufacturer_id"])."', ";
            $rq .= "`traps_log` = '".$this->_db->escape($ret["traps_log"])."', ";
            $rq .= "`traps_exec_interval` = '".$this->_db->escape($ret["traps_exec_interval"])."', ";
            $rq .= "`traps_exec_interval_type` = '".$this->_db->escape($ret["traps_exec_interval_type"])."', ";
            $rq .= "`traps_downtime` = '".$this->_db->escape($ret["traps_downtime"])."', ";
            $rq .= "`traps_exec_method` = '".$this->_db->escape($ret["traps_exec_method"])."', ";
            $rq .= "`traps_output_transform` = '".$this->_db->escape($ret["traps_output_transform"])."', ";
            $rq .= "`traps_advanced_treatment_default` = '".$this->_db->escape($ret['traps_advanced_treatment_default'])."', ";
            $rq .= "`traps_timeout` = '".$this->_db->escape($ret["traps_timeout"])."' ";
            $rq .= "WHERE `traps_id` = '".$traps_id."'";
            $this->_db->query($rq);

            /*
             * Logs
             */
            $fields["traps_name"] = $this->_db->escape($ret["traps_name"]);
            $fields["traps_args"] = $this->_db->escape($ret["traps_args"]);
            $fields["traps_status"] = $this->_db->escape($ret["traps_status"]);
            $fields["traps_submit_result_enable"] = $this->_db->escape($ret["traps_submit_result_enable"]);
            $fields["traps_reschedule_svc_enable"] = $this->_db->escape($ret["traps_reschedule_svc_enable"]);
            $fields["traps_execution_command"] = $this->_db->escape($ret["traps_execution_command"]);
            $fields["traps_execution_command_enable"] = $this->_db->escape($ret["traps_execution_command_enable"]);
            $fields["traps_comments"] = $this->_db->escape($ret["traps_comments"]);
            $fields["traps_routing_mode"] = $this->_db->escape($ret["traps_routing_mode"]);
            $fields["traps_routing_value"] = $this->_db->escape($ret["traps_routing_value"]);
            $fields["manufacturer_id"] = $this->_db->escape($ret["manufacturer_id"]);

            $this->_setMatchingOptions($traps_id, $_POST);
            $this->_setServiceRelations($traps_id);
            $this->_setServiceTemplateRelations($traps_id);
            $this->_setPreexec($traps_id);
            $this->_centreon->CentreonLogAction->insertLog("traps", $traps_id, $fields["traps_name"], "c", $fields);
	}

        /**
         * Set preexec commands
         * 
         * @param int $trapId
         */
        protected function _setPreexec($trapId) {
            $this->_db->query("DELETE FROM traps_preexec 
                WHERE trap_id = ".$this->_db->escape($trapId));
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
                    $insertStr .= "($trapId, '".$this->_db->escape($value)."', $i)";
                    $i++;
                }
            }
            if ($insertStr) {
                $this->_db->query("INSERT INTO traps_preexec (trap_id, tpe_string, tpe_order) VALUES $insertStr");
            }
        }
        
        /**
         * Delete & insert service relations
         * 
         * @param int $trapId
         */
        protected function _setServiceRelations($trapId) {
            $this->_db->query("DELETE FROM traps_service_relation 
                    WHERE traps_id = " . $this->_db->escape($trapId). "
                    AND NOT EXISTS (SELECT s.service_id 
                        FROM service s 
                        WHERE s.service_register = '0'
                        AND s.service_id = traps_service_relation.service_id)");
            $services = CentreonUtils::mergeWithInitialValues($this->_form, 'services');
            $insertStr = "";
            $first = true;
            $already = array();
            foreach ($services as $id) {
                if (!$first) {
                    $insertStr .= ",";
                } else {
                    $first = false;
                }
                $t = preg_split("/\-/", $id);
                if (!isset($already[$t[1]])) {
                    $insertStr .= "($trapId, $t[1])";
                }
                $already[$t[1]] = true;
            }
            if ($insertStr) {
                $this->_db->query("INSERT INTO traps_service_relation (traps_id, service_id) VALUES $insertStr");
            }
        }
        
        /**
         * Delete & insert service template relations
         * 
         * @param int $trapId
         */
        protected function _setServiceTemplateRelations($trapId) {
            $this->_db->query("DELETE FROM traps_service_relation 
                    WHERE traps_id = " . $this->_db->escape($trapId). "
                    AND NOT EXISTS (SELECT s.service_id 
                        FROM service s 
                        WHERE s.service_register = '1'
                        AND s.service_id = traps_service_relation.service_id)");
            $serviceTpl = (array)$this->_form->getSubmitValue('service_templates');
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
                $this->_db->query("INSERT INTO traps_service_relation (traps_id, service_id) VALUES $insertStr");
            }
        }
        
        /**
         * Insert Traps
         * 
         * @param array $ret
         */
	public function insert($ret = array())	{
            if (!count($ret)) {
                $ret = $this->_form->getSubmitValues();
            }
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
                traps_advanced_treatment, traps_comments, traps_routing_mode, traps_routing_value, traps_routing_filter_services, manufacturer_id,
                traps_log, traps_exec_interval, traps_exec_interval_type, traps_exec_method, traps_downtime, traps_output_transform, traps_advanced_treatment_default,
                traps_timeout) ";
            $rq .= "VALUES ";
            $rq .= "('".$this->_db->escape($ret["traps_name"])."',";
            $rq .= "'".$this->_db->escape($ret["traps_oid"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_args"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_status"])."', ";
            $rq .= "".$this->_db->escape($ret["severity"]).", ";
            $rq .= "'".$this->_db->escape($ret["traps_submit_result_enable"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_reschedule_svc_enable"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_execution_command"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_execution_command_enable"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_advanced_treatment"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_comments"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_routing_mode"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_routing_value"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_routing_filter_services"])."', ";
            $rq .= "'".$this->_db->escape($ret["manufacturer_id"])."',";
            $rq .= "'".$this->_db->escape($ret["traps_log"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_exec_interval"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_exec_interval_type"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_exec_method"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_downtime"])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_output_transform"])."', ";
            $rq .= "'".$this->_db->escape($ret['traps_advanced_treatment_default'])."', ";
            $rq .= "'".$this->_db->escape($ret["traps_timeout"])."') ";
            $this->_db->query($rq);
            $res = $this->_db->query("SELECT MAX(traps_id) FROM traps");
            $traps_id = $res->fetchRow();

            /*
             * logs
             */
            $fields["traps_name"] = $this->_db->escape($ret["traps_name"]);
            $fields["traps_args"] = $this->_db->escape($ret["traps_args"]);
            $fields["traps_status"] = $this->_db->escape($ret["traps_status"]);
            $fields["traps_submit_result_enable"] = $this->_db->escape($ret["traps_submit_result_enable"]);
            $fields["traps_reschedule_svc_enable"] = $this->_db->escape($ret["traps_reschedule_svc_enable"]);
            $fields["traps_execution_command"] = $this->_db->escape($ret["traps_execution_command"]);
            $fields["traps_execution_command_enable"] = $this->_db->escape($ret["traps_execution_command_enable"]);
            $fields["traps_advanced_treatment"] = $this->_db->escape($ret["traps_advanced_treatment"]);
            $fields["traps_comments"] = $this->_db->escape($ret["traps_comments"]);
            $fields["traps_routing_mode"] = $this->_db->escape($ret["traps_routing_mode"]);
            $fields["manufacturer_id"] = $this->_db->escape($ret["manufacturer_id"]);
            $this->_centreon->CentreonLogAction->insertLog("traps", $traps_id["MAX(traps_id)"], $fields["traps_name"], "a", $fields);

            $this->_setMatchingOptions($traps_id['MAX(traps_id)'], $_POST);
            $this->_setServiceRelations($traps_id['MAX(traps_id)']);
            $this->_setServiceTemplateRelations($traps_id['MAX(traps_id)']);
            $this->_setPreexec($traps_id['MAX(traps_id)']);
            if ($this->_centreon->user->admin) {
                $this->_setServiceTemplateRelations($traps_id['MAX(traps_id)'], $ret['service_templates']);
            }

            return ($traps_id["MAX(traps_id)"]);
	}
        
        /**
         * Get pre exec commands from trap_id
         * 
         * @param int $trapId
         * @return array
         */
        public function getPreexecFromTrapId($trapId) {
            $res = $this->_db->query("SELECT tpe_string
                    FROM traps_preexec
                    WHERE trap_id = ".$this->_db->escape($trapId)."
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
         * @param int $trapId
         * @return array
         */
        public function getMatchingRulesFromTrapId($trapId) {
            $res = $this->_db->query("SELECT tmo_string, tmo_regexp, tmo_status, severity_id
                    FROM traps_matching_properties
                    WHERE trap_id = ".$this->_db->escape($trapId)."
                    ORDER BY tmo_order");
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
}
?>