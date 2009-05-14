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
	
	function testGroupExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('acl_group_id');
		$DBRESULT =& $pearDB->query("SELECT acl_group_id, acl_group_name FROM acl_groups WHERE acl_group_name = '".htmlentities($name, ENT_QUOTES)."'");
		$cg =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $cg["acl_group_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $cg["acl_group_id"] != $id)
			return false;
		else
			return true;
	}

	function enableGroupInDB ($acl_group_id = null)	{
		if (!$acl_group_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE acl_groups SET acl_group_activate = '1' WHERE acl_group_id = '".$acl_group_id."'");		
	}
	
	function disableGroupInDB ($acl_group_id = null)	{
		if (!$acl_group_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE acl_groups SET acl_group_activate = '0' WHERE acl_group_id = '".$acl_group_id."'");		
	}
	
	function deleteGroupInDB ($Groups = array())	{
		global $pearDB;
		foreach($Groups as $key=>$value)	{
			$DBRESULT =& $pearDB->query("DELETE FROM acl_groups WHERE acl_group_id = '".$key."'");			
		}
	}
	
	function multipleGroupInDB ($Groups = array(), $nbrDup = array())	{
		foreach($Groups as $key=>$value)	{
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT * FROM acl_groups WHERE acl_group_id = '".$key."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			$row["acl_group_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "acl_group_name" ? ($acl_group_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ", '".$value2."'" : $val .= "'".$value2."'";
				}
				if (testGroupExistence($acl_group_name))	{
					$val ? $rq = "INSERT INTO acl_groups VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
					$DBRESULT =& $pearDB->query("SELECT MAX(acl_group_id) FROM acl_groups");
					$maxId =& $DBRESULT->fetchRow();
					$DBRESULT->free();
					if (isset($maxId["MAX(cg_id)"]))	{
						$DBRESULT =& $pearDB->query("SELECT DISTINCT acl_group_id FROM acl_group_contacts_relations WHERE acl_group_id = '".$key."'");
						while ($cct =& $DBRESULT->fetchRow())	{
							$DBRESULT2 =& $pearDB->query("INSERT INTO acl_group_contacts_relations VALUES ('', '".$cct["contact_contact_id"]."', '".$maxId["MAX(acl_group_id)"]."')");
						}
						$DBRESULT->free();
					}
				}
			}
		}
	}	
	
	function insertGroupInDB ($ret = array())	{
		$acl_group_id = insertGroup($ret);
		updateGroupContacts($acl_group_id, $ret);
		return $acl_group_id;
	}
	
	function insertGroup($ret)	{
		global $form, $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO acl_groups ";
		$rq .= "(acl_group_name, acl_group_alias, acl_group_activate) ";
		$rq .= "VALUES ";
		$rq .= "('".htmlentities($ret["acl_group_name"], ENT_QUOTES)."', '".htmlentities($ret["acl_group_alias"], ENT_QUOTES)."', '".htmlentities($ret["acl_group_activate"]["acl_group_activate"], ENT_QUOTES)."')";
		$DBRESULT =& $pearDB->query($rq);
		$DBRESULT =& $pearDB->query("SELECT MAX(acl_group_id) FROM acl_groups");
		$cg_id =& $DBRESULT->fetchRow();
		return ($cg_id["MAX(acl_group_id)"]);
	}
	
	function updateGroupInDB ($acl_group_id = NULL)	{
		if (!$acl_group_id) return;
		updateGroup($acl_group_id);
		updateGroupContacts($acl_group_id);
	}
	
	function updateGroup($acl_group_id = null)	{
		if (!$acl_group_id) return;
		global $form, $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE acl_groups ";
		$rq .= "SET acl_group_name = '".htmlentities($ret["acl_group_name"], ENT_QUOTES)."', " .
				"acl_group_alias = '".htmlentities($ret["acl_group_alias"], ENT_QUOTES)."', " .
				"acl_group_activate = '".htmlentities($ret["acl_group_activate"]["acl_group_activate"], ENT_QUOTES)."' " .
				"WHERE acl_group_id = '".$acl_group_id."'";
		$DBRESULT =& $pearDB->query($rq);		
	}
	
	function updateGroupContacts($acl_group_id, $ret = array())	{
		if (!$acl_group_id) return;
		global $form, $pearDB;
		$rq = "DELETE FROM acl_group_contacts_relations WHERE acl_group_id = '".$acl_group_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (isset($_POST["cg_contacts"]))
			foreach ($_POST["cg_contacts"] as $id){
				$rq = "INSERT INTO acl_group_contacts_relations ";
				$rq .= "(contact_contact_id, acl_group_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$id."', '".$acl_group_id."')";
				$DBRESULT =& $pearDB->query($rq);
			}
	}
?>