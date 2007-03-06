<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf - Cedrick Facon

Adapted to Pear library Quickform & Template_PHPLIB by Merethis company, under direction of Cedrick Facon

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
	/*
	 * Set your path here
	 */
	$path_oreon = '/srv/oreon/';
	$NagiosPathArchive = '/srv/nagios/var/archives';

	require_once 'DB.php';	
	include_once($path_oreon . "/www/oreon.conf.php");
	require_once($path_oreon."www/include/reporting/dashboard/reporting-func.php");
	require_once($path_oreon."www/include/reporting/dashboard/simple-func.php");


	/* Connect to oreon DB */	
	$dsn = array(
		     'phptype'  => 'mysql',
		     'username' => $conf_oreon['user'],
		     'password' => $conf_oreon['password'],
		     'hostspec' => $conf_oreon['host'],
		     'database' => $conf_oreon['db'],
		     );

	$options = array(
			 'debug'       => 2,
			 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,
			 );

	$pearDB =& DB::connect($dsn, $options);
	if (PEAR::isError($pearDB)) 
		die("Connecting probems with oreon database : " . $pearDB->getMessage());

	$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);


#################################
######## clean up table  ########
#################################
/*
	$sql = "TRUNCATE TABLE `log_archive_file_name`";
	$res = $pearDB->query($sql);
	$sql = "TRUNCATE TABLE `log_archive_host`";
	$res = $pearDB->query($sql);
	$sql = "TRUNCATE TABLE `log_archive_service`";
	$res = $pearDB->query($sql);
*/
#################################
#################################
#################################




	function check_file_name_in_db($filename)
	{
		global $pearDB;
		$res = $pearDB->query("SELECT * FROM log_archive_file_name WHERE file_name = '".$filename."'");
		if ($res->numRows())
			return $res;
	}
	function 	insert_file_name_in_db($key)
	{
		global $pearDB;
		$date = time();
		$sql = "INSERT INTO `log_archive_file_name` (`id_log_file`, `file_name`, `date`)" .
				" VALUES(NULL , '$key','$date')";
		$res = $pearDB->query($sql);
				
		if (PEAR::isError($res)){			
			die($res->getMessage());		  
		  }
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
	  while ($s =& $res->fetchRow()){
	    $service_list[$s["service_description"]] = $s["service_id"];
	  }
	}	
	require_once $path_oreon . 'www/include/common/common-Func.php';


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


	function day_is_in_db($start_day, $end_day, $host_id)
	{
		global $pearDB;
		$sql = "SELECT * FROM `log_archive_host` WHERE `host_id` = ".$host_id." AND `date_start` >= ".$start_day." AND `date_end` <= ". $end_day; 
		$res = $pearDB->query($sql);
		if (PEAR::isError($res)){
		  die($res->getMessage());}
		if($res->numRows())
			return true;
		return false;					
	}
	
	function insert_in_db($tab_hosts, $tab_services, $day_current_start, $day_current_end)
	{
		global $host_list;
		global $service_list;
		global $pearDB;

		#
		## Hosts in db
		#
		if(is_array($tab_hosts))
		foreach($tab_hosts as $host => $htab)
		{
			if (isset($host_list[trim($host)]) && isset($htab["current_state"]))
			{
				#
				## last host alert
				#
				if(!strncmp($htab["current_state"], "UP", 2))
					$htab["timeUP"] += ($day_current_end - $htab["current_time"]);
				elseif(!strncmp($htab["current_state"], "DOWN", 4))
					$htab["timeDOWN"] += ($day_current_end - $htab["current_time"]);
				elseif(!strncmp($htab["current_state"], "UNREACHABLE", 11))
					$htab["timeUNREACHABLE"] += ($day_current_end - $htab["current_time"]);
				else
					$htab["timeNONE"] += ($day_current_end - $htab["current_time"]);

				#
				## insert in db the host time
				#		
				$host_id = $host_list[trim($host)];
				$Upsc =$htab["timeUP"];
				$UPnbEvent =$htab["UPnbEvent"];
				$DOWNsc =$htab["timeDOWN"];
				$DOWNnbEvent =$htab["DOWNnbEvent"];
				$UNREACHABLEsc = $htab["timeUNREACHABLE"];
				$UNREACHABLEnbEvent = $htab["UNREACHABLEnbEvent"];
	
				$sql = "INSERT INTO `log_archive_host` ( `log_id` , `host_id` ," .
						" `UPTimeScheduled` , `UPnbEvent` ,`UPTimeAverageAck` ,`UPTimeAverageRecovery` ," .
						" `DOWNTimeScheduled` , `DOWNnbEvent` ,`DOWNTimeAverageAck` ,`DOWNTimeAverageRecovery` ," .
						" `UNREACHABLETimeScheduled` , `UNREACHABLEnbEvent` ,`UNREACHABLETimeAverageAck` ,`UNREACHABLETimeAverageRecovery` ," .
						" `date_end`, `date_start` ) VALUES" .
					" (NULL , '$host_id'," .
					" '$Upsc', $UPnbEvent,'0','0'," .
					" '$DOWNsc', $DOWNnbEvent,'0','0'," .
					" '$UNREACHABLEsc', $UNREACHABLEnbEvent,'0','0'," .
					" '$day_current_end', '$day_current_start')";
	
				$res = $pearDB->query($sql);
				if (PEAR::isError($res)){
				  die($res->getMessage());}
				  
			}
		}

		#
		## Services in db
		#
		if(is_array($tab_services))
		foreach($tab_services as $svc => $htabsvc)
		{
			if (isset($service_list[trim($svc)]))
			foreach($htabsvc as $host => $htab)
			{
				if (isset($host_list[trim($host)]) && $htab["service_id"])
				{
					#
					## last service alert
					#	
					if(!strncmp($htab["current_state"], "OK", 2))
						$htab["timeOK"] += ($day_current_end-$htab["current_time"]) ;
					elseif(!strncmp($htab["current_state"], "WARNING", 4))
						$htab["timeWARNING"] += ($day_current_end-$htab["current_time"]) ;
					elseif(!strncmp($htab["current_state"], "UNKNOWN", 11))
						$htab["timeUNKNOWN"] += ($day_current_end-$htab["current_time"]) ;
					elseif(!strncmp($htab["current_state"], "CRITICAL", 11))
						$htab["timeCRITICAL"] += ($day_current_end-$htab["current_time"]) ;
					else
						$htab["timeNONE"] += ($day_current_end-$htab["current_time"]) ;
		
					$host_id = $host_list[trim($host)];
					$service_id = $htab["service_id"];
					$OKsc =$htab["timeOK"];
					$OKnbEvent =$htab["OKnbEvent"];
					$WARNINGsc =$htab["timeWARNING"];
					$WARNINGnbEvent =$htab["WARNINGnbEvent"];
					$UNKNOWNsc = $htab["timeUNKNOWN"];
					$UNKNOWNnbEvent = $htab["UNKNOWNnbEvent"];
					$CRITICALsc = $htab["timeCRITICAL"];			
					$CRITICALnbEvent = $htab["CRITICALnbEvent"];			
		
					$sql = "INSERT INTO `log_archive_service` ( `log_id` , `host_id`, `service_id` ," .
							" `OKTimeScheduled` , `OKnbEvent` ,`OKTimeAverageAck` ,`OKTimeAverageRecovery` ," .
							" `WARNINGTimeScheduled` , `WARNINGnbEvent` ,`WARNINGTimeAverageAck` ,`WARNINGTimeAverageRecovery` ," .
							" `UNKNOWNTimeScheduled` , `UNKNOWNnbEvent` ,`UNKNOWNTimeAverageAck` ,`UNKNOWNTimeAverageRecovery` ," .
							" `CRITICALTimeScheduled` , `CRITICALnbEvent` ,`CRITICALTimeAverageAck` ,`CRITICALTimeAverageRecovery` ," .
							" `date_end`, `date_start` ) VALUES" .
						" (NULL , '$host_id', '$service_id'," .
						" '$OKsc', $OKnbEvent,'0','0'," .
						" '$WARNINGsc', $WARNINGnbEvent,'0','0'," .
						" '$UNKNOWNsc', $UNKNOWNnbEvent,'0','0'," .
						" '$CRITICALsc', $CRITICALnbEvent,'0','0'," .
						" '$day_current_end', '$day_current_start')";
		
					$result = $pearDB->query($sql);
					if (PEAR::isError($result)){
					  die($result->getMessage());}
				}
			}
		}
	}


	$tab_hosts = array();
	$tab_services = array();
	
	$day_current_start = 0;
	$day_current_end = 0;
	
	foreach($tableFile2 as $key => $time)
	{
		insert_file_name_in_db($key);
		parseFile($key, $time, $tab_hosts, $tab_services,$day_current_start, $day_current_end, false);
	}

	//insert_pending();


?>