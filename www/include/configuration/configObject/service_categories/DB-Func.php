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

	function testServiceCategorieExistence ($name = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('sc_id');
		$DBRESULT =& $pearDB->query("SELECT `sc_name`, `sc_id` FROM `service_categories` WHERE `sc_name` = '".htmlentities($name, ENT_QUOTES)."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$DBRESULT->fetchInto($sc);
		if ($DBRESULT->numRows() >= 1 && $sc["sc_id"] == $id)
			return true;
		else if ($DBRESULT->numRows() >= 1 && $sc["sc_id"] != $id)
			return false;
		else
			return true;
	}

	function multipleServiceCategorieInDB ($sc = array(), $nbrDup = array())	{
		foreach($sc as $key => $value)	{
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT * FROM `service_categories` WHERE `sc_id` = '".$key."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row = $DBRESULT->fetchRow();
			$row["sc_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "sc_name" ? ($sc_name = $value2 = $value2."_".$i) : null;
					$key2 == "sc_description" ? ($sc_alias = $value2 = $value2) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2 != NULL?("'".$value2."'"):"NULL");
				}
				if (testServiceCategorieExistence($sc_name))	{
					$val ? $rq = "INSERT INTO `service_categories` VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
					$DBRESULT =& $pearDB->query("SELECT MAX(sc_id) FROM `service_categories`");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
					$maxId =& $DBRESULT->fetchRow();
				}
			}
		}
	}

	function enableServiceCategorieInDB($sc_id = null, $sc_arr = array())	{
		if (!$sc_id && !count($sc_arr)) return;
		global $pearDB;
		if ($sc_id)
			$sc_arr = array($sc_id=>"1");
		foreach($sc_arr as $key=>$value)	{
			$DBRESULT =& $pearDB->query("UPDATE service_categories SET sc_activate = '1' WHERE sc_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}

	function disableServiceCategorieInDB($sc_id = null, $sc_arr = array())	{
		if (!$sc_id && !count($sc_arr)) return;
		global $pearDB;
		if ($sc_id)
			$sc_arr = array($sc_id=>"1");
		foreach($sc_arr as $key=>$value)	{
			$DBRESULT =& $pearDB->query("UPDATE service_categories SET sc_activate = '0' WHERE sc_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}
	
	function insertServiceCategorieInDB(){
		global $pearDB;
		if (testServiceCategorieExistence($_POST["sc_name"])){
			$DBRESULT =& $pearDB->query("INSERT INTO `service_categories` (`sc_name` , `sc_description` , `sc_activate` ) VALUES ('".$_POST["sc_name"]."', '".$_POST["sc_description"]."', '1')");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}
	
	function updateServiceCategorieInDB(){
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE `service_categories` SET `sc_name` = '".$_POST["sc_name"]."' , `sc_description` = '".$_POST["sc_description"]."' , `sc_activate` = '".$_POST["sc_activate"]."' WHERE `sc_id` = '".$_POST["sc_id"]."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	}
	
	function deleteServiceCategorieInDB($sc_id = NULL){
		global $pearDB;
		$select = $_POST["select"];
		foreach ($select as $key => $value){
			$DBRESULT =& $pearDB->query("DELETE FROM `service_categories` WHERE `sc_id` = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}

?>