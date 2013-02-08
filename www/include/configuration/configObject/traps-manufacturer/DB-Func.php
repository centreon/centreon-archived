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
 

	function testMnftrExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('id');
		$DBRESULT = $pearDB->query("SELECT name, id FROM traps_vendor WHERE name = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
		$mnftr = $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $mnftr["id"] == $id)	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $mnftr["id"] != $id)
			return false;
		else
			return true;
	}

	function deleteMnftrInDB ($mnftr = array())	{
		global $pearDB, $oreon;
		foreach($mnftr as $key=>$value)		{
			$DBRESULT2 = $pearDB->query("SELECT name FROM `traps_vendor` WHERE `id` = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
			
			$DBRESULT = $pearDB->query("DELETE FROM traps_vendor WHERE id = '".htmlentities($key, ENT_QUOTES, "UTF-8")."'");
			$oreon->CentreonLogAction->insertLog("manufacturer", $key, $row['name'], "d");
		}
	}
	
	function multipleMnftrInDB ($mnftr = array(), $nbrDup = array())	{
		foreach($mnftr as $key=>$value)	{
			global $pearDB, $oreon;
			$DBRESULT = $pearDB->query("SELECT * FROM traps_vendor WHERE id = '".htmlentities($key, ENT_QUOTES, "UTF-8")."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "name" ? ($name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
					if ($key2 != "id")
						$fields[$key2] = $value2;
					$fields["name"] = $name;
				}
				if (testMnftrExistence($name)) {
					$val ? $rq = "INSERT INTO traps_vendor VALUES (".$val.")" : $rq = null;
					$DBRESULT = $pearDB->query($rq);
					$oreon->CentreonLogAction->insertLog("manufacturer", htmlentities($key, ENT_QUOTES, "UTF-8"), $name, "a", $fields);
				}
			}
		}
	}
	
	function updateMnftrInDB ($id = NULL)	{
		if (!$id) return;
		updateMnftr($id);
	}
	
	function updateMnftr($id = null)	{
		global $form, $pearDB, $oreon;
		
		if (!$id) 
			return;
		
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE traps_vendor ";
		$rq .= "SET name = '".htmlentities($ret["name"], ENT_QUOTES, "UTF-8")."', ";
		$rq .= "alias = '".htmlentities($ret["alias"], ENT_QUOTES, "UTF-8")."', ";
		$rq .= "description = '".htmlentities($ret["description"], ENT_QUOTES, "UTF-8")."' ";
		$rq .= "WHERE id = '".$id."'";
		$DBRESULT = $pearDB->query($rq);
		$fields["name"] = htmlentities($ret["name"], ENT_QUOTES, "UTF-8");
		$fields["alias"] = htmlentities($ret["alias"], ENT_QUOTES, "UTF-8");
		$fields["description"] = htmlentities($ret["description"], ENT_QUOTES, "UTF-8");
		$oreon->CentreonLogAction->insertLog("manufacturer", $id, $fields["name"], "c", $fields);
	}
	
	function insertMnftrInDB ($ret = array())	{
		$id = insertMnftr($ret);
		return ($id);
	}
	
	function insertMnftr($ret = array())	{
		global $form, $pearDB, $oreon;
		
		if (!count($ret))
			$ret = $form->getSubmitValues();
		
		$rq = "INSERT INTO traps_vendor ";
		$rq .= "(name, alias, description) ";
		$rq .= "VALUES ";
		$rq .= "('".htmlentities($ret["name"], ENT_QUOTES, "UTF-8")."', ";
		$rq .= "'".htmlentities($ret["alias"], ENT_QUOTES, "UTF-8")."', ";
		$rq .= "'".htmlentities($ret["description"], ENT_QUOTES, "UTF-8")."')";
		$DBRESULT = $pearDB->query($rq);
		$DBRESULT = $pearDB->query("SELECT MAX(id) FROM traps_vendor");
		$mnftr_id = $DBRESULT->fetchRow();
		
		$fields["name"] = htmlentities($ret["name"], ENT_QUOTES, "UTF-8");
		$fields["alias"] = htmlentities($ret["alias"], ENT_QUOTES, "UTF-8");
		$fields["description"] = htmlentities($ret["description"], ENT_QUOTES, "UTF-8");
		$oreon->CentreonLogAction->insertLog("manufacturer", $mnftr_id["MAX(id)"], $fields["name"], "a", $fields);
		
		return ($mnftr_id["MAX(id)"]);
	}
?>
