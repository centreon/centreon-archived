<?php
/*
 * Copyright 2005-2009 MERETHIS
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
		$DBRESULT =& $pearDB->query("SELECT name, id FROM `nagios_server` WHERE `name` = '".htmlentities($name, ENT_QUOTES)."'");
		$ndomod =& $DBRESULT->fetchRow();
		if ($DBRESULT->numRows() >= 1 && $ndomod["id"] == $id)#Modif case	
			return true;
		else if ($DBRESULT->numRows() >= 1 && $ndomod["id"] != $id)#Duplicate entry
			return false;
		else
			return true;
	}	
	
	function enableServerInDB ($id = null)	{
		global $pearDB;
		
		if (!$id) 
			return;
		$DBRESULT =& $pearDB->query("UPDATE `nagios_server` SET `ns_activate` = '1' WHERE id = '".$id."'");		
	}
	
	function disableServerInDB ($id = null)	{
		if (!$id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE `nagios_server` SET `ns_activate` = '0' WHERE id = '".$id."'");
	}
	
	function deleteServerInDB ($server = array())	{
		global $pearDB;
		foreach($server as $key => $value)	{
			$DBRESULT =& $pearDB->query("DELETE FROM `nagios_server` WHERE id = '".$key."'");
		}
	}
	
	function multipleServerInDB ($server = array(), $nbrDup = array())	{
		global $pearDB;
		
		foreach ($server as $key => $value)	{
			$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` WHERE id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["id"] = '';
			$row["ns_activate"] = '0';
			$row["localhost"] = '0';
			$DBRESULT->free();
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "name" ? ($server_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testExistence($server_name))	{
					$val ? $rq = "INSERT INTO `nagios_server` VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
				}
			}
		}
	}
	
	function updateServerInDB ($id = NULL)	{
		if (!$id) 
			return;
		updateServer($id);
	}	
	
	function insertServerInDB ()	{
		$id = insertServer();
		return ($id);
	}
	
	function insertServer($ret = array())	{
		global $form, $pearDB, $oreon;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO `nagios_server` (`name` , `localhost` , `ns_ip_address`, `ssh_port`, `nagios_bin`, `nagiostats_bin` , `init_script`, `nagios_perfdata` , `ssh_private_key`, `ns_activate`) ";
		$rq .= "VALUES (";
		isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "'".htmlentities($ret["name"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["localhost"]["localhost"]) && $ret["localhost"]["localhost"] != NULL ? $rq .= "'".htmlentities($ret["localhost"]["localhost"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
        isset($ret["ns_ip_address"]) && $ret["ns_ip_address"] != NULL ? $rq .= "'".htmlentities($ret["ns_ip_address"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
        isset($ret["ssh_port"]) && $ret["ssh_port"] != NULL ? $rq .= "'".htmlentities($ret["ssh_port"], ENT_QUOTES)."',  " : $rq .= "'22', ";
        isset($ret["nagios_bin"]) && $ret["nagios_bin"] != NULL ? $rq .= "'".htmlentities($ret["nagios_bin"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
        isset($ret["nagiostats_bin"]) && $ret["nagiostats_bin"] != NULL ? $rq .= "'".htmlentities($ret["nagiostats_bin"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
        isset($ret["init_script"]) && $ret["init_script"] != NULL ? $rq .= "'".htmlentities($ret["init_script"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
        isset($ret["nagios_perfdata"]) && $ret["nagios_perfdata"] != NULL ? $rq .= "'".htmlentities($ret["nagios_perfdata"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
        isset($ret["ssh_private_key"]) && $ret["ssh_private_key"] != NULL ? $rq .= "'".htmlentities($ret["ssh_private_key"], ENT_QUOTES)."',  " : $rq .= "NULL, ";
        isset($ret["ns_activate"]["ns_activate"]) && $ret["ns_activate"]["ns_activate"] != 2 ? $rq .= "'".$ret["ns_activate"]["ns_activate"]."'  "  : $rq .= "NULL)";
       	$rq .= ")";
       	$DBRESULT =& $pearDB->query($rq);
		$DBRESULT =& $pearDB->query("SELECT MAX(id) FROM `nagios_server`");
		$ndomod_id = $DBRESULT->fetchRow();
		$DBRESULT->free();
		return ($ndomod_id["MAX(id)"]);
	}
	
	function updateServer($id = null)	{
		global $form, $pearDB;
		if (!$id) 
			return;
		$ret = array();
		$ret = $form->getSubmitValues();
		if ($ret["localhost"]["localhost"] == 1){
			$DBRESULT =& $pearDB->query("UPDATE `nagios_server` SET `localhost` = '0'");
		}
		$rq = "UPDATE `nagios_server` SET ";
        isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "name = '".htmlentities($ret["name"], ENT_QUOTES)."', " : $rq .= "name = NULL, ";
        isset($ret["localhost"]["localhost"]) && $ret["localhost"]["localhost"] != NULL ? $rq .= "localhost = '".htmlentities($ret["localhost"]["localhost"], ENT_QUOTES)."', " : $rq .= "localhost = NULL, ";
		isset($ret["ns_ip_address"]) && $ret["ns_ip_address"] != NULL ? $rq .= "ns_ip_address = '".htmlentities($ret["ns_ip_address"], ENT_QUOTES)."',  " : $rq .= "ns_ip_address = NULL, ";
        isset($ret["ssh_port"]) && $ret["ssh_port"] != NULL ? $rq .= "ssh_port = '".htmlentities($ret["ssh_port"], ENT_QUOTES)."',  " : $rq .= "ssh_port = '22', ";
        isset($ret["init_script"]) && $ret["init_script"] != NULL ? $rq .= "init_script = '".htmlentities($ret["init_script"], ENT_QUOTES)."',  " : $rq .= "init_script = NULL, ";
        isset($ret["nagios_bin"]) && $ret["nagios_bin"] != NULL ? $rq .= "nagios_bin = '".htmlentities($ret["nagios_bin"], ENT_QUOTES)."',  " : $rq .= "nagios_bin = NULL, ";
        isset($ret["nagiostats_bin"]) && $ret["nagiostats_bin"] != NULL ? $rq .= "nagiostats_bin = '".htmlentities($ret["nagiostats_bin"], ENT_QUOTES)."',  " : $rq .= "nagiostats_bin = NULL, ";
        isset($ret["nagios_perfdata"]) && $ret["nagios_perfdata"] != NULL ? $rq .= "nagios_perfdata = '".htmlentities($ret["nagios_perfdata"], ENT_QUOTES)."',  " : $rq .= "nagios_perfdata = NULL, ";
        isset($ret["ssh_private_key"]) && $ret["ssh_private_key"] != NULL ? $rq .= "ssh_private_key = '".htmlentities($ret["ssh_private_key"], ENT_QUOTES)."',  " : $rq .= "ssh_private_key = NULL, ";
        $rq .= "ns_activate = '".$ret["ns_activate"]["ns_activate"]."' ";
		$rq .= "WHERE id = '".$id."'";
		$DBRESULT =& $pearDB->query($rq);
	}
?>