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
	if (!isset($oreon)) {
		exit ();
	}

	require_once $centreon_path . 'www/class/centreonLDAP.class.php';
 	require_once $centreon_path . 'www/class/centreonContactgroup.class.php';

 	/**
 	 * Set the Acl group changed flag to 1
 	 */
 	function setAclGroupChanged($db, $aclGroupId) {
        $db->query("UPDATE acl_groups SET acl_group_changed = '1' WHERE acl_group_id = " . $db->escape($aclGroupId));
 	}

 	/**
 	 *
 	 * Test if group exists
 	 * @param $name
 	 */
	function testGroupExistence($name = NULL)	{
		global $pearDB, $form;

		$id = NULL;

		if (isset($form)) {
			$id = $form->getSubmitValue('acl_group_id');
		}
		$DBRESULT = $pearDB->query("SELECT acl_group_id, acl_group_name FROM acl_groups WHERE acl_group_name = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
		$cg = $DBRESULT->fetchRow();
		if ($DBRESULT->numRows() >= 1 && $cg["acl_group_id"] == $id) {
			return true;
		} else if ($DBRESULT->numRows() >= 1 && $cg["acl_group_id"] != $id) {
			# Duplicate entry
			return false;
		} else {
			return true;
		}
	}

	/**
	 *
	 * Enable the selected group in DB
	 * @param $acl_group_id
	 */
	function enableGroupInDB($acl_group_id = null)	{
		global $pearDB;

		if (!$acl_group_id) {
			return;
		}
		$DBRESULT = $pearDB->query("UPDATE acl_groups SET acl_group_activate = '1' WHERE acl_group_id = '".$acl_group_id."'");
	}

	/**
	 *
	 * Disable the selected group in DB
	 * @param $acl_group_id
	 */
	function disableGroupInDB($acl_group_id = null)	{
		global $pearDB;

		if (!$acl_group_id) {
			return;
		}
		$DBRESULT = $pearDB->query("UPDATE acl_groups SET acl_group_activate = '0' WHERE acl_group_id = '".$acl_group_id."'");
	}

	/**
	 *
	 * Delete the selected group in DB
	 * @param $Groups
	 */
	function deleteGroupInDB($Groups = array())	{
		global $pearDB;

		foreach ($Groups as $key=>$value)	{
			$DBRESULT = $pearDB->query("DELETE FROM acl_groups WHERE acl_group_id = '".$key."'");
		}
	}

	/**
	 *
	 * Duplicate the selected group
	 * @param $Groups
	 * @param $nbrDup
	 */
	function multipleGroupInDB($Groups = array(), $nbrDup = array()) {
		global $pearDB;

		foreach ($Groups as $key => $value)	{
			$DBRESULT = $pearDB->query("SELECT * FROM acl_groups WHERE acl_group_id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["acl_group_id"] = '';

			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2) {
					$key2 == "acl_group_name" ? ($acl_group_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ", '".$value2."'" : $val .= "'".$value2."'";
				}

				if (testGroupExistence($acl_group_name)) {
					$val ? $rq = "INSERT INTO acl_groups VALUES (".$val.")" : $rq = null;
					$DBRESULT = $pearDB->query($rq);
					$DBRESULT = $pearDB->query("SELECT MAX(acl_group_id) FROM acl_groups");
					$maxId = $DBRESULT->fetchRow();
					$DBRESULT->free();

					/*
					 * Duplicate Links
					 */
					duplicateContacts($key, $maxId["MAX(acl_group_id)"], $pearDB);
					duplicateContactGroups($key, $maxId["MAX(acl_group_id)"], $pearDB);
					duplicateResources($key, $maxId["MAX(acl_group_id)"], $pearDB);
					duplicateActions($key, $maxId["MAX(acl_group_id)"], $pearDB);
					duplicateMenus($key, $maxId["MAX(acl_group_id)"], $pearDB);
				}
			}
		}
	}

	/**
	 *
	 * Insert group in DB
	 * @param $ret
	 */
	function insertGroupInDB($ret = array())	{
		$acl_group_id = insertGroup($ret);
		updateGroupContacts($acl_group_id, $ret);
		updateGroupContactGroups($acl_group_id);
		updateGroupActions($acl_group_id);
		updateGroupResources($acl_group_id);
		updateGroupMenus($acl_group_id);
		return $acl_group_id;
	}

	/**
	 *
	 * Insert Group
	 * @param $ret
	 */
	function insertGroup($ret)	{
		global $form, $pearDB;

		if (!count($ret)) {
			$ret = $form->getSubmitValues();
		}

		$rq = "INSERT INTO acl_groups ";
		$rq .= "(acl_group_name, acl_group_alias, acl_group_activate) ";
		$rq .= "VALUES ";
		$rq .= "('".htmlentities($ret["acl_group_name"], ENT_QUOTES, "UTF-8")."', '".htmlentities($ret["acl_group_alias"], ENT_QUOTES, "UTF-8")."', '".htmlentities($ret["acl_group_activate"]["acl_group_activate"], ENT_QUOTES, "UTF-8")."')";
		$DBRESULT = $pearDB->query($rq);
		$DBRESULT = $pearDB->query("SELECT MAX(acl_group_id) FROM acl_groups");
		$cg_id = $DBRESULT->fetchRow();
		return ($cg_id["MAX(acl_group_id)"]);
	}

	/**
	 *
	 * Update Group in DB
	 * @param $acl_group_id
	 */
	function updateGroupInDB($acl_group_id = NULL)	{
		if (!$acl_group_id) {
			return;
		}

		updateGroup($acl_group_id);
		updateGroupContacts($acl_group_id);
		updateGroupContactGroups($acl_group_id);
		updateGroupActions($acl_group_id);
		updateGroupResources($acl_group_id);
		updateGroupMenus($acl_group_id);
	}

	/**
	 *
	 * Upgrade information of the selected group
	 * @param $acl_group_id
	 */
	function updateGroup($acl_group_id = null)	{
		global $form, $pearDB;

		if (!$acl_group_id) {
			return;
		}

		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE acl_groups ";
		$rq .= "SET acl_group_name = '".htmlentities($ret["acl_group_name"], ENT_QUOTES, "UTF-8")."', " .
				"acl_group_alias = '".htmlentities($ret["acl_group_alias"], ENT_QUOTES, "UTF-8")."', " .
				"acl_group_activate = '".htmlentities($ret["acl_group_activate"]["acl_group_activate"], ENT_QUOTES, "UTF-8")."' " .
				"WHERE acl_group_id = '".$acl_group_id."'";
		$DBRESULT = $pearDB->query($rq);
		setAclGroupChanged($pearDB, $acl_group_id);
	}

	/**
	 *
	 * Update Contacts lists
	 * @param $acl_group_id
	 * @param $ret
	 */
	function updateGroupContacts($acl_group_id, $ret = array())	{
		global $form, $pearDB;

		if (!$acl_group_id)
			return;

		$rq = "DELETE FROM acl_group_contacts_relations WHERE acl_group_id = '".$acl_group_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (isset($_POST["cg_contacts"]))
			foreach ($_POST["cg_contacts"] as $id){
				$rq = "INSERT INTO acl_group_contacts_relations ";
				$rq .= "(contact_contact_id, acl_group_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$id."', '".$acl_group_id."')";
				$DBRESULT = $pearDB->query($rq);
			}
	}

	/**
	 *
	 * Update contact group list
	 * @param $acl_group_id
	 * @param $ret
	 */
	function updateGroupContactGroups($acl_group_id, $ret = array())	{
		global $form, $pearDB;

		if (!$acl_group_id) {
			return;
		}

		$rq = "DELETE FROM acl_group_contactgroups_relations WHERE acl_group_id = '".$acl_group_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (isset($_POST["cg_contactGroups"])) {
		    $cg = new CentreonContactgroup($pearDB);
			foreach ($_POST["cg_contactGroups"] as $id){
			    if (!is_numeric($id)) {
			        $res = $cg->insertLdapGroup($id);
			        if ($res != 0) {
			            $id = $res;
			        } else {
			            continue;
			        }
			    }
				$rq = "INSERT INTO acl_group_contactgroups_relations ";
				$rq .= "(cg_cg_id, acl_group_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$id."', '".$acl_group_id."')";
				$DBRESULT = $pearDB->query($rq);
			}
		}
	}

	/**
	 *
	 * Update Group actions
	 * @param $acl_group_id
	 * @param $ret
	 */
	function updateGroupActions($acl_group_id, $ret = array())	{
		global $form, $pearDB;

		if (!$acl_group_id) {
			return;
		}

		$rq = "DELETE FROM acl_group_actions_relations WHERE acl_group_id = '".$acl_group_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (isset($_POST["actionAccess"]))
			foreach ($_POST["actionAccess"] as $id){
				$rq = "INSERT INTO acl_group_actions_relations ";
				$rq .= "(acl_action_id, acl_group_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$id."', '".$acl_group_id."')";
				$DBRESULT = $pearDB->query($rq);
			}
	}

	/**
	 *
	 * Update Menu Access
	 * @param $acl_group_id
	 * @param $ret
	 */
	function updateGroupMenus($acl_group_id, $ret = array())	{
		global $form, $pearDB;

		if (!$acl_group_id) {
			return;
		}

		$rq = "DELETE FROM acl_group_topology_relations WHERE acl_group_id = '".$acl_group_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (isset($_POST["menuAccess"]))
			foreach ($_POST["menuAccess"] as $id){
				$rq = "INSERT INTO acl_group_topology_relations ";
				$rq .= "(acl_topology_id, acl_group_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$id."', '".$acl_group_id."')";
				$DBRESULT = $pearDB->query($rq);
			}
	}

	/**
	 *
	 * Update Group ressources
	 * @param $acl_group_id
	 * @param $ret
	 */
	function updateGroupResources($acl_group_id, $ret = array())	{
		global $form, $pearDB;

		if (!$acl_group_id) {
			return;
		}

		$DBRESULT = $pearDB->query("DELETE FROM acl_res_group_relations WHERE acl_group_id = '".$acl_group_id."'");
		if (isset($_POST["resourceAccess"])) {
			foreach ($_POST["resourceAccess"] as $id) {
				$rq = "INSERT INTO acl_res_group_relations ";
				$rq .= "(acl_res_id, acl_group_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$id."', '".$acl_group_id."')";
				$DBRESULT = $pearDB->query($rq);
			}
		}
	}

	/**
	 *
	 * Duplicate Contacts lists
	 * @param $acl_group_id
	 * @param $ret
	 */
	function duplicateContacts($idTD, $acl_id, $pearDB) {
		$request = "INSERT INTO acl_group_contacts_relations (contact_contact_id, acl_group_id) SELECT contact_contact_id, '$acl_id' AS acl_group_id FROM acl_group_contacts_relations WHERE acl_group_id = '$idTD'";
		$DBRESULT = $pearDB->query($request);
	}

	/**
	 *
	 * Duplicate Contactgroups lists
	 * @param $acl_group_id
	 * @param $ret
	 */
	function duplicateContactGroups($idTD, $acl_id, $pearDB) {
		$request = "INSERT INTO acl_group_contactgroups_relations (cg_cg_id, acl_group_id) SELECT cg_cg_id, '$acl_id' AS acl_group_id FROM acl_group_contactgroups_relations WHERE acl_group_id = '$idTD'";
		$DBRESULT = $pearDB->query($request);
	}

	/**
	 *
	 * Duplicate Resources lists
	 * @param $acl_group_id
	 * @param $ret
	 */
	function duplicateResources($idTD, $acl_id, $pearDB) {
		$request = "INSERT INTO acl_res_group_relations (acl_res_id, acl_group_id) SELECT acl_res_id, '$acl_id' AS acl_group_id FROM acl_res_group_relations WHERE acl_group_id = '$idTD'";
		$DBRESULT = $pearDB->query($request);
	}

	/**
	 *
	 * Duplicate Actions lists
	 * @param $acl_group_id
	 * @param $ret
	 */
	function duplicateActions($idTD, $acl_id, $pearDB) {
		$request = "INSERT INTO acl_group_actions_relations (acl_action_id, acl_group_id) SELECT acl_action_id, '$acl_id' AS acl_group_id FROM acl_group_actions_relations WHERE acl_group_id = '$idTD'";
		$DBRESULT = $pearDB->query($request);
	}

	/**
	 *
	 * Duplicate Menu lists
	 * @param $acl_group_id
	 * @param $ret
	 */
	function duplicateMenus($idTD, $acl_id, $pearDB) {
		$request = "INSERT INTO acl_group_topology_relations (acl_topology_id, acl_group_id) SELECT acl_topology_id, '$acl_id' AS acl_group_id FROM acl_group_topology_relations WHERE acl_group_id = '$idTD'";
		$DBRESULT = $pearDB->query($request);
	}

?>