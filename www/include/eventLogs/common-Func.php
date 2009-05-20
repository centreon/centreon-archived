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
  
	function get_user_param($user_id, $pearDB){
		$tab_row = array();
		$DBRESULT =& $pearDB->query("SELECT * FROM contact_param where cp_contact_id = '".$user_id."'");		
		while( $row =& $DBRESULT->fetchRow())
			$tab_row[$row["cp_key"]] = $row["cp_value"];
		return $tab_row;
	}

	function set_user_param($user_id, $pearDB, $key, $value){
		$DBRESULT =& $pearDB->query("SELECT * FROM contact_param WHERE cp_contact_id like '".$user_id."' AND cp_key like '".$key."'");		
		if ($DBRESULT->numRows()){
			$DBRESULT =& $pearDB->query("UPDATE contact_param set cp_value ='".$value."' where cp_contact_id like '".$user_id."' AND cp_key like '".$key."' ");		
		} else {
			$DBRESULT =& $pearDB->query("INSERT INTO `contact_param` ( `cp_value`, `cp_contact_id`, `cp_key`) VALUES ('".$value."', '".$user_id."', '".$key."')");		
		}
	}
 
 	function getMyHostIDService($svc_id = NULL)	{
		if (!$svc_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT host_id FROM host h, host_service_relation hs WHERE h.host_id = hs.host_host_id AND hs.service_service_id = '".$svc_id."'");
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["host_id"];
		}
		return NULL;
	}
 	
?>