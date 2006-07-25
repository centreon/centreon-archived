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
	

	function testServiceDependencyExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('dep_id');
		$res =& $pearDB->query("SELECT dep_name, dep_id FROM dependency WHERE dep_name = '".htmlentities($name, ENT_QUOTES)."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$dep =& $res->fetchRow();
		#Modif case
		if ($res->numRows() >= 1 && $dep["dep_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($res->numRows() >= 1 && $dep["dep_id"] != $id)
			return false;
		else
			return true;
	}
	
	function testCycleH ($childs = NULL)	{
		global $pearDB;
		global $form;
		$parents = array();
		$childs = array();
		if (isset($form))	{
			$parents = $form->getSubmitValue('dep_hSvPar');
			$childs = $form->getSubmitValue('dep_hSvChi');
			$childs =& array_flip($childs);
		}
		foreach ($parents as $parent)
			if (array_key_exists($parent, $childs))
				return false;
		return true;
	}
	
	function testCycleHg ($childs = NULL)	{
		global $pearDB;
		global $form;
		$parents = array();
		$childs = array();
		if (isset($form))	{
			$parents = $form->getSubmitValue('dep_hgSvPar');
			$childs = $form->getSubmitValue('dep_hgSvChi');
			$childs =& array_flip($childs);
		}
		foreach ($parents as $parent)
			if (array_key_exists($parent, $childs))
				return false;
		return true;
	}

	function deleteServiceDependencyInDB ($dependencies = array())	{
		global $pearDB;
		foreach($dependencies as $key=>$value)
		{
			$pearDB->query("DELETE FROM dependency WHERE dep_id = '".$key."'");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
	}
	
	function multipleServiceDependencyInDB ($dependencies = array(), $nbrDup = array())	{
		foreach($dependencies as $key=>$value)	{
			global $pearDB;
			$res =& $pearDB->query("SELECT * FROM dependency WHERE dep_id = '".$key."' LIMIT 1");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			$row = $res->fetchRow();
			$row["dep_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "dep_name" ? ($dep_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testServiceDependencyExistence($dep_name))	{
					$val ? $rq = "INSERT INTO dependency VALUES (".$val.")" : $rq = null;
					$pearDB->query($rq);
					$res =& $pearDB->query("SELECT MAX(dep_id) FROM dependency");
					if (PEAR::isError($pearDB)) {
						print "Mysql Error : ".$pearDB->getMessage();
					}
					$maxId =& $res->fetchRow();
					if (isset($maxId["MAX(dep_id)"]))	{
						$res =& $pearDB->query("SELECT DISTINCT service_service_id FROM dependency_serviceParent_relation WHERE dependency_dep_id = '".$key."'");
						if (PEAR::isError($pearDB)) {
							print "Mysql Error : ".$pearDB->getMessage();
						}
						while($res->fetchInto($service))
						{
							$pearDB->query("INSERT INTO dependency_serviceParent_relation VALUES ('', '".$maxId["MAX(dep_id)"]."', '".$service["service_service_id"]."')");
							if (PEAR::isError($pearDB)) {
								print "Mysql Error : ".$pearDB->getMessage();
							}
						}
						$res->free();
						$res =& $pearDB->query("SELECT DISTINCT service_service_id FROM dependency_serviceChild_relation WHERE dependency_dep_id = '".$key."'");
						if (PEAR::isError($pearDB)) {
							print "Mysql Error : ".$pearDB->getMessage();
						}
						while($res->fetchInto($service))
						{
							$pearDB->query("INSERT INTO dependency_serviceChild_relation VALUES ('', '".$maxId["MAX(dep_id)"]."', '".$service["service_service_id"]."')");
							if (PEAR::isError($pearDB)) {
								print "Mysql Error : ".$pearDB->getMessage();
							}
						}
						$res->free();
					}
				}
			}
		}
	}
	
	function updateServiceDependencyInDB ($dep_id = NULL)	{
		if (!$dep_id) exit();
		updateServiceDependency($dep_id);
		updateServiceDependencyServiceParents($dep_id);
		updateServiceDependencyServiceChilds($dep_id);
	}	
	
	function insertServiceDependencyInDB ($ret = array())	{
		$dep_id = insertServiceDependency($ret);
		updateServiceDependencyServiceParents($dep_id, $ret);
		updateServiceDependencyServiceChilds($dep_id, $ret);
		return ($dep_id);
	}
	
	function insertServiceDependency($ret = array())	{
		global $form;
		global $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO dependency ";
		$rq .= "(dep_name, dep_description, inherits_parent, execution_failure_criteria, notification_failure_criteria, dep_comment) ";
		$rq .= "VALUES (";
		isset($ret["dep_name"]) && $ret["dep_name"] != NULL ? $rq .= "'".htmlentities($ret["dep_name"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["dep_description"]) && $ret["dep_description"] != NULL ? $rq .= "'".htmlentities($ret["dep_description"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["inherits_parent"]["inherits_parent"]) && $ret["inherits_parent"]["inherits_parent"] != NULL ? $rq .= "'".$ret["inherits_parent"]["inherits_parent"]."', " : $rq .= "NULL, ";
		isset($ret["execution_failure_criteria"]) && $ret["execution_failure_criteria"] != NULL ? $rq .= "'".implode(",", array_keys($ret["execution_failure_criteria"]))."', " : $rq .= "NULL, ";
		isset($ret["notification_failure_criteria"]) && $ret["notification_failure_criteria"] != NULL ? $rq .= "'".implode(",", array_keys($ret["notification_failure_criteria"]))."', " : $rq .= "NULL, ";
		isset($ret["dep_comment"]) && $ret["dep_comment"] != NULL ? $rq .= "'".htmlentities($ret["dep_comment"], ENT_QUOTES)."' " : $rq .= "NULL ";
		$rq .= ")";
		$pearDB->query($rq);
		$res =& $pearDB->query("SELECT MAX(dep_id) FROM dependency");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$dep_id = $res->fetchRow();
		return ($dep_id["MAX(dep_id)"]);
	}
	
	function updateServiceDependency($dep_id = null)	{
		if (!$dep_id) exit();
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE dependency SET ";
		$rq .= "dep_name = ";
		isset($ret["dep_name"]) && $ret["dep_name"] != NULL ? $rq .= "'".htmlentities($ret["dep_name"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "dep_description = ";
		isset($ret["dep_description"]) && $ret["dep_description"] != NULL ? $rq .= "'".htmlentities($ret["dep_description"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "inherits_parent = ";
		isset($ret["inherits_parent"]["inherits_parent"]) && $ret["inherits_parent"]["inherits_parent"] != NULL ? $rq .= "'".$ret["inherits_parent"]["inherits_parent"]."', " : $rq .= "NULL, ";
		$rq .= "execution_failure_criteria = ";
		isset($ret["execution_failure_criteria"]) && $ret["execution_failure_criteria"] != NULL ? $rq .= "'".implode(",", array_keys($ret["execution_failure_criteria"]))."', " : $rq .= "NULL, ";
		$rq .= "notification_failure_criteria = ";
		isset($ret["notification_failure_criteria"]) && $ret["notification_failure_criteria"] != NULL ? $rq .= "'".implode(",", array_keys($ret["notification_failure_criteria"]))."', " : $rq .= "NULL, ";
		$rq .= "dep_comment = ";
		isset($ret["dep_comment"]) && $ret["dep_comment"] != NULL ? $rq .= "'".htmlentities($ret["dep_comment"], ENT_QUOTES)."' " : $rq .= "NULL ";
		$rq .= "WHERE dep_id = '".$dep_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	}
		
	function updateServiceDependencyServiceParents($dep_id = null, $ret = array())	{
		if (!$dep_id) exit();
		global $form;
		global $pearDB;
		$rq = "DELETE FROM dependency_serviceParent_relation ";
		$rq .= "WHERE dependency_dep_id = '".$dep_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		if (isset($ret["dep_hSvPar"]))
			$ret1 = $ret["dep_hSvPar"]; 
		else
			$ret1 = $form->getSubmitValue("dep_hSvPar");
		for($i = 0; $i < count($ret1); $i++)	{
			$rq = "INSERT INTO dependency_serviceParent_relation ";
			$rq .= "(dependency_dep_id, service_service_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$dep_id."', '".$ret1[$i]."')";
			$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
		if (isset($ret["dep_hgSvPar"]))
			$ret2 = $ret["dep_hgSvPar"]; 
		else
			$ret2 = $form->getSubmitValue("dep_hgSvPar");
		for($i = 0; $i < count($ret2); $i++)	{
			$rq = "INSERT INTO dependency_serviceParent_relation ";
			$rq .= "(dependency_dep_id, service_service_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$dep_id."', '".$ret2[$i]."')";
			$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
	}
		
	function updateServiceDependencyServiceChilds($dep_id = null, $ret = array())	{
		if (!$dep_id) exit();
		global $form;
		global $pearDB;
		$rq = "DELETE FROM dependency_serviceChild_relation ";
		$rq .= "WHERE dependency_dep_id = '".$dep_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		if (isset($ret["dep_hSvChi"]))
			$ret1 = $ret["dep_hSvChi"];
		else
			$ret1 = $form->getSubmitValue("dep_hSvChi");
		for($i = 0; $i < count($ret1); $i++)	{
			$rq = "INSERT INTO dependency_serviceChild_relation ";
			$rq .= "(dependency_dep_id, service_service_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$dep_id."', '".$ret1[$i]."')";
			$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
		if (isset($ret["dep_hgSvChi"]))
			$ret2 = $ret["dep_hgSvChi"];
		else
			$ret2 = $form->getSubmitValue("dep_hgSvChi");
		for($i = 0; $i < count($ret2); $i++)	{
			$rq = "INSERT INTO dependency_serviceChild_relation ";
			$rq .= "(dependency_dep_id, service_service_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$dep_id."', '".$ret2[$i]."')";
			$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
	}
?>