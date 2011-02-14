<?php
/*
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
 
	if (!isset ($oreon))
		exit ();

	function testPurgePolicyExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('purge_policy_id');
		$DBRESULT = $pearDB->query("SELECT purge_policy_name, purge_policy_id FROM purge_policy WHERE purge_policy_name = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
		$pp = $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $pp["purge_policy_id"] == $id)
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $pp["purge_policy_id"] != $id)
			return false;
		else
			return true;
	}
	
	function deletePurgePolicyInDB ($ppols = array())	{
		global $pearDB;
		foreach($ppols as $key=>$value)		{
			$DBRESULT = $pearDB->query("DELETE FROM purge_policy WHERE purge_policy_id = '".$key."'");
		}
	}

	function multiplePurgePolicyInDB ($ppols = array(), $nbrDup = array())	{
		foreach($ppols as $key=>$value)	{
			global $pearDB;
			$DBRESULT = $pearDB->query("SELECT * FROM purge_policy WHERE purge_policy_id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["purge_policy_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "purge_policy_name" ? ($purge_policy_name = $value2 = $value2." ".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2 != NULL?("'".$value2."'"):"NULL");
				}
				if (testPurgePolicyExistence($purge_policy_name))	{
					$val ? $rq = "INSERT INTO purge_policy VALUES (".$val.")" : $rq = null;
					$DBRESULT = $pearDB->query($rq);
				}
			}
		}
	}

	function updatePurgePolicyInDB ($purge_policy_id = NULL)	{
		if (!$purge_policy_id) return;
		updatePurgePolicy($purge_policy_id);
	}

	function insertPurgePolicyInDB ($ret = array())	{
		$purge_policy_id = insertPurgePolicy($ret);
		return ($purge_policy_id);
	}

	function insertPurgePolicy($ret = array())	{
		global $form;
		global $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO `purge_policy` " .
				"( `purge_policy_id` , `purge_policy_name` , `purge_policy_alias` , " .
				"`purge_policy_retention` , `purge_policy_raw` , `purge_policy_bin` , " .
				"`purge_policy_metric` , `purge_policy_service` , `purge_policy_host` , " .
				"`purge_policy_comment` )" .
				"VALUES ('', ";
		isset($ret["purge_policy_name"]) && $ret["purge_policy_name"] != NULL ? $rq .= "'".htmlentities($ret["purge_policy_name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["purge_policy_alias"]) && $ret["purge_policy_alias"] != NULL ? $rq .= "'".htmlentities($ret["purge_policy_alias"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["purge_policy_retention"]) && $ret["purge_policy_retention"] != NULL ? $rq .= "'".$ret["purge_policy_retention"]."', ": $rq .= "NULL, ";
		isset($ret["purge_policy_raw"]["purge_policy_raw"]) && $ret["purge_policy_raw"]["purge_policy_raw"] != NULL ? $rq .= "'".$ret["purge_policy_raw"]["purge_policy_raw"]."', ": $rq .= "NULL, ";
		isset($ret["purge_policy_bin"]["purge_policy_bin"]) && $ret["purge_policy_bin"]["purge_policy_bin"] != NULL ? $rq .= "'".$ret["purge_policy_bin"]["purge_policy_bin"]."', ": $rq .= "NULL, ";
		isset($ret["purge_policy_metric"]["purge_policy_metric"]) && $ret["purge_policy_metric"]["purge_policy_metric"] != NULL ? $rq .= "'".$ret["purge_policy_metric"]["purge_policy_metric"]."', ": $rq .= "NULL, ";
		isset($ret["purge_policy_service"]["purge_policy_service"]) && $ret["purge_policy_service"]["purge_policy_service"] != NULL ? $rq .= "'".$ret["purge_policy_service"]["purge_policy_service"]."', ": $rq .= "NULL, ";
		isset($ret["purge_policy_host"]["purge_policy_host"]) && $ret["purge_policy_host"]["purge_policy_host"] != NULL ? $rq .= "'".$ret["purge_policy_host"]["purge_policy_host"]."', ": $rq .= "NULL, ";
		isset($ret["purge_policy_comment"]) && $ret["purge_policy_comment"] != NULL ? $rq .= "'".htmlentities($ret["purge_policy_comment"], ENT_QUOTES, "UTF-8")."'": $rq .= "NULL";
		$rq .= ")";
		$DBRESULT = $pearDB->query($rq);
		$DBRESULT = $pearDB->query("SELECT MAX(purge_policy_id) FROM purge_policy");
		$purge_policy_id = $DBRESULT->fetchRow();
		return ($purge_policy_id["MAX(purge_policy_id)"]);
	}

	function updatePurgePolicy($purge_policy_id = null)	{
		if (!$purge_policy_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE purge_policy ";
		$rq .= "SET  purge_policy_name = ";
		isset($ret["purge_policy_name"]) && $ret["purge_policy_name"] != NULL ? $rq .= "'".htmlentities($ret["purge_policy_name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "purge_policy_alias = ";
		isset($ret["purge_policy_alias"]) && $ret["purge_policy_alias"] != NULL ? $rq .= "'".htmlentities($ret["purge_policy_alias"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "purge_policy_retention = ";
		isset($ret["purge_policy_retention"]) && $ret["purge_policy_retention"] != NULL ? $rq .= "'".htmlentities($ret["purge_policy_retention"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "purge_policy_raw = ";
		isset($ret["purge_policy_raw"]["purge_policy_raw"]) && $ret["purge_policy_raw"]["purge_policy_raw"] != NULL ? $rq .= "'".$ret["purge_policy_raw"]["purge_policy_raw"]."', ": $rq .= "NULL, ";
		$rq .= "purge_policy_bin = ";
		isset($ret["purge_policy_bin"]["purge_policy_bin"]) && $ret["purge_policy_bin"]["purge_policy_bin"] != NULL ? $rq .= "'".$ret["purge_policy_bin"]["purge_policy_bin"]."', ": $rq .= "NULL, ";
		$rq .= "purge_policy_metric = ";
		isset($ret["purge_policy_metric"]["purge_policy_metric"]) && $ret["purge_policy_metric"]["purge_policy_metric"] != NULL ? $rq .= "'".$ret["purge_policy_metric"]["purge_policy_metric"]."', ": $rq .= "NULL, ";
		$rq .= "purge_policy_service = ";
		isset($ret["purge_policy_service"]["purge_policy_service"]) && $ret["purge_policy_service"]["purge_policy_service"] != NULL ? $rq .= "'".$ret["purge_policy_service"]["purge_policy_service"]."', ": $rq .= "NULL, ";
		$rq .= "purge_policy_host = ";
		isset($ret["purge_policy_host"]["purge_policy_host"]) && $ret["purge_policy_host"]["purge_policy_host"] != NULL ? $rq .= "'".$ret["purge_policy_host"]["purge_policy_host"]."', ": $rq .= "NULL, ";
		$rq .= "purge_policy_comment = ";
		isset($ret["purge_policy_comment"]) && $ret["purge_policy_comment"] != NULL ? $rq .= "'".htmlentities($ret["purge_policy_comment"], ENT_QUOTES, "UTF-8")."' ": $rq .= "NULL ";
		$rq .= "WHERE purge_policy_id = '".$purge_policy_id."'";
		$DBRESULT = $pearDB->query($rq);
	}
?>