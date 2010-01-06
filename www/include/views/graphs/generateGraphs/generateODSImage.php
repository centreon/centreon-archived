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

	/*
	 * Include config file
	 */
	include "@CENTREON_ETC@/centreon.conf.php";
		
	require_once "./DB-Func.php";
	require_once "$centreon_path/www/class/centreonGraph.class.php";

	/*
	 * Create XML Request Objects
	 */
	$obj = new CentreonGraph($_GET["session_id"], $_GET["index"], 0, 1);
	
	if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
		;
	} else {
		$obj->displayError();
	}
	
	require_once $centreon_path."www/include/common/common-Func.php";

	/*
	 * Set General Options
	 */
	$obj->setGeneralOption();
	
	/*
	 * Set arguments from GET
	 */	
	$obj->start 	= $obj->checkArgument("start", $_GET, time() - (60*60*48));
	$obj->end 		= $obj->checkArgument("end", $_GET, time());
		
 	$obj->GMT->getMyGMTFromSession($obj->session_id, $pearDB);

	/*
	 * Check Graphs size
	 */
	if (isset($graph_width) && $graph_width != "") 
		$obj->setWidth($graph_width);
	if (isset($graph_height) && $graph_height != "")
		$obj->setHeight($graph_height);	

	/*
	 * Template Management
	 */
	$obj->setTemplate($_GET["template_id"]);
	
	/*
	 * Get Activate Metrics
	 */
	$obj->setActivateMetrics();
	
	/*
	 * Begin command line build
	 */
	$obj->initCommandLine();
	$obj->addCommandLineTimeLimit($_GET["flagperiod"]);
			
	/*
	 * Init DS template For each curv
	 */
	$cpt = 0;
	$metrics = array();		
	$DBRESULT =& $obj->DBC->query("SELECT metric_id, metric_name, unit_name, warn, crit FROM metrics WHERE index_id = '".$_GET["index"]."' AND `hidden` = '0' ORDER BY metric_name");
	while ($metric =& $DBRESULT->fetchRow()){
		if (!isset($_GET["metric"]) || (isset($_GET["metric"]) && isset($_GET["metric"][$metric["metric_id"]]))){	
			if (!isset($obj->metricsActivate) || (isset($obj->metricsActivate) && isset($obj->metricsActivate[$metric["metric_id"]]) && $obj->metricsActivate[$metric["metric_id"]])){
				
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
				foreach ($ds_data as $key => $ds_d) {
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
			$obj->commandLine .= " DEF:va".$cpt."=".$obj->dbPath.$key.".rrd:".substr($metrics[$key]["metric"],0 , 19).":AVERAGE CDEF:v".$cpt."=va".$cpt.",-1,* ";
		else
			$obj->commandLine .= " DEF:v".$cpt."=".$obj->dbPath.$key.".rrd:".substr($metrics[$key]["metric"],0 , 19).":AVERAGE ";
		if ($tm["legend_len"] > $longer)
			$longer = $tm["legend_len"];
		$cpt++;
	}
	
	/*
	 * Comment time
	 */
	$obj->addCommentTime();
	
	/*
	 * Create Legende
	 */
	$cpt = 0;
	foreach ($metrics as $key => $tm) {
		
		if ($metrics[$key]["ds_filled"])
			$obj->commandLine .= " AREA:v".($cpt).$tm["ds_color_area"].$tm["ds_transparency"]." ";
		
		$obj->commandLine .= " LINE".$tm["ds_tickness"].":v".$cpt.$tm["ds_color_line"].":\"".$metrics[$key]["legend"];
		
		for ($i = $metrics[$key]["legend_len"]; $i != $longer + 1; $i++)
			$obj->commandLine .= " ";
			$obj->commandLine .= "\"";
		if ($tm["ds_last"]){
			$obj->commandLine .= " GPRINT:v".($cpt).":LAST:\"Last\:%7.2lf".($gprint_scale_cmd);
			$tm["ds_min"] || $tm["ds_max"] || $tm["ds_average"] ? $obj->commandLine .= "\"" : $obj->commandLine .= "\\l\" ";
		}
		if ($tm["ds_min"]){
			$obj->commandLine .= " GPRINT:v".($cpt).":MIN:\"Min\:%7.2lf".($gprint_scale_cmd);
			$tm["ds_max"] || $tm["ds_average"] ? $obj->commandLine .= "\"" : $obj->commandLine .= "\\l\" ";
		}
		if ($tm["ds_max"]){
			$obj->commandLine .= " GPRINT:v".($cpt).":MAX:\"Max\:%7.2lf".($gprint_scale_cmd); 
			$tm["ds_average"] ? $obj->commandLine .= "\"" : $obj->commandLine .= "\\l\" ";
		}
		if ($tm["ds_average"]){
			$obj->commandLine .= " GPRINT:v".($cpt).":AVERAGE:\"Average\:%7.2lf".($gprint_scale_cmd)."\\l\"";
		}
		$cpt++;
	}

	$obj->endCommandLine();
	/*
	 * Add Timezone for current user.
	 */
	$obj->setTimezone();
	//print $obj->commandLine;
	
	/*
	 * Display Images Binary Data
	 */
	$obj->displayImageFlow();
?>