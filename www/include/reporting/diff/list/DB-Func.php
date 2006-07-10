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
			$id = $form->getSubmitValue('rtdl_id');
		$res =& $pearDB->query("SELECT name, rtdl_id FROM reporting_diff_list WHERE name = '".htmlentities($name, ENT_QUOTES)."'");
		$list =& $res->fetchRow();
		#Modif case
		if ($res->numRows() >= 1 && $list["rtdl_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($res->numRows() >= 1 && $list["rtdl_id"] != $id)
			return false;
		else
			return true;
	}
	
	function enableListInDB ($rtdl_id = null)	{
		if (!$rtdl_id) return;
		global $pearDB;
		$pearDB->query("UPDATE reporting_diff_list SET activate = '1' WHERE rtdl_id = '".$rtdl_id."'");
	}
	
	function disableListInDB ($rtdl_id = null)	{
		if (!$rtdl_id) return;
		global $pearDB;
		$pearDB->query("UPDATE reporting_diff_list SET activate = '0' WHERE rtdl_id = '".$rtdl_id."'");
	}
	
	function deleteListInDB ($lists = array())	{
		global $pearDB;
		foreach($lists as $key=>$value)
			$pearDB->query("DELETE FROM reporting_diff_list WHERE rtdl_id = '".$key."'");
	}
	
	function multipleListInDB ($lists = array(), $nbrDup = array())	{
		foreach($lists as $key=>$value)	{
			global $pearDB;
			$res =& $pearDB->query("SELECT * FROM reporting_diff_list WHERE rtdl_id = '".$key."' LIMIT 1");
			$row = $res->fetchRow();
			$row["rtdl_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "name" ? ($name = clone($value2 = $value2."_".$i)) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testExistence($name))	{
					$val ? $rq = "INSERT INTO reporting_diff_list VALUES (".$val.")" : $rq = null;
					$pearDB->query($rq);
					$res =& $pearDB->query("SELECT MAX(rtdl_id) FROM reporting_diff_list");
					$maxId =& $res->fetchRow();
					if (isset($maxId["MAX(rtdl_id)"]))	{
						$res =& $pearDB->query("SELECT DISTINCT rtde_id, oreon_contact FROM reporting_email_list_relation WHERE rtdl_id = '".$key."'");
						while($res->fetchInto($mail))
							$pearDB->query("INSERT INTO reporting_email_list_relation VALUES ('', '".$maxId["MAX(rtdl_id)"]."', '".$mail["rtde_id"]."', '".$mail["oreon_contact"]."')");
						$res->free();
					}
				}
			}
		}
	}
	function updateListInDB ($rtdl_id = NULL)	{
		if (!$rtdl_id) return;
		updateList($rtdl_id);
		updateListDiffMail($rtdl_id);
		updateListDiffOreonMail($rtdl_id);
	}	
	
	function insertListInDB ()	{
		$rtdl_id = insertList();
		updateListDiffMail($rtdl_id);
		updateListDiffOreonMail($rtdl_id);
		return ($rtdl_id);
	}
	
	function insertList()	{
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "INSERT INTO `reporting_diff_list` ( " .
				"`rtdl_id` , `name`, `description`, `tp_id`, `activate`, `comment`)" .
				"VALUES ( ";
		$rq .= "NULL, ";
		isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "'".htmlentities($ret["name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["description"]) && $ret["description"] != NULL ? $rq .= "'".htmlentities($ret["description"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["tp_id"]) && $ret["tp_id"] != NULL ? $rq .= "'".$ret["tp_id"]."', ": $rq .= "NULL, ";
		isset($ret["activate"]["activate"]) && $ret["activate"]["activate"] != NULL ? $rq .= "'".$ret["activate"]["activate"]."', ": $rq .= "NULL, ";
		isset($ret["comment"]) && $ret["comment"] != NULL ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= ")";
		$pearDB->query($rq);
		$res =& $pearDB->query("SELECT MAX(rtdl_id) FROM reporting_diff_list");
		$rtdl_id = $res->fetchRow();
		return ($rtdl_id["MAX(rtdl_id)"]);
	}
	
	function updateList($rtdl_id = null)	{
		if (!$rtdl_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE reporting_diff_list SET ";
		$rq .= "name = ";
		isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "'".htmlentities($ret["name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "description = ";
		isset($ret["description"]) && $ret["description"] != NULL ? $rq .= "'".htmlentities($ret["description"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "tp_id = ";
		isset($ret["tp_id"]) && $ret["tp_id"] != NULL ? $rq .= "'".$ret["tp_id"]."', ": $rq .= "NULL, ";
		$rq .= "activate = ";
		isset($ret["activate"]["activate"]) && $ret["activate"]["activate"] != NULL ? $rq .= "'".$ret["activate"]["activate"]."', ": $rq .= "NULL, ";
		$rq .= "comment = ";
		isset($ret["comment"]) && $ret["comment"] != NULL ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= "WHERE rtdl_id = '".$rtdl_id."'";
		$pearDB->query($rq);
	}
	
	function updateListDiffMail($rtdl_id = null)	{
		if (!$rtdl_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM reporting_email_list_relation ";
		$rq .= "WHERE rtdl_id = '".$rtdl_id."' AND oreon_contact = '0'";
		$pearDB->query($rq);
		$ret = array();
		$ret = $form->getSubmitValue("list_mails");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO reporting_email_list_relation ";
			$rq .= "(rtdl_id, rtde_id, oreon_contact) ";
			$rq .= "VALUES ";
			$rq .= "('".$rtdl_id."', '".$ret[$i]."', '0')";
			$pearDB->query($rq);
		}
	}
	
	function updateListDiffOreonMail($rtdl_id = null)	{
		if (!$rtdl_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM reporting_email_list_relation ";
		$rq .= "WHERE rtdl_id = '".$rtdl_id."' AND oreon_contact = '1'";
		$pearDB->query($rq);
		$ret = array();
		$ret = $form->getSubmitValue("list_oreonMails");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO reporting_email_list_relation ";
			$rq .= "(rtdl_id, rtde_id, oreon_contact) ";
			$rq .= "VALUES ";
			$rq .= "('".$rtdl_id."', '".$ret[$i]."', '1')";
			$pearDB->query($rq);
		}
	}
?>