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
 
	if (!isset ($oreon))
		exit ();

	function testCommandCategorieExistence ($name = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		
		if (isset($form))
			$id = $form->getSubmitValue('cmd_category_id');
		
		$DBRESULT = $pearDB->query("SELECT `category_name`, `cmd_category_id` FROM `command_categories` WHERE `category_name` = '".$pearDB->escape($name)."'");
		$cat = $DBRESULT->fetchRow();
		
		if ($DBRESULT->numRows() >= 1 && $cat["cmd_category_id"] == $id)
			return true;
		else if ($DBRESULT->numRows() >= 1 && $cat["cmd_category_id"] != $id)
			return false;
		else
			return true;
	}

	function multipleCommandCategorieInDB ($sc = array(), $nbrDup = array())	{
		global $pearDB;
			
		foreach ($sc as $key => $value)	{
			
			$DBRESULT = $pearDB->query("SELECT * FROM `command_categories` WHERE `cmd_category_id` = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["cmd_category_id"] = '';
			
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2 => $value2)	{
					$key2 == "category_name" ? ($sc_name = $value2 = $value2."_".$i) : null;
					$key2 == "category_alias" ? ($sc_alias = $value2 = $value2) : null;
					$val ? $val .= ($value2 != NULL?(", '".$value2."'"):", NULL") : $val .= ($value2 != NULL?("'".$value2."'"):"NULL");
				}
				if (testCommandCategorieExistence($sc_name))	{
					$val ? $rq = "INSERT INTO `command_categories` VALUES (".$val.")" : $rq = null;
					$DBRESULT = $pearDB->query($rq);
					
					$DBRESULT = $pearDB->query("SELECT MAX(cmd_category_id) FROM `command_categories`");
					$maxId = $DBRESULT->fetchRow();
				}
			}
		}
	}
	
	function insertCommandCategorieInDB(){
		global $pearDB;
		
		if (testCommandCategorieExistence($_POST["category_name"])){
			$DBRESULT = $pearDB->query("INSERT INTO `command_categories` (`category_name` , `category_alias`, `category_order`) VALUES ('".$pearDB->escape($_POST["category_name"])."', '".$pearDB->escape($_POST["category_alias"])."', '1')");
		}
	}
	
	function updateCommandCategorieInDB(){
		global $pearDB;
		
		$DBRESULT = $pearDB->query("UPDATE `command_categories` SET `category_name` = '".$pearDB->escape($_POST["category_name"])."' , `category_alias` = '".$pearDB->escape($_POST["category_alias"])."' , `category_order` = '".$pearDB->escape($_POST["category_order"])."' WHERE `cmd_category_id` = '".$pearDB->escape($_POST["cmd_category_id"])."'");
	}
	
	function deleteCommandCategorieInDB($sc_id = NULL){
		global $pearDB;
		$select = $_POST["select"];
		foreach ($select as $key => $value){
			$DBRESULT = $pearDB->query("DELETE FROM `command_categories` WHERE `cmd_category_id` = '".$key."'");
		}
	}

?>