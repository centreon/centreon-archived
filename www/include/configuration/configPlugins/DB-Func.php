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