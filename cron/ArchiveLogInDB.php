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

	function insert_pending()
	{
		global $pearDB;
		$sql = "SELECT * FROM `log_archive_host` WHERE `date_start` = (SELECT min(`date_start`) FROM `log_archive_host`)"; 
		$res = $pearDB->query($sql);
		if (PEAR::isError($res)){
		  die($res->getMessage());}


		$time = time();
		$start_date_day = $time-1;
		$end_date_day = $time;
		while ($h =& $res->fetchRow()){
			$start_date_day = my_getStartDay($h["date_start"]);
			$end_date_day = $start_date_day + 86400;
		  }
		$sd = $start_date_day;
		$ed = $end_date_day;
		
		$sql = "SELECT distinct(host_id) FROM `log_archive_host`";
		$res = $pearDB->query($sql);
		if (PEAR::isError($res)){
		  die($res->getMessage());}

		while ($res->fetchInto($h)){			
			while($end_date_day < $time){
				## if day doesn't exist in bdd
				$host_id = $h["host_id"];
				if(!day_is_in_db($start_date_day, $end_date_day,$host_id)){
					echo date("d/m/Y H:i:s", $start_date_day)." -->";
					echo date("d/m/Y H:i:s", $end_date_day)."\n";

					$sql = "INSERT INTO `log_archive_host` ( `log_id` , `host_id` ," .
							" `UPTimeScheduled` , `UPTimeUnScheduled` ," .
							" `DOWNTimeScheduled` , `DOWNTimeUnScheduled` ," .
							" `UNREACHABLETimeScheduled` , `UNREACHABLETimeUnScheduled` ," .
							" `UNDETERMINATETimeScheduled` , `UNDETERMINATETimeUnScheduled` ," .
							" `date_end`, `date_start` ) VALUES" .
						" (NULL , '$host_id'," .
						" '0', '0'," .
						" '0', '0'," .
						" '0', '0'," .
						" '86400', '0'," .
						" '$end_date_day', '$start_date_day')";
		
					$res2 = $pearDB->query($sql);
					if (PEAR::isError($res2)){
					  die($res2->getMessage());}

		
				}			
		
				## next day
				$start_date_day = my_getNextStartDay($start_date_day);
				$end_date_day = $start_date_day+86400;
			}
			$start_date_day = $sd;
			$end_date_day = $ed;			
		}
	}

	
	function insert_in_db($tab_hosts, $tab_services, $day_current_start, $day_current_end)
	{
		global $host_list;
		global $service_list;
		global $pearDB;
		/* pensez a verifier si day existe deja dans le cas ou les log sont melangé... */


		#
		## Hosts in db
		#
		if(is_array($tab_hosts))
		foreach($tab_hosts as $host => $htab)
		{
			if (isset($host_list[trim($host)]))
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
				$UpUnsc =$htab["timeUP"];
				$DOWNsc =$htab["timeDOWN"];
				$DOWNUnsc =$htab["timeDOWN"];
				$UNREACHABLEsc = $htab["timeUNREACHABLE"];
				$UNREACHABLEUnsc = $htab["timeUNREACHABLE"];
				$NONEsc = ($day_current_end - $day_current_start) - ($Upsc+$DOWNsc+$UNREACHABLEsc);
				$NONEUnsc = $htab["timeNONE"];
	
				$sql = "INSERT INTO `log_archive_host` ( `log_id` , `host_id` ," .
						" `UPTimeScheduled` , `UPTimeUnScheduled` ," .
						" `DOWNTimeScheduled` , `DOWNTimeUnScheduled` ," .
						" `UNREACHABLETimeScheduled` , `UNREACHABLETimeUnScheduled` ," .
						" `UNDETERMINATETimeScheduled` , `UNDETERMINATETimeUnScheduled` ," .
						" `date_end`, `date_start` ) VALUES" .
					" (NULL , '$host_id'," .
					" '$Upsc', '$UpUnsc'," .
					" '$DOWNsc', '$DOWNUnsc'," .
					" '$UNREACHABLEsc', '$UNREACHABLEUnsc'," .
					" '$NONEsc', '$NONEUnsc'," .
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
				if (isset($host_list[trim($host)]))
				{
					#
					## last service alert
					#	
					if(!strncmp($htab["current_state"], "OK", 2))
						$htab["timeOK"] += ($day_current_end-$htab["current_time"]) + 1;
					elseif(!strncmp($htab["current_state"], "WARNING", 4))
						$htab["timeWARNING"] += ($day_current_end-$htab["current_time"]) + 1;
					elseif(!strncmp($htab["current_state"], "UNKNOWN", 11))
						$htab["timeUNKNOWN"] += ($day_current_end-$htab["current_time"]) + 1;
					elseif(!strncmp($htab["current_state"], "CRITICAL", 11))
						$htab["timeCRITICAL"] += ($day_current_end-$htab["current_time"]) + 1;
					else
						$htab["timeNONE"] += ($day_current_end-$htab["current_time"]) + 1; // ?? calcul des none et autre pending


		if($htab["timeOK"] < 0){
			//echo "-->negative\n";
			exit();
		}
		
					$host_id = $host_list[trim($host)];
					
					$service_id = $htab["service_id"];						
					$OKsc =$htab["timeOK"];
					$OKUnsc =$htab["timeOK"];
					$WARNINGsc =$htab["timeWARNING"];
					$WARNINGUnsc =$htab["timeWARNING"];
					$UNKNOWNsc = $htab["timeUNKNOWN"];
					$UNKNOWNUnsc = $htab["timeUNKNOWN"];
					$CRITICALsc = $htab["timeCRITICAL"];
					$CRITICALUnsc = $htab["timeCRITICAL"];
					$NONEsc = $htab["timeNONE"];
					$NONEUnsc = $htab["timeNONE"];
		
					$sql = "INSERT INTO `log_archive_service` ( `log_id` , `host_id`, `service_id` ," .
							" `OKTimeScheduled` , `OKTimeUnScheduled` ," .
							" `WARNINGTimeScheduled` , `WARNINGTimeUnScheduled` ," .
							" `UNKNOWNTimeScheduled` , `UNKNOWNTimeUnScheduled` ," .
							" `CRITICALTimeScheduled` , `CRITICALTimeUnScheduled` ," .
							"`UNDETERMINATETimeScheduled` ,`UNDETERMINATETimeUnScheduled` ," .
							" `date_end`, `date_start` ) VALUES" .
						" (NULL , '$host_id', '$service_id'," .
						" '$OKsc', '$OKUnsc'," .
						" '$WARNINGsc', '$WARNINGUnsc'," .
						" '$UNKNOWNsc', '$UNKNOWNUnsc'," .
						" '$CRITICALsc', '$CRITICALUnsc'," .
						" '$NONEsc', '$NONEUnsc'," .
						" '$day_current_end', '$day_current_start')";
		
					$result = $pearDB->query($sql);
					if (PEAR::isError($res)){
					  die($res->getMessage());}
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