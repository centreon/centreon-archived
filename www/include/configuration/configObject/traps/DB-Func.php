<?php
/*
 * Copyright 2005-2009 MERETHIS
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
 
	function testTrapExistence($oid = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('traps_id');
		$DBRESULT =& $pearDB->query("SELECT traps_oid, traps_id FROM traps WHERE traps_oid = '".htmlentities($oid, ENT_QUOTES)."'");
		$trap =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $trap["traps_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $trap["traps_id"] != $id)
			return false;
		else
			return true;
	}

	function deleteTrapInDB($traps = array())	{
		global $pearDB, $oreon;
		foreach($traps as $key=>$value)		{
			$DBRESULT2 =& $pearDB->query("SELECT traps_name FROM `traps` WHERE `traps_id` = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
			
			$DBRESULT =& $pearDB->query("DELETE FROM traps WHERE traps_id = '".$key."'");
			$oreon->CentreonLogAction->insertLog("traps", $key, $row['traps_name'], "d");
		}
	}
	
	function multipleTrapInDB($traps = array(), $nbrDup = array())	{
		global $pearDB, $oreon;
		foreach ($traps as $key => $value)	{
			$DBRESULT =& $pearDB->query("SELECT * FROM traps WHERE traps_id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["traps_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2 => $value2)	{
					$key2 == "traps_name" ? ($traps_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
					if ($key2 != "traps_id")
						$fields[$key2] = $value2;
					$fields["traps_name"] = $traps_name;
				}
				$val ? $rq = "INSERT INTO traps VALUES (".$val.")" : $rq = null;
				$DBRESULT =& $pearDB->query($rq);
				$DBRESULT2 =& $pearDB->query("SELECT MAX(traps_id) FROM traps");
				$maxId = $DBRESULT2->fetchRow();
				$oreon->CentreonLogAction->insertLog("traps", $maxId["MAX(traps_id)"], $traps_name, "a", $fields);
			}
		}
	}
	
	function updateTrapInDB ($traps_id = NULL)	{
		if (!$traps_id) return;
		updateTrap($traps_id);
	}
	
	function updateTrap($traps_id = null)	{
		global $form, $pearDB, $oreon;
		
		if (!$traps_id) 
			return;
		
		$ret = array();
		$ret = $form->getSubmitValues();
		if (!isset($ret["traps_reschedule_svc_enable"]) || !$ret["traps_reschedule_svc_enable"])
			$ret["traps_reschedule_svc_enable"] = 0;
		if (!isset($ret["traps_submit_result_enable"]) || !$ret["traps_submit_result_enable"])
			$ret["traps_submit_result_enable"] = 0;
		if (!isset($ret["traps_execution_command_enable"])|| !$ret["traps_execution_command_enable"])
			$ret["traps_execution_command_enable"] = 0;
		
		$rq = "UPDATE traps ";
		$rq .= "SET `traps_name` = '".htmlentities($ret["traps_name"], ENT_QUOTES)."', ";
		$rq .= "`traps_oid` = '".htmlentities($ret["traps_oid"], ENT_QUOTES)."', ";
		$rq .= "`traps_args` = '".htmlentities($ret["traps_args"], ENT_QUOTES)."', ";
		$rq .= "`traps_status` = '".htmlentities($ret["traps_status"], ENT_QUOTES)."', ";
		$rq .= "`traps_submit_result_enable` = '".$ret["traps_submit_result_enable"]."', ";
		$rq .= "`traps_reschedule_svc_enable` = '".$ret["traps_reschedule_svc_enable"]."', ";
		$rq .= "`traps_execution_command` = '".htmlentities($ret["traps_execution_command"], ENT_QUOTES)."', ";
		$rq .= "`traps_execution_command_enable` = '".$ret["traps_execution_command_enable"]."', ";
		$rq .= "`traps_comments` = '".htmlentities($ret["traps_comments"], ENT_QUOTES)."', ";
		$rq .= "`manufacturer_id` = '".htmlentities($ret["manufacturer_id"], ENT_QUOTES)."' ";
		$rq .= "WHERE `traps_id` = '".$traps_id."'";
		$DBRESULT =& $pearDB->query($rq);
		$fields["traps_name"] = htmlentities($ret["traps_name"], ENT_QUOTES);
		$fields["traps_args"] = htmlentities($ret["traps_args"], ENT_QUOTES);
		$fields["traps_status"] = htmlentities($ret["traps_status"], ENT_QUOTES);
		$fields["traps_submit_result_enable"] = $ret["traps_submit_result_enable"];
		$fields["traps_reschedule_svc_enable"] = $ret["traps_reschedule_svc_enable"];
		$fields["traps_execution_command"] = htmlentities($ret["traps_execution_command"], ENT_QUOTES);
		$fields["traps_execution_command_enable"] = $ret["traps_execution_command_enable"];
		$fields["traps_comments"] = htmlentities($ret["traps_comments"], ENT_QUOTES);
		$fields["manufacturer_id"] = htmlentities($ret["manufacturer_id"], ENT_QUOTES);
		$oreon->CentreonLogAction->insertLog("traps", $traps_id, $fields["traps_name"], "c", $fields);
	}
	
	function insertTrapInDB ($ret = array())	{
		$traps_id = insertTrap($ret);
		return ($traps_id);
	}
	
	function insertTrap($ret = array())	{
		global $form, $pearDB, $oreon;
		
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO traps ";
		$rq .= "(traps_name, traps_oid, traps_args, traps_status, traps_submit_result_enable, traps_reschedule_svc_enable, traps_execution_command, traps_execution_command_enable, traps_comments, manufacturer_id) ";
		$rq .= "VALUES ";
		$rq .= "('".htmlentities($ret["traps_name"], ENT_QUOTES)."',";
		$rq .= "'".htmlentities($ret["traps_oid"], ENT_QUOTES)."', ";
		$rq .= "'".htmlentities($ret["traps_args"], ENT_QUOTES)."', ";
		$rq .= "'".htmlentities($ret["traps_status"], ENT_QUOTES)."', ";
		$rq .= "'".htmlentities($ret["traps_submit_result_enable"], ENT_QUOTES)."', ";
		$rq .= "'".htmlentities($ret["traps_reschedule_svc_enable"], ENT_QUOTES)."', ";
		$rq .= "'".htmlentities($ret["traps_execution_command"], ENT_QUOTES)."', ";
		$rq .= "'".htmlentities($ret["traps_execution_command_enable"], ENT_QUOTES)."', ";
		$rq .= "'".htmlentities($ret["traps_comments"], ENT_QUOTES)."', ";
		$rq .= "'".htmlentities($ret["manufacturer_id"], ENT_QUOTES)."')";
		$DBRESULT =& $pearDB->query($rq);
		$DBRESULT =& $pearDB->query("SELECT MAX(traps_id) FROM traps");
		$traps_id = $DBRESULT->fetchRow();
		
		$fields["traps_name"] = htmlentities($ret["traps_name"], ENT_QUOTES);
		$fields["traps_args"] = htmlentities($ret["traps_args"], ENT_QUOTES);
		$fields["traps_status"] = htmlentities($ret["traps_status"], ENT_QUOTES);
		$fields["traps_submit_result_enable"] = $ret["traps_submit_result_enable"];
		$fields["traps_reschedule_svc_enable"] = $ret["traps_reschedule_svc_enable"];
		$fields["traps_execution_command"] = htmlentities($ret["traps_execution_command"], ENT_QUOTES);
		$fields["traps_execution_command_enable"] = $ret["traps_execution_command_enable"];
		$fields["traps_comments"] = htmlentities($ret["traps_comments"], ENT_QUOTES);
		$fields["manufacturer_id"] = htmlentities($ret["manufacturer_id"], ENT_QUOTES);
		$oreon->CentreonLogAction->insertLog("traps", $traps_id["MAX(traps_id)"], $fields["traps_name"], "a", $fields);
		
		return ($traps_id["MAX(traps_id)"]);
	}
?>