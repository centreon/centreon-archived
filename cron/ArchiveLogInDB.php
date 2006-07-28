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
	$path_oreon = '/usr/local/oreon/';
	$NagiosPathArchive = "/var/log/nagios/archives";

	function getLogData($time_event, $host, $service, $status, $output, $type){
		global $lang;
		$tab["time"] = date($lang["header_format"], $time_event);
		$tab["timeb"] = $time_event;
		$tab["host"] = $host;
		$tab["service"] = $service;
		$tab["status"] = $status;
		$tab["output"] = $output;
		$tab["type"] = $type;
		return $tab ;
	}

	require_once 'DB.php';	
	include_once($path_oreon . "/www/oreon.conf.php");
	

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

function trim_value(&$value)
{
   $value = trim($value);
}



function parseFile($file,$end_time){
	$start_time = 0;
	$log = NULL;
	$matches = "";

	if (file_exists($file) && !($log = fopen($file, "r")))
		echo "pel_cant_open" . $file . "<br>";
	$tab_log = array();	
	$tab_svc_log = array();
	$tablist = array();
	$tab_host = array();
	$tab_services = array();
	$res1 = array();	

	if ($log)
		for ($a=0, $b= 0, $i = 0; $str = fgets($log); $i++){
			if (preg_match("/^\[([0-9]*)\] (.+)/", $str, $matches)){				
				$time_event = $matches[1];
				$res = preg_split("/:/", $matches[2], 2);
			if (isset($res[1]))
				$res1 = preg_split("/;/", $res[1]);
			$type = $res[0];
				array_walk($res1, 'trim_value');


			#
			## find the log's start time
			#				
			if ($i == 0)// take start time
			$start_time = $time_event;			



			
			if (!strncmp($type, "LOG ROTATION", 12))
			{
				if($res1[0] == "DAILY")
					$start_time = $end_time - (24*60*60);
				else
					$start_time = $time_event;
			}						
			#
			## host
			#			
			else if (!strncmp($type, "CURRENT HOST STATE", 18) || !strncmp($type, "INITIAL HOST STATE", 18)){
				$tab_host[$res1[0]] = array();
				$tab_host[$res1[0]]["current_time"] = $start_time;
				$tab_host[$res1[0]]["current_state"] = $res1[1];
				$tab_host[$res1[0]]["timeUP"] = 0;
				$tab_host[$res1[0]]["timeDOWN"] = 0;
				$tab_host[$res1[0]]["timeUNREACHABLE"] = 0;
				$tab_host[$res1[0]]["timeNONE"] = 0;
				$tab_host[$res1[0]]["start_time"] = $start_time;
				$tab_host[$res1[0]]["tab_svc_log"] = array();
			}
			else if (!strncmp($type, "HOST ALERT", 10) )
			{
				if(!isset($tab_host[$res1[0]]))
				{
					$tab_host[$res1[0]] = array();
					$tab_host[$res1[0]]["current_time"] = $start_time;
					$tab_host[$res1[0]]["current_state"] = "NONE";
					$tab_host[$res1[0]]["timeUP"] = 0;
					$tab_host[$res1[0]]["timeDOWN"] = 0;
					$tab_host[$res1[0]]["timeUNREACHABLE"] = 0;
					$tab_host[$res1[0]]["timeNONE"] = 0;
					$tab_host[$res1[0]]["start_time"] = $start_time;
					$tab_host[$res1[0]]["tab_svc_log"] = array();					

				}						
				if(!strncmp($tab_host[$res1[0]]["current_state"], "UP", 2))
				$tab_host[$res1[0]]["timeUP"] += ($time_event-$tab_host[$res1[0]]["current_time"]);
				elseif(!strncmp($tab_host[$res1[0]]["current_state"], "DOWN", 4))
				$tab_host[$res1[0]]["timeDOWN"] += ($time_event-$tab_host[$res1[0]]["current_time"]);
				elseif(!strncmp($tab_host[$res1[0]]["current_state"], "UNREACHABLE", 11))
				$tab_host[$res1[0]]["timeUNREACHABLE"] += ($time_event-$tab_host[$res1[0]]["current_time"]);
				else
				$tab_host[$res1[0]]["timeNONE"] += ($time_event-$tab_host[$res1[0]]["current_time"]);
				

				$tab_host[$res1[0]]["current_state"] = $res1[1];
				$tab_host[$res1[0]]["current_time"] = $time_event; //save time
			}
				
			#
			## services
			#
			else if (!strncmp($type, "CURRENT SERVICE STATE", 21) || !strncmp($type, "INITIAL SERVICE STATE", 21))
			{
				$tab_services[$res1[1]][$res1[0]] = array();
				$tab_tmp = array();
				$tab_tmp["current_state"] = $res1[2];
				$tab_tmp["current_time"] = $start_time;
				$tab_tmp["timeOK"] = 0;
				$tab_tmp["timeWARNING"] = 0;
				$tab_tmp["timeUNKNOWN"] = 0;
				$tab_tmp["timeCRITICAL"] = 0;
				$tab_tmp["timeNONE"] = 0;
				$tab_tmp["start_time"] = $start_time;
				$tab_tmp["service_id"] = getMyServiceID($res1[1],getMyHostID($res1[0]));
				$tab_services[$res1[1]][$res1[0]] = $tab_tmp;
			}			
			else if (!strncmp($type, "SERVICE ALERT", 13))
			{
				if(isset($tab_services[$res1[1]][$res1[0]]))
				{
					$tab_tmp = array();
					$tab_tmp = $tab_services[$res1[1]][$res1[0]];
					if(!strncmp($tab_tmp["current_state"], "OK", 2))
						$tab_tmp["timeOK"] += ($time_event-$tab_tmp["current_time"]);
					elseif(!strncmp($tab_tmp["current_state"], "WARNING", 7))
						$tab_tmp["timeWARNING"] += ($time_event-$tab_tmp["current_time"]);
					elseif(!strncmp($tab_tmp["current_state"], "UNKNOWN", 7))
						$tab_tmp["timeUNKNOWN"] += ($time_event-$tab_tmp["current_time"]);
					elseif(!strncmp($tab_tmp["current_state"], "CRITICAL", 8))
						$tab_tmp["timeCRITICAL"] += ($time_event-$tab_tmp["current_time"]);
					else
						$tab_tmp["timeNONE"] += ($time_event-$tab_tmp["current_time"]);
					$tab_tmp["current_time"] = $time_event; //save time
					$tab_tmp["current_state"] = $res1[2]; //save time
					$tab_services[$res1[1]][$res1[0]] = $tab_tmp;
				}
			}
		}
	}
	
	$tablist["time_start"] = $start_time;
	$tablist["tab_host"] = $tab_host;
	$tablist["tab_services"] = $tab_services;
	
	return($tablist);
}


foreach($tableFile2 as $key => $time)
{
	$tab = array();
	$tab = parseFile($key,$time);

	insert_file_name_in_db($key);
	
	$time_start = $tab["time_start"];
	$tab_host = $tab["tab_host"];
	$tab_services = $tab["tab_services"];
	
	
	foreach($tab_host as $host => $htab)
	{
		if (isset($host_list[trim($host)]))
		{	
			#
			## last host alert
			#	
			if(!strncmp($htab["current_state"], "UP", 2))
				$htab["timeUP"] += ($time-$htab["current_time"]);
			elseif(!strncmp($htab["current_state"], "DOWN", 4))
				$htab["timeDOWN"] += ($time-$htab["current_time"]);
			elseif(!strncmp($htab["current_state"], "UNREACHABLE", 11))
				$htab["timeUNREACHABLE"] += ($time-$htab["current_time"]);
			else
				$htab["timeNONE"] += ($time-$htab["current_time"]);
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
			$NONEsc = $htab["timeNONE"];
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
				" '$time', '$time_start')";

			$result = $pearDB->query($sql);
			if (PEAR::isError($res)){
			  die($res->getMessage());}
			  
		}
	}
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
				$htab["timeOK"] += ($time-$htab["current_time"]);
			elseif(!strncmp($htab["current_state"], "WARNING", 4))
				$htab["timeWARNING"] += ($time-$htab["current_time"]);
			elseif(!strncmp($htab["current_state"], "UNKNOWN", 11))
				$htab["timeUNKNOWN"] += ($time-$htab["current_time"]);
			elseif(!strncmp($htab["current_state"], "CRITICAL", 11))
				$htab["timeCRITICAL"] += ($time-$htab["current_time"]);
			else
				$htab["timeNONE"] += ($time-$htab["current_time"]);


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
				" '$time', '$time_start')";

			$result = $pearDB->query($sql);
			if (PEAR::isError($res)){
			  die($res->getMessage());}
			}
		}		
	}	
}
	
?>
