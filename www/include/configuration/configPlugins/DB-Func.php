<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (!isset($oreon))
		exit();
		
	function return_plugin_list($rep){
		global $oreon;
		$plugins = array();
		$is_not_a_plugin = array("."=>".", ".."=>"..", "oreon.conf"=>"oreon.conf", "oreon.pm"=>"oreon.pm", "utils.pm"=>"utils.pm", "negate"=>"negate");
		if (substr($oreon->optGen["nagios_path_plugins"], -1) == "/" && isset($rep[0]) && $rep[0] == "/")
			$rep = substr($rep, 1);
		$handle = opendir($oreon->optGen["nagios_path_plugins"].$rep);
		while (false !== ($filename = readdir($handle))){
			if (!is_dir($oreon->optGen["nagios_path_plugins"].$rep."/".$filename) && !array_key_exists($filename, $is_not_a_plugin) && substr($filename, -1)!= "~"){
				$key = substr($oreon->optGen["nagios_path_plugins"].$rep."/".$filename, strlen($oreon->optGen["nagios_path_plugins"].$rep));
				$plugins[$key] = $key;
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