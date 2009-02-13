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
	if (!isset($oreon))
		exit();
	
	function testExistence ($name = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('id');
		$DBRESULT =& $pearDB->query("SELECT description, id FROM cfg_ndomod WHERE `description` = '".htmlentities($name, ENT_QUOTES)."'");
		$ndomod =& $DBRESULT->fetchRow();
		if ($DBRESULT->numRows() >= 1 && $ndomod["id"] == $id)#Modif case	
			return true;
		else if ($DBRESULT->numRows() >= 1 && $ndomod["id"] != $id)#Duplicate entry
			return false;
		else
			return true;
	}	
	
	function enableNdomodInDB ($id = null)	{
		if (!$id) return;
		global $pearDB, $oreon;
		$DBRESULT =& $pearDB->query("UPDATE `cfg_ndomod` SET `activate` = '1' WHERE id = '".$id."'");
	}
	
	function disableNdomodInDB ($id = null)	{
		if (!$id) return;
		global $pearDB,$oreon;
		$DBRESULT =& $pearDB->query("UPDATE `cfg_ndomod` SET `activate` = '0' WHERE id = '".$id."'");
	}
	
	function deleteNdomodInDB ($ndomod = array())	{
		global $pearDB;
		foreach($ndomod as $key => $value)	{
			$DBRESULT =& $pearDB->query("DELETE FROM `cfg_ndomod` WHERE id = '".$key."'");
		}
	}
	
	function multipleNdomodInDB ($ndomod = array(), $nbrDup = array())	{
		foreach($ndomod as $key => $value)	{
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT * FROM `cfg_ndomod` WHERE id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["id"] = '';
			$row["activate"] = '0';
			$DBRESULT->free();
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "description" ? ($nagios_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testExistence($nagios_name))	{
					$val ? $rq = "INSERT INTO `cfg_ndomod` VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
				}
			}
		}
	}
	
	function updateNdomodInDB ($id = NULL)	{
		if (!$id) return;
		updateNdomod($id);
	}	
	
	function insertNdomodInDB ()	{
		$id = insertNdomod();
		return ($id);
	}
	
	function insertNdomod($ret = array())	{
		global $form, $pearDB, $oreon;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO `cfg_ndomod` (" .
				"`description` , `ns_nagios_server` , `instance_name` , `output_type` , `output` , `buffer_file` , " .
				"`tcp_port` , `output_buffer_items`, `file_rotation_interval` , `file_rotation_command` , `file_rotation_timeout` , `reconnect_interval` , " .
				"`reconnect_warning_interval` , `data_processing_options` , `config_output_options`, `activate`) ";
		$rq .= "VALUES (";
		isset($ret["description"]) && $ret["description"] != NULL ? $rq .= "'".htmlentities($ret["description"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["ns_nagios_server"]) && $ret["ns_nagios_server"] != NULL ? $rq .= "'".htmlentities($ret["ns_nagios_server"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
        isset($ret["instance_name"]) && $ret["instance_name"] != NULL ? $rq .= "'".htmlentities($ret["instance_name"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
       	isset($ret["output_type"]) && $ret["output_type"] != NULL ? $rq .= "'".htmlentities($ret["output_type"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
        isset($ret["output"]) && $ret["output"] != NULL ? $rq .= "'".htmlentities($ret["output"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
        isset($ret["buffer_file"]) && $ret["buffer_file"] != NULL ? $rq .= "'".htmlentities($ret["buffer_file"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
        isset($ret["tcp_port"]) && $ret["tcp_port"] != NULL ? $rq .= "'".htmlentities($ret["tcp_port"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
        isset($ret["output_buffer_items"]) && $ret["output_buffer_items"] != 2 ? $rq .= "'".$ret["output_buffer_items"]."',  "  : $rq .= "NULL, ";
        isset($ret["file_rotation_interval"]) && $ret["file_rotation_interval"] != NULL ? $rq .= "'".htmlentities($ret["file_rotation_interval"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
 	    isset($ret["file_rotation_command"]) && $ret["file_rotation_command"] != NULL ? $rq .= "'".htmlentities($ret["file_rotation_command"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
        isset($ret["file_rotation_timeout"]) && $ret["file_rotation_timeout"] != NULL ? $rq .= "'".htmlentities($ret["file_rotation_timeout"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
        isset($ret["reconnect_interval"]) && $ret["reconnect_interval"] != 2 ? $rq .= "'".$ret["reconnect_interval"]."',  " : $rq .= "NULL, ";
        isset($ret["reconnect_warning_interval"]) && $ret["reconnect_warning_interval"] != 2 ? $rq .= "'".$ret["reconnect_warning_interval"]."',  " : $rq .= "NULL, ";
        isset($ret["data_processing_options"]) && $ret["data_processing_options"] != 2 ? $rq .= "'".$ret["data_processing_options"]."',  " : $rq .= "NULL, ";
        isset($ret["config_output_options"]) && $ret["config_output_options"] != 2 ? $rq .= "'".$ret["config_output_options"]."',  " : $rq .= "NULL, ";
        isset($ret["activate"]) && $ret["activate"]["activate"] != NULL ? $rq .= "'".$ret["activate"]["activate"]."')" : $rq .= "NULL )";
       	$DBRESULT =& $pearDB->query($rq);
		$DBRESULT =& $pearDB->query("SELECT MAX(id) FROM `cfg_ndomod`");
		$ndomod_id = $DBRESULT->fetchRow();
		$DBRESULT->free();
		return ($ndomod_id["MAX(id)"]);
	}
	
	function updateNdomod($id = null)	{
		if (!$id) return;
		global $form, $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `cfg_ndomod` SET ";
        isset($ret["description"]) && $ret["description"] != NULL ? $rq .= "description = '".htmlentities($ret["description"], ENT_QUOTES)."', " : $rq .= "description = NULL, ";
        isset($ret["ns_nagios_server"]["ns_nagios_server"]) && $ret["ns_nagios_server"]["ns_nagios_server"] != NULL ? $rq .= "ns_nagios_server = '".htmlentities($ret["ns_nagios_server"]["ns_nagios_server"], ENT_QUOTES)."', " : $rq .= "ns_nagios_server = NULL, ";
		isset($ret["instance_name"]) && $ret["instance_name"] != NULL ? $rq .= "instance_name = '".htmlentities($ret["instance_name"], ENT_QUOTES)."',  " : $rq .= "instance_name = NULL, ";
        isset($ret["output_type"]) && $ret["output_type"] != NULL ? $rq .= "output_type = '".htmlentities($ret["output_type"], ENT_QUOTES)."',  " : $rq .= "output_type = NULL, ";
       	isset($ret["output"]) && $ret["output"] != NULL ? $rq .= "output = '".htmlentities($ret["output"], ENT_QUOTES)."',  " : $rq .= "output = NULL, ";
        isset($ret["buffer_file"]) && $ret["buffer_file"] != NULL ? $rq .= "buffer_file = '".htmlentities($ret["buffer_file"], ENT_QUOTES)."',  " : $rq .= "buffer_file = NULL, ";
        isset($ret["tcp_port"]) && $ret["tcp_port"] != NULL ? $rq .= "tcp_port = '".htmlentities($ret["tcp_port"], ENT_QUOTES)."',  " : $rq .= "tcp_port = NULL, ";
        isset($ret["output_buffer_items"]) && $ret["output_buffer_items"] != NULL ? $rq .= "output_buffer_items = '".htmlentities($ret["output_buffer_items"], ENT_QUOTES)."',  " : $rq .= "output_buffer_items = NULL, ";
 	    isset($ret["file_rotation_interval"]) && $ret["file_rotation_interval"] != NULL ? $rq .= "file_rotation_interval = '".htmlentities($ret["file_rotation_interval"], ENT_QUOTES)."',  " : $rq .= "file_rotation_interval = NULL, ";
        isset($ret["file_rotation_command"]) && $ret["file_rotation_command"] != NULL ? $rq .= "file_rotation_command = '".htmlentities($ret["file_rotation_command"], ENT_QUOTES)."',  " : $rq .= "file_rotation_command = NULL, ";
        isset($ret["file_rotation_timeout"]) && $ret["file_rotation_timeout"] != 2 ? $rq .= "file_rotation_timeout = '".$ret["file_rotation_timeout"]."',  " : $rq .= "file_rotation_timeout = '2', ";
        isset($ret["reconnect_interval"]) && $ret["reconnect_interval"] != NULL ? $rq .= "reconnect_interval = '".htmlentities($ret["reconnect_interval"], ENT_QUOTES)."',  " : $rq .= "reconnect_interval = NULL, ";
        isset($ret["reconnect_warning_interval"]) && $ret["reconnect_warning_interval"] != NULL ? $rq .= "reconnect_warning_interval = '".htmlentities($ret["reconnect_warning_interval"], ENT_QUOTES)."',  " : $rq .= "reconnect_warning_interval = NULL, ";
        isset($ret["data_processing_options"]) && $ret["data_processing_options"] != NULL ? $rq .= "data_processing_options = '".htmlentities($ret["data_processing_options"], ENT_QUOTES)."',  " : $rq .= "data_processing_options = NULL, ";
        isset($ret["config_output_options"]) && $ret["config_output_options"] != NULL ? $rq .= "config_output_options = '".htmlentities($ret["config_output_options"], ENT_QUOTES)."',  " : $rq .= "config_output_options = NULL, ";
        $rq .= "activate = '".$ret["activate"]["activate"]."' ";
		$rq .= "WHERE id = '".$id."'";
		$DBRESULT =& $pearDB->query($rq);
	}
?>