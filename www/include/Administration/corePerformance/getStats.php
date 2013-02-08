<?php
/*
 * Copyright 2005-2011 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
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
		$tab = preg_split('/\;/', $_GET[$key]);
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
		return preg_replace("/(\\\$|`)/", "", $command);
	}

	require_once "@CENTREON_ETC@/centreon.conf.php";
	require_once "$centreon_path/www/class/centreonGMT.class.php";
	require_once "$centreon_path/www/class/centreonDB.class.php";

	/*
	 * Connect DB
	 */
	$pearDB = new CentreonDB();
	$pearDBO = new CentreonDB("centstorage");

	/*
	 * Init GMT Class
	 */
	$CentreonGMT = new CentreonGMT($pearDB);

	/*
	 * Check Session activity
	 */
	$session = $pearDB->query("SELECT * FROM `session` WHERE session_id = '".$_GET["session_id"]."'");
	if (!$session->numRows()){
		;
	} else {

	 	/*
	 	 * Get GMT for current user
	 	 */
	 	$gmt = $CentreonGMT->getMyGMTFromSession($_GET["session_id"], $pearDB);

		/*
		 * Get RRDTool binary Path
		 */
		$DBRESULT = $pearDB->query("SELECT * FROM `options`");
		while ($option = $DBRESULT->fetchRow()) {
			$optGen[$option["key"]] = $option["value"];
			if ($option["key"] == 'rrdtool_path_bin') {
				$rrdtoolPath = $option["value"];
			}
		}
		$DBRESULT->free();

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

		if ($optGen["rrdtool_version"] == "1.3") {
           if (isset($optGen["rrdtool_title_font"]) && isset($optGen["rrdtool_title_fontsize"]))
              $command_line .= " --font TITLE:".$optGen["rrdtool_title_fontsize"].":".$optGen["rrdtool_title_font"]." ";
           if (isset($optGen["rrdtool_unit_font"]) && isset($optGen["rrdtool_unit_fontsize"]))
              $command_line .= " --font UNIT:".$optGen["rrdtool_unit_fontsize"].":".$optGen["rrdtool_unit_font"]." ";
           if (isset($optGen["rrdtool_axis_font"]) && isset($optGen["rrdtool_axis_fontsize"]))
              $command_line .= " --font AXIS:".$optGen["rrdtool_axis_fontsize"].":".$optGen["rrdtool_axis_font"]." ";
           if (isset($optGen["rrdtool_title_font"]) && isset($optGen["rrdtool_title_fontsize"]))
              $command_line .= " --font WATERMARK:".$optGen["rrdtool_title_fontsize"].":".$optGen["rrdtool_title_font"]." ";
           if (isset($optGen["rrdtool_legend_title"]) && isset($optGen["rrdtool_legend_fontsize"]))
              $command_line .= " --font LEGEND:".$optGen["rrdtool_legend_fontsize"].":".$optGen["rrdtool_legend_title"]." ";
        }

		/*
		 * get all template infos
		 */
		$command_line .= " --interlaced --imgformat PNG --width=400 --height=150 --title='".$title[$_GET["key"]]."' --vertical-label='".$_GET["key"]."' --slope-mode  --rigid --alt-autoscale-max ";

		/*
		 * Init DS template For each curv
		 */

		$colors = array("Min"=>"#19EE11", "Max"=>"#F91E05", "Average"=>"#2AD1D4",
						"Last_Min"=>"#2AD1D4", "Last_5_Min"=>"#13EB3A", "Last_15_Min"=>"#F8C706",
						"Last_Hour"=>"#F91D05", "Up"=>"#19EE11", "Down"=>"#F91E05",
						"Unreach"=>"#2AD1D4", "Ok"=>"#13EB3A", "Warn"=>"#F8C706",
						"Crit"=>"#F91D05", "Unk"=>"#2AD1D4", "In_Use"=>"#13EB3A",
						"Max_Used"=>"#F91D05", "Total_Available"=>"#2AD1D4");
		$metrics = $differentStats[$options[$_GET["key"]]];
		$DBRESULT = $pearDBO->query("SELECT RRDdatabase_nagios_stats_path FROM config");
		$nagios_stats = $DBRESULT->fetchRow();
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
			$command_line .= " LINE1:v".$cpt.$colors[$tm].":\"".$tm."\"";
			$command_line .= " GPRINT:v". ($cpt) .":LAST:\"\:%7.2lf%s\l\"";
			$cpt++;
		}

		$command_line = "$rrdtoolPath ".$command_line." 2>&1";

		/*
		 * Add Timezone for current user.
		 */

        if ($CentreonGMT->used())
            $command_line = "export TZ='GMT".$CentreonGMT->getMyGMTForRRD()."' ; ".$command_line;

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