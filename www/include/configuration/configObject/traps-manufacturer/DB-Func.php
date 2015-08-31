<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
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
