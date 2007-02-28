<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

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
	if (!isset($oreon))
		exit();

	function getModuleInfoInDB($name = NULL, $id = NULL) {
		if (!$name && !$id) return;
		global $pearDB;
		if ($id)
			$rq = "SELECT * FROM modules_informations WHERE id='".$id."'  LIMIT 1";
		else if ($name)
			$rq = "SELECT * FROM modules_informations WHERE name='".$name."' LIMIT 1";		
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print $DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())
			return ($DBRESULT->fetchRow());
		else
			return array();	
	}
	
	function isvalidInstallation()	{
		global $pearDB;
		global $form1;
		$name = $form1->getSubmitValue('name');
		$DBRESULT =& $pearDB->query("SELECT name FROM modules_informations WHERE name = '".$name."'  LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print $DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())
			return false;
		else
			return true;
	}
	
	function insertModuleInDB($name = NULL, $module_conf = array())	{
		if (!$name) return NULL;
		global $pearDB;
		$rq = "INSERT INTO `modules_informations` " .
				"(`name` , `rname` , `release` , `is_removeable` , `infos` , `author` , `lang_files` ) " .
				"VALUES ( ";
		isset($name) && $name != NULL ? $rq .= "'".htmlentities($name , ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($module_conf["rname"]) && $module_conf["rname"] != NULL ? $rq .= "'".htmlentities($module_conf["rname"] , ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($module_conf["release"]) && $module_conf["release"] != NULL ? $rq .= "'".htmlentities($module_conf["release"] , ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($module_conf["is_removeable"]) && $module_conf["is_removeable"] != NULL ? $rq .= "'".htmlentities($module_conf["is_removeable"] , ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($module_conf["infos"]) && $module_conf["infos"] != NULL ? $rq .= "'".htmlentities($module_conf["infos"] , ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($module_conf["author"]) && $module_conf["author"] != NULL ? $rq .= "'".htmlentities($module_conf["author"] , ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($module_conf["lang_files"]) && $module_conf["lang_files"] != NULL ? $rq .= "'".htmlentities($module_conf["lang_files"] , ENT_QUOTES)."'": $rq .= "NULL";
		$rq .= ")";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$DBRESULT =& $pearDB->query("SELECT MAX(id) FROM modules_informations");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$id = $DBRESULT->fetchRow();
		return ($id["MAX(id)"]);
	}
?>