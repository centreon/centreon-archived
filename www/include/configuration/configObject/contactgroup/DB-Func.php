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
	
	function testContactGroupExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('cg_id');
		$DBRESULT =& $pearDB->query("SELECT cg_name, cg_id FROM contactgroup WHERE cg_name = '".htmlentities($name, ENT_QUOTES)."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : SELECT cg_name, cg_id FROM contactgroup WHERE cg_name = '".htmlentities($name, ENT_QUOTES)."' : ".$DBRESULT->getMessage()."<br>";
		$cg =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $cg["cg_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $cg["cg_id"] != $id)
			return false;
		else
			return true;
	}

	function enableContactGroupInDB ($cg_id = null)	{
		if (!$cg_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE contactgroup SET cg_activate = '1' WHERE cg_id = '".$cg_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : UPDATE contactgroup SET cg_activate = '1' WHERE cg_id = '".$cg_id."' : ".$DBRESULT->getMessage()."<br>";
	}
	
	function disableContactGroupInDB ($cg_id = null)	{
		if (!$cg_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE contactgroup SET cg_activate = '0' WHERE cg_id = '".$cg_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : UPDATE contactgroup SET cg_activate = '0' WHERE cg_id = '".$cg_id."' : ".$DBRESULT->getMessage()."<br>";
	}
	
	function deleteContactGroupInDB ($contactGroups = array())	{
		global $pearDB;
		foreach($contactGroups as $key=>$value)	{
			$DBRESULT =& $pearDB->query("DELETE FROM contactgroup WHERE cg_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : DELETE FROM contactgroup WHERE cg_id = '".$key."' : ".$DBRESULT->getMessage()."<br>";
		}
	}
	
	function multipleContactGroupInDB ($contactGroups = array(), $nbrDup = array())	{
		foreach($contactGroups as $key=>$value)	{
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT * FROM contactgroup WHERE cg_id = '".$key."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : SELECT * FROM contactgroup WHERE cg_id = '".$key."' LIMIT 1 : ".$DBRESULT->getMessage()."<br>";
			$row = $DBRESULT->fetchRow();
			$row["cg_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "cg_name" ? ($cg_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ", '".$value2."'" : $val .= "'".$value2."'";
				}
				if (testContactGroupExistence($cg_name))	{
					$val ? $rq = "INSERT INTO contactgroup VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : INSERT INTO contactgroup VALUES (".$val.") : ".$DBRESULT->getMessage()."<br>";
					$DBRESULT =& $pearDB->query("SELECT MAX(cg_id) FROM contactgroup");
					if (PEAR::isError($DBRESULT))
						print "DB Error : SELECT MAX(cg_id) FROM contactgroup : ".$DBRESULT->getMessage()."<br>";
					$maxId =& $DBRESULT->fetchRow();
					if (isset($maxId["MAX(cg_id)"]))	{
						$DBRESULT =& $pearDB->query("SELECT DISTINCT cgcr.contact_contact_id FROM contactgroup_contact_relation cgcr WHERE cgcr.contactgroup_cg_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : SELECT DISTINCT cgcr.contact_contact_id FROM contactgroup_contact_relation cgcr.. : ".$DBRESULT->getMessage()."<br>";
						while($DBRESULT->fetchInto($cct))	{
							$DBRESULT2 =& $pearDB->query("INSERT INTO contactgroup_contact_relation VALUES ('', '".$cct["contact_contact_id"]."', '".$maxId["MAX(cg_id)"]."')");
							if (PEAR::isError($DBRESULT2))
								print "DB Error : INSERT INTO contactgroup_contact_relation VALUES.. : ".$DBRESULT2->getMessage()."<br>";
						}
					}
				}
			}
		}
	}	
	
	function insertContactGroupInDB ($ret = array())	{
		$cg_id = insertContactGroup($ret);
		updateContactGroupContacts($cg_id, $ret);
		return $cg_id;
	}
	
	function insertContactGroup($ret)	{
		global $form;
		global $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO contactgroup ";
		$rq .= "(cg_name, cg_alias, cg_comment, cg_activate) ";
		$rq .= "VALUES ";
		$rq .= "('".htmlentities($ret["cg_name"], ENT_QUOTES)."', '".htmlentities($ret["cg_alias"], ENT_QUOTES)."', '".htmlentities($ret["cg_comment"], ENT_QUOTES)."', '".$ret["cg_activate"]["cg_activate"]."')";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : INSERT INTO contactgroup .. : ".$DBRESULT->getMessage()."<br>";
		$DBRESULT =& $pearDB->query("SELECT MAX(cg_id) FROM contactgroup");
		if (PEAR::isError($DBRESULT))
			print "DB Error : SELECT MAX(cg_id) FROM contactgroup : ".$DBRESULT->getMessage()."<br>";
		$cg_id = $DBRESULT->fetchRow();
		return ($cg_id["MAX(cg_id)"]);
	}
	
	function updateContactGroupInDB ($cg_id = NULL)	{
		if (!$cg_id) return;
		updateContactGroup($cg_id);
		updateContactGroupContacts($cg_id);
	}
	
	function updateContactGroup($cg_id = null)	{
		if (!$cg_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE contactgroup ";
		$rq .= "SET cg_name = '".htmlentities($ret["cg_name"], ENT_QUOTES)."', " .
				"cg_alias = '".htmlentities($ret["cg_alias"], ENT_QUOTES)."', " .
				"cg_comment = '".htmlentities($ret["cg_comment"], ENT_QUOTES)."', " .
				"cg_activate = '".$ret["cg_activate"]["cg_activate"]."' " .
				"WHERE cg_id = '".$cg_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : UPDATE contactgroup.. : ".$DBRESULT->getMessage()."<br>";
	}
	
	function updateContactGroupContacts($cg_id, $ret = array())	{
		if (!$cg_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM contactgroup_contact_relation ";
		$rq .= "WHERE contactgroup_cg_id = '".$cg_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : DELETE FROM contactgroup_contact_relation.. : ".$DBRESULT->getMessage()."<br>";
		if (isset($ret["cg_contacts"]))
			$ret = $ret["cg_contacts"];
		else
			$ret = $form->getSubmitValue("cg_contacts");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contactgroup_contact_relation ";
			$rq .= "(contact_contact_id, contactgroup_cg_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$cg_id."')";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : INSERT INTO contactgroup_contact_relation.. : ".$DBRESULT->getMessage()."<br>";
		}
	}
?>