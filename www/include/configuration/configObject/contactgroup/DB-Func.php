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
 
	if (!isset ($oreon))
		exit ();
	
	function testContactGroupExistence ($name = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		
		if (isset($form))
			$id = $form->getSubmitValue('cg_id');
		
		$DBRESULT =& $pearDB->query("SELECT `cg_name`, `cg_id` FROM `contactgroup` WHERE `cg_name` = '".htmlentities($name, ENT_QUOTES)."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$cg =& $DBRESULT->fetchRow();
		
		if ($DBRESULT->numRows() >= 1 && $cg["cg_id"] == $id){
			/*
			 * Modif case
			 */
			return true;
		} else if ($DBRESULT->numRows() >= 1 && $cg["cg_id"] != $id){
			/*
			 * Duplicate entry
			 */
			return false;
		} else {
			return true;
		}	
	}

	function enableContactGroupInDB ($cg_id = null)	{
		global $pearDB;
		if (!$cg_id) 
			return;
		$DBRESULT =& $pearDB->query("UPDATE `contactgroup` SET `cg_activate` = '1' WHERE `cg_id` = '".$cg_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
	
	function disableContactGroupInDB ($cg_id = null)	{
		global $pearDB;
		if (!$cg_id) 
			return;
		$DBRESULT =& $pearDB->query("UPDATE `contactgroup` SET `cg_activate` = '0' WHERE `cg_id` = '".$cg_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
	
	function deleteContactGroupInDB ($contactGroups = array())	{
		global $pearDB;
		
		foreach($contactGroups as $key => $value)	{
			$DBRESULT =& $pearDB->query("DELETE FROM `contactgroup` WHERE `cg_id` = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}
	
	function multipleContactGroupInDB ($contactGroups = array(), $nbrDup = array())	{
		global $pearDB;
		
		foreach ($contactGroups as $key=>$value)	{

			$DBRESULT =& $pearDB->query("SELECT * FROM `contactgroup` WHERE `cg_id` = '".$key."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		
			$row =& $DBRESULT->fetchRow();
			$row["cg_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2) {
					$key2 == "`cg_name`" ? ($cg_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ", '".$value2."'" : $val .= "'".$value2."'";
				}
				if (testContactGroupExistence($cg_name))	{
					$val ? $rq = "INSERT INTO `contactgroup` VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					
					$DBRESULT =& $pearDB->query("SELECT MAX(cg_id) FROM `contactgroup`");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					$maxId =& $DBRESULT->fetchRow();
					
					if (isset($maxId["MAX(cg_id)"])) {
						$DBRESULT =& $pearDB->query("SELECT DISTINCT `cgcr`.`contact_contact_id` FROM `contactgroup_contact_relation` `cgcr` WHERE `cgcr`.`contactgroup_cg_id` = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
						while($cct =& $DBRESULT->fetchRow())	{
				
							$DBRESULT2 =& $pearDB->query("INSERT INTO `contactgroup_contact_relation` VALUES ('', '".$cct["contact_contact_id"]."', '".$maxId["MAX(cg_id)"]."')");
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
				
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
		global $form, $pearDB;
		
		if (!count($ret))
			$ret = $form->getSubmitValues();
		
		$rq = "INSERT INTO `contactgroup` (`cg_name`, `cg_alias`, `cg_comment`, `cg_activate`) ";
		$rq .= "VALUES ('".htmlentities($ret["cg_name"], ENT_QUOTES)."', '".htmlentities($ret["cg_alias"], ENT_QUOTES)."', '".htmlentities($ret["cg_comment"], ENT_QUOTES)."', '".$ret["cg_activate"]["cg_activate"]."')";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		
		$DBRESULT =& $pearDB->query("SELECT MAX(cg_id) FROM `contactgroup`");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$cg_id = $DBRESULT->fetchRow();
		return ($cg_id["MAX(cg_id)"]);
	}
	
	function updateContactGroupInDB ($cg_id = NULL)	{
		if (!$cg_id) 
			return;
		updateContactGroup($cg_id);
		updateContactGroupContacts($cg_id);
	}
	
	function updateContactGroup($cg_id = null)	{
		global $form, $pearDB;
		if (!$cg_id) 
			return;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `contactgroup` ";
		$rq .= "SET `cg_name` = '".htmlentities($ret["cg_name"], ENT_QUOTES)."', " .
				"`cg_alias` = '".htmlentities($ret["cg_alias"], ENT_QUOTES)."', " .
				"`cg_comment` = '".htmlentities($ret["cg_comment"], ENT_QUOTES)."', " .
				"`cg_activate` = '".$ret["cg_activate"]["cg_activate"]."' " .
				"WHERE `cg_id` = '".$cg_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
	
	function updateContactGroupContacts($cg_id, $ret = array())	{
		global $form, $pearDB;
		if (!$cg_id) 
			return;
			
		$rq = "DELETE FROM `contactgroup_contact_relation` WHERE `contactgroup_cg_id` = '".$cg_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if (isset($ret["cg_contacts"]))
			$ret = $ret["cg_contacts"];
		else
			$ret = $form->getSubmitValue("cg_contacts");

		for ($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO `contactgroup_contact_relation` (`contact_contact_id`, `contactgroup_cg_id`) ";
			$rq .= "VALUES ('".$ret[$i]."', '".$cg_id."')";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}
?>