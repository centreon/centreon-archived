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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/views/graphs/generateGraphs/generateODSMetricImage.php $
 * SVN : $Id: generateODSMetricImage.php 8741 2009-07-31 10:20:47Z jmathis $
 * 
 */
	if (!isset($oreon))
		exit;

	/* Need Global Varriables */
	$rmetrics = array();
	$vmetrics = array();
	$mlist = array();
	$ptr = array(0, 0);
 	
	function escape_command($command) {
		return ereg_replace("(\\\$|`)", "", $command);
	}

	function checkRRDGraphData($v_id = null, $force = 0) {
		if (!$v_id) return;
		
		global $pearDB, $pearDBO, $rmetrics, $vmetrics, $mlist, $ptr;

		/* Check if already Valid */

        $l_pqy =& $pearDB->query("SELECT vmetric_id, def_type FROM virtual_metrics WHERE vmetric_id = '".$v_id."' AND ( ck_state <> '1' OR ck_state IS NULL );");
        /* There is only one metric_id */
        if ( $l_pqy->numRows() == 1 ) {
			$l_pqy->free();
			$oreon =& $_SESSION["oreon"];

			# array of replacement
			$repA = array("#S#", "#BS#");
			$repB = array("/", "\\");
			$repC = array("slash_", "bslash_");

			$po_qy =& $pearDBO->query("SELECT RRDdatabase_path FROM config LIMIT 1");
			$config =& $po_qy->fetchRow();
			$po_qy->free();

			$command_line = " graph -";

			# Init DS template For each curv
			$rmetrics = array();
			$vmetrics = array();

			$ptr = array(0, 0); /* Metrics Pointer*/
			$mlist = array() ; /* List Metrics Already Known */
			manageVMetric($v_id, NULL, NULL, 1);

			# Merge All metrics
			$mmetrics = array_merge($rmetrics, $vmetrics);

			$metrics = array();
			foreach( $mmetrics as $key => $metric) {
				/*
			 	* Construct metric name for detect metric graph template.
			 	*/
				$metricNameForGraph = str_replace($repA, $repB, $metric["metric_name"]);
	
				$mid = $metric["metric_id"];
				if (isset($metric["virtual"]))
					$metrics[$mid]["virtual"] = $metric["virtual"];
				$metrics[$mid]["metric_id"] = $metric["metric_id"];
				$metrics[$mid]["metric"] = str_replace($repA, $repC, $metric["metric_name"]);
			
				$res_ds =& $pearDB->query("SELECT * FROM giv_components_template WHERE ( host_id = '".$metric["host_id"]."' OR host_id IS NULL ) AND ( service_id = '".$metric["service_id"]."' OR service_id IS NULL ) AND ds_name  = '".$metricNameForGraph."' ORDER BY host_id DESC");
				$ds_data =& $res_ds->fetchRow();
				$res_ds->free();

				if (!isset($metric["need"]) || $metric["need"] != 1) {
					if (!$ds_data){
						$ds = getDefaultDS();
						$res_ds =& $pearDB->query("SELECT * FROM giv_components_template WHERE compo_id = '".$ds."'");
						$ds_data =& $res_ds->fetchRow();
						$res_ds->free();
						$metrics[$mid]["ds_id"] = $ds;
					}

					/*
				 	* Fetch Datas
				 	*/

					foreach ($ds_data as $key => $ds_d){
						if ($key == "ds_transparency"){
							$transparency = dechex(255-($ds_d*255)/100);
							if (strlen($transparency) == 1)
								$transparency = "0" . $transparency;
							$metrics[$mid][$key] = $transparency;
						} else
							$metrics[$mid][$key] = $ds_d;
					}
				}

				if ( strlen($ds_data["ds_legend"]) > 0 )
					$metrics[$mid]["legend"] = $ds_data["ds_legend"];
				else {	
					if (preg_match('/DS/', $ds_data["ds_name"], $matches))
						$metrics[$mid]["legend"] = str_replace($repC, $repB, $metric["metric_name"]);
					else
						$metrics[$mid]["legend"] = $ds_data["ds_name"];
				}
				$metrics[$mid]["stack"] = $ds_data["ds_stack"];
				if (isset($metric["need"])) {
					$metrics[$mid]["need"] = $metric["need"];
					if ($metric["need"] == 1)
						$metrics[$mid]["ds_order"] = "0";
				} else
					$metrics[$mid]["ds_order"] = $ds_data["ds_order"];
				if (isset($metric["def_type"]))
					$metrics[$mid]["def_type"] = $metric["def_type"];
				if (isset($metric["rpn_function"]))
					$metrics[$mid]["rpn_function"] = $metric["rpn_function"];
			}

			$cpt = 0;
			$longer = 0;
			$vname = array();
			$stack = array();
			$lcdef = array();
			foreach ($metrics as $key => $tm){
				if (!isset($tm["virtual"]) && isset($tm["need"]) && $tm["need"] == 1) {
					$command_line .= " DEF:v".$cpt."=".$config["RRDdatabase_path"].$key.".rrd:".substr($tm["metric"],0,19).":AVERAGE ";
					$vname[$tm["metric"]] = "v".$cpt;
					$cpt++;
					continue;
				}
				if (isset($tm["virtual"])) {
					$lcdef[$key] = $tm;
					$vname[$tm["metric"]] = "vv".$cpt;
					$cpt++;
				} else {
					if (isset($tm["ds_invert"]) && $tm["ds_invert"])
						$command_line .= " DEF:vi".$cpt."=".$config["RRDdatabase_path"].$key.".rrd:".substr($tm["metric"],0,19).":AVERAGE CDEF:v".$cpt."=vi".$cpt.",-1,* ";
					else
						$command_line .= " DEF:v".$cpt."=".$config["RRDdatabase_path"].$key.".rrd:".substr($tm["metric"],0,19).":AVERAGE ";
					$vname[$tm["metric"]] = "v".$cpt;
					$cpt++;
				}
			}
			$deftype = array(0 => "CDEF", 1 => "VDEF");
			foreach ($lcdef as $key => $tm){
				$rpn = subsRPN($tm["rpn_function"],$vname);
				$command_line .= $deftype[$tm["def_type"]].":".$vname[$tm["metric"]]."=".$rpn;
				if (isset($tm["ds_invert"]) && $tm["ds_invert"])
					$command_line .= ",-1,*";
				$command_line .= " ";
			}

			# Create Legende
			$i = 0;
			$cpt = 1;
			$rpn_values = "";
			$rpn_expr = "";
			foreach ($metrics as $key => $tm){
				if (isset($tm["need"]) && $tm["need"] >= 0)
					continue;
				if ($tm["ds_filled"] || $tm["ds_stack"] ) {
					$command_line .= " AREA:".$vname[$tm["metric"]].$tm["ds_color_area"].$tm["ds_transparency"]."";
					if ( $cpt != 0 && $tm["ds_stack"] ) {
						$command_line .= "::STACK";
						$command_line .= " CDEF:vc".$cpt."=".$rpn_values.$vname[$tm["metric"]].$rpn_expr;
					}
					$rpn_values .= $vname[$tm["metric"]].",";
					$rpn_expr .= ",+";
				}
				if (!isset($tm["ds_stack"]) || !$tm["ds_stack"] || $cpt == 0)
					$command_line .= " LINE".$tm["ds_tickness"].":".$vname[$tm["metric"]];
				else
					$command_line .= " LINE".$tm["ds_tickness"].":vc".$cpt;
				$command_line .= $tm["ds_color_line"].":\"";
				$command_line .= $tm["legend"];
				$command_line .= "\"";
				if ($tm["ds_stack"])
					$cpt++;
			}

			$command_line = $oreon->optGen["rrdtool_path_bin"].$command_line;
			$command_line = escape_command("$command_line");

			exec($command_line, $result, $rc);
			if ( $rc == 0 )
				$p_qy  =& $pearDB->query("UPDATE `virtual_metrics` SET `ck_state` = '1' WHERE `vmetric_id` ='".$v_id."';");
			else
				$p_qy  =& $pearDB->query("UPDATE `virtual_metrics` SET `ck_state` = '2' WHERE `vmetric_id` ='".$v_id."';");
			return $rc;
		} else
			return 0;
	}
?>
