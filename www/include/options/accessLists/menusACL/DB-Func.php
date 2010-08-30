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
		$DBRESULT =& $pearDB->query("SELECT acl_topo_name, acl_topo_id FROM `acl_topology` WHERE acl_topo_name = '".$name."'");
		$lca =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $lca["acl_topo_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $lca["acl_topo_id"] != $id)
			return false;
		else
			return true;
	}
	
	function enableLCAInDB ($acl_id = null)	{
		global $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("UPDATE `acl_topology` SET acl_topo_activate = '1' WHERE `acl_topo_id` = '".$acl_id."'");		
	}
	
	function disableLCAInDB ($acl_id = null)	{
		global $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("UPDATE `acl_topology` SET acl_topo_activate = '0' WHERE `acl_topo_id` = '".$acl_id."'");
	}
	
	function deleteLCAInDB ($acls = array())	{
		global $pearDB;
		foreach($acls as $key=>$value){
			$DBRESULT =& $pearDB->query("DELETE FROM `acl_topology` WHERE acl_topo_id = '".$key."'");
		}
	}
	
	function multipleLCAInDB ($lcas = array(), $nbrDup = array())	{
		global $pearDB;
		foreach($lcas as $key=>$value)	{
			$DBRESULT =& $pearDB->query("SELECT * FROM `acl_topology` WHERE acl_topo_id = '".$key."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			$row["acl_topo_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "acl_topo_name" ? ($acl_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testExistence($acl_name))	{
					$val ? $rq = "INSERT INTO acl_topology VALUES (".$val.")" : $rq = null;
					$pearDB->query($rq);
					$DBRESULT =& $pearDB->query("SELECT MAX(acl_topo_id) FROM acl_topology");
					$maxId =& $DBRESULT->fetchRow();
					$DBRESULT->free();
					if (isset($maxId["MAX(lca_id)"]))	{
						$DBRESULT =& $pearDB->query("SELECT DISTINCT topology_topology_id FROM acl_topology_relations WHERE acl_topo_id = '".$key."'");
						while ($sg =& $DBRESULT->fetchRow())	{
							$DBRESULT2 =& $pearDB->query("INSERT INTO acl_topology_relations VALUES ('', '".$maxId["MAX(acl_topo_id)"]."', '".$sg["topology_topology_id"]."')");
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
		updateLCATopology($acl_id);
		updateGroups($acl_id);
	}	
	
	function insertLCAInDB ()	{
		$acl_id = insertLCA();
		updateLCATopology($acl_id);
		updateGroups($acl_id);
		return ($acl_id);
	}
	
	function insertLCA()	{
		global $form, $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "INSERT INTO `acl_topology` (acl_topo_name, acl_topo_alias, acl_topo_activate) ";
		$rq .= "VALUES ('".htmlentities($ret["acl_topo_name"], ENT_QUOTES, "UTF-8")."', '".htmlentities($ret["acl_topo_alias"], ENT_QUOTES, "UTF-8")."', '".htmlentities($ret["acl_topo_activate"]["acl_topo_activate"], ENT_QUOTES, "UTF-8")."')";
		$DBRESULT =& $pearDB->query($rq);
		$DBRESULT =& $pearDB->query("SELECT MAX(acl_topo_id) FROM `acl_topology`");
		$acl =& $DBRESULT->fetchRow();
		return ($acl["MAX(acl_topo_id)"]);
	}
	
	function updateLCA($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `acl_topology` SET acl_topo_name = '".htmlentities($ret["acl_topo_name"], ENT_QUOTES, "UTF-8")."', acl_topo_alias = '".htmlentities($ret["acl_topo_alias"], ENT_QUOTES, "UTF-8")."', acl_topo_activate = '".htmlentities($ret["acl_topo_activate"]["acl_topo_activate"], ENT_QUOTES, "UTF-8")."' WHERE acl_topo_id = '".$acl_id."'";
		$DBRESULT =& $pearDB->query($rq);	
	}
	
	function updateLCATopology($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_topology_relations WHERE acl_topo_id = '".$acl_id."'");
		$ret = array();
		$ret = $form->getSubmitValue("acl_r_topos");
		if (is_array($ret))
			$ret = array_keys($ret);		
		for ($i = 0; $i < count($ret); $i++)	{
			if (isset($ret[$i]))	{
				$DBRESULT =& $pearDB->query("INSERT INTO acl_topology_relations (acl_topo_id, topology_topology_id) VALUES ('".$acl_id."', '".$ret[$i]."')");		
			}
		}
	}

	function updateGroups($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_group_topology_relations WHERE acl_topology_id = '".$acl_id."'");
		$ret = array();
		$ret = $form->getSubmitValue("acl_groups");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_group_topology_relations (acl_topology_id, acl_group_id) VALUES ('".$acl_id."', '".$value."')");
				}
			}
	}
?>