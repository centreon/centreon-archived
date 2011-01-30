<?php
/*
 * Copyright 2005-2010 MERETHIS
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

	function testCommandCategorieExistence ($name = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		
		if (isset($form))
			$id = $form->getSubmitValue('cmd_category_id');
		
		$DBRESULT = $pearDB->query("SELECT `category_name`, `cmd_category_id` FROM `command_categories` WHERE `category_name` = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
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
			$DBRESULT = $pearDB->query("INSERT INTO `command_categories` (`category_name` , `category_alias`, `category_order`) VALUES ('".$_POST["category_name"]."', '".$_POST["category_alias"]."', '1')");
		}
	}
	
	function updateCommandCategorieInDB(){
		global $pearDB;
		
		$DBRESULT = $pearDB->query("UPDATE `command_categories` SET `category_name` = '".$_POST["category_name"]."' , `category_alias` = '".$_POST["category_alias"]."' , `category_order` = '".$_POST["category_order"]."' WHERE `cmd_category_id` = '".$_POST["cmd_category_id"]."'");
	}
	
	function deleteCommandCategorieInDB($sc_id = NULL){
		global $pearDB;
		$select = $_POST["select"];
		foreach ($select as $key => $value){
			$DBRESULT = $pearDB->query("DELETE FROM `command_categories` WHERE `cmd_category_id` = '".$key."'");
		}
	}

?>