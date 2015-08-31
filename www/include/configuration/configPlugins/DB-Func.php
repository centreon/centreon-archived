<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
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