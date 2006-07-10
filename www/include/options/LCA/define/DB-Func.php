<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	function testExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('lca_id');
		$res =& $pearDB->query("SELECT lca_name, lca_id FROM lca_define WHERE lca_name = '".$name."'");
		$lca =& $res->fetchRow();
		#Modif case
		if ($res->numRows() >= 1 && $lca["lca_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($res->numRows() >= 1 && $lca["lca_id"] != $id)
			return false;
		else
			return true;
	}
	
	function enableLCAInDB ($lca_id = null)	{
		if (!$lca_id) return;
		global $pearDB;
		$pearDB->query("UPDATE lca_define SET lca_activate = '1' WHERE lca_id = '".$lca_id."'");
	}
	
	function disableLCAInDB ($lca_id = null)	{
		if (!$lca_id) return;
		global $pearDB;
		$pearDB->query("UPDATE lca_define SET lca_activate = '0' WHERE lca_id = '".$lca_id."'");
	}
	
	function deleteLCAInDB ($lcas = array())	{
		global $pearDB;
		foreach($lcas as $key=>$value)
			$pearDB->query("DELETE FROM lca_define WHERE lca_id = '".$key."'");
	}
	
	function multipleLCAInDB ($lcas = array(), $nbrDup = array())	{
		foreach($lcas as $key=>$value)	{
			global $pearDB;
			$res =& $pearDB->query("SELECT * FROM lca_define WHERE lca_id = '".$key."' LIMIT 1");
			$row = $res->fetchRow();
			$row["lca_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "lca_name" ? ($lca_name = clone($value2 = $value2."_".$i)) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testExistence($lca_name))	{
					$val ? $rq = "INSERT INTO lca_define VALUES (".$val.")" : $rq = null;
					$pearDB->query($rq);
					$res =& $pearDB->query("SELECT MAX(lca_id) FROM lca_define");
					$maxId =& $res->fetchRow();
					if (isset($maxId["MAX(lca_id)"]))	{
						$res =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM lca_define_contactgroup_relation WHERE lca_define_lca_id = '".$key."'");
						while($res->fetchInto($cg))
							$pearDB->query("INSERT INTO lca_define_contactgroup_relation VALUES ('', '".$maxId["MAX(lca_id)"]."', '".$cg["contactgroup_cg_id"]."')");
						$res =& $pearDB->query("SELECT DISTINCT host_host_id FROM lca_define_host_relation WHERE lca_define_lca_id = '".$key."'");
						while($res->fetchInto($host))
							$pearDB->query("INSERT INTO lca_define_host_relation VALUES ('', '".$maxId["MAX(lca_id)"]."', '".$host["host_host_id"]."')");
						$res =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM lca_define_hostgroup_relation WHERE lca_define_lca_id = '".$key."'");
						while($res->fetchInto($hg))
							$pearDB->query("INSERT INTO lca_define_hostgroup_relation VALUES ('', '".$maxId["MAX(lca_id)"]."', '".$hg["hostgroup_hg_id"]."')");
						$res =& $pearDB->query("SELECT DISTINCT servicegroup_sg_id FROM lca_define_servicegroup_relation WHERE lca_define_lca_id = '".$key."'");
						while($res->fetchInto($sg))
							$pearDB->query("INSERT INTO lca_define_servicegroup_relation VALUES ('', '".$maxId["MAX(lca_id)"]."', '".$sg["servicegroup_sg_id"]."')");
					}
				}
			}
		}
	}
	
	function updateLCAInDB ($lca_id = NULL)	{
		if (!$lca_id) return;
		updateLCA($lca_id);
		updateLCAContactGroups($lca_id);
		updateLCAHosts($lca_id);
		updateLCAHostGroups($lca_id);
		updateLCAServiceGroups($lca_id);
		updateLCATopology($lca_id);
	}	
	
	function insertLCAInDB ()	{
		$lca_id = insertLCA();
		updateLCAContactGroups($lca_id);
		updateLCAHosts($lca_id);
		updateLCAHostGroups($lca_id);
		updateLCAServiceGroups($lca_id);
		updateLCATopology($lca_id);
		return ($lca_id);
	}
	
	function insertLCA()	{
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "INSERT INTO lca_define ";
		$rq .= "(lca_name, lca_comment, lca_hg_childs, lca_activate) ";
		$rq .= "VALUES ";
		$rq .= "('".htmlentities($ret["lca_name"], ENT_QUOTES)."', '".htmlentities($ret["lca_comment"], ENT_QUOTES)."', '".$ret["lca_hg_childs"]["lca_hg_childs"]."', '".$ret["lca_activate"]["lca_activate"]."')";
		$pearDB->query($rq);
		$res =& $pearDB->query("SELECT MAX(lca_id) FROM lca_define");
		$lca_id = $res->fetchRow();
		return ($lca_id["MAX(lca_id)"]);
	}
	
	function updateLCA($lca_id = null)	{
		if (!$lca_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE lca_define ";
		$rq .= "SET lca_name = '".htmlentities($ret["lca_name"], ENT_QUOTES)."', " .
				"lca_comment = '".htmlentities($ret["lca_comment"], ENT_QUOTES)."', " .
				"lca_hg_childs = '".$ret["lca_hg_childs"]["lca_hg_childs"]."', " .
				"lca_activate = '".$ret["lca_activate"]["lca_activate"]."' " .
				"WHERE lca_id = '".$lca_id."'";
		$pearDB->query($rq);
	}
	
	function updateLCAContactGroups($lca_id = null)	{
		if (!$lca_id) return;
		global $form;
		global $pearDB;
		global $oreon;
		$lcas = array();
		$rq = "DELETE FROM lca_define_contactgroup_relation ";
		$rq .= "WHERE lca_define_lca_id = '".$lca_id."'";
		$pearDB->query($rq);
		$ret = array();
		$ret = $form->getSubmitValue("lca_cgs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO lca_define_contactgroup_relation ";
			$rq .= "(lca_define_lca_id, contactgroup_cg_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$lca_id."', '".$ret[$i]."')";
			$pearDB->query($rq);
		}
	}
	
	function updateLCAHosts($lca_id = null)	{
		if (!$lca_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM lca_define_host_relation ";
		$rq .= "WHERE lca_define_lca_id = '".$lca_id."'";
		$pearDB->query($rq);
		$ret = array();
		$ret = $form->getSubmitValue("lca_hosts");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO lca_define_host_relation ";
			$rq .= "(lca_define_lca_id, host_host_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$lca_id."', '".$ret[$i]."')";
			$pearDB->query($rq);
		}
	}
	
	function updateLCAHostGroups($lca_id = null)	{
		if (!$lca_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM lca_define_hostgroup_relation ";
		$rq .= "WHERE lca_define_lca_id = '".$lca_id."'";
		$pearDB->query($rq);
		$ret = array();
		$ret = $form->getSubmitValue("lca_hgs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO lca_define_hostgroup_relation ";
			$rq .= "(lca_define_lca_id, hostgroup_hg_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$lca_id."', '".$ret[$i]."')";
			$pearDB->query($rq);
		}
	}
	
	function updateLCAServiceGroups($lca_id = null)	{
		if (!$lca_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM lca_define_servicegroup_relation ";
		$rq .= "WHERE lca_define_lca_id = '".$lca_id."'";
		$pearDB->query($rq);
		$ret = array();
		$ret = $form->getSubmitValue("lca_sgs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO lca_define_servicegroup_relation ";
			$rq .= "(lca_define_lca_id, servicegroup_sg_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$lca_id."', '".$ret[$i]."')";
			$pearDB->query($rq);
		}
	}
	
	function updateLCATopology($lca_id = null)	{
		if (!$lca_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM lca_define_topology_relation ";
		$rq .= "WHERE lca_define_lca_id = '".$lca_id."'";
		$pearDB->query($rq);
		$ret = array();
		$ret = $form->getSubmitValue("lca_topos");
		$ret = array_keys($ret);		
		for($i = 0; $i < count($ret); $i++)	{
			if (isset($ret[$i]))	{
				$rq = "INSERT INTO lca_define_topology_relation ";
				$rq .= "(lca_define_lca_id, topology_topology_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$lca_id."', '".$ret[$i]."')";
				$pearDB->query($rq);
				//updateLCATopologyParents($ret[$i], $lca_id);
			}
		}
	}
	
	function updateLCATopologyChilds($topology_id = NULL, $lca_id = NULL)	{
		if (!$topology_id || !$lca_id) return;
		global $pearDB;
		$res =& $pearDB->query("SELECT DISTINCT topology_page FROM topology WHERE topology_id = '".$topology_id."'");
		$level1 =& $res->fetchRow();
		$res2 =& $pearDB->query("SELECT topology_id, topology_page FROM topology WHERE topology_parent = '".$level1["topology_page"]."'");
		while($res2->fetchInto($level2))	{
			$rq = "INSERT INTO lca_define_topology_relation ";
			$rq .= "(lca_define_lca_id, topology_topology_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$lca_id."', '".$level2["topology_id"]."')";
			$pearDB->query($rq);
			updateLCATopologyChilds($level2["topology_id"], $lca_id);
		}
	}
?>