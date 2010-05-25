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
		
	function return_plugin_list($rep, $search = NULL){
		global $oreon;
		$plugins = array();
		$is_not_a_plugin = array("."=>".", ".."=>"..", "oreon.conf"=>"oreon.conf", "oreon.pm"=>"oreon.pm", "utils.pm"=>"utils.pm", "negate"=>"negate");
		if (substr($oreon->optGen["nagios_path_plugins"], -1) == "/" && isset($rep[0]) && $rep[0] == "/")
			$rep = substr($rep, 1);
		$handle = opendir($oreon->optGen["nagios_path_plugins"].$rep);
		while (false !== ($filename = readdir($handle))){
			if (!is_dir($oreon->optGen["nagios_path_plugins"].$rep."/".$filename) && !array_key_exists($filename, $is_not_a_plugin) && substr($filename, -1)!= "~"){
				if (!$search || $search == "" || ($search && stristr($filename, $search))) {
					$key = substr($oreon->optGen["nagios_path_plugins"].$rep."/".$filename, strlen($oreon->optGen["nagios_path_plugins"].$rep));
					$plugins[$key] = $key;
				}
			}
		}
		ksort($plugins);
		closedir($handle);
		return ($plugins);
	}
	
	function return_plugin_dir($rep){
		global $oreon;
		$plugins_rep = array("/"=>"/");
		$is_not_a_plugin = array("."=>".", ".."=>"..", "oreon.conf"=>"oreon.conf", "oreon.pm"=>"oreon.pm", "utils.pm"=>"utils.pm", "negate"=>"negate");
		$handle = opendir($oreon->optGen["nagios_path_plugins"].$rep);
		while (false !== ($filename = readdir($handle))){
			if (is_dir($oreon->optGen["nagios_path_plugins"].$rep.$filename) && !array_key_exists($filename, $is_not_a_plugin) && substr($filename, -1)!= "~"){
				$key = substr($oreon->optGen["nagios_path_plugins"].$rep."/".$filename, strlen($oreon->optGen["nagios_path_plugins"].$rep));
				$plugins_rep[$key] = $key;
			}
		}
		closedir($handle);
		return ($plugins_rep);
	}
?>