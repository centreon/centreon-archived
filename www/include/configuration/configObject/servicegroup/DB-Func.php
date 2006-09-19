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
	
	function testServiceGroupExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('sg_id');
		$res =& $pearDB->query("SELECT sg_name, sg_id FROM servicegroup WHERE sg_name = '".htmlentities($name, ENT_QUOTES)."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$sg =& $res->fetchRow();
		#Modif case
		if ($res->numRows() >= 1 && $sg["sg_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($res->numRows() >= 1 && $sg["sg_id"] != $id)
			return false;
		else
			return true;
	}

	function enableServiceGroupInDB ($sg_id = null)	{
		if (!$sg_id) return;
		global $pearDB;
		$pearDB->query("UPDATE servicegroup SET sg_activate = '1' WHERE sg_id = '".$sg_id."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	}
	
	function disableServiceGroupInDB ($sg_id = null)	{
		if (!$sg_id) return;
		global $pearDB;
		$pearDB->query("UPDATE servicegroup SET sg_activate = '0' WHERE sg_id = '".$sg_id."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	}
	
	function deleteServiceGroupInDB ($serviceGroups = array())	{
		global $pearDB;
		foreach($serviceGroups as $key=>$value)
			$pearDB->query("DELETE FROM servicegroup WHERE sg_id = '".$key."'");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
	}
	
	function multipleServiceGroupInDB ($serviceGroups = array(), $nbrDup = array())	{
		global $pearDB;
		global $oreon;
		foreach($serviceGroups as $key=>$value)	{
			$res =& $pearDB->query("SELECT * FROM servicegroup WHERE sg_id = '".$key."' LIMIT 1");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			$row = $res->fetchRow();
			$row["sg_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = NULL;
				$rq = NULL;
				foreach ($row as $key2=>$value2)	{
					$key2 == "sg_name" ? ($sg_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testServiceGroupExistence($sg_name))	{
					$val ? $rq = "INSERT INTO servicegroup VALUES (".$val.")" : $rq = null;
					$pearDB->query($rq);
					if (PEAR::isError($pearDB)) {
						print "Mysql Error : ".$pearDB->getMessage();
					}
					$res =& $pearDB->query("SELECT MAX(sg_id) FROM servicegroup");
					if (PEAR::isError($pearDB)) {
						print "Mysql Error : ".$pearDB->getMessage();
					}
					$maxId =& $res->fetchRow();
					if (isset($maxId["MAX(sg_id)"]))	{
						# Update LCA
						$res1 =& $pearDB->query("SELECT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$oreon->user->get_id()."'");
						while($res1->fetchInto($contactGroup))	{
						 	$res2 =& $pearDB->query("SELECT lca_define_lca_id FROM lca_define_contactgroup_relation ldcgr WHERE ldcgr.contactgroup_cg_id = '".$contactGroup["contactgroup_cg_id"]."'");	
							while ($res2->fetchInto($lca))	{
								$rq = "INSERT INTO lca_define_servicegroup_relation ";
								$rq .= "(lca_define_lca_id, servicegroup_sg_id) ";
								$rq .= "VALUES ";
								$rq .= "('".$lca["lca_define_lca_id"]."', '".$maxId["MAX(sg_id)"]."')";
								$pearDB->query($rq);
								if (PEAR::isError($pearDB)) {
									print "Mysql Error : ".$pearDB->getMessage();
								}
							}
						}
						#
						$res =& $pearDB->query("SELECT DISTINCT sgr.service_service_id FROM servicegroup_relation sgr WHERE sgr.servicegroup_sg_id = '".$key."'");
						if (PEAR::isError($pearDB)) {
							print "Mysql Error : ".$pearDB->getMessage();
						}
						while($res->fetchInto($service))
						{
							$pearDB->query("INSERT INTO servicegroup_relation VALUES ('', '".$service["service_service_id"]."', '".$maxId["MAX(sg_id)"]."')");
							if (PEAR::isError($pearDB)) {
								print "Mysql Error : ".$pearDB->getMessage();
							}
						}
					}
				}
			}
		}
	}
		
	function insertServiceGroupInDB ($ret = array())	{
		$sg_id = insertServiceGroup($ret);
		updateServiceGroupServices($sg_id, $ret);
		return $sg_id;
	}
	
	function updateServiceGroupInDB ($sg_id = NULL)	{
		if (!$sg_id) return;
		updateServiceGroup($sg_id);
		updateServiceGroupServices($sg_id);
	}
		
	function insertServiceGroup($ret = array())	{
		global $form;
		global $pearDB;
		global $oreon;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO servicegroup ";
		$rq .= "(sg_name, sg_alias, country_id, city_id, sg_comment, sg_activate) ";
		$rq .= "VALUES (";
		isset($ret["sg_name"]) && $ret["sg_name"] != NULL ? $rq .= "'".htmlentities($ret["sg_name"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["sg_alias"]) && $ret["sg_alias"] != NULL ? $rq .= "'".htmlentities($ret["sg_alias"], ENT_QUOTES)."', " : $rq .= "NULL, ";
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
		isset($ret["sg_comment"]) && $ret["sg_comment"] != NULL ? $rq .= "'".htmlentities($ret["sg_comment"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["sg_activate"]["sg_activate"]) && $ret["sg_activate"]["sg_activate"] != NULL ? $rq .= "'".$ret["sg_activate"]["sg_activate"]."'" : $rq .= "'0'";
		$rq .= ")";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$res =& $pearDB->query("SELECT MAX(sg_id) FROM servicegroup");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$sg_id = $res->fetchRow();
		# Update LCA
		$oreon->user->lcaServiceGroup[$sg_id["MAX(sg_id)"]] = $ret["sg_name"];
		$oreon->user->lcaSGStr != '\'\''? $oreon->user->lcaSGStr .= ",".$sg_id["MAX(sg_id)"] : $oreon->user->lcaSGStr = $sg_id["MAX(sg_id)"];
		$oreon->user->lcaSGStrName != '\'\''? $oreon->user->lcaSGStrName .= ",".$ret["sg_name"] : $oreon->user->lcaSGStrName = $ret["sg_name"];
		$res1 =& $pearDB->query("SELECT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$oreon->user->get_id()."'");
		while($res1->fetchInto($contactGroup))	{
		 	$res2 =& $pearDB->query("SELECT lca_define_lca_id FROM lca_define_contactgroup_relation ldcgr WHERE ldcgr.contactgroup_cg_id = '".$contactGroup["contactgroup_cg_id"]."'");	
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			while ($res2->fetchInto($lca))	{
				$rq = "INSERT INTO lca_define_servicegroup_relation ";
				$rq .= "(lca_define_lca_id, servicegroup_sg_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$lca["lca_define_lca_id"]."', '".$sg_id["MAX(sg_id)"]."')";
				$pearDB->query($rq);
				if (PEAR::isError($pearDB)) {
					print "Mysql Error : ".$pearDB->getMessage();
				}
			}
		}
		#
		return ($sg_id["MAX(sg_id)"]);
	}
	
	function updateServiceGroup($sg_id)	{
		if (!$sg_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE servicegroup SET ";
		isset($ret["sg_name"]) && $ret["sg_name"] != NULL ? $rq .= "sg_name = '".htmlentities($ret["sg_name"], ENT_QUOTES)."', " : $rq .= "sg_name = NULL,";
		isset($ret["sg_alias"]) && $ret["sg_alias"] != NULL ? $rq.=	"sg_alias = '".htmlentities($ret["sg_alias"], ENT_QUOTES)."', " : $rq .= "sg_alias = NULL";
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
		isset($ret["sg_comment"]) && $ret["sg_comment"] != NULL ? $rq .= "sg_comment = '".htmlentities($ret["sg_comment"], ENT_QUOTES)."', " : $rq .= "sg_comment = NULL,";
		isset($ret["sg_activate"]["sg_activate"]) && $ret["sg_activate"]["sg_activate"] != NULL ? $rq .= "sg_activate = '".$ret["sg_activate"]["sg_activate"]."' " : $rq .= "sg_activate = '0'";
		$rq .= "WHERE sg_id = '".$sg_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	}
	
	function updateServiceGroupServices($sg_id, $ret = array())	{
		if (!$sg_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM servicegroup_relation ";
		$rq .= "WHERE servicegroup_sg_id = '".$sg_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		if (isset($ret["sg_hServices"]))
			$ret = $ret["sg_hServices"];
		else
			$ret = $form->getSubmitValue("sg_hServices");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO servicegroup_relation ";
			$rq .= "(service_service_id, servicegroup_sg_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$sg_id."')";
			$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
		if (isset($ret["sg_hgServices"]))
			$ret = $ret["sg_hgServices"];
		else
			$ret = $form->getSubmitValue("sg_hgServices");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO servicegroup_relation ";
			$rq .= "(service_service_id, servicegroup_sg_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$sg_id."')";
			$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
	}
?>