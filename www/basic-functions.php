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
	/*
	 * Functions
	 */
	function get_path($abs_path){
		$len = strlen($abs_path);
		for ($i = 0, $flag = 0; $i < $len; $i++){
			if ($flag == 3)
				break;
			if ($abs_path{$i} == "/")
				$flag++;
		}
		return substr($abs_path, 0, $i);
	}
	
	function get_child($id_page, $lcaTStr){
		global $pearDB;
		
		if ($lcaTStr != "")
			$rq = "	SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt 
					FROM topology 
					WHERE  topology_page IN ($lcaTStr) 
					AND topology_parent = '".$id_page."' AND topology_page IS NOT NULL AND topology_show = '1' 
					ORDER BY topology_order, topology_group "; 
		else
			$rq = "	SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt 
					FROM topology 
					WHERE  topology_parent = '".$id_page."' AND topology_page IS NOT NULL AND topology_show = '1' 
					ORDER BY topology_order, topology_group "; 
			
		$DBRESULT =& $pearDB->query($rq);		
		$redirect =& $DBRESULT->fetchRow();
		return $redirect;
	}

	function reset_search_page($url){
		# Clean Vars
		global $oreon;
		if (!isset($url))
			return;
		if (isset($_GET["search"]) && isset($oreon->historySearch[$url]) && $_GET["search"] != $oreon->historySearch[$url]){		
			$_POST["num"] = 0;
			$_GET["num"] = 0;
		}	
	}

	function get_my_first_allowed_root_menu($lcaTStr){
		global $pearDB;
		
		if ($lcaTStr !=  "")
			$rq = "	SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt 
					FROM topology 
					WHERE topology_page IN ($lcaTStr) 
					AND topology_parent IS NULL AND topology_page IS NOT NULL AND topology_show = '1' 
					LIMIT 1"; 
		else
			$rq = "	SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt 
					FROM topology 
					WHERE topology_parent IS NULL AND topology_page IS NOT NULL AND topology_show = '1' 
					LIMIT 1";
		$DBRESULT =& $pearDB->query($rq);
		$root_menu = array();
		if ($DBRESULT->numRows())
			$root_menu =& $DBRESULT->fetchRow();
		return $root_menu;
	}
	
	function getSkin($pearDB) {
		$DBRESULT =& $pearDB->query("SELECT * FROM `options` WHERE `key` = 'template' LIMIT 1");
		$data =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		return "./Themes/".$data["value"]."/";
	}
	
?>