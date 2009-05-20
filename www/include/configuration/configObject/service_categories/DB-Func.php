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
 
	if (!isset ($oreon))
		exit ();

	function testServiceCategorieExistence ($name = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('sc_id');
		$DBRESULT =& $pearDB->query("SELECT `sc_name`, `sc_id` FROM `service_categories` WHERE `sc_name` = '".htmlentities($name, ENT_QUOTES)."'");
		$sc =& $DBRESULT->fetchRow();
		if ($DBRESULT->numRows() >= 1 && $sc["sc_id"] == $id)
			return true;
		else if ($DBRESULT->numRows() >= 1 && $sc["sc_id"] != $id)
			return false;
		else
			return true;
	}

	function multipleServiceCategorieInDB ($sc = array(), $nbrDup = array())	{
		foreach($sc as $key => $value)	{
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT * FROM `service_categories` WHERE `sc_id` = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["sc_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "sc_name" ? ($sc_name = $value2 = $value2."_".$i) : null;
					$key2 == "sc_description" ? ($sc_alias = $value2 = $value2) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2 != NULL?("'".$value2."'"):"NULL");
				}
				if (testServiceCategorieExistence($sc_name))	{
					$val ? $rq = "INSERT INTO `service_categories` VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
					$DBRESULT =& $pearDB->query("SELECT MAX(sc_id) FROM `service_categories`");
					$maxId =& $DBRESULT->fetchRow();
				}
			}
		}
	}

	function enableServiceCategorieInDB($sc_id = null, $sc_arr = array())	{
		if (!$sc_id && !count($sc_arr)) return;
		global $pearDB;
		if ($sc_id)
			$sc_arr = array($sc_id=>"1");
		foreach($sc_arr as $key=>$value)	{
			$DBRESULT =& $pearDB->query("UPDATE service_categories SET sc_activate = '1' WHERE sc_id = '".$key."'");
		}
	}

	function disableServiceCategorieInDB($sc_id = null, $sc_arr = array())	{
		if (!$sc_id && !count($sc_arr)) return;
		global $pearDB;
		if ($sc_id)
			$sc_arr = array($sc_id=>"1");
		foreach($sc_arr as $key=>$value)	{
			$DBRESULT =& $pearDB->query("UPDATE service_categories SET sc_activate = '0' WHERE sc_id = '".$key."'");
		}
	}
	
	function insertServiceCategorieInDB(){
		global $pearDB;
		if (testServiceCategorieExistence($_POST["sc_name"])){
			$DBRESULT =& $pearDB->query("INSERT INTO `service_categories` (`sc_name` , `sc_description` , `sc_activate` ) VALUES ('".$_POST["sc_name"]."', '".$_POST["sc_description"]."', '".$_POST["sc_activate"]["sc_activate"]."')");
		}
	}
	
	function updateServiceCategorieInDB(){
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE `service_categories` SET `sc_name` = '".$_POST["sc_name"]."' , `sc_description` = '".$_POST["sc_description"]."' , `sc_activate` = '".$_POST["sc_activate"]["sc_activate"]."' WHERE `sc_id` = '".$_POST["sc_id"]."'");
	}
	
	function deleteServiceCategorieInDB($sc_id = NULL){
		global $pearDB;
		$select = $_POST["select"];
		foreach ($select as $key => $value){
			$DBRESULT =& $pearDB->query("DELETE FROM `service_categories` WHERE `sc_id` = '".$key."'");
		}
	}

?>