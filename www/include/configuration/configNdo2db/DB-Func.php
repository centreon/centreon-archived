<?php
/*
 * Copyright 2005-2010 MERETHIS
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
			$id = $form->getSubmitValue('ndo2db_id');
		$DBRESULT =& $pearDB->query("SELECT description, id FROM cfg_ndo2db WHERE `description` = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
		$ndo2db =& $DBRESULT->fetchRow();
		if ($DBRESULT->numRows() >= 1 && $ndo2db["id"] == $id)#Modif case	
			return true;
		else if ($DBRESULT->numRows() >= 1 && $ndo2db["id"] != $id)#Duplicate entry
			return false;
		else
			return true;
	}	
	
	function enableNdo2dbInDB ($id = null)	{
		if (!$id) return;
		global $pearDB, $oreon;
		$DBRESULT =& $pearDB->query("UPDATE `cfg_ndo2db` SET `activate` = '1' WHERE id = '".$id."'");
	}
	
	function disableNdo2dbInDB ($id = null)	{
		if (!$id) return;
		global $pearDB,$oreon;
		$DBRESULT =& $pearDB->query("UPDATE `cfg_ndo2db` SET `activate` = '0' WHERE id = '".$id."'");
	}
	
	function deleteNdo2dbInDB ($ndo2db = array())	{
		global $pearDB;
		foreach ($ndo2db as $key => $value)	{
			$DBRESULT =& $pearDB->query("DELETE FROM `cfg_ndo2db` WHERE id = '".$key."'");
		}
	}
	
	function multipleNdo2dbInDB ($ndo2db = array(), $nbrDup = array())	{
		foreach($ndo2db as $key => $value)	{
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT * FROM `cfg_ndo2db` WHERE id = '".$key."' LIMIT 1");
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
					$val ? $rq = "INSERT INTO `cfg_ndo2db` VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
				}
			}
		}
	}
	
	function updateNdo2dbInDB ($id = NULL)	{
		if (!$id) return;
		updateNdo2db($id);
	}	
	
	function insertNdo2dbInDB ()	{
		$id = insertNdo2db();
		return ($id);
	}
	
	function insertNdo2db($ret = array())	{
		global $form, $pearDB, $oreon;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO `cfg_ndo2db` (" .
				"`description` , `ns_nagios_server` , `socket_type` , `socket_name` , `tcp_port` , `ndo2db_user` , `ndo2db_group` ," .
				"`db_servertype` , `db_host`, `db_name`, `db_port` , `db_prefix` , `db_user` , `db_pass` , " .
				"`max_timedevents_age` , `max_systemcommands_age` , `max_servicechecks_age` , `max_hostchecks_age` , " .
				"`max_eventhandlers_age` , `activate`) ";
		$rq .= "VALUES (";
		isset($ret["description"]) && $ret["description"] != NULL ? $rq .= "'".htmlentities($ret["description"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
		isset($ret["ns_nagios_server"]) && $ret["ns_nagios_server"] != NULL ? $rq .= "'".htmlentities($ret["ns_nagios_server"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["socket_type"]["socket_type"]) && $ret["socket_type"]["socket_type"] != NULL ? $rq .= "'".htmlentities($ret["socket_type"]["socket_type"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
       	isset($ret["socket_name"]) && $ret["socket_name"] != NULL ? $rq .= "'".htmlentities($ret["socket_name"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["tcp_port"]) && $ret["tcp_port"] != NULL ? $rq .= "'".htmlentities($ret["tcp_port"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
       	isset($ret["ndo2db_user"]) && $ret["ndo2db_user"] != NULL ? $rq .= "'".htmlentities($ret["ndo2db_user"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
       	isset($ret["ndo2db_group"]) && $ret["ndo2db_group"] != NULL ? $rq .= "'".htmlentities($ret["ndo2db_group"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["db_servertype"]) && $ret["db_servertype"] != NULL ? $rq .= "'".htmlentities($ret["db_servertype"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["db_host"]) && $ret["db_host"] != 2 ? $rq .= "'".$ret["db_host"]."',  "  : $rq .= "NULL, ";
        isset($ret["db_name"]) && $ret["db_name"] != 2 ? $rq .= "'".$ret["db_name"]."',  "  : $rq .= "NULL, ";
        isset($ret["db_port"]) && $ret["db_port"] != NULL ? $rq .= "'".htmlentities($ret["db_port"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
 	    isset($ret["db_prefix"]) && $ret["db_prefix"] != NULL ? $rq .= "'".htmlentities($ret["db_prefix"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["db_user"]) && $ret["db_user"] != NULL ? $rq .= "'".htmlentities($ret["db_user"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["db_pass"]) && $ret["db_pass"] != 2 ? $rq .= "'".$ret["db_pass"]."',  " : $rq .= "NULL, ";
        isset($ret["max_timedevents_age"]) && $ret["max_timedevents_age"] != 2 ? $rq .= "'".$ret["max_timedevents_age"]."',  " : $rq .= "NULL, ";
        isset($ret["max_systemcommands_age"]) && $ret["max_systemcommands_age"] != 2 ? $rq .= "'".$ret["max_systemcommands_age"]."',  " : $rq .= "NULL, ";
        isset($ret["max_servicechecks_age"]) && $ret["max_servicechecks_age"] != 2 ? $rq .= "'".$ret["max_servicechecks_age"]."',  " : $rq .= "NULL, ";
        isset($ret["max_hostchecks_age"]) && $ret["max_hostchecks_age"] != NULL ? $rq .= "'".htmlentities($ret["max_hostchecks_age"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["max_eventhandlers_age"]) && $ret["max_eventhandlers_age"] != NULL ? $rq .= "'".htmlentities($ret["max_eventhandlers_age"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["activate"]) && $ret["activate"]["activate"] != NULL ? $rq .= "'".$ret["activate"]["activate"]."')" : $rq .= "NULL )";
       	$DBRESULT =& $pearDB->query($rq);
		$DBRESULT =& $pearDB->query("SELECT MAX(id) FROM `cfg_ndo2db`");
		$ndo2db_id = $DBRESULT->fetchRow();
		$DBRESULT->free();
		return ($ndo2db_id["MAX(id)"]);
	}
	
	function updateNdo2db($id = null)	{
		if (!$id) return;
		global $form, $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `cfg_ndo2db` SET ";
        isset($ret["description"]) && $ret["description"] != NULL ? $rq .= "description = '".htmlentities($ret["description"], ENT_QUOTES, "UTF-8")."', " : $rq .= "description = NULL, ";
        isset($ret["ns_nagios_server"]) && $ret["ns_nagios_server"] != NULL ? $rq .= "ns_nagios_server = '".htmlentities($ret["ns_nagios_server"], ENT_QUOTES, "UTF-8")."', " : $rq .= "ns_nagios_server = NULL, ";
		isset($ret["socket_type"]) && $ret["socket_type"] != NULL ? $rq .= "socket_type = '".htmlentities($ret["socket_type"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "socket_type = NULL, ";
        isset($ret["socket_name"]) && $ret["socket_name"] != NULL ? $rq .= "socket_name = '".htmlentities($ret["socket_name"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "socket_name = NULL, ";
       	isset($ret["tcp_port"]) && $ret["tcp_port"] != NULL ? $rq .= "tcp_port = '".htmlentities($ret["tcp_port"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "tcp_port = NULL, ";
        isset($ret["ndo2db_user"]) && $ret["ndo2db_user"] != NULL ? $rq .= "ndo2db_user = '".htmlentities($ret["ndo2db_user"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "ndo2db_user = NULL, ";
        isset($ret["ndo2db_group"]) && $ret["ndo2db_group"] != NULL ? $rq .= "ndo2db_group = '".htmlentities($ret["ndo2db_group"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "ndo2db_group = NULL, ";
        isset($ret["db_servertype"]) && $ret["db_servertype"] != NULL ? $rq .= "db_servertype = '".htmlentities($ret["db_servertype"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "db_servertype = NULL, ";
        isset($ret["db_host"]) && $ret["db_host"] != NULL ? $rq .= "db_host = '".htmlentities($ret["db_host"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "db_host = NULL, ";
 	    isset($ret["db_name"]) && $ret["db_name"] != NULL ? $rq .= "db_name = '".htmlentities($ret["db_name"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "db_host = NULL, ";
 	    isset($ret["db_port"]) && $ret["db_port"] != NULL ? $rq .= "db_port = '".htmlentities($ret["db_port"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "db_port = NULL, ";
        isset($ret["db_prefix"]) && $ret["db_prefix"] != NULL ? $rq .= "db_prefix = '".htmlentities($ret["db_prefix"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "db_prefix = NULL, ";
        isset($ret["db_user"]) && $ret["db_user"] != 2 ? $rq .= "db_user = '".$ret["db_user"]."',  " : $rq .= "db_user = '2', ";
        isset($ret["db_pass"]) && $ret["db_pass"] != NULL ? $rq .= "db_pass = '".htmlentities($ret["db_pass"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "db_pass = NULL, ";
        isset($ret["max_timedevents_age"]) && $ret["max_timedevents_age"] != NULL ? $rq .= "max_timedevents_age = '".htmlentities($ret["max_timedevents_age"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "max_timedevents_age = NULL, ";
        isset($ret["max_systemcommands_age"]) && $ret["max_systemcommands_age"] != NULL ? $rq .= "max_systemcommands_age = '".htmlentities($ret["max_systemcommands_age"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "max_systemcommands_age = NULL, ";
        isset($ret["max_servicechecks_age"]) && $ret["max_servicechecks_age"] != NULL ? $rq .= "max_servicechecks_age = '".htmlentities($ret["max_servicechecks_age"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "max_servicechecks_age = NULL, ";
        isset($ret["max_hostchecks_age"]) && $ret["max_hostchecks_age"] != 2 ? $rq .= "max_hostchecks_age = '".$ret["max_hostchecks_age"]."',  " : $rq .= "max_hostchecks_age = '2', ";
        isset($ret["max_eventhandlers_age"]) && $ret["max_eventhandlers_age"] != NULL ? $rq .= "max_eventhandlers_age = '".htmlentities($ret["max_eventhandlers_age"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "max_eventhandlers_age = NULL, ";
       	$rq .= "activate = '".$ret["activate"]["activate"]."' ";
		$rq .= "WHERE id = '".$id."'";
		$DBRESULT =& $pearDB->query($rq);
	}
?>