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

	function testCmdExistence ($name = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		
		if (isset($form))
			$id = $form->getSubmitValue('command_id');
		
		$DBRESULT =& $pearDB->query("SELECT `command_name`, `command_id` FROM `command` WHERE `command_name` = '".htmlentities($name, ENT_QUOTES)."'");
		$command =& $DBRESULT->fetchRow();
		if ($DBRESULT->numRows() >= 1 && $command["command_id"] == $id)	{
			/*
			 * Mofication case
			 */
			return true;
		} else if ($DBRESULT->numRows() >= 1 && $command["command_id"] != $id) {
			/*
			 * Duplicate case
			 */
			return false;
		} else
			return true;
	}

	function deleteCommandInDB ($commands = array())	{
		global $pearDB, $oreon;
		
		foreach ($commands as $key => $value)	{
			$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE `command_id` = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
			$DBRESULT =& $pearDB->query("DELETE FROM `command` WHERE `command_id` = '".$key."'");
			$oreon->CentreonLogAction->insertLog("command", $key, $row['command_name'], "d");
		}
	}
	
	function multipleCommandInDB ($commands = array(), $nbrDup = array())	{
		global $pearDB, $oreon;
			
		foreach($commands as $key => $value)	{
			
			$DBRESULT =& $pearDB->query("SELECT * FROM `command` WHERE `command_id` = '".$key."' LIMIT 1");

			$row = $DBRESULT->fetchRow();
			$row["command_id"] = '';

			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;

				foreach ($row as $key2=>$value2)	{
					$key2 == "command_name" ? ($command_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2 != NULL?(", '".$value2."'"):", NULL") : $val .= ($value2 != NULL?("'".$value2."'"):"NULL");
					if ($key2 != "command_id")
						$fields[$key2] = $value2;
					$fields["command_name"] = $command_name;
				}

				if (testCmdExistence($command_name))	{
					$val ? $rq = "INSERT INTO `command` VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
					/*
		 			* Get Max ID
		 			*/
					$DBRESULT =& $pearDB->query("SELECT MAX(command_id) FROM `command`");
					$cmd_id = $DBRESULT->fetchRow();	
					$oreon->CentreonLogAction->insertLog("command", $cmd_id["MAX(command_id)"], $command_name, "a", $fields);
				}
			}
		}
	}
	
	function updateCommandInDB ($cmd_id = NULL)	{
		if (!$cmd_id) return;
		updateCommand($cmd_id);
	}
	
	function updateCommand($cmd_id = null)	{
		global $form, $pearDB, $oreon;
		
		if (!$cmd_id) 
			return;
		
		$ret = array();
		$ret = $form->getSubmitValues();
		
		set_magic_quotes_runtime(1);
		
		$ret["command_line"] = str_replace('\n', "#BR#", $ret["command_line"]);
		$ret["command_line"] = str_replace('\t', "#T#", $ret["command_line"]);
		$ret["command_line"] = str_replace('\r', "#R#", $ret["command_line"]);
		$ret["command_line"] = str_replace('/', "#S#", $ret["command_line"]);
		$ret["command_line"] = str_replace('\\', "#BS#", $ret["command_line"]);
		
		$ret["command_example"] = str_replace('\n', "#BR#", $ret["command_example"]);
		$ret["command_example"] = str_replace('\t', "#T#", $ret["command_example"]);
		$ret["command_example"] = str_replace('\r', "#R#", $ret["command_example"]);
		$ret["command_example"] = str_replace('/', "#S#", $ret["command_example"]);
		$ret["command_example"] = str_replace('\\', "#BS#", $ret["command_example"]);
		
		$rq = "UPDATE `command` SET `command_name` = '".htmlentities($ret["command_name"], ENT_QUOTES)."', " .
				"`command_line` = '".htmlentities($ret["command_line"], ENT_QUOTES)."', " .
				"`command_example` = '".htmlentities($ret["command_example"], ENT_QUOTES)."', " .
				"`command_type` = '".htmlentities($ret["command_type"]["command_type"], ENT_QUOTES)."', " .
				"`graph_id` = '".htmlentities($ret["graph_id"], ENT_QUOTES)."' " .
				"WHERE `command_id` = '".$cmd_id."'";
		$DBRESULT =& $pearDB->query($rq);
			
		$fields["command_name"] = htmlentities($ret["command_name"], ENT_QUOTES);
		$fields["command_line"] = htmlentities($ret["command_line"], ENT_QUOTES);
		$fields["command_example"] = htmlentities($ret["command_example"], ENT_QUOTES);
		$fields["command_type"] = $ret["command_type"]["command_type"];
		$fields["graph_id"] = $ret["graph_id"];
		$oreon->CentreonLogAction->insertLog("command", $cmd_id, htmlentities($ret["command_name"], ENT_QUOTES), "c", $fields);
	}
	
	function insertCommandInDB ($ret = array())	{
		$cmd_id = insertCommand($ret);
		return ($cmd_id);
	}
	
	function insertCommand($ret = array())	{
		global $form, $pearDB, $oreon;
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
		
		/*
		 * Insert
		 */
		$rq = "INSERT INTO `command` (`command_name`, `command_line`, `command_example`, `command_type`, `graph_id`) ";
		$rq .= "VALUES ('".htmlentities($ret["command_name"], ENT_QUOTES)."', '".htmlentities($ret["command_line"], ENT_QUOTES)."', '".htmlentities($ret["command_example"], ENT_QUOTES)."', '".$ret["command_type"]["command_type"]."', '".$ret["graph_id"]."')";
		$DBRESULT =& $pearDB->query($rq);
		$fields["command_name"] = htmlentities($ret["command_name"], ENT_QUOTES);
		$fields["command_line"] = htmlentities($ret["command_line"], ENT_QUOTES);
		$fields["command_example"] = htmlentities($ret["command_example"], ENT_QUOTES);
		$fields["command_type"] = $ret["command_type"]["command_type"];
		$fields["graph_id"] = $ret["graph_id"];
		
		/*
		 * Get Max ID
		 */
		$DBRESULT =& $pearDB->query("SELECT MAX(command_id) FROM `command`");
		$cmd_id = $DBRESULT->fetchRow();
		
		$oreon->CentreonLogAction->insertLog("command", $cmd_id["MAX(command_id)"], htmlentities($ret["command_name"], ENT_QUOTES), "a", $fields);
		return ($cmd_id["MAX(command_id)"]);
	}
	
	function return_plugin($rep){
		global $oreon;
		$plugins = array();
		$is_not_a_plugin = array("." => 1, ".." => 1, "oreon.conf" => 1, "oreon.pm" => 1, "utils.pm" => 1, "negate" => 1, "centreon.conf" => 1, "centreon.pm" => 1);
		$handle[$rep] = opendir($rep);
		while (false !== ($filename = readdir($handle[$rep]))){
			if ($filename != "." && $filename != ".."){
				if (is_dir($rep.$filename)){
					$plg_tmp = return_plugin($rep."/".$filename, $handle[$rep]);
					$plugins = array_merge($plugins, $plg_tmp);
					unset($plg_tmp);
				} else if (!isset($is_not_a_plugin[$filename]) && substr($filename, -1)!= "~"){
					$key = substr($rep."/".$filename, strlen($oreon->optGen["nagios_path_plugins"]));
					$plugins[$key] = $key;
				}
			}
		}
		closedir($handle[$rep]);
		return ($plugins);
	}
?>