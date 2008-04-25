<?php
/*
 * 
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

	set_time_limit(150000);

	$option = false;

	if ($argc == 2 && in_array($argv[1], array('-h', '--help')) ) {
	?>
	
	This is a command line PHP script with one option.
	
	  Usage:
	  <?php echo $argv[0]; ?> <option>
	
	  <option>
	  -d delete value in db and insert all file
	  -h or --help for this help
	<?
	} else if ($argc == 2 && in_array($argv[1], array('-d')) ) {
		$option = true;
	}

	require_once 'DB.php';	

	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path ."/cron/reporting/DB-Func.php");
	require_once($centreon_path ."/www/include/reporting/dashboard/common-Func.php");
	require_once($centreon_path ."/www/include/reporting/dashboard/DB-Func.php");

	global $pearDB, $pearDBO;

	/* Connect to Centreon DB */	
	$dsn = array(	'phptype'  => 'mysql',
			'username' => $conf_oreon['user'],
			'password' => $conf_oreon['password'],
			'hostspec' => $conf_oreon['host'],
			'database' => $conf_oreon['db']);

	$pearDB =& DB::connect($dsn, array('debug' => 2, 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE));
	if (PEAR::isError($pearDB)) 
		die("Connecting probems with centreon database : " . $pearDB->getMessage());
	$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);


	/* Connect to Centstorage DB */	
	$dsn = array(	'phptype'  => 'mysql',
			'username' => $conf_oreon['user'],
			'password' => $conf_oreon['password'],
			'hostspec' => $conf_oreon['host'],
			'database' => $conf_oreon['ods']);

	$pearDBO =& DB::connect($dsn, array('debug' => 2, 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE));
	if (PEAR::isError($pearDBO)) 
		die("Connecting probems with cent-storage database : " . $pearDBO->getMessage());
	$pearDBO->setFetchMode(DB_FETCHMODE_ASSOC);

	/*
	 *  End of Db connexion
	 */

	$res = $pearDB->query("SELECT `log_archive_path` FROM `cfg_nagios` WHERE `nagios_activate` = '1'");
	$nagios_cfg = $res->fetchRow();
	$NagiosPathArchive = $nagios_cfg["log_archive_path"];

	
	#################################
	######## clean up table  ########
	#################################
	if ($option){
		$res = $pearDBO->query("TRUNCATE TABLE `log_archive_file_name`");
		$res = $pearDBO->query("TRUNCATE TABLE `log_archive_host`");		
		$res = $pearDBO->query("TRUNCATE TABLE `log_archive_service`");
	}
	#################################

	$tablist = array();
	$h = array();
	$host_list = array();
	$res =& $pearDB->query('SELECT `host_name`, `host_id` FROM `host`');
	if (PEAR::isError($res)){
	  	die($res->getMessage());
	} else { 
	  	while ($h =& $res->fetchRow()){
			$tablist[$h["host_name"]] = array();
		    	$host_list[$h["host_name"]] = $h["host_id"];
	  	}
	}
	
	$service_list = array();
	$res =& $pearDB->query('SELECT `service_description`, `service_id` FROM `service`');
	if (PEAR::isError($res)){
	  	die($res->getMessage());
	} else { 
	  	while ($s =& $res->fetchRow()){
	  		$s["service_description"] = str_replace("#S#", "/", $s["service_description"]);
	  		$s["service_description"] = str_replace("#BS#", "\\", $s["service_description"]);	  		
	  		$service_list[$s["service_description"]] = $s["service_id"];
	  	}
	}	
	require_once $centreon_path . '/www/include/common/common-Func.php';

	$tableFile2 = array();
	if ($handle  = @opendir($NagiosPathArchive))	{
		while ($file = @readdir($handle))
			if (is_file($NagiosPathArchive."/$file"))	{
				preg_match("/nagios\-([0-9]*)\-([0-9]*)\-([0-9]*)\-([0-9]*).log/", $file, $matches);
				$time = mktime("0", "0", "0", $matches[1], $matches[2], $matches[3]) - 1;				
				if (!check_file_name_in_db($NagiosPathArchive."/$file"))
					$tableFile2[$NagiosPathArchive."/$file"] =  "  " . $time . " ";
			}
		@closedir($handle);
	}
	krsort($tableFile2);

	$tab_hosts = array();
	$tab_services = array();	
	$day_current_start = 0;
	$day_current_end = 0;

	foreach ($tableFile2 as $key => $time){
		if ($option)
			echo "Parsing : $key \n";
		parseFile($key, $time, $tab_hosts, $tab_services, $day_current_start, $day_current_end, false);
		insert_file_name_in_db($key);	
	}

	if ($day_current_start > 0)
		insert_in_db($key, $tab_hosts, $tab_services, $day_current_start, $day_current_end);
?>