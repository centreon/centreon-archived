<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
	
	
	/*
	 * Filter get parameters
	 *
	 * <code>
	 * $_GET["o"] = filter_get($_GET["o"]);
	 * </code>
	 *
	 * @param{TAB}string{TAB}$str{TAB}a get string
	 * @return{TAB}string{TAB}return get string
	 */
		
	function filter_get($str){
		if (preg_match("/([a-zA-Z0-9\_\-\%\ ]*)/", $str, $matches))
			return $matches[1];
		return NULL;	
	}
	
	foreach ($_GET as $key => $get){
		$tab = split(';', $_GET[$key]);
		$_GET[$key] = $tab[0];
		if (function_exists("filter_var")){
			$_GET[$key] = filter_var($_GET[$key], FILTER_SANITIZE_SPECIAL_CHARS);
		} else {
			$_GET[$key] = filter_get($_GET[$key]);
		}
	}

	/*
	 * escape special char for commands
	 *
	 * <code>
	 * $string = escape_command($string);
	 * </code>
	 *
	 * @param{TAB}string{TAB}$command{TAB}command line
	 * @return{TAB}string{TAB}command line
	 */
		
	function escape_command($command) {
		return ereg_replace("(\\\$|`)", "", $command);
	}
	
	require_once "@CENTREON_ETC@/centreon.conf.php";
	require_once "$centreon_path/www/class/centreonGMT.class.php";
	require_once "$centreon_path/www/class/centreonDB.class.php";
	
	$pearDB = new CentreonDB();
	$pearDBO = new CentreonDB("centstorage");
	
	/*
	 * Init GMT Class
	 */
	$CentreonGMT = new CentreonGMT();
	
	/*
	 * Check Session activity 
	 */	 
	$session =& $pearDB->query("SELECT * FROM `session` WHERE session_id = '".$_GET["session_id"]."'");
	if (!$session->numRows()){
		;
	} else {
	 
	 	/*
	 	 * Get GMT for current user
	 	 */
	 	$gmt = $CentreonGMT->getMyGMTFromSession($_GET["session_id"]);
	 
		/*
		 * Get RRDTool binary Path 
		 */
		$DBRESULT =& $pearDB->query("SELECT * FROM `options` WHERE `key` = 'rrdtool_path_bin'");
		while ($options =& $DBRESULT->fetchRow())
			$rrdtoolPath = $options["value"];
		$DBRESULT->free();
		unset($options);
				
		$title	 = array(	
					"active_host_check" => _("Host Check Execution Time"), 
					"active_host_last" => _("Hosts Actively Checked"),
					"host_latency" => _("Host check latency"),
					"active_service_check" => _("Service Check Execution Time"), 
					"active_service_last" => _("Services Actively Checked"), 
					"service_latency" => _("Service check latency"), 
					"cmd_buffer" => _("Commands in buffer"), 
					"host_states" => _("Host status"), 
					"service_states" => _("Service status"));
	
		$options = array(	
					"active_host_check" => "nagios_active_host_execution.rrd", 
					"active_host_last" => "nagios_active_host_last.rrd",
					"host_latency" => "nagios_active_host_latency.rrd",
					"active_service_check" => "nagios_active_service_execution.rrd", 
					"active_service_last" => "nagios_active_service_last.rrd", 
					"service_latency" => "nagios_active_service_latency.rrd", 
					"cmd_buffer" => "nagios_cmd_buffer.rrd", 
					"host_states" => "nagios_hosts_states.rrd", 
					"service_states" => "nagios_services_states.rrd");
	
		$differentStats = array(	
					"nagios_active_host_execution.rrd" => array("Min", "Max", "Average"), 
					"nagios_active_host_last.rrd" => array("Last_Min", "Last_5_Min", "Last_15_Min", "Last_Hour"), 
					"nagios_active_host_latency.rrd" => array("Min", "Max", "Average"), 
					"nagios_active_service_execution.rrd" => array("Min", "Max", "Average"), 
					"nagios_active_service_last.rrd" => array("Last_Min", "Last_5_Min", "Last_15_Min", "Last_Hour"), 
					"nagios_active_service_latency.rrd" => array("Min", "Max", "Average"), 
					"nagios_cmd_buffer.rrd" => array("In_Use", "Max_Used", "Total_Available"), 
					"nagios_hosts_states.rrd" => array("Up", "Down", "Unreach"), 
					"nagios_services_states.rrd" => array("Ok", "Warn", "Crit", "Unk"));
	
		/*
		 * Verify if start and end date
		 */	
	
		if (!isset($_GET["start"])) {		
			$start = time() - (60*60*24);		
		} else {				
			switch ($_GET["start"]) {
				case "last3hours" :
					$start = time() - (60*60*3);
					break;
				case "today" : 
					$start = time() - (60*60*24); 
					break;
				case "yesterday" : 
					$start = time() - (60*60*48); 
					break;
				case "last4days" : 
					$start = time() - (60*60*96);
					break;
				case "lastweek" : 
					$start = time() - (60*60*168); 
					break;
				case "lastmonth" : 
					$start = time() - (60*60*24*30); 
					break;
				case "last6month" : 
					$start = time() - (60*60*24*30*6); 
					break;
				case "lastyear" : 
					$start = time() - (60*60*24*30*12); 
					break;
			}
		}
		
		/*
		 * Get end values
		 */
		if (!isset($_GET["end"]))
			$end = time();
		else
			$end = $_GET["end"];
			
		/*
		 * Begin Command Line
		 */
		$command_line = " graph - --start=".$start." --end=".$end;
	
		/*
		 * get all template infos
		 */
		 
		$command_line .= " --interlaced --imgformat PNG --width=500 --height=150 --title='".$title[$_GET["key"]]."' --vertical-label='".$_GET["key"]."' --slope-mode  --rigid --alt-autoscale-max ";
				
		/*
		 * Init DS template For each curv
		 */
		
		$colors = array("1"=>"#19EE11", "2"=>"#82CFD8", "3"=>"#F8C706", "4"=>"#F8C706");
		
		$metrics = $differentStats[$options[$_GET["key"]]];
		$DBRESULT =& $pearDBO->query("SELECT RRDdatabase_nagios_stats_path FROM config");
		$nagios_stats =& $DBRESULT->fetchRow();
		$nagios_stats_path = $nagios_stats['RRDdatabase_nagios_stats_path'];
	
		$cpt = 1;
		foreach ($metrics as $key => $value){
			$command_line .= " DEF:v".$cpt."=".$nagios_stats_path."perfmon-".$_GET["ns_id"]."/".$options[$_GET["key"]].":".$value.":AVERAGE ";
			$cpt++;
		}
		
		/*
		 * Add comment start and end time inf graph footer.
		 */
		
		$rrd_time  = addslashes($CentreonGMT->getDate("Y\/m\/d G:i", $start, $gmt));
		$rrd_time = str_replace(":", "\:", $rrd_time);
		$rrd_time2 = addslashes($CentreonGMT->getDate("Y\/m\/d G:i", $end, $gmt)) ;
		$rrd_time2 = str_replace(":", "\:", $rrd_time2);
		$command_line .= " COMMENT:\" From $rrd_time to $rrd_time2 \\c\" ";
		
		/*
		 * Create Legende
		 */
		$cpt = 1;
		foreach ($metrics as $key => $tm){
			$command_line .= " LINE1:v".$cpt.$colors[$cpt].":\"".$tm."\"";			
			$command_line .= " GPRINT:v". ($cpt) .":LAST:\":%7.2lf%s\l\"";
			$cpt++;
		}
		
		$command_line = "$rrdtoolPath ".$command_line." 2>&1";
	
		/*
		 * Add Timezone for current user.
		 */
		 
		$command_line = "export TZ='CMT".$CentreonGMT->getMyGMTForRRD()."' ; ".$command_line;
	
		$command_line = escape_command("$command_line");

		/*
		 * Debug
		 */
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
	}
?>