<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Traps unit developped by Nicolas Cordier for Merethis company. <ncordier@merethis.com>

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

	function testTrapExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('traps_id');
		$res =& $pearDB->query("SELECT traps_name, traps_id FROM traps WHERE traps_name = '".htmlentities($name, ENT_QUOTES)."'");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		$trap =& $res->fetchRow();
		#Modif case
		if ($res->numRows() >= 1 && $trap["traps_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($res->numRows() >= 1 && $trap["traps_id"] != $id)
			return false;
		else
			return true;
	}

	function deleteTrapInDB ($traps = array())	{
		global $pearDB;
		foreach($traps as $key=>$value)
		{
			$pearDB->query("DELETE FROM traps WHERE traps_id = '".$key."'");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
	}
	
	function multipleTrapInDB ($traps = array(), $nbrDup = array())	{
		foreach($traps as $key=>$value)	{
			global $pearDB;
			$res =& $pearDB->query("SELECT * FROM traps WHERE traps_id = '".$key."' LIMIT 1");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			$row = $res->fetchRow();
			$row["traps_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "traps_name" ? ($traps_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testTrapExistence($traps_name))	{
					$val ? $rq = "INSERT INTO traps VALUES (".$val.")" : $rq = null;
					$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
				}
			}
		}
	}
	
	function updateTrapInDB ($traps_id = NULL)	{
		if (!$traps_id) return;
		updateTrap($traps_id);
	}
	
	function updateTrap($traps_id = null)	{
		if (!$traps_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE traps ";
		$rq .= "SET traps_name = '".htmlentities($ret["traps_name"], ENT_QUOTES)."', ";
		$rq .= "traps_oid = '".htmlentities($ret["traps_oid"], ENT_QUOTES)."', ";
		$rq .= "traps_handler = '".htmlentities($ret["traps_handler"], ENT_QUOTES)."', ";
		$rq .= "traps_args = '".htmlentities($ret["traps_args"], ENT_QUOTES)."', ";
		$rq .= "traps_comments = '".htmlentities($ret["traps_comments"], ENT_QUOTES)."' ";
		$rq .= "WHERE traps_id = '".$traps_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	}
	
	function insertTrapInDB ($ret = array())	{
		$traps_id = insertTrap($ret);
		return ($traps_id);
	}
	
	function insertTrap($ret = array())	{
		global $form;
		global $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO traps ";
		$rq .= "(traps_name, traps_oid, traps_handler, traps_args, traps_comments) ";
		$rq .= "VALUES ";
		$rq .= "('".htmlentities($ret["traps_name"], ENT_QUOTES)."',";
		$rq .= "'".htmlentities($ret["traps_oid"], ENT_QUOTES)."', ";
		$rq .= "'".htmlentities($ret["traps_handler"], ENT_QUOTES)."', ";
		$rq .= "'".htmlentities($ret["traps_args"], ENT_QUOTES)."', ";
		$rq .= "'".htmlentities($ret["traps_comments"], ENT_QUOTES)."')";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$res =& $pearDB->query("SELECT MAX(traps_id) FROM traps");
		$traps_id = $res->fetchRow();
		return ($traps_id["MAX(traps_id)"]);
	}
?>
