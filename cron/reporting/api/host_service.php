<?
/*
 * Created on 12 mars 07 by Cedrick Facon
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
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@oreon-project.org
*/

	global $pearDBO;

	/*
	 * Parsing Data for Hosts
	 */
	if (is_array($tab_hosts))
	{
		foreach($tab_hosts as $host => $htab)	{
			
			if (isset($host_list[trim($host)]) && isset($htab["current_state"])){
				## last host alert
				if (!strncmp($htab["current_state"], "UP", 2))
					$htab["timeUP"] += ($day_current_end - $htab["current_time"]);
				else if (!strncmp($htab["current_state"], "DOWN", 4))
					$htab["timeDOWN"] += ($day_current_end - $htab["current_time"]);
				else if (!strncmp($htab["current_state"], "UNREACHABLE", 11))
					$htab["timeUNREACHABLE"] += ($day_current_end - $htab["current_time"]);
				else
					$htab["timeNONE"] += ($day_current_end - $htab["current_time"]);
	
				## insert in db the host time
				$host_id = $host_list[trim($host)];
				$Upsc = $htab["timeUP"];
				$UPnbEvent = $htab["UPnbEvent"];
				$DOWNsc = $htab["timeDOWN"];
				$DOWNnbEvent = $htab["DOWNnbEvent"];
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
				$res = $pearDBO->query($sql);
				if (PEAR::isError($res)){
				  	die($res->getMessage());}
			}
		}
	}

	/*
	 * Parsing Datas for Services
	 */

	if (is_array($tab_services))
	{
		foreach($tab_services as $svc => $htabsvc){
			if (isset($service_list[trim($svc)]))
				foreach($htabsvc as $host => $htab){
					if (isset($host_list[trim($host)]) && $htab["service_id"]){
						## last service alert	
						if (!strncmp($htab["current_state"], "OK", 2))
							$htab["timeOK"] += ($day_current_end-$htab["current_time"]) ;
						else if(!strncmp($htab["current_state"], "WARNING", 4))
							$htab["timeWARNING"] += ($day_current_end-$htab["current_time"]) ;
						else if(!strncmp($htab["current_state"], "UNKNOWN", 11))
							$htab["timeUNKNOWN"] += ($day_current_end-$htab["current_time"]) ;
						else if(!strncmp($htab["current_state"], "CRITICAL", 11))
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
						$result = $pearDBO->query($sql);
						if (PEAR::isError($result)){
						 	die($result->getMessage());}
					}
				}
		}
	}
?>