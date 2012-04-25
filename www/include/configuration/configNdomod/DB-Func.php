<?php
/*
 * Copyright 2005-2011 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

	if (!isset($oreon))
		exit();

	function testExistence ($name = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('id');
		$DBRESULT = $pearDB->query("SELECT description, id FROM cfg_ndomod WHERE `description` = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
		$ndomod = $DBRESULT->fetchRow();
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
		$DBRESULT = $pearDB->query("UPDATE `cfg_ndomod` SET `activate` = '1' WHERE id = '".$id."'");
	}

	function disableNdomodInDB ($id = null)	{
		if (!$id) return;
		global $pearDB,$oreon;
		$DBRESULT = $pearDB->query("UPDATE `cfg_ndomod` SET `activate` = '0' WHERE id = '".$id."'");
	}

	function deleteNdomodInDB ($ndomod = array())	{
		global $pearDB;
		foreach($ndomod as $key => $value)	{
			$DBRESULT = $pearDB->query("DELETE FROM `cfg_ndomod` WHERE id = '".$key."'");
		}
	}

	function multipleNdomodInDB ($ndomod = array(), $nbrDup = array())	{
		foreach($ndomod as $key => $value)	{
			global $pearDB;
			$DBRESULT = $pearDB->query("SELECT * FROM `cfg_ndomod` WHERE id = '".$key."' LIMIT 1");
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
					$DBRESULT = $pearDB->query($rq);
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

		/*
		 * Get Nagios Server List
		 */
		$nagios_servers = array();
		$DBRESULT = $pearDB->query("SELECT * FROM nagios_server ORDER BY name");
		while ($nagios_server = $DBRESULT->fetchRow())
			$nagios_servers[$nagios_server["id"]] = $nagios_server["name"];
		$DBRESULT->free();

		$rq = "INSERT INTO `cfg_ndomod` (" .
				"`description` , `ns_nagios_server` , `output_type` , `output` , `buffer_file` , " .
				"`tcp_port` , `output_buffer_items`, `file_rotation_interval` , `file_rotation_command` , `file_rotation_timeout` , `reconnect_interval` , " .
				"`reconnect_warning_interval` , `data_processing_options` , `config_output_options`, `activate`) ";
		$rq .= "VALUES (";
		isset($ret["description"]) && $ret["description"] != NULL ? $rq .= "'".htmlentities($ret["description"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
		isset($ret["ns_nagios_server"]) && $ret["ns_nagios_server"] != NULL ? $rq .= "'".htmlentities($ret["ns_nagios_server"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
       	isset($ret["output_type"]) && $ret["output_type"] != NULL ? $rq .= "'".htmlentities($ret["output_type"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["output"]) && $ret["output"] != NULL ? $rq .= "'".htmlentities($ret["output"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["buffer_file"]) && $ret["buffer_file"] != NULL ? $rq .= "'".htmlentities($ret["buffer_file"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["tcp_port"]) && $ret["tcp_port"] != NULL ? $rq .= "'".htmlentities($ret["tcp_port"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["output_buffer_items"]) && $ret["output_buffer_items"] != 2 ? $rq .= "'".$ret["output_buffer_items"]."',  "  : $rq .= "NULL, ";
        isset($ret["file_rotation_interval"]) && $ret["file_rotation_interval"] != NULL ? $rq .= "'".htmlentities($ret["file_rotation_interval"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
 	    isset($ret["file_rotation_command"]) && $ret["file_rotation_command"] != NULL ? $rq .= "'".htmlentities($ret["file_rotation_command"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["file_rotation_timeout"]) && $ret["file_rotation_timeout"] != NULL ? $rq .= "'".htmlentities($ret["file_rotation_timeout"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["reconnect_interval"]) && $ret["reconnect_interval"] != 2 ? $rq .= "'".$ret["reconnect_interval"]."',  " : $rq .= "NULL, ";
        isset($ret["reconnect_warning_interval"]) && $ret["reconnect_warning_interval"] != 2 ? $rq .= "'".$ret["reconnect_warning_interval"]."',  " : $rq .= "NULL, ";
        isset($ret["data_processing_options"]) && $ret["data_processing_options"] != 2 ? $rq .= "'".$ret["data_processing_options"]."',  " : $rq .= "NULL, ";
        isset($ret["config_output_options"]) && $ret["config_output_options"] != 2 ? $rq .= "'".$ret["config_output_options"]."',  " : $rq .= "NULL, ";
        isset($ret["activate"]) && $ret["activate"]["activate"] != NULL ? $rq .= "'".$ret["activate"]["activate"]."')" : $rq .= "NULL )";
       	$DBRESULT = $pearDB->query($rq);
		$DBRESULT = $pearDB->query("SELECT MAX(id) FROM `cfg_ndomod`");
		$ndomod_id = $DBRESULT->fetchRow();
		$DBRESULT->free();
		return ($ndomod_id["MAX(id)"]);
	}

	function updateNdomod($id = null)	{
		global $form, $pearDB;
		if (!$id)
			return;

		/*
		 * Get Nagios Server List
		 */
		$nagios_servers = array();
		$DBRESULT = $pearDB->query("SELECT * FROM nagios_server ORDER BY name");
		while ($nagios_server = $DBRESULT->fetchRow())
			$nagios_servers[$nagios_server["id"]] = $nagios_server["name"];
		$DBRESULT->free();

		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `cfg_ndomod` SET ";
        isset($ret["description"]) && $ret["description"] != NULL ? $rq .= "description = '".htmlentities($ret["description"], ENT_QUOTES, "UTF-8")."', " : $rq .= "description = NULL, ";
        isset($ret["ns_nagios_server"]) && $ret["ns_nagios_server"] != NULL ? $rq .= "ns_nagios_server = '".htmlentities($ret["ns_nagios_server"], ENT_QUOTES, "UTF-8")."', " : $rq .= "ns_nagios_server = NULL, ";
		isset($ret["output_type"]) && $ret["output_type"] != NULL ? $rq .= "output_type = '".htmlentities($ret["output_type"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "output_type = NULL, ";
       	isset($ret["output"]) && $ret["output"] != NULL ? $rq .= "output = '".htmlentities($ret["output"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "output = NULL, ";
        isset($ret["buffer_file"]) && $ret["buffer_file"] != NULL ? $rq .= "buffer_file = '".htmlentities($ret["buffer_file"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "buffer_file = NULL, ";
        isset($ret["tcp_port"]) && $ret["tcp_port"] != NULL ? $rq .= "tcp_port = '".htmlentities($ret["tcp_port"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "tcp_port = NULL, ";
        isset($ret["output_buffer_items"]) && $ret["output_buffer_items"] != NULL ? $rq .= "output_buffer_items = '".htmlentities($ret["output_buffer_items"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "output_buffer_items = NULL, ";
 	    isset($ret["file_rotation_interval"]) && $ret["file_rotation_interval"] != NULL ? $rq .= "file_rotation_interval = '".htmlentities($ret["file_rotation_interval"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "file_rotation_interval = NULL, ";
        isset($ret["file_rotation_command"]) && $ret["file_rotation_command"] != NULL ? $rq .= "file_rotation_command = '".htmlentities($ret["file_rotation_command"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "file_rotation_command = NULL, ";
        isset($ret["file_rotation_timeout"]) && $ret["file_rotation_timeout"] != 2 ? $rq .= "file_rotation_timeout = '".$ret["file_rotation_timeout"]."',  " : $rq .= "file_rotation_timeout = '2', ";
        isset($ret["reconnect_interval"]) && $ret["reconnect_interval"] != NULL ? $rq .= "reconnect_interval = '".htmlentities($ret["reconnect_interval"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "reconnect_interval = NULL, ";
        isset($ret["reconnect_warning_interval"]) && $ret["reconnect_warning_interval"] != NULL ? $rq .= "reconnect_warning_interval = '".htmlentities($ret["reconnect_warning_interval"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "reconnect_warning_interval = NULL, ";
        isset($ret["data_processing_options"]) && $ret["data_processing_options"] != NULL ? $rq .= "data_processing_options = '".htmlentities($ret["data_processing_options"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "data_processing_options = NULL, ";
        isset($ret["config_output_options"]) && $ret["config_output_options"] != NULL ? $rq .= "config_output_options = '".htmlentities($ret["config_output_options"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "config_output_options = NULL, ";
        $rq .= "activate = '".$ret["activate"]["activate"]."' ";
		$rq .= "WHERE id = '".$id."'";
		$DBRESULT = $pearDB->query($rq);
	}
?>