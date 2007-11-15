<?
/*
 * 
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * OREON makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * safety, contents, performance, merchantability, non-infringement or suitability for
 * any particular or intended purpose of the Software found on the OREON web site.
 * In no event will OREON be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if OREON has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@oreon-project.org
 * 
 */

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
	} else
		;

	$path_oreon = '@OREON_PATH@';
	require_once 'DB.php';	

	include_once($path_oreon . "/www/centreon.conf.php");
	require_once($path_oreon ."/www/include/reporting/dashboard/reporting-func.php");
	require_once($path_oreon ."/www/include/reporting/dashboard/simple-func.php");

	/* Connect to oreon DB */	
	$dsn = array(	'phptype'  => 'mysql',
				    'username' => $conf_oreon['user'],
				    'password' => $conf_oreon['password'],
				    'hostspec' => $conf_oreon['host'],
				    'database' => $conf_oreon['db']);

	$options = array(	'debug'       => 2,
			 			'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE);

	$pearDB =& DB::connect($dsn, $options);
	if (PEAR::isError($pearDB)) 
		die("Connecting probems with oreon database : " . $pearDB->getMessage());
	$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);

	$res = $pearDB->query("SELECT log_archive_path FROM cfg_nagios WHERE nagios_activate = '1'");
	$res->fetchInto($nagios_cfg);
	$NagiosPathArchive = $nagios_cfg["log_archive_path"];

	set_time_limit(150000);
	
	#################################
	######## clean up table  ########
	#################################
	if ($option){
		$sql = "TRUNCATE TABLE `log_archive_file_name`";
		$res = $pearDB->query($sql);
		$sql = "TRUNCATE TABLE `log_archive_host`";
		$res = $pearDB->query($sql);
		$sql = "TRUNCATE TABLE `log_archive_service`";
		$res = $pearDB->query($sql);
	}
	#################################
	#################################
	#################################

	function check_file_name_in_db($filename){
		global $pearDB;
		$res = $pearDB->query("SELECT * FROM log_archive_file_name WHERE file_name = '".$filename."'");
		if ($res->numRows())
			return $res;
	}
	
	function insert_file_name_in_db($key){
		global $pearDB;
		$date = time();
		$sql = "INSERT INTO `log_archive_file_name` (`id_log_file`, `file_name`, `date`) VALUES(NULL , '$key','$date')";
		$res = $pearDB->query($sql);
		if (PEAR::isError($res)){die($res->getMessage());}
	}

	$tablist = array();
	$h = array();
	$host_list = array();
	$res =& $pearDB->query('SELECT host_name, host_id FROM `host`');
	if (PEAR::isError($res)){
	  	die($res->getMessage());
	} else { 
	  	while ($h =& $res->fetchRow()){
			$tablist[$h["host_name"]] = array();
	    	$host_list[$h["host_name"]] = $h["host_id"];
	  	}
	}
	
	$service_list = array();
	$res =& $pearDB->query('SELECT service_description, service_id FROM `service`');
	if (PEAR::isError($res)){
	  	die($res->getMessage());
	} else { 
	  	while ($s =& $res->fetchRow())
	  		$service_list[$s["service_description"]] = $s["service_id"];
	}	
	require_once $path_oreon . '/www/include/common/common-Func.php';

	$tableFile2 = array();
	if ($handle  = @opendir($NagiosPathArchive))	{
		while ($file = @readdir($handle))
			if (is_file($NagiosPathArchive."/$file"))	{
				preg_match("/nagios\-([0-9]*)\-([0-9]*)\-([0-9]*)\-([0-9]*).log/", $file, $matches);
				$time = mktime("0", "0", "0", $matches[1], $matches[2], $matches[3]) - 1;				
				if(!check_file_name_in_db($NagiosPathArchive."/$file"))
					$tableFile2[$NagiosPathArchive."/$file"] =  "  " . $time . " ";
			}
		@closedir($handle);
	}
	krsort($tableFile2);


	function day_is_in_db($start_day, $end_day, $host_id){
		global $pearDB;
		$sql = "SELECT * FROM `log_archive_host` WHERE `host_id` = ".$host_id." AND `date_start` >= ".$start_day." AND `date_end` <= ". $end_day; 
		$res = $pearDB->query($sql);
		if (PEAR::isError($res)){
		  die($res->getMessage());}
		if($res->numRows())
			return true;
		return false;					
	}
	
	function insert_in_db($file, $tab_hosts, $tab_services, $day_current_start, $day_current_end){
		global $host_list, $service_list, $pearDB, $path_oreon;
		
		## Api insert in db type
		if ($handle  = opendir("$path_oreon/cron/reporting/api"))	{
			while ($file = @readdir($handle)){
				if (is_file("$path_oreon/cron/reporting/api/$file"))
					include("$path_oreon/cron/reporting/api/$file");
			}
			@closedir($handle);
		}
	}

	$tab_hosts = array();
	$tab_services = array();	
	$day_current_start = 0;
	$day_current_end = 0;

	foreach($tableFile2 as $key => $time){
		if ($option)
			echo $key . "\n";	
		parseFile($key, $time, $tab_hosts, $tab_services, $day_current_start, $day_current_end, false);
		insert_file_name_in_db($key);	
	}

	if($day_current_start > 0){
		insert_in_db($key, $tab_hosts, $tab_services, $day_current_start, $day_current_end);
	//	print "insert_in_db";
	}

?>