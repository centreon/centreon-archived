<?php
/*
 * Copyright 2005-2009 MERETHIS
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

 	header('Content-Type: image/png');
 	
	function escape_command($command) {
		return ereg_replace("(\\\$|`)", "", $command);
	}

	/*
	 * Include config file
	 */
	include "@CENTREON_ETC@/centreon.conf.php";
		
	require_once "./DB-Func.php";
	require_once $centreon_path."www/class/centreonDB.class.php";
	require_once $centreon_path."www/class/Session.class.php";
	require_once $centreon_path."www/class/Oreon.class.php";
	require_once "$centreon_path/www/class/centreonGMT.class.php";
	
	Session::start();
	$oreon =& $_SESSION["oreon"];

	require_once $centreon_path."www/include/common/common-Func.php";

	$pearDB = new CentreonDB();

	/*
	 * Verify if start and end date
	 */	

	(!isset($_GET["start"])) ? $start = time() - 120 - (60*60*48) : $start = $_GET["start"] - 120;
	(!isset($_GET["end"])) ? $end = time() - 120 : $end = $_GET["end"] - 120;

	/*
	 * Verify if session is active
	 */	

	$session =& $pearDB->query("SELECT session_id, user_id FROM `session` WHERE session_id = '".htmlentities($_GET["session_id"], ENT_QUOTES)."'");
	if (!$session->numRows()){
		$image = imagecreate(250,100);
		$fond = imagecolorallocate($image,0xEF,0xF2,0xFB);		
		header("Content-Type: image/gif");
		imagegif($image);
		exit;
	} else {
		
		/*
	 	 * Get GMT for current user
	 	 */
	 	$CentreonGMT = new CentreonGMT();
	 	$gmt = $CentreonGMT->getMyGMTFromSession($_GET["session_id"]);
	
		/*
		 * Get Values
		 */
		$session_value =& $session->fetchRow();
		$session->free();

		/*
		 * Connect to ods
		 */
		 
		$pearDBO = new CentreonDB("centstorage");
		$RRDdatabase_path = getRRDToolPath($pearDBO);
	
		/*
		 * Get index information to have acces to graph
		 */
		
		$DBRESULT =& $pearDBO->query("SELECT * FROM index_data WHERE id = '".$_GET["index"]."' LIMIT 1");
		$index_data_ODS =& $DBRESULT->fetchRow();
		if (!isset($_GET["template_id"])|| !$_GET["template_id"]){
			$host_id = getMyHostID($index_data_ODS["host_name"]);
			$svc_id = getMyServiceID($index_data_ODS["service_description"], $host_id);
			$template_id = getDefaultGraph($svc_id, 1);
		} else
			$template_id = $_GET["template_id"];
		$DBRESULT->free();	

		/*
		 * Create command line
		 */
		
		if (isset($_GET["flagperiod"]) && $_GET["flagperiod"] == 0) {
			$start 	= $CentreonGMT->getUTCDate($start);
			$end 	= $CentreonGMT->getUTCDate($end);
		}
		
		$command_line = " graph - --start=".$start." --end=".$end;

		/*
		 * get all template infos
		 */
		 
		$DBRESULT =& $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$template_id."' LIMIT 1");
		$GraphTemplate =& $DBRESULT->fetchRow();
		
		if (preg_match("/meta_([0-9]*)/", $index_data_ODS["service_description"], $matches)){
			$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
			$meta =& $DBRESULT_meta->fetchRow();
			$index_data_ODS["service_description"] = $meta["meta_name"];
		}
		
		$index_data_ODS["service_description"] = str_replace("#S#", "/", $index_data_ODS["service_description"]);
		$index_data_ODS["service_description"] = str_replace("#BS#", "\\", $index_data_ODS["service_description"]);
		
		$gprint_scale_cmd = "%s"; 
 		if ($GraphTemplate["scaled"] == "0"){ 
 	    	# Disable y-axis scaling 
 			$command_line .= " -X0 "; 
 	        # Suppress Scaling in Text Output 
 	        $gprint_scale_cmd = ""; 
 	    } 
		
		$base = "";
		if (isset($GraphTemplate["base"]) && $GraphTemplate["base"])
			$base = "-b ".$GraphTemplate["base"];
		
		$command_line .= " --interlaced $base --imgformat PNG --width=500 --height=120 --title='".$index_data_ODS["service_description"]." graph on ".$index_data_ODS["host_name"]."' --vertical-label='".$GraphTemplate["vertical_label"]."' ";

		if ($oreon->optGen["rrdtool_version"] == "1.2")
			$command_line .= " --slope-mode ";
				
		/*
		 * Init Graph Template Value
		 */
		if (isset($GraphTemplate["bg_grid_color"]) && $GraphTemplate["bg_grid_color"])
			$command_line .= "--color CANVAS".$GraphTemplate["bg_grid_color"]." ";

		if (isset($GraphTemplate["bg_color"]) && $GraphTemplate["bg_color"])
			$command_line .= "--color BACK".$GraphTemplate["bg_color"]." ";
		else
			$command_line .= "--color BACK#F0F0F0 ";

		if (isset($GraphTemplate["police_color"]) && $GraphTemplate["police_color"])
			$command_line .= "--color FONT".$GraphTemplate["police_color"]." ";
		if (isset($GraphTemplate["grid_main_color"]) && $GraphTemplate["grid_main_color"])
			$command_line .= "--color MGRID".$GraphTemplate["grid_main_color"]." ";
		if (isset($GraphTemplate["grid_sec_color"]) && $GraphTemplate["grid_sec_color"])
			$command_line .= "--color GRID".$GraphTemplate["grid_sec_color"]." ";
		if (isset($GraphTemplate["contour_cub_color"]) && $GraphTemplate["contour_cub_color"])
			$command_line .= "--color FRAME".$GraphTemplate["contour_cub_color"]." ";
		if (isset($GraphTemplate["col_arrow"]) && $GraphTemplate["col_arrow"])
			$command_line .= "--color ARROW".$GraphTemplate["col_arrow"]." ";
		if (isset($GraphTemplate["col_top"]) && $GraphTemplate["col_top"])
			$command_line .= "--color SHADEA".$GraphTemplate["col_top"]." ";
		if (isset($GraphTemplate["col_bot"]) && $GraphTemplate["col_bot"])
			$command_line .= "--color SHADEB".$GraphTemplate["col_bot"]." ";
		
		if (isset($GraphTemplate["lower_limit"]) && $GraphTemplate["lower_limit"] != NULL)
			$command_line .= "--lower-limit ".$GraphTemplate["lower_limit"]." ";
		if (isset($GraphTemplate["upper_limit"]) && $GraphTemplate["upper_limit"] != NULL)
			$command_line .= "--upper-limit ".$GraphTemplate["upper_limit"]." ";
		if ((isset($GraphTemplate["lower_limit"]) && $GraphTemplate["lower_limit"] != NULL) || (isset($GraphTemplate["upper_limit"]) && $GraphTemplate["upper_limit"] != NULL))
			$command_line .= "--rigid --alt-autoscale-max ";
			
		$DBRESULT =& $pearDB->query("SELECT `metric_id` FROM `ods_view_details` WHERE `index_id` = '".$_GET["index"]."' AND `contact_id` = '".$session_value["user_id"]."'");
		while ($metric_activate =& $DBRESULT->fetchRow()){
			$metrics_activate[$metric_activate["metric_id"]] = $metric_activate["metric_id"];
		}
				
		/*
		 * Init DS template For each curv
		 */
		$metrics = array();
		$DBRESULT =& $pearDBO->query("SELECT metric_id, metric_name, unit_name, warn, crit FROM metrics WHERE index_id = '".$_GET["index"]."' AND `hidden` = '0' ORDER BY metric_name");
		$cpt = 0;
		
		$metrics = array();		
		while ($metric =& $DBRESULT->fetchRow()){
			if (!isset($_GET["metric"]) || (isset($_GET["metric"]) && isset($_GET["metric"][$metric["metric_id"]]))){	
				if (!isset($metrics_activate) || (isset($metrics_activate) && isset($metrics_activate[$metric["metric_id"]]) && $metrics_activate[$metric["metric_id"]])){
					
					/*
					 * Construct metric name for detect metric graph template.
					 */
					$metricNameForGraph = $metric["metric_name"];
					$metricNameForGraph = str_replace("#S#", "/", $metricNameForGraph);
					$metricNameForGraph = str_replace("#BS#", "\\", $metricNameForGraph);
				
					$metrics[$metric["metric_id"]]["metric_id"] = $metric["metric_id"];
					$metrics[$metric["metric_id"]]["metric"] = str_replace("#S#", "slash_", $metric["metric_name"]);
					$metrics[$metric["metric_id"]]["metric"] = str_replace("#BS#", "bslash_", $metrics[$metric["metric_id"]]["metric"]);
					$metrics[$metric["metric_id"]]["unit"] = $metric["unit_name"];
					$metrics[$metric["metric_id"]]["warn"] = $metric["warn"];
					$metrics[$metric["metric_id"]]["crit"] = $metric["crit"];
					
					$res_ds =& $pearDB->query("SELECT * FROM giv_components_template WHERE `ds_name` = '".$metricNameForGraph."'");
					$ds_data =& $res_ds->fetchRow();
					if (!$ds_data){
						$ds = getDefaultDS();						
						$res_ds =& $pearDB->query("SELECT * FROM giv_components_template WHERE compo_id = '".$ds."'");
						$ds_data =& $res_ds->fetchRow();
						$metrics[$metric["metric_id"]]["ds_id"] = $ds;
					}
					/*
					 * Fetch Datas
					 */
					foreach ($ds_data as $key => $ds_d){
						if ($key == "ds_transparency"){
							$transparency = dechex(255-($ds_d*255)/100);
							if (strlen($transparency) == 1)
								$transparency = "0" . $transparency;
							$metrics[$metric["metric_id"]][$key] = $transparency;
						} else
							$metrics[$metric["metric_id"]][$key] = $ds_d ;
						
					}
					$res_ds->free();
					
					if (preg_match('/DS/', $ds_data["ds_name"], $matches)){
						$metrics[$metric["metric_id"]]["legend"] = str_replace("#S#", "/", $metric["metric_name"]);
					} else {
	                	$metrics[$metric["metric_id"]]["legend"] = $ds_data["ds_name"];
					}
					
					if (strcmp($metric["unit_name"], ""))
						$metrics[$metric["metric_id"]]["legend"] .= " (".$metric["unit_name"].") ";
					
					$metrics[$metric["metric_id"]]["legend_len"] = strlen($metrics[$metric["metric_id"]]["legend"]);
				}
			}
			$cpt++;
		}
		$DBRESULT->free();
		
		$cpt = 0;
		$longer = 0;
		foreach ($metrics as $key => $tm){
			if (isset($tm["ds_invert"]) && $tm["ds_invert"])
				$command_line .= " DEF:va".$cpt."=".$RRDdatabase_path.$key.".rrd:".substr($metrics[$key]["metric"],0 , 19).":AVERAGE CDEF:v".$cpt."=va".$cpt.",-1,* ";
			else
				$command_line .= " DEF:v".$cpt."=".$RRDdatabase_path.$key.".rrd:".substr($metrics[$key]["metric"],0 , 19).":AVERAGE ";
			if ($tm["legend_len"] > $longer)
				$longer = $tm["legend_len"];
			$cpt++;
		}
		
		/*
		 * Display Start and end time on graph
		 */
		$rrd_time  = addslashes($CentreonGMT->getDate("Y\/m\/d G:i", $start));
		$rrd_time = str_replace(":", "\:", $rrd_time);
		$rrd_time2 = addslashes($CentreonGMT->getDate("Y\/m\/d G:i", $end)) ;
		$rrd_time2 = str_replace(":", "\:", $rrd_time2);
		
		$command_line .= " COMMENT:\" From $rrd_time to $rrd_time2 \\c\" ";
		
		
		# Create Legende
		$cpt = 0;
		foreach ($metrics as $key => $tm){
			
			if ($metrics[$key]["ds_filled"])
				$command_line .= " AREA:v".($cpt).$tm["ds_color_area"].$tm["ds_transparency"]." ";
			
			$command_line .= " LINE".$tm["ds_tickness"].":v".($cpt);
			$command_line .= $tm["ds_color_line"].":\"";
			$command_line .= $metrics[$key]["legend"];
			for ($i = $metrics[$key]["legend_len"]; $i != $longer + 1; $i++)
				$command_line .= " ";
				$command_line .= "\"";
			if ($tm["ds_last"]){
				$command_line .= " GPRINT:v".($cpt).":LAST:\"Last\:%7.2lf".($gprint_scale_cmd);
				$tm["ds_min"] || $tm["ds_max"] || $tm["ds_average"] ? $command_line .= "\"" : $command_line .= "\\l\" ";
			}
			if ($tm["ds_min"]){
				$command_line .= " GPRINT:v".($cpt).":MIN:\"Min\:%7.2lf".($gprint_scale_cmd);
				$tm["ds_max"] || $tm["ds_average"] ? $command_line .= "\"" : $command_line .= "\\l\" ";
			}
			if ($tm["ds_max"]){
				$command_line .= " GPRINT:v".($cpt).":MAX:\"Max\:%7.2lf".($gprint_scale_cmd); 
				$tm["ds_average"] ? $command_line .= "\"" : $command_line .= "\\l\" ";
			}
			if ($tm["ds_average"]){
				$command_line .= " GPRINT:v".($cpt).":AVERAGE:\"Average\:%7.2lf".($gprint_scale_cmd)."\\l\"";
			}
			$cpt++;
		}

		$command_line = $oreon->optGen["rrdtool_path_bin"].$command_line." 2>&1";
	
		/*
		 * Add Timezone for current user.
		 */
		 //".$CentreonGMT->getMyGMTForRRD()."
		$command_line = "export TZ='UTC-2' ; ".$command_line;
	
		$command_line = escape_command("$command_line");
		if ($oreon->optGen["debug_rrdtool"] == "1")
			error_log("[" . date("d/m/Y H:s") ."] RDDTOOL : $command_line \n", 3, $oreon->optGen["debug_path"]."rrdtool.log");
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