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
		global $pearDB, $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('resource_id');
		$DBRESULT =& $pearDB->query("SELECT resource_name, resource_id FROM cfg_resource WHERE resource_name = '".htmlentities($name, ENT_QUOTES)."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : SELECT resource_name, resource_id FROM cfg_resource WHERE resource_name = '".htmlentities($name, ENT_QUOTES)."' : ".$DBRESULT->getMessage()."<br>";
		$res =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $res["resource_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $res["resource_id"] != $id)	
			return false;
		else
			return true;
	}

	function deleteResourceInDB ($DBRESULT = array())	{
		global $pearDB;
		foreach($DBRESULT as $key=>$value){
			$DBRESULT =& $pearDB->query("DELETE FROM cfg_resource WHERE resource_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : DELETE FROM cfg_resource WHERE resource_id = '".$key."' : ".$DBRESULT->getMessage()."<br>";
		}
	}
	
	function enableResourceInDB ($resource_id = null)	{
		if (!$resource_id) exit();
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE cfg_resource SET resource_activate = '1' WHERE resource_id = '".$resource_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : UPDATE cfg_resource SET resource_activate = '1' WHERE resource_id = '".$resource_id."' : ".$DBRESULT->getMessage()."<br>";
	}
	
	function disableResourceInDB ($resource_id = null)	{
		if (!$resource_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE cfg_resource SET resource_activate = '0' WHERE resource_id = '".$resource_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : UPDATE cfg_resource SET resource_activate = '0' WHERE resource_id = '".$resource_id."' : ".$DBRESULT->getMessage()."<br>";
	}
	
	function multipleResourceInDB ($DBRESULT = array(), $nbrDup = array())	{
		foreach($DBRESULT as $key=>$value)	{
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT * FROM cfg_resource WHERE resource_id = '".$key."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : SELECT * FROM cfg_resource WHERE resource_id = '".$key."' LIMIT 1 : ".$DBRESULT->getMessage()."<br>";
			$row = $DBRESULT->fetchRow();
			$row["resource_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "resource_name" ? ($DBRESULTource_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testExistence($DBRESULTource_name))
					$DBRESULT =& $pearDB->query($val ? $rq = "INSERT INTO cfg_resource VALUES (".$val.")" : $rq = null);
				if (PEAR::isError($DBRESULT))
					print "DB Error : INSERT INTO cfg_resource VALUES (".$val.") : ".$DBRESULT->getMessage()."<br>";
			}
		}
	}
	
	function updateResourceInDB ($resource_id = NULL)	{
		if (!$resource_id) return;
		updateResource($resource_id);
	}
	
	function updateResource($resource_id)	{
		if (!$resource_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE cfg_resource ";
		$rq .= "SET resource_name = '".htmlentities($ret["resource_name"], ENT_QUOTES)."', " .
				"resource_line = '".htmlentities($ret["resource_line"], ENT_QUOTES)."', " .
				"resource_comment= '".htmlentities($ret["resource_comment"], ENT_QUOTES)."', " .
				"resource_activate= '".$ret["resource_activate"]["resource_activate"]."' " .
				"WHERE resource_id = '".$resource_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : UPDATE cfg_resource : ".$DBRESULT->getMessage()."<br>";
	}
	
	function insertResourceInDB ()	{
		$resource_id = insertResource();
		return ($resource_id);
	}
	
	function insertResource($ret = array())	{
		global $form, $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO cfg_resource ";
		$rq .= "(resource_name, resource_line, resource_comment, resource_activate) ";
		$rq .= "VALUES (";
		isset($ret["resource_name"]) && $ret["resource_name"] != NULL ? $rq .= "'".htmlentities($ret["resource_name"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["resource_line"]) && $ret["resource_line"] != NULL ? $rq .= "'".htmlentities($ret["resource_line"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["resource_comment"]) && $ret["resource_comment"] != NULL ? $rq .= "'".htmlentities($ret["resource_comment"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["resource_activate"]["resource_activate"]) && $ret["resource_activate"]["resource_activate"] != NULL ? $rq .= "'".$ret["resource_activate"]["resource_activate"]."'" : $rq .= "NULL";
		$rq .= ")";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : INSERT INTO cfg_resource : ".$DBRESULT->getMessage()."<br>";
		$DBRESULT =& $pearDB->query("SELECT MAX(resource_id) FROM cfg_resource");
		$resource_id = $DBRESULT->fetchRow();
		if (PEAR::isError($DBRESULT))
			print "DB Error : SELECT MAX(resource_id) FROM cfg_resource : ".$DBRESULT->getMessage()."<br>";
		return ($resource_id["MAX(resource_id)"]);
	}
?>