<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Sugumaran Mathavarajan - Julien Mathis - Romain Le Merlus

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

	function testMnftrExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('id');
		$DBRESULT =& $pearDB->query("SELECT name, id FROM traps_vendor WHERE name = '".htmlentities($name, ENT_QUOTES)."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$mnftr =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $mnftr["id"] == $id)	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $mnftr["id"] != $id)
			return false;
		else
			return true;
	}

	function deleteMnftrInDB ($mnftr = array())	{
		global $pearDB;
		foreach($mnftr as $key=>$value)		{
			$DBRESULT =& $pearDB->query("DELETE FROM traps_vendor WHERE id = '".htmlentities($key, ENT_QUOTES)."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}
	
	function multipleMnftrInDB ($mnftr = array(), $nbrDup = array())	{
		foreach($mnftr as $key=>$value)	{
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT * FROM traps_vendor WHERE id = '".htmlentities($key, ENT_QUOTES)."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$row = $DBRESULT->fetchRow();
			$row["id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "name" ? ($name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testMnftrExistence($name)) {
					$val ? $rq = "INSERT INTO traps_vendor VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
				}
			}
		}
	}
	
	function updateMnftrInDB ($id = NULL)	{
		if (!$id) return;
		updateMnftr($id);
	}
	
	function updateMnftr($id = null)	{
		if (!$id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE traps_vendor ";
		$rq .= "SET name = '".htmlentities($ret["name"], ENT_QUOTES)."', ";
		$rq .= "alias = '".htmlentities($ret["alias"], ENT_QUOTES)."', ";
		$rq .= "description = '".htmlentities($ret["description"], ENT_QUOTES)."' ";
		$rq .= "WHERE id = '".$id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
	
	function insertMnftrInDB ($ret = array())	{
		$id = insertMnftr($ret);
		return ($id);
	}
	
	function insertMnftr($ret = array())	{
		global $form;
		global $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO traps_vendor ";
		$rq .= "(name, alias, description) ";
		$rq .= "VALUES ";
		$rq .= "('".htmlentities($ret["name"], ENT_QUOTES)."', ";
		$rq .= "'".htmlentities($ret["alias"], ENT_QUOTES)."', ";
		$rq .= "'".htmlentities($ret["description"], ENT_QUOTES)."')";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$DBRESULT =& $pearDB->query("SELECT MAX(id) FROM traps_vendor");
		$mnftr_id = $DBRESULT->fetchRow();
		return ($mnftr_id["MAX(id)"]);
	}
?>
