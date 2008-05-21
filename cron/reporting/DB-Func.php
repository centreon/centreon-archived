<?php
/* 
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * safety, contents, performance, merchantability, non-infringement or suitability for
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if OREON has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 * 
 */

	function day_is_in_db($start_day, $end_day, $host_id){
		global $pearDBO;
		$res = $pearDBO->query(	"SELECT * FROM `log_archive_host` WHERE `host_id` = '".$host_id."' AND `date_start` >= '$start_day' AND `date_end` <= '$end_day'" );
		if (PEAR::isError($res))
		  	die($res->getMessage());
		if ($res->numRows())
			return true;
		return false;					
	}
	
	function insert_in_db($file, $tab_hosts, $tab_services, $day_current_start, $day_current_end){
		global $host_list, $service_list, $pearDB, $centreon_path;
		
		## Api insert in db type
		$directory = "$centreon_path/cron/reporting/api";
		if ($handle  = opendir($directory))	{
			while ($file = @readdir($handle)){
				if (is_file("$directory/$file"))
					include("$directory/$file");
			}
			@closedir($handle);
		}
	}

	function check_file_name_in_db($filename){
		global $pearDBO;
		$res = $pearDBO->query("SELECT * FROM log_archive_file_name WHERE file_name = '".$filename."'");
		if ($res->numRows())
			return $res;
	}
	
	function insert_file_name_in_db($key){
		global $pearDBO;
		$date = time();
		$res = $pearDBO->query("INSERT INTO `log_archive_file_name` (`file_name`, `date`) VALUES ('$key','$date')");
		if (PEAR::isError($res)){
			die($res->getMessage());
		}
	}

?>