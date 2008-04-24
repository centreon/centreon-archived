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
		$DBRESULT =& $pearDB->query("SELECT acl_topo_name, acl_topo_id FROM `acl_topology` WHERE acl_topo_name = '".$name."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
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
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
	
	function disableLCAInDB ($acl_id = null)	{
		global $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("UPDATE `acl_topology` SET acl_topo_activate = '0' WHERE `acl_topo_id` = '".$acl_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
	
	function deleteLCAInDB ($acls = array())	{
		global $pearDB;
		foreach($acls as $key=>$value){
			$DBRESULT =& $pearDB->query("DELETE FROM `acl_topology` WHERE acl_topo_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}
	
	function multipleLCAInDB ($lcas = array(), $nbrDup = array())	{
		global $pearDB;
		foreach($lcas as $key=>$value)	{
			$DBRESULT =& $pearDB->query("SELECT * FROM `acl_topology` WHERE acl_topo_id = '".$key."' LIMIT 1");
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
					$DBRESULT =& $pearDB->query("SELECT MAX(acl_topo_id) FROM acl_topology");
					$maxId =& $DBRESULT->fetchRow();
					if (isset($maxId["MAX(lca_id)"]))	{
						$DBRESULT =& $pearDB->query("SELECT DISTINCT topology_topology_id FROM acl_topology_relations WHERE acl_topo_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
						while($DBRESULT->fetchInto($sg))	{
							$DBRESULT2 =& $pearDB->query("INSERT INTO acl_topology_relations VALUES ('', '".$maxId["MAX(acl_topo_id)"]."', '".$sg["topology_topology_id"]."')");
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
						}
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
		print_r($ret);
		$rq = "INSERT INTO `acl_topology` (acl_topo_name, acl_topo_alias, acl_topo_activate) ";
		$rq .= "VALUES ('".htmlentities($ret["acl_topo_name"], ENT_QUOTES)."', '".htmlentities($ret["acl_topo_alias"], ENT_QUOTES)."', '".htmlentities($ret["acl_topo_activate"]["acl_topo_activate"], ENT_QUOTES)."')";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$DBRESULT =& $pearDB->query("SELECT MAX(acl_topo_id) FROM `acl_topology`");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$acl = $DBRESULT->fetchRow();
		return ($acl["MAX(acl_topo_id)"]);
	}
	
	function updateLCA($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `acl_topology` SET acl_topo_name = '".htmlentities($ret["acl_topo_name"], ENT_QUOTES)."', acl_topo_alias = '".htmlentities($ret["acl_topo_alias"], ENT_QUOTES)."', acl_topo_activate = '".htmlentities($ret["acl_topo_activate"]["acl_topo_activate"], ENT_QUOTES)."' WHERE acl_topo_id = '".$acl_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
	
	function updateLCATopology($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_topology_relations WHERE acl_topo_id = '".$acl_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$ret = array();
		$ret = $form->getSubmitValue("acl_r_topos");
		if (is_array($ret))
			$ret = array_keys($ret);		
		for ($i = 0; $i < count($ret); $i++)	{
			if (isset($ret[$i]))	{
				$DBRESULT =& $pearDB->query("INSERT INTO acl_topology_relations (acl_topo_id, topology_topology_id) VALUES ('".$acl_id."', '".$ret[$i]."')");
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			}
		}
	}

	function updateGroups($acl_id = null)	{
		global $form, $pearDB;
		if (!$acl_id) 
			return;
		$DBRESULT =& $pearDB->query("DELETE FROM acl_group_topology_relations WHERE acl_topology_id = '".$acl_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$ret = array();
		$ret = $form->getSubmitValue("acl_groups");
		if (isset($ret))
			foreach ($ret as $key => $value){
				if (isset($value))	{
					$DBRESULT =& $pearDB->query("INSERT INTO acl_group_topology_relations (acl_topology_id, acl_group_id) VALUES ('".$acl_id."', '".$value."')");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
				}
			}
	}
?>