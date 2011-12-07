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
     * @param $trapId
     * @param $tab
     */
    private function _setMatchingOptions($trapId, $tab = array()) {
       	/**
         * Remove all data before insert them again.
         */
    	$this->_db->query("DELETE FROM traps_matching_properties WHERE trap_id = '" . $trapId ."'");

        if (isset($tab['traps_advanced_treatment']) && $tab['traps_advanced_treatment']) {
            $matchingTab = array();
            $i = 0;
            foreach ($tab as $key => $value) {
                if (preg_match('/^regularRegexp_(\d)/', $key, $matches)) {
                    $index = $matches[1];
                    $matchingTab['order'][$i] = $this->_db->escape($tab['regularOrder_'.$index]);
                    $matchingTab['regexp'][$i] = $this->_db->escape($tab['regularRegexp_'.$index]);
                    $matchingTab['status'][$i] = $this->_db->escape($tab['regularStatus_'.$index]);
                    $matchingTab['var'][$i] = $this->_db->escape($tab['regularVar_'.$index]);
                    $i++;
                } else if (preg_match('/^additionalRegexp_(\d)/', $key, $matches)) {
                    $index = $matches[1];
                    $matchingTab['order'][$i] = $this->_db->escape($tab['additionalOrder_'.$index]);
                    $matchingTab['regexp'][$i] = $this->_db->escape($tab['additionalRegexp_'.$index]);
                    $matchingTab['status'][$i] = $this->_db->escape($tab['additionalStatus_'.$index]);
                    $matchingTab['var'][$i] = $this->_db->escape($tab['additionalVar_'.$index]);
                    $i++;
                }
            }
            if (isset($matchingTab['order'])) {
	            asort($matchingTab['order']);
	            $j = 1;
	            foreach ($matchingTab['order'] as $key => $value) {
	                $query = "INSERT INTO traps_matching_properties (trap_id, tmo_order, tmo_regexp, tmo_string, tmo_status) VALUES (";
	                $query .= "'".$trapId."', ";
	                $query .= "'".$j."', ";
	                $query .= "'".$matchingTab['regexp'][$key]."', ";
	                $query .= "'".$matchingTab['var'][$key]."', ";
	                $query .= "'".$matchingTab['status'][$key]."' ";
	                $query .= ")";
	                $this->_db->query($query);
	                $j++;
	            }
            }
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
		foreach ($traps as $key => $value)	{
			$res = $this->_db->query("SELECT * FROM traps WHERE traps_id = '".$key."' LIMIT 1");
			$row = $res->fetchRow();
			$row["traps_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2 => $value2)	{
					$key2 == "traps_name" ? ($traps_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$this->_db->escape($value2)."'"):", NULL") : $val .= ($value2!=NULL?("'".$this->_db->escape($value2)."'"):"NULL");
					if ($key2 != "traps_id") {
						$fields[$key2] = $value2;
                    }
					$fields["traps_name"] = $traps_name;
				}
				$val ? $rq = "INSERT INTO traps VALUES (".$val.")" : $rq = null;
				$res = $this->_db->query($rq);
				$res2 = $this->_db->query("SELECT MAX(traps_id) FROM traps");
				$maxId = $res2->fetchRow();
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

		$rq = "UPDATE traps ";
		$rq .= "SET `traps_name` = '".$this->_db->escape($ret["traps_name"])."', ";
		$rq .= "`traps_oid` = '".$this->_db->escape($ret["traps_oid"])."', ";
		$rq .= "`traps_args` = '".$this->_db->escape($ret["traps_args"])."', ";
		$rq .= "`traps_status` = '".$this->_db->escape($ret["traps_status"])."', ";
		$rq .= "`traps_submit_result_enable` = '".$this->_db->escape($ret["traps_submit_result_enable"])."', ";
		$rq .= "`traps_reschedule_svc_enable` = '".$this->_db->escape($ret["traps_reschedule_svc_enable"])."', ";
		$rq .= "`traps_execution_command` = '".$this->_db->escape($ret["traps_execution_command"])."', ";
		$rq .= "`traps_execution_command_enable` = '".$this->_db->escape($ret["traps_execution_command_enable"])."', ";
		$rq .= "`traps_advanced_treatment` = '".$this->_db->escape($ret["traps_advanced_treatment"])."', ";
		$rq .= "`traps_comments` = '".$this->_db->escape($ret["traps_comments"])."', ";
		$rq .= "`manufacturer_id` = '".$this->_db->escape($ret["manufacturer_id"])."' ";
		$rq .= "WHERE `traps_id` = '".$traps_id."'";
		$res = $this->_db->query($rq);

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
		$fields["manufacturer_id"] = $this->_db->escape($ret["manufacturer_id"]);

        $this->_setMatchingOptions($traps_id, $_POST);

		$this->_centreon->CentreonLogAction->insertLog("traps", $traps_id, $fields["traps_name"], "c", $fields);
	}

    /**
     *
     * Insert Traps
     *  @param $ret
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


		$rq = "INSERT INTO traps ";
		$rq .= "(traps_name, traps_oid, traps_args, traps_status, traps_submit_result_enable, traps_reschedule_svc_enable, traps_execution_command, traps_execution_command_enable, traps_advanced_treatment, traps_comments, manufacturer_id) ";
		$rq .= "VALUES ";
		$rq .= "('".$this->_db->escape($ret["traps_name"])."',";
		$rq .= "'".$this->_db->escape($ret["traps_oid"])."', ";
		$rq .= "'".$this->_db->escape($ret["traps_args"])."', ";
		$rq .= "'".$this->_db->escape($ret["traps_status"])."', ";
		$rq .= "'".$this->_db->escape($ret["traps_submit_result_enable"])."', ";
		$rq .= "'".$this->_db->escape($ret["traps_reschedule_svc_enable"])."', ";
		$rq .= "'".$this->_db->escape($ret["traps_execution_command"])."', ";
		$rq .= "'".$this->_db->escape($ret["traps_execution_command_enable"])."', ";
		$rq .= "'".$this->_db->escape($ret["traps_advanced_treatment"])."', ";
		$rq .= "'".$this->_db->escape($ret["traps_comments"])."', ";
		$rq .= "'".$this->_db->escape($ret["manufacturer_id"])."')";
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
		$fields["manufacturer_id"] = $this->_db->escape($ret["manufacturer_id"]);
		$this->_centreon->CentreonLogAction->insertLog("traps", $traps_id["MAX(traps_id)"], $fields["traps_name"], "a", $fields);

        $this->_setMatchingOptions($traps_id['MAX(traps_id)'], $_POST);

		return ($traps_id["MAX(traps_id)"]);
	}
}
?>