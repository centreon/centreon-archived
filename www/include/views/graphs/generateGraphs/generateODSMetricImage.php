<?php
/*
 * Copyright 2005-2010 MERETHIS
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
 	
	$warn = 0;
	if (isset($_GET["warn"]) && $_GET["warn"] == 1)
		$warn = 1;

	$crit = 0;
	if (isset($_GET["crit"]) && $_GET["crit"] == 1)
		$crit = 1;


	function escape_command($command) {
		return ereg_replace("(\\\$|`)", "", $command);
	}

	function legendcmp($item1, $item2) {
	    return strnatcasecmp($item1["legend"], $item2["legend"]);
	}
	function metricsort($metrics) {
	    uasort($metrics, "legendcmp");
	}

	/*
	 * Include config file
	 */
	include "@CENTREON_ETC@/centreon.conf.php";
		
	require_once "./DB-Func.php";
	require_once $centreon_path."www/class/centreonDB.class.php";
	require_once $centreon_path."www/class/centreonSession.class.php";
	require_once $centreon_path."www/class/centreon.class.php";
	require_once "$centreon_path/www/class/centreonGMT.class.php";

	/*
	 * Verify if start and end date
	 */	

	(!isset($_GET["start"])) ? $start = time() - 120 - (60*60*48) : $start = $_GET["start"] - 120;
	(!isset($_GET["end"])) ? $end = time() - 120 : $end = $_GET["end"] - 120;

	/*
	 * Verify if session is active
	 */	
	
	CentreonSession::start();
	$centreon =& $_SESSION["centreon"];

	require_once $centreon_path."www/include/common/common-Func.php";

	$pearDB = new CentreonDB();

	$session =& $pearDB->query("SELECT session_id, user_id FROM `session` WHERE session_id = '".htmlentities($_GET["session_id"], ENT_QUOTES)."'");
	if (!$session->numRows()){
		$image = imagecreate(250,100);
		$fond = imagecolorallocate($image,0xEF,0xF2,0xFB);		
		header("Content-Type: image/gif");
		imagegif($image);
		exit;
	} else {
		/*
		 * Get Values
		 */
		$session_value =& $session->fetchRow();
		$session->free();
		
		/*
	 	 * Get GMT for current user
	 	 */
	 	$CentreonGMT = new CentreonGMT($pearDB);
	 	$gmt = $CentreonGMT->getMyGMTFromSession($_GET["session_id"], $pearDB);
	 	
		/*
		 * Connect to ods
		 */
		 
		$pearDBO = new CentreonDB("centstorage");
		$RRDdatabase_path = getRRDToolPath($pearDBO);
	
		/*
		 * Get index information to have acces to graph
		 */
		
		$DBRESULT =& $pearDBO->query("SELECT index_id, metric_name FROM metrics WHERE metric_id = '".$_GET["metric"]."' LIMIT 1");
		$metric_ODS =& $DBRESULT->fetchRow();
		$metric_ODS["metric_name"] = str_replace("#S#", "/", $metric_ODS["metric_name"]);
		$metric_ODS["metric_name"] = str_replace("#BS#", "\\", $metric_ODS["metric_name"]);
		
		$DBRESULT =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$metric_ODS["index_id"]."'");
		$metricnumber = $DBRESULT->numRows();
		$DBRESULT->free();
		$order = ($_GET["cpt"] - 1);
		$order = $order % $metricnumber;

		$svc_instance = $metric_ODS["index_id"];
		$DBRESULT =& $pearDBO->query("SELECT * FROM index_data WHERE id = '".$svc_instance."' LIMIT 1");
		$index_data_ODS =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		
		if (!isset($_GET["template_id"])|| !$_GET["template_id"]){
			if ($index_data_ODS["host_name"] != "_Module_Meta") {
				$host_id = getMyHostID($index_data_ODS["host_name"]);
				$svc_id = getMyServiceID($index_data_ODS["service_description"], $host_id);
				$template_id = getDefaultGraph($svc_id, 1);			
			} else {
				$tab = split("_", $index_data_ODS["service_description"]);
				$DBRESULT =& $pearDB->query("SELECT graph_id FROM meta_service WHERE meta_id = '".$tab[1]."'");
				$tempRes =& $DBRESULT->fetchRow();
				$DBRESULT->free();
				$template_id = $tempRes["graph_id"];
				unset($tempRes);
				unset($tab);
			}
		} else {
			$template_id = $_GET["template_id"];
		}

		/*
		 * Create command line
		 */
		
		if (isset($_GET["flagperiod"]) && $_GET["flagperiod"] == 0) {
			if ($CentreonGMT->used()) {
				$start 	= $CentreonGMT->getUTCDate($start);
				$end 	= $CentreonGMT->getUTCDate($end);
			}
		}
		
		/*
		 * get all template infos
		 */
		 
		$DBRESULT =& $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$template_id."' LIMIT 1");
		$GraphTemplate =& $DBRESULT->fetchRow();

		if($end - $start > 2160000 and $end - $start < 12960000)
		{
			if($end - $start < 12960000 - (86400*7))
				$xconfig = "--x-grid DAY:1:DAY:7:DAY:7:0:%d/%m";
			else
				$xconfig = "--x-grid DAY:1:DAY:7:DAY:14:0:%d/%m";
		}
		$command_line = " graph - $xconfig --start=".$start." --end=".$end;

		
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

		if ($index_data_ODS["host_name"] != "_Module_Meta")
			$title = $index_data_ODS["service_description"]." "._("graph on")." ".$index_data_ODS["host_name"];
		else
			$title = _("Graph")." ".$index_data_ODS["service_description"] ;

		if (isset($metric_ODS["metric_name"]) && $metric_ODS["metric_name"]!="")
			$title .= " metric ".$metric_ODS["metric_name"];
		
		if (!isset($GraphTemplate["width"]) || $GraphTemplate["width"] == "")
			$GraphTemplate["width"] = 600;
		if (!isset($GraphTemplate["height"]) || $GraphTemplate["height"] == "")
			$GraphTemplate["height"] = 200;
		if (!isset($GraphTemplate["vertical_label"]) || $GraphTemplate["vertical_label"] == "")
			$GraphTemplate["vertical_label"] = "";		
		$command_line .= " --interlaced $base --imgformat PNG --width=$width --height=$height --title='$title metric ".$metric_ODS["metric_name"] ."' --vertical-label='".$GraphTemplate["vertical_label"]."' ";
		if ($centreon->optGen["rrdtool_version"] != "1.0")
			$command_line .= " --slope-mode ";
		
		if ($centreon->optGen["rrdtool_version"] == "1.3") {
           if (isset($centreon->optGen["rrdtool_title_font"]) && isset($centreon->optGen["rrdtool_title_fontsize"]))
              $command_line .= " --font TITLE:".$centreon->optGen["rrdtool_title_fontsize"].":".$centreon->optGen["rrdtool_title_font"]." ";
           if (isset($centreon->optGen["rrdtool_unit_font"]) && isset($centreon->optGen["rrdtool_unit_fontsize"]))
              $command_line .= " --font UNIT:".$centreon->optGen["rrdtool_unit_fontsize"].":".$centreon->optGen["rrdtool_unit_font"]." ";
           if (isset($centreon->optGen["rrdtool_axis_font"]) && isset($centreon->optGen["rrdtool_axis_fontsize"]))
              $command_line .= " --font AXIS:".$centreon->optGen["rrdtool_axis_fontsize"].":".$centreon->optGen["rrdtool_axis_font"]." ";
           if (isset($centreon->optGen["rrdtool_title_font"]) && isset($centreon->optGen["rrdtool_title_fontsize"]))
              $command_line .= " --font WATERMARK:".$centreon->optGen["rrdtool_title_fontsize"].":".$centreon->optGen["rrdtool_title_font"]." ";
           if (isset($centreon->optGen["rrdtool_legend_title"]) && isset($centreon->optGen["rrdtool_legend_fontsize"]))
              $command_line .= " --font LEGEND:".$centreon->optGen["rrdtool_legend_fontsize"].":".$centreon->optGen["rrdtool_legend_title"]." ";
        }

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
				
		/*
		 * Init DS template For each curve
		 */
		$metrics = array();
		$selector = "metric_id = '".$_GET["metric"]."'";
		$DBRESULT =& $pearDBO->query("SELECT metric_id, metric_name, unit_name, warn, crit, min, max FROM metrics WHERE ".$selector." AND `hidden` = '0' ORDER BY metric_name");
		while ($metric =& $DBRESULT->fetchRow()) {

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

			if (!$ds_data) {
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
				$metrics[$metric["metric_id"]]["legend"] = str_replace("slash_", "/", $metric["metric_name"]);
				$metrics[$metric["metric_id"]]["legend"] = str_replace("#S#", "/", $metrics[$metric["metric_id"]]["legend"]);
				$metrics[$metric["metric_id"]]["legend"] = str_replace("#BS#", "\/", $metrics[$metric["metric_id"]]["legend"]);
			} else {
				$metrics[$metric["metric_id"]]["legend"] = $ds_data["ds_name"];
			}

			if (strcmp($metric["unit_name"], ""))
				$metrics[$metric["metric_id"]]["legend"] .= " (".$metric["unit_name"].") ";
			$metrics[$metric["metric_id"]]["legend_len"] = strlen($metrics[$metric["metric_id"]]["legend"]);
		} /* while */
		$DBRESULT->free();
		
		$cpt = 0;
		$longer = 0;
		foreach ($metrics as $key => $tm){
			if (isset($tm["ds_invert"]) && $tm["ds_invert"])
				$command_line .= " DEF:va".$cpt."=".$RRDdatabase_path.$key.".rrd:".substr($metrics[$key]["metric"],0 , 19).":AVERAGE CDEF:v".$cpt."=va".$cpt.",-1,*";
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
		metricsort($metrics);
		foreach ($metrics as $key => $tm){
			
			if ($metrics[$key]["ds_filled"])
				$command_line .= " AREA:v".($cpt)."".$tm["ds_color_area"].$tm["ds_transparency"]." ";

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
			if (isset($tm["warn"]) && $tm["warn"] != 0)
				$command_line .= " HRULE:".$tm["warn"]."#00FF00:\"Warning \: ".$tm["warn"]."\\l\" "; 
			if (isset($tm["crit"]) && $tm["crit"] != 0)	
				$command_line .= " HRULE:".$tm["crit"]."#FF0000:\"Critical \: ".$tm["crit"]."\""; 
		}

		$command_line = $centreon->optGen["rrdtool_path_bin"].$command_line." 2>&1";
		
		/*
		 * Add Timezone for current user.
		 */
		if ($CentreonGMT->used())
			$command_line = "export TZ='CMT".$CentreonGMT->getMyGMTForRRD()."' ; ".$command_line;
	
		/*
		 * Escape Special Chars
		 */
		$command_line = escape_command("$command_line");
		if ( $centreon->optGen["debug_rrdtool"] == "1" )
			error_log("[" . date("d/m/Y H:s") ."] RDDTOOL : $command_line \n", 3, $centreon->optGen["debug_path"]."rrdtool.log");

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