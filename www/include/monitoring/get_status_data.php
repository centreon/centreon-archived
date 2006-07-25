<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Developped by Julien Mathis For Merethis

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org

**/
	require ("../../oreon.conf.php");
	require ("../../DBconnect.php");
	
	function get_status_data($status_file = NULL){
		/*
		 * Error
		 * 1 : No status_file in parameter
		 * 2 : Status_file is not readable
		 * 
		 */  
		$status_data = array();
		global $pearDB;
		
		$res =& $pearDB->query("SELECT nagios_version FROM general_opt");
		if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		$res->fetchInto($nagios_version);
		$version = $nagios_version["nagios_version"];
		
		$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
		$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");
		
		
		if (!$status_file)
			return $status_data;
		
		if (is_readable($status_file))
			$log_file = fopen($status_file, "r");
		else
			return $status_data;
		
		$tab = array();
		
		// Read 
		$cpt = 0;
		if ($log_file)
			while ($str = fgets($log_file))
				if ($version == 1){		
					if (!preg_match("/^\#.*/", $str)){ // Do not get comments
						if (preg_match("/^[\[\]0-9\ ]* SERVICE[.]*/", $str)){
							$log = split(";", $str);
							$status_data[$cpt] = array("0"=>$log["1"], "1"=>$log["3"], "2"=>$log["2"]);
							$cpt++;
						}	
						if (preg_match("/^[\[\]0-9]* HOST[.]*/", $str)){
							$log = split(";", $str);
							$status_data[$cpt] = array("0"=>$log["1"], "1"=>$log["2"]);
							$cpt++;
						}
					}
				} else {
					if (!preg_match("/^\#.*/", $str)){ // Do not get comments
						if (preg_match("/^service/", $str)){   
						  	$log = array();
						 	 while ($str2 = fgets($log_file))
				          			if (!strpos($str2, "}")){      
					      				if (preg_match("/([A-Za-z0-9\_\-]*)\=(.*)[\ \t]*/", $str2, $tab)){
											$log[$tab[1]] = $tab[2];
											//print $tab[1] . "->" . $tab[2] . "<br>";
				          				}
					    			} else
					      				break;
					      			$status_data[$cpt] = array("0"=>$log["host_name"], "1"=>$tab_status_svc[$log['current_state']], "2"=>$log["service_description"]);
									$cpt++;
					  				unset($log);
					  	} else if (preg_match("/^host/", $str)){ // get host stat
							$log = array();
						  	while ($str2 = fgets($log_file))
						    	if (!strpos($str2, "}")){
						      		if (preg_match("/([A-Za-z0-9\_\-]*)\=(.*)[\ \t]*/", $str2, $tab)){
										$log[$tab[1]] = $tab[2];
										//print $tab[1] . "->" . $tab[2] . "<br>";
						      		} 
						    	} else
						      		break;
						      	$status_data[$cpt] = array("0"=>$log['host_name'], "1"=>$tab_status_host[$log['current_state']]);
								$cpt++;
								unset($log);
						}
					}
				}			
		return ($status_data);
	}
	
?>