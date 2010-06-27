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
 * For more information : contact@centreon.com
 * 
 * File : Image-Func.php D.Porte
 * 
 */

	function subsRPN($rpn, $vname) {
		$l_list = split(",",$rpn);
		$l_rpn = "";
		foreach( $l_list as $l_m) {
			if ( isset($vname[$l_m]) )
				$l_rpn .= $vname[$l_m].",";
			else
				$l_rpn .= $l_m.",";
		}
		return substr($l_rpn,0,strlen($l_rpn) - 1);
	}


	/* need : [0]->need/visible [1]->need/hidden */
	/* Recursive function */
	function manageVMetric($vm_id, $vm_name, $index_id, $check = 0) {
		global $rmetrics, $vmetrics, $mlist, $ptr;
		
		/* Manage Virtual Metrics */
		$l_whidden = "";
		if ( $check == 0 )
			$l_whidden = " AND ( hidden = '0' OR hidden IS NULL ) AND vmetric_activate = '1'";

		if ( is_null($vm_id) )
			$l_where = "vmetric_name = '".$vm_name."' AND index_id ='".$index_id."'";
		else
			$l_where = "vmetric_id = '".$vm_id."'".$l_whidden;

		$l_pqy =& $this->DB->query("SELECT vmetric_id metric_id, index_id, vmetric_name metric_name, unit_name, def_type, rpn_function FROM virtual_metrics WHERE ".$l_where." ORDER BY metric_name");
		/* metric_id should be unique*/
		if ( $l_pqy->numRows() == 1 ) {
			$l_vmetric =& $l_pqy->fetchRow();
			$l_pqy->free();
			if ( !isset($mlist["v".$l_vmetric["metric_id"]]) ) {
				if ( is_null($vm_id) )
					$l_vmetric["need"] = 1; /* 1 : Need this virtual metric : Hidden */
				/* Find Host/Service For this metric_id */
				$l_poqy =& $this->DBC->query("SELECT host_id, service_id FROM index_data WHERE id = '".$l_vmetric["index_id"]."'");
				$l_indd =& $l_poqy->fetchRow();
				$l_poqy->free();
				/* Check for real or virtual metric(s) in the RPN function */
				$l_mlist = split(",",$l_vmetric["rpn_function"]);
				foreach ( $l_mlist as $l_mnane ) {
					/* Check for a real metric */
					$l_poqy =& $this->DBC->query("SELECT host_id, service_id, metric_id, metric_name, unit_name, warn, crit FROM metrics AS m, index_data as i WHERE index_id = id AND index_id = '".$l_vmetric["index_id"]."' AND metric_name = '".$l_mnane."'");
					if ( $l_poqy->numRows() == 1) {
						/* Find a real metric in the RPN function */
						$l_rmetric =& $l_poqy->fetchrow();
						$l_poqy->free();
						$l_rmetric["need"] = 1; /* 1 : Need this real metric - hidden */
						if ( !isset($mlist[$l_rmetric["metric_id"]]) ) {
							$mlist[$l_rmetric["metric_id"]] = $ptr[0]++;
							$rmetrics[] = $l_rmetric;
						} else {
							/* We Already found the real metrics in the array
							 * Make sure, it's added 
							 */
							$l_pointer = $mlist[$l_rmetric["metric_id"]];
							if ( !isset($rmetrics[$l_pointer]["need"]) )
								$rmetrics[$l_pointer]["need"] = 0;
						}
					} elseif ( $l_poqy->numRows() == 0 ) {
						/* key : id or vname and iid */
						$l_poqy->free();
						manageVMetric(NULL, $l_mnane, $l_vmetric["index_id"]);
					} else
						$l_poqy->free();
				}
				$l_vmetric["metric_id"] = "v".$l_vmetric["metric_id"];
				$l_vmetric["host_id"] = $l_indd["host_id"];
				$l_vmetric["service_id"] = $l_indd["service_id"];
				$l_vmetric["virtual"] = 1;
				$l_vmetric["cdef_order"]=$ptr[1];
				$mlist[$l_vmetric["metric_id"]] = $ptr[1]++;
				$vmetrics[] = $l_vmetric;
			} else {
				/* We Already found the virtual metrics in the array
				 * Make sure, it's added 
				 */
				$l_pointer = $mlist["v".$l_vmetric["metric_id"]];
				if ( is_null($vm_id) )
					if ( !isset($vmetrics[$l_pointer]["need"]) || $vmetrics[$l_pointer]["need"] != 1 )
						$vmetrics[$l_pointer]["need"] = 0;
				else
					if ( !isset($vmetrics[$l_pointer]["need"]) || $vmetrics[$l_pointer]["need"] == 1 )
						$vmetrics[$l_pointer]["need"] = 0;
			}
		} else {
			$l_pqy->free();
		}
	}
?>
