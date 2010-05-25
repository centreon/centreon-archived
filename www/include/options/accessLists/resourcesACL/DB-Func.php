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
	function testExistence ($name = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('lca_id');
		
		$DBRESULT =& $pearDB->query("SELECT acl_res_name, acl_res_id FROM `acl_resources` WHERE acl_res_name = '".$name."'");
		$lca =& $DBRESULT->fetchRow();
		if ($DBRESULT->numRows() >= 1 && $lca["acl_res_id"] == $id)	
			return true;
		else if ($DBRESULT->numRows() >= 1 && $lca["acl_res_id"] != $id)
			return false;
		else
			return true;
	}
	
	function enableLCAInDB ($acl_id = null)	{
		if (!$acl_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE `acl_resources` SET acl_res_activate = '1', `changed` = '1' WHERE `acl_res_id` = '".$acl_id."'");		
	}
	
	function disableLCAInDB ($acl_id = null)	{
		if (!$acl_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE `acl_resources` SET acl_res_activate = '0', `changed` = '1' WHERE `acl_res_id` = '".$acl_id."'");		
	}
	
	function deleteLCAInDB ($acls = array())	{
		global $pearDB;
		foreach($acls as $key=>$value){
			$DBRESULT =& $pearDB->query("DELETE FROM `acl_resources` WHERE acl_res_id = '".$key."'");			
		}
	}
	
	function multipleLCAInDB ($lcas = array(), $nbrDup = array())	{		
		foreach($lcas as $key=>$value)	{
			global $pearDB;		
			$DBRESULT =& $pearDB->query("SELECT * FROM `acl_resources` WHERE acl_res_id = '".$key."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			$row["acl_res_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{				
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "acl_res_name" ? ($acl_name = $value2 = $value2."_".$i) : null;					
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				//$val .= ", '".time()."'";				
				if (testExistence($acl_name))	{
					$val ? $rq = "INSERT INTO acl_resources VALUES (".$val.")" : $rq = null;					
					$pearDB->query($rq);
					$DBRESULT =& $pearDB->query("SELECT MAX(acl_res_id) FROM acl_resources");
					$maxId =& $DBRESULT->fetchRow();
					$DBRESULT->free();
					if (isset($maxId["MAX(lca_id)"]))	{
						$DBRESULT =& $pearDB->query("SELECT DISTINCT acl_group_id FROM acl_res_group_relations WHERE acl_res_id = '".$key."'");
						while ($sg =& $DBRESULT->fetchRow())	{
							$DBRESULT2 =& $pearDB->query("INSERT INTO acl_res_group_relations VALUES ('', '".$maxId["MAX(acl_res_id)"]."', '".$sg["acl_group_id"]."')");
						}
						$DBRESULT->free();
					}
				}
			}
		}
	}
	
	function updateLCAInDB ($acl_id = NULL)	{
		if (!$acl_id) return;
		updateLCA($acl_id);
		updateGroups($acl_id);
		updateHosts($acl_id);
		updateHostGroups($acl_id);
		updateHostexcludes($acl_id);
		updateServiceCategories($acl_id);
		updateServiceGroups($acl_id);
		updateMetaServices($acl_id);
	}	
	
	function insertLCAInDB ()	{
		$acl_id = insertLCA();
		updateGroups($acl_id);
		updateHosts($acl_id);
		updateHostGroups($acl_id);
		updateHostexcludes($acl_id);
		updateServiceCategories($acl_id);
		updateServiceGroups($acl_id);
		updateMetaServices($acl_id);
		return ($acl_id);
	}
	
	function insertLCA()	{
		global $form, $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "INSERT INTO `acl_resources` ";
		$rq .= "(acl_res_name, acl_res_alias, acl_res_activate, changed) ";
		$rq .= "VALUES ('".htmlentities($ret["acl_res_name"], ENT_QUOTES)."', '".htmlentities($ret["acl_res_alias"], ENT_QUOTES)."', '".htmlentities($ret["acl_res_activate"]["acl_res_activate"], ENT_QUOTES)."', '1')";
		$DBRESULT =& $pearDB->query($rq);
		$DBRESULT =& $pearDB->query("SELECT MAX(acl_res_id) FROM `acl_resources`");
		$acl =& $DBRESULT->fetchRow();
		return ($acl["MAX(acl_res_id)"]);
	}
	
	function updateLCA($acl_id = null)	{
		if (!$acl_id) return;
		global $form, $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `acl_resources` ";
		$rq .= "SET acl_res_name = '".htmlentities($ret["acl_res_name"], ENT_QUOTES)."', " .
				"acl_res_alias = '".htmlentities($ret["acl_res_alias"], ENT_QUOTES)."', " .
				"acl_res_activate = '".htmlentities($ret["acl_res_activate"]["acl_res_activate"], ENT_QUOTES)."', " .
				"changed = '1' " .
				"WHERE acl_res_id = '".$acl_id."'";
		$DBRESULT =& $pearDB->query($rq);		
	}

	function updateGroups($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_res_group_relations WHERE acl_res_id = '".$acl_id."'");
		$ret = array();
		$ret = $form->getSubmitValue("acl_groups");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_res_group_relations (acl_res_id, acl_group_id) VALUES ('".$acl_id."', '".$value."')");
				}
			}
	}
	
	function updateHosts($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_resources_host_relations WHERE acl_res_id = '".$acl_id."'");
		$ret = array();
		$ret = $form->getSubmitValue("acl_hosts");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_resources_host_relations (acl_res_id, host_host_id) VALUES ('".$acl_id."', '".$value."')");
				}
			}
	}
	
	function updateHostexcludes($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_resources_hostex_relations WHERE acl_res_id = '".$acl_id."'");
		$ret = array();
		$ret = $form->getSubmitValue("acl_hostexclude");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_resources_hostex_relations (acl_res_id, host_host_id) VALUES ('".$acl_id."', '".$value."')");
				}
			}
	}
	
	function updateHostGroups($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id)
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_resources_hg_relations WHERE acl_res_id = '".$acl_id."'");
		$ret = array();
		$ret = $form->getSubmitValue("acl_hostgroup");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_resources_hg_relations (acl_res_id, hg_hg_id) VALUES ('".$acl_id."', '".$value."')");
				}
			}
	}
	
	function updateServiceCategories($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_resources_sc_relations WHERE acl_res_id = '".$acl_id."'");
		$ret = array();
		$ret = $form->getSubmitValue("acl_sc");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_resources_sc_relations (acl_res_id, sc_id) VALUES ('".$acl_id."', '".$value."')");
				}
			}
	}

	function updateServiceGroups($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_resources_sg_relations WHERE acl_res_id = '".$acl_id."'");
		$ret = array();
		$ret = $form->getSubmitValue("acl_sg");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_resources_sg_relations (acl_res_id, sg_id) VALUES ('".$acl_id."', '".$value."')");
				}
			}
	}
	
	function updateMetaServices($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_resources_meta_relations WHERE acl_res_id = '".$acl_id."'");
		$ret = array();
		$ret = $form->getSubmitValue("acl_meta");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_resources_meta_relations (acl_res_id, meta_id) VALUES ('".$acl_id."', '".$value."')");
				}
			}
	}

?>