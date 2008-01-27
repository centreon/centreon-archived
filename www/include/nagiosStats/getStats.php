<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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

	
	function escape_command($command) {
		return ereg_replace("(\\\$|`)", "", $command);
	}
	
	require_once ('DB.php');

	$title	 = array(	"active_host_check" => "Verifications d'hotes", 
						"active_host_last" => "Hotes Actifs",
						"host_latency" => "Latence des verifications d'hotes",
						"active_host_check" => "Verifications des services", 
						"active_service_last" => "Services Actifs", 
						"service_latency" => "Latences des verifications des services", 
						"cmd_buffer" => "Commandes en buffer", 
						"host_states" => "Etats des hotes", 
						"service_states" => "Etats des Services");


	$options = array(	"active_host_check" => "nagios_active_host_execution.rrd", 
						"active_host_last" => "nagios_active_host_last.rrd",
						"host_latency" => "nagios_active_host_latency.rrd",
						"active_host_check" => "nagios_active_service_execution.rrd", 
						"active_service_last" => "nagios_active_service_last.rrd", 
						"service_latency" => "nagios_active_service_latency.rrd", 
						"cmd_buffer" => "nagios_cmd_buffer.rrd", 
						"host_states" => "nagios_hosts_states.rrd", 
						"service_states" => "nagios_services_states.rrd");
	
	$differentStats = array(	"nagios_active_host_execution.rrd" => array("Used", "High", "Total"), 
								"nagios_active_host_last.rrd" => array("T1", "T5", "T15", "T60"), 
								"nagios_active_host_latency.rrd" => array("Used", "High", "Total"), 
								"nagios_active_service_execution.rrd" => array("Used", "High", "Total"), 
								"nagios_active_service_last.rrd" => array("T1", "T5", "T15", "T60"), 
								"nagios_active_service_latency.rrd" => array("Used", "High", "Total"), 
								"nagios_cmd_buffer.rrd" => array("Used", "High", "Total"), 
								"nagios_hosts_states.rrd" => array("Up", "Down", "Unreach"), 
								"nagios_services_states.rrd" => array("Ok", "Warn", "Crit", "Unk"));


	/*
	 * Verify if start and end date
	 */	

	if (!isset($_GET["start"]))
		$start = time() - (60*60*96);
	else
		$start = $_GET["start"];
	
	if (!isset($_GET["end"]))
		$end = time();
	else
		$end = $_GET["end"];

		 
		$command_line = " graph - --start=".$start." --end=".$end;

		/*
		 * get all template infos
		 */
		 
		$command_line .= " --interlaced --imgformat PNG --width=500 --height=120 --title='".$title[$_GET["key"]]."' --vertical-label='".$_GET["key"]."' --slope-mode  ";
		$command_line .= "--rigid --alt-autoscale-max ";
				
		/*
		 * Init DS template For each curv
		 */
		
		$colors = array("1"=>"#19EE11", "2"=>"#82CFD8", "3"=>"#F8C706", "4"=>"#F8C706");
		//$metrics = array("Used" => 1, "High" => 2, "Total" => 3);
		
		$metrics = $differentStats[$options[$_GET["key"]]];
		$cpt = 1;
		foreach ($metrics as $key => $value){
			$command_line .= " DEF:v".$cpt."=/var/lib/ods/perfmon-".$_GET["ns_id"]."/".$options[$_GET["key"]].":".$value.":AVERAGE ";
			$cpt++;
		}
		$command_line .= " COMMENT:\" \\l\" ";
		
		
		# Create Legende
		$cpt = 1;

		foreach ($metrics as $key => $tm){
			//$command_line .= " AREA:v".($cpt).$colors[$cpt]."3c ";
			$command_line .= " LINE1:v".($cpt);
			$command_line .= $colors[$cpt].":\"";
			$command_line .= $tm."\"";
			$cpt++;
		}

		$command_line = "/usr/bin/rrdtool ".$command_line." 2>&1";
		$command_line = escape_command("$command_line");

		//print $command_line;
		$fp = popen($command_line  , 'r');
		if (isset($fp) && $fp ) {
			$str ='';
			while (!feof ($fp)) {
		  		$buffer = fgets($fp, 4096);
		 		$str = $str . $buffer ;
			}
			print $str;
		}
	
?>