<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
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
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
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
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
	
	function disableGroupInDB ($acl_group_id = null)	{
		if (!$acl_group_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE acl_groups SET acl_group_activate = '0' WHERE acl_group_id = '".$acl_group_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
	
	function deleteGroupInDB ($Groups = array())	{
		global $pearDB;
		foreach($Groups as $key=>$value)	{
			$DBRESULT =& $pearDB->query("DELETE FROM acl_groups WHERE acl_group_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}
	
	function multipleGroupInDB ($Groups = array(), $nbrDup = array())	{
		foreach($Groups as $key=>$value)	{
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT * FROM acl_groups WHERE acl_group_id = '".$key."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$row = $DBRESULT->fetchRow();
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
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					$DBRESULT =& $pearDB->query("SELECT MAX(acl_group_id) FROM acl_groups");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					$maxId =& $DBRESULT->fetchRow();
					if (isset($maxId["MAX(cg_id)"]))	{
						$DBRESULT =& $pearDB->query("SELECT DISTINCT acl_group_id FROM acl_group_contacts_relations WHERE acl_group_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
						while($DBRESULT->fetchInto($cct))	{
							$DBRESULT2 =& $pearDB->query("INSERT INTO acl_group_contacts_relations VALUES ('', '".$cct["contact_contact_id"]."', '".$maxId["MAX(acl_group_id)"]."')");
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
						}
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
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$DBRESULT =& $pearDB->query("SELECT MAX(acl_group_id) FROM acl_groups");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$cg_id = $DBRESULT->fetchRow();
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
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
	
	function updateGroupContacts($acl_group_id, $ret = array())	{
		if (!$acl_group_id) return;
		global $form, $pearDB;
		$rq = "DELETE FROM acl_group_contacts_relations WHERE acl_group_id = '".$acl_group_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if (isset($_POST["cg_contacts"]))
			foreach ($_POST["cg_contacts"] as $id){
				$rq = "INSERT INTO acl_group_contacts_relations ";
				$rq .= "(contact_contact_id, acl_group_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$id."', '".$acl_group_id."')";
				$DBRESULT =& $pearDB->query($rq);
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			}
	}
?>