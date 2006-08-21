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

	if (!isset ($oreon))
		exit ();

	function testHostGroupExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('hg_id');
		$res =& $pearDB->query("SELECT hg_name, hg_id FROM hostgroup WHERE hg_name = '".htmlentities($name, ENT_QUOTES)."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$hg =& $res->fetchRow();
		#Modif case
		if ($res->numRows() >= 1 && $hg["hg_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($res->numRows() >= 1 && $hg["hg_id"] != $id)
			return false;
		else
			return true;
	}

	function enableHostGroupInDB ($hg_id = null)	{
		if (!$hg_id) return;
		global $pearDB;
		$pearDB->query("UPDATE hostgroup SET hg_activate = '1' WHERE hg_id = '".$hg_id."'");
	}
	
	function disableHostGroupInDB ($hg_id = null)	{
		if (!$hg_id) return;
		global $pearDB;
		$pearDB->query("UPDATE hostgroup SET hg_activate = '0' WHERE hg_id = '".$hg_id."'");
	}
	
	function deleteHostGroupInDB ($hostGroups = array())	{
		global $pearDB;
		foreach($hostGroups as $key=>$value)	{
			$rq = "SELECT @nbr := (SELECT COUNT( * ) FROM host_service_relation WHERE service_service_id = hsr.service_service_id GROUP BY service_service_id ) AS nbr, hsr.service_service_id FROM host_service_relation hsr WHERE hsr.hostgroup_hg_id = '".$key."'";
			$res = & $pearDB->query($rq);
			while ($res->fetchInto($row))
				if ($row["nbr"] == 1)
					$pearDB->query("DELETE FROM service WHERE service_id = '".$row["service_service_id"]."'");
			$pearDB->query("DELETE FROM hostgroup WHERE hg_id = '".$key."'");
		}
	}
	
	function multipleHostGroupInDB ($hostGroups = array(), $nbrDup = array())	{
		global $pearDB;
		global $oreon;
		foreach($hostGroups as $key=>$value)	{
			$res =& $pearDB->query("SELECT * FROM hostgroup WHERE hg_id = '".$key."' LIMIT 1");
			$row = $res->fetchRow();
			$row["hg_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = NULL;
				$rq = NULL;
				foreach ($row as $key2=>$value2)	{
					$key2 == "hg_name" ? ($hg_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testHostGroupExistence($hg_name))	{
					$val ? $rq = "INSERT INTO hostgroup VALUES (".$val.")" : $rq = null;
					$pearDB->query($rq);
					if (PEAR::isError($pearDB)) {
						print "Mysql Error : ".$pearDB->getMessage();
					}
					$res =& $pearDB->query("SELECT MAX(hg_id) FROM hostgroup");
					if (PEAR::isError($pearDB)) {
						print "Mysql Error : ".$pearDB->getMessage();
					}
					$maxId =& $res->fetchRow();
					if (isset($maxId["MAX(hg_id)"]))	{
						# Update LCA
						$oreon->user->lcaHostGroup[$maxId["MAX(hg_id)"]] = $hg_name;
						$oreon->user->lcaHGStr != '\'\''? $oreon->user->lcaHGStr .= ",".$maxId["MAX(hg_id)"] : $oreon->user->lcaHGStr = $maxId["MAX(hg_id)"];
						$oreon->user->lcaHGStrName != '\'\''? $oreon->user->lcaHGStrName .= ",".$hg_name : $oreon->user->lcaHGStrName = $hg_name;
						$res1 =& $pearDB->query("SELECT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$oreon->user->get_id()."'");
						while($res1->fetchInto($contactGroup))	{
						 	$res2 =& $pearDB->query("SELECT lca_define_lca_id FROM lca_define_contactgroup_relation ldcgr WHERE ldcgr.contactgroup_cg_id = '".$contactGroup["contactgroup_cg_id"]."'");	
							while ($res2->fetchInto($lca))	{
								$rq = "INSERT INTO lca_define_hostgroup_relation ";
								$rq .= "(lca_define_lca_id, hostgroup_hg_id) ";
								$rq .= "VALUES ";
								$rq .= "('".$lca["lca_define_lca_id"]."', '".$maxId["MAX(hg_id)"]."')";
								$pearDB->query($rq);
								if (PEAR::isError($pearDB)) {
									print "Mysql Error : ".$pearDB->getMessage();
								}
							}
						}
						#
						$res =& $pearDB->query("SELECT DISTINCT hgr.host_host_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$key."'");
						if (PEAR::isError($pearDB)) {
							print "Mysql Error : ".$pearDB->getMessage();
						}
						while($res->fetchInto($host))
						{
							$pearDB->query("INSERT INTO hostgroup_relation VALUES ('', '".$maxId["MAX(hg_id)"]."', '".$host["host_host_id"]."')");
							if (PEAR::isError($pearDB)) {
								print "Mysql Error : ".$pearDB->getMessage();
							}
						}
						$res =& $pearDB->query("SELECT DISTINCT cghgr.contactgroup_cg_id FROM contactgroup_hostgroup_relation cghgr WHERE cghgr.hostgroup_hg_id = '".$key."'");
						if (PEAR::isError($pearDB)) {
							print "Mysql Error : ".$pearDB->getMessage();
						}
						while($res->fetchInto($cg))
						{
							$pearDB->query("INSERT INTO contactgroup_hostgroup_relation VALUES ('', '".$cg["contactgroup_cg_id"]."', '".$maxId["MAX(hg_id)"]."')");
							if (PEAR::isError($pearDB)) {
								print "Mysql Error : ".$pearDB->getMessage();
							}
						}
					}
				}
			}
		}
	}
		
	function insertHostGroupInDB ($ret = array())	{
		$hg_id = insertHostGroup($ret);
		updateHostGroupHosts($hg_id, $ret);
		updateHostGroupContactGroups($hg_id, $ret);
		return $hg_id;
	}
	
	function updateHostGroupInDB ($hg_id = NULL)	{
		if (!$hg_id) return;
		updateHostGroup($hg_id);
		updateHostGroupHosts($hg_id);
		updateHostGroupContactGroups($hg_id);
	}
		
	function insertHostGroup($ret = array())	{
		global $form;
		global $pearDB;
		global $oreon;
		if (!count($ret))
		$ret = $form->getSubmitValues();
		$rq = "INSERT INTO hostgroup ";
		$rq .= "(hg_name, hg_alias, `country_id`, `city_id`, hg_comment, hg_activate) ";
		$rq .= "VALUES (";
		isset($ret["hg_name"]) && $ret["hg_name"] ? $rq .= "'".htmlentities($ret["hg_name"], ENT_QUOTES)."', " : $rq .= "NULL,";
		isset($ret["hg_alias"]) && $ret["hg_alias"] ? $rq .= "'".htmlentities($ret["hg_alias"], ENT_QUOTES)."', " : $rq .= "NULL,";
		isset($ret["country_id"]) && $ret["country_id"] != NULL ? $rq .= "'".$ret["country_id"]."', ": $rq .= "NULL, ";
		if (isset($ret["city_name"]) && $ret["city_name"])	{
			$res =& $pearDB->query("SELECT DISTINCT city_id FROM view_city WHERE city_name = '".$ret["city_name"]."' AND country_id = '".$ret["country_id"]."'");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			$city = $res->fetchRow();
			isset($city["city_id"]) ? $rq .= "'".$city["city_id"]."', ": $rq .= "NULL, ";
		}	
		else
			$rq .= "NULL, ";
		isset($ret["hg_comment"]) && $ret["hg_comment"] ? $rq .= "'".htmlentities($ret["hg_comment"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["hg_activate"]["hg_activate"]) && $ret["hg_activate"]["hg_activate"] ? $rq .= "'".$ret["hg_activate"]["hg_activate"]."'" : $rq .= "'0'";
		$rq .= ")";
		$pearDB->query($rq);
		$res =& $pearDB->query("SELECT MAX(hg_id) FROM hostgroup");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$hg_id = $res->fetchRow();
		# Update LCA
		$oreon->user->lcaHostGroup[$hg_id["MAX(hg_id)"]] = $ret["hg_name"];
		$oreon->user->lcaHGStr != '\'\''? $oreon->user->lcaHGStr .= ",".$hg_id["MAX(hg_id)"] : $oreon->user->lcaHGStr = $hg_id["MAX(hg_id)"];
		$oreon->user->lcaHGStrName != '\'\''? $oreon->user->lcaHGStrName .= ",".$ret["hg_name"] : $oreon->user->lcaHGStrName = $ret["hg_name"];
		$res1 =& $pearDB->query("SELECT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$oreon->user->get_id()."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		while($res1->fetchInto($contactGroup))	{
		 	$res2 =& $pearDB->query("SELECT lca_define_lca_id FROM lca_define_contactgroup_relation ldcgr WHERE ldcgr.contactgroup_cg_id = '".$contactGroup["contactgroup_cg_id"]."'");	
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			while ($res2->fetchInto($lca))	{
				$rq = "INSERT INTO lca_define_hostgroup_relation ";
				$rq .= "(lca_define_lca_id, hostgroup_hg_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$lca["lca_define_lca_id"]."', '".$hg_id["MAX(hg_id)"]."')";
				$pearDB->query($rq);
				if (PEAR::isError($pearDB)) {
					print "Mysql Error : ".$pearDB->getMessage();
				}
			}
		}
		#
		return ($hg_id["MAX(hg_id)"]);
	}
	
	function updateHostGroup($hg_id)	{
		if (!$hg_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE hostgroup SET ";
		isset($ret["hg_name"]) && $ret["hg_name"] ? $rq .= "hg_name = '".htmlentities($ret["hg_name"], ENT_QUOTES)."', " : "hg_name = NULL,";
		isset($ret["hg_alias"]) && $ret["hg_alias"] ? $rq .= "hg_alias = '".htmlentities($ret["hg_alias"], ENT_QUOTES)."', " : "hg_alias = NULL,";
		isset($ret["country_id"]) && $ret["country_id"] != NULL ? $rq .= "country_id = '".$ret["country_id"]."', ": $rq .= "country_id = NULL, ";
		$rq .= "city_id = ";
		if (isset($ret["city_name"]) && $ret["city_name"])	{
			$res =& $pearDB->query("SELECT DISTINCT city_id FROM view_city WHERE city_name = '".$ret["city_name"]."' AND country_id = '".$ret["country_id"]."'");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			$city = $res->fetchRow();
			isset($city["city_id"]) ? $rq .= "'".$city["city_id"]."', ": $rq .= "NULL, ";
		}	
		else
			$rq .= "NULL, ";
		isset($ret["hg_comment"]) && $ret["hg_comment"] ? $rq .= "hg_comment = '".htmlentities($ret["hg_comment"], ENT_QUOTES)."', " : $rq .= "hg_comment = NULL, ";
		isset($ret["hg_activate"]["hg_activate"]) && $ret["hg_activate"]["hg_activate"] ? $rq .= "hg_activate = '".$ret["hg_activate"]["hg_activate"]."' " : $rq .= "hg_activate = '0' ";
		$rq .= "WHERE hg_id = '".$hg_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	}
	
	function updateHostGroupHosts($hg_id, $ret = array())	{
		if (!$hg_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM hostgroup_relation ";
		$rq .= "WHERE hostgroup_hg_id = '".$hg_id."'";
		$pearDB->query($rq);
		if (isset($ret["hg_hosts"]))
			$ret = $ret["hg_hosts"];
		else
			$ret = $form->getSubmitValue("hg_hosts");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO hostgroup_relation ";
			$rq .= "(hostgroup_hg_id, host_host_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$hg_id."', '".$ret[$i]."')";
			$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
	}
	
	function updateHostGroupContactGroups($hg_id, $ret = array())	{
		if (!$hg_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM contactgroup_hostgroup_relation ";
		$rq .= "WHERE hostgroup_hg_id = '".$hg_id."'";
		$pearDB->query($rq);
		if (isset($ret["hg_cgs"]))
			$ret = $ret["hg_cgs"];
		else
			$ret = $form->getSubmitValue("hg_cgs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contactgroup_hostgroup_relation ";
			$rq .= "(contactgroup_cg_id, hostgroup_hg_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$hg_id."')";
			$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
	}
?>