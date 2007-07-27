<?
/**
Oreon is developped with GPL Licence 2.0 :
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

	function testCmdExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('command_id');
		$DBRESULT =& $pearDB->query("SELECT command_name, command_id FROM command WHERE command_name = '".htmlentities($name, ENT_QUOTES)."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$command =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $command["command_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $command["command_id"] != $id)
			return false;
		else
			return true;
	}

	function deleteCommandInDB ($commands = array())	{
		global $pearDB;
		foreach($commands as $key=>$value)	{
			$DBRESULT =& $pearDB->query("DELETE FROM command WHERE command_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}
	
	function multipleCommandInDB ($commands = array(), $nbrDup = array())	{
		foreach($commands as $key=>$value)	{
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT * FROM command WHERE command_id = '".$key."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row = $DBRESULT->fetchRow();
			$row["command_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "command_name" ? ($command_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2 != NULL?(", '".$value2."'"):", NULL") : $val .= ($value2 != NULL?("'".$value2."'"):"NULL");
				}
				if (testCmdExistence($command_name))	{
					$val ? $rq = "INSERT INTO command VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
				}
			}
		}
	}
	
	function updateCommandInDB ($cmd_id = NULL)	{
		if (!$cmd_id) return;
		updateCommand($cmd_id);
	}
	
	function updateCommand($cmd_id = null)	{
		if (!$cmd_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		set_magic_quotes_runtime(1);
		$ret["command_line"] = str_replace('\n', "#BR#", $ret["command_line"]);
		$ret["command_line"] = str_replace('\t', "#T#", $ret["command_line"]);
		$ret["command_line"] = str_replace('\r', "#R#", $ret["command_line"]);
		$ret["command_line"] = str_replace('/', "#S#", $ret["command_line"]);
		$ret["command_line"] = str_replace('\\', "#BS#", $ret["command_line"]);
		$ret["command_example"] = str_replace("\n", "#BR#", $ret["command_example"]);
		$ret["command_example"] = str_replace("\t", "#T#", $ret["command_example"]);
		$ret["command_example"] = str_replace("\r", "#R#", $ret["command_example"]);
		$ret["command_example"] = str_replace('/', "#S#", $ret["command_example"]);
		$ret["command_example"] = str_replace('\\', "#BS#", $ret["command_example"]);
		$rq = "UPDATE command ";
		$rq .= "SET command_name = '".htmlentities($ret["command_name"], ENT_QUOTES)."', " .
				"command_line = '".htmlentities($ret["command_line"], ENT_QUOTES)."', " .
				"command_example = '".htmlentities($ret["command_example"], ENT_QUOTES)."', " .
				"command_type = '".htmlentities($ret["command_type"]["command_type"], ENT_QUOTES)."' " .
				"WHERE command_id = '".$cmd_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	}
	
	function insertCommandInDB ($ret = array())	{
		$cmd_id = insertCommand($ret);
		return ($cmd_id);
	}
	
	function insertCommand($ret = array())	{
		global $form;
		global $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		set_magic_quotes_runtime(1);
		$ret["command_line"] = str_replace("\n", "#BR#", $ret["command_line"]);
		$ret["command_line"] = str_replace("\t", "#T#", $ret["command_line"]);
		$ret["command_line"] = str_replace("\r", "#R#", $ret["command_line"]);
		$ret["command_line"] = str_replace('/', "#S#", $ret["command_line"]);
		$ret["command_line"] = str_replace('\\', "#BS#", $ret["command_line"]);
		$ret["command_example"] = str_replace("\n", "#BR#", $ret["command_example"]);
		$ret["command_example"] = str_replace("\t", "#T#", $ret["command_example"]);
		$ret["command_example"] = str_replace("\r", "#R#", $ret["command_example"]);
		$ret["command_example"] = str_replace('/', "#S#", $ret["command_example"]);
		$ret["command_example"] = str_replace('\\', "#BS#", $ret["command_example"]);
		$rq = "INSERT INTO command ";
		$rq .= "(command_name, command_line, command_example, command_type) ";
		$rq .= "VALUES ";
		$rq .= "('".htmlentities($ret["command_name"], ENT_QUOTES)."', '".htmlentities($ret["command_line"], ENT_QUOTES)."', '".htmlentities($ret["command_example"], ENT_QUOTES)."', '".$ret["command_type"]["command_type"]."')";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$DBRESULT =& $pearDB->query("SELECT MAX(command_id) FROM command");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$cmd_id = $DBRESULT->fetchRow();
		return ($cmd_id["MAX(command_id)"]);
	}
	
	function return_plugin($rep){
		global $oreon;
		$plugins = array();
		$is_not_a_plugin = array("."=>".", ".."=>"..", "oreon.conf"=>"oreon.conf", "oreon.pm"=>"oreon.pm", "utils.pm"=>"utils.pm", "negate"=>"negate");
		$handle[$rep] = opendir($rep);
		while (false !== ($filename = readdir($handle[$rep]))){
			if ($filename != "." && $filename != ".."){
				if (is_dir($rep.$filename)){
					$plg_tmp = return_plugin($rep."/".$filename, $handle[$rep]);
					$plugins = array_merge($plugins, $plg_tmp);
					unset($plg_tmp);
				} else if (!array_key_exists($filename, $is_not_a_plugin) && substr($filename, -1)!= "~"){
					$key = substr($rep."/".$filename, strlen($oreon->optGen["nagios_path_plugins"]));
					$plugins[$key] = $key;
				}
			}
		}
		closedir($handle[$rep]);
		return ($plugins);
	}
?>