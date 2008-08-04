<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	function testExistence ($name = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('lca_id');
		$DBRESULT =& $pearDB->query("SELECT acl_res_name, acl_res_id FROM `acl_resources` WHERE acl_res_name = '".$name."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$lca =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $lca["acl_res_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $lca["acl_res_id"] != $id)
			return false;
		else
			return true;
	}
	
	function enableLCAInDB ($acl_id = null)	{
		if (!$acl_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE `acl_resources` SET acl_res_activate = '1', `changed` = '1' WHERE `acl_res_id` = '".$acl_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
	
	function disableLCAInDB ($acl_id = null)	{
		if (!$acl_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE `acl_resources` SET acl_res_activate = '0', `changed` = '1' WHERE `acl_res_id` = '".$acl_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
	
	function deleteLCAInDB ($acls = array())	{
		global $pearDB;
		foreach($acls as $key=>$value){
			$DBRESULT =& $pearDB->query("DELETE FROM `acl_resources` WHERE acl_res_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
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
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
						while ($sg =& $DBRESULT->fetchRow())	{
							$DBRESULT2 =& $pearDB->query("INSERT INTO acl_res_group_relations VALUES ('', '".$maxId["MAX(acl_res_id)"]."', '".$sg["acl_group_id"]."')");
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
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
	}	
	
	function insertLCAInDB ()	{
		$acl_id = insertLCA();
		updateGroups($acl_id);
		updateHosts($acl_id);
		updateHostGroups($acl_id);
		updateHostexcludes($acl_id);
		updateServiceCategories($acl_id);
		updateServiceGroups($acl_id);
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
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$DBRESULT =& $pearDB->query("SELECT MAX(acl_res_id) FROM `acl_resources`");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
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
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}

	function updateGroups($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_res_group_relations WHERE acl_res_id = '".$acl_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$ret = array();
		$ret = $form->getSubmitValue("acl_groups");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_res_group_relations (acl_res_id, acl_group_id) VALUES ('".$acl_id."', '".$value."')");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
				}
			}
	}
	
	function updateHosts($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_resources_host_relations WHERE acl_res_id = '".$acl_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$ret = array();
		$ret = $form->getSubmitValue("acl_hosts");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_resources_host_relations (acl_res_id, host_host_id) VALUES ('".$acl_id."', '".$value."')");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
				}
			}
	}
	
	function updateHostexcludes($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_resources_hostex_relations WHERE acl_res_id = '".$acl_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$ret = array();
		$ret = $form->getSubmitValue("acl_hostexclude");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_resources_hostex_relations (acl_res_id, host_host_id) VALUES ('".$acl_id."', '".$value."')");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
				}
			}
	}
	
	function updateHostGroups($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id)
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_resources_hg_relations WHERE acl_res_id = '".$acl_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$ret = array();
		$ret = $form->getSubmitValue("acl_hostgroup");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_resources_hg_relations (acl_res_id, hg_hg_id) VALUES ('".$acl_id."', '".$value."')");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
				}
			}
	}
	
	function updateServiceCategories($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_resources_sc_relations WHERE acl_res_id = '".$acl_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$ret = array();
		$ret = $form->getSubmitValue("acl_sc");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_resources_sc_relations (acl_res_id, sc_id) VALUES ('".$acl_id."', '".$value."')");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
				}
			}
	}

	function updateServiceGroups($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_resources_sg_relations WHERE acl_res_id = '".$acl_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$ret = array();
		$ret = $form->getSubmitValue("acl_sg");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_resources_sg_relations (acl_res_id, sg_id) VALUES ('".$acl_id."', '".$value."')");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
				}
			}
	}
	

?>