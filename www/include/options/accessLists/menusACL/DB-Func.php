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

	function testExistence ($name = null)
	{
		global $pearDB, $form;
		$id = null;
		if (isset($form))
			$id = $form->getSubmitValue('lca_id');
		$DBRESULT = $pearDB->query("SELECT acl_topo_name, acl_topo_id FROM `acl_topology` WHERE acl_topo_name = '".$name."'");
		$lca = $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $lca["acl_topo_id"] == $id)
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $lca["acl_topo_id"] != $id)
			return false;
		else
			return true;
	}

	function enableLCAInDB ($acl_id = null)
	{
		global $pearDB;
		if (!$acl_id)
			return;
		$DBRESULT = $pearDB->query("UPDATE `acl_topology` SET acl_topo_activate = '1' WHERE `acl_topo_id` = '".$acl_id."'");
	}

	function disableLCAInDB ($acl_id = null)
	{
		global $pearDB;
		if (!$acl_id)
			return;
		$DBRESULT = $pearDB->query("UPDATE `acl_topology` SET acl_topo_activate = '0' WHERE `acl_topo_id` = '".$acl_id."'");
	}

	function deleteLCAInDB ($acls = array())
	{
		global $pearDB;
		foreach($acls as $key=>$value){
			$DBRESULT = $pearDB->query("DELETE FROM `acl_topology` WHERE acl_topo_id = '".$key."'");
		}
	}

	function multipleLCAInDB ($lcas = array(), $nbrDup = array())
	{
		global $pearDB;
		foreach($lcas as $key=>$value)	{
			$DBRESULT = $pearDB->query("SELECT * FROM `acl_topology` WHERE acl_topo_id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
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
					$DBRESULT = $pearDB->query("SELECT MAX(acl_topo_id) FROM acl_topology");
					$maxId = $DBRESULT->fetchRow();
					$DBRESULT->free();
				    if (isset($maxId["MAX(acl_topo_id)"]))	{
						$maxTopoId = $maxId['MAX(acl_topo_id)'];
						$pearDB->query("INSERT INTO acl_topology_relations (acl_topo_id, topology_topology_id, access_right)
										(SELECT $maxTopoId, topology_topology_id, access_right FROM acl_topology_relations WHERE acl_topo_id = ".$pearDB->escape($key).")");
                        $pearDB->query("INSERT INTO acl_group_topology_relations (acl_topology_id, acl_group_id)
										(SELECT $maxTopoId, acl_group_id FROM acl_group_topology_relations WHERE acl_topology_id = ".$pearDB->escape($key).")");
					}
				}
			}
		}
	}

	function updateLCAInDB ($acl_id = null)
	{
		if (!$acl_id) return;
		updateLCA($acl_id);
		updateLCATopology($acl_id);
		updateGroups($acl_id);
	}

	function insertLCAInDB ()
	{
		$acl_id = insertLCA();
		updateLCATopology($acl_id);
		updateGroups($acl_id);
		return ($acl_id);
	}

	function insertLCA()
	{
		global $form, $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq  = "INSERT INTO `acl_topology` (acl_topo_name, acl_topo_alias, acl_topo_activate, acl_comments) ";
		$rq .= "VALUES ('".$pearDB->escape($ret["acl_topo_name"])."', '".$pearDB->escape($ret["acl_topo_alias"])."', '".$pearDB->escape($ret["acl_topo_activate"]["acl_topo_activate"])."', '".$pearDB->escape($ret['acl_comments'])."')";
		$DBRESULT = $pearDB->query($rq);
		$DBRESULT = $pearDB->query("SELECT MAX(acl_topo_id) FROM `acl_topology`");
		$acl = $DBRESULT->fetchRow();
		return ($acl["MAX(acl_topo_id)"]);
	}

	function updateLCA($acl_id = null)
	{
		global $form, $pearDB;
		if (!$acl_id)
			return;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `acl_topology`
			   SET acl_topo_name = '".$pearDB->escape($ret["acl_topo_name"])."',
			       acl_topo_alias = '".$pearDB->escape($ret["acl_topo_alias"])."',
			       acl_topo_activate = '".$pearDB->escape($ret["acl_topo_activate"]["acl_topo_activate"])."',
			       acl_comments = '".$pearDB->escape($ret['acl_comments'])."'
			   WHERE acl_topo_id = '".$acl_id."'";
		$DBRESULT = $pearDB->query($rq);
	}

	function updateLCATopology($acl_id = null)
	{
		global $form, $pearDB;
		if (!$acl_id)
			return;
		$DBRESULT = $pearDB->query("DELETE FROM acl_topology_relations WHERE acl_topo_id = '".$acl_id."'");
		$ret = array();
		$ret = $form->getSubmitValue("acl_r_topos");
		foreach ($ret as $key => $value) {
			if (isset($ret) && $key != 0)	{
				$DBRESULT = $pearDB->query("INSERT INTO acl_topology_relations (acl_topo_id, topology_topology_id, access_right) VALUES ('".$acl_id."', '".$key."', " . $value . ")");
			}
		}
	}

	function updateGroups($acl_id = null)
	{
		global $form, $pearDB;
		if (!$acl_id)
			return;
		$DBRESULT = $pearDB->query("DELETE FROM acl_group_topology_relations WHERE acl_topology_id = '".$acl_id."'");
		$ret = array();
		$ret = $form->getSubmitValue("acl_groups");
		if (isset($ret)) {
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT = $pearDB->query("INSERT INTO acl_group_topology_relations (acl_topology_id, acl_group_id) VALUES ('".$acl_id."', '".$value."')");
				}
			}
		}
	}
?>