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
 

	function testMnftrExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('id');
		$DBRESULT =& $pearDB->query("SELECT name, id FROM traps_vendor WHERE name = '".htmlentities($name, ENT_QUOTES)."'");
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
		global $pearDB, $oreon;
		foreach($mnftr as $key=>$value)		{
			$DBRESULT2 =& $pearDB->query("SELECT name FROM `traps_vendor` WHERE `id` = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
			
			$DBRESULT =& $pearDB->query("DELETE FROM traps_vendor WHERE id = '".htmlentities($key, ENT_QUOTES)."'");
			$oreon->CentreonLogAction->insertLog("manufacturer", $key, $row['name'], "d");
		}
	}
	
	function multipleMnftrInDB ($mnftr = array(), $nbrDup = array())	{
		foreach($mnftr as $key=>$value)	{
			global $pearDB, $oreon;
			$DBRESULT =& $pearDB->query("SELECT * FROM traps_vendor WHERE id = '".htmlentities($key, ENT_QUOTES)."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "name" ? ($name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
					if ($key2 != "id")
						$fields[$key2] = $value2;
					$fields["name"] = $name;
				}
				if (testMnftrExistence($name)) {
					$val ? $rq = "INSERT INTO traps_vendor VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
					$oreon->CentreonLogAction->insertLog("manufacturer", htmlentities($key, ENT_QUOTES), $name, "a", $fields);
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
		global $pearDB, $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE traps_vendor ";
		$rq .= "SET name = '".htmlentities($ret["name"], ENT_QUOTES)."', ";
		$rq .= "alias = '".htmlentities($ret["alias"], ENT_QUOTES)."', ";
		$rq .= "description = '".htmlentities($ret["description"], ENT_QUOTES)."' ";
		$rq .= "WHERE id = '".$id."'";
		$DBRESULT =& $pearDB->query($rq);
		$fields["name"] = htmlentities($ret["name"], ENT_QUOTES);
		$fields["alias"] = htmlentities($ret["alias"], ENT_QUOTES);
		$fields["description"] = htmlentities($ret["description"], ENT_QUOTES);
		$oreon->CentreonLogAction->insertLog("manufacturer", $id, $fields["name"], "c", $fields);
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
		$DBRESULT =& $pearDB->query("SELECT MAX(id) FROM traps_vendor");
		$mnftr_id = $DBRESULT->fetchRow();
		
		$fields["name"] = htmlentities($ret["name"], ENT_QUOTES);
		$fields["alias"] = htmlentities($ret["alias"], ENT_QUOTES);
		$fields["description"] = htmlentities($ret["description"], ENT_QUOTES);
		$oreon->CentreonLogAction->insertLog("manufacturer", $mnftr_id["MAX(id)"], $fields["name"], "a", $fields);
		
		return ($mnftr_id["MAX(id)"]);
	}
?>
