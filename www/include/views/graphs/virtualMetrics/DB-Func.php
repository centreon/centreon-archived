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
 *
 * DB-Func.php david PORTE $
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

	function NameTestExistence ($vmetric_name = NULL, $index_id = NULL) {
		global $pearDB, $pearDBO, $form;
		$gsvs = NULL;
		if (isset($form))
			$gsvs = $form->getSubmitValues();

		$sql = "SELECT vmetric_id FROM virtual_metrics WHERE ";
		$sql .= "vmetric_name = '".($vmetric_name == NULL ? $gsvs["vmetric_name"] : $vmetric_name)."' ";
		$sql .= "AND index_id = '".($index_id == NULL ? $gsvs["index_id"] : $index_id)."'";
		$DBRESULT = $pearDB->query($sql);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo();
		$vmetric = $DBRESULT->fetchRow();
		$nR = $DBRESULT->numRows();
		$DBRESULT->free();

		$sql = "SELECT metric_id FROM metrics WHERE ";
		$sql .= "metric_name = '".($vmetric_name == NULL ? $gsvs["vmetric_name"] : $vmetric_name)."' ";
		$sql .= "AND index_id = '".($index_id == NULL ? $gsvs["index_id"] : $index_id)."'";
		$DBRESULT = $pearDBO->query($sql);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo();
		$metric = $DBRESULT->fetchRow();
		$nR = $nR + $DBRESULT->numRows();
		$DBRESULT->free();

		if ( $nR >= 1 && $vmetric["vmetric_id"] != $gsvs["vmetric_id"] || isset($metric["metric_id"]) )
			return false;
		else
			return true;
	}

	function deleteVirtualMetricInDB ($vmetrics = array())	{
		global $pearDB;
		foreach($vmetrics as $key => $value){
			$DBRESULT = $pearDB->query("DELETE FROM virtual_metrics WHERE vmetric_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo();
		}
	}	

	function multipleVirtualMetricInDB ($vmetrics = array(), $nbrDup = array())	{
		global $pearDB;
		foreach($vmetrics as $key=>$value) {
			$DBRESULT = $pearDB->query("SELECT * FROM virtual_metrics WHERE vmetric_id = '".$key."' LIMIT 1");
			
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo();
			$row = $DBRESULT->fetchRow();
			$row["vmetric_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "index_id" ? $i_id = $value2 : null;
					if ($key2 == "vmetric_name") {
						$count = 1;
						$v_name = $value2."_".$count;
						while (!NameTestExistence($v_name,$i_id)) {
							$count++;
							$v_name = $value2."_".$count;
						}
						$value2 = $v_name;
					}
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				$val ? $rq = "INSERT INTO virtual_metrics VALUES (".$val.")" : $rq = null;
				$DBRESULT2 = $pearDB->query($rq);
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo();
			}
		}
	}
	
	function updateVirtualMetricInDB ($vmetric_id = NULL)	{
		if (!$vmetric_id) return;
		updateVirtualMetric($vmetric_id);
	}	
	
	function insertVirtualMetricInDB ()	{
		$vmetric_id = insertVirtualMetric();
		return ($vmetric_id);
	}

	/* recursive function */
	function enableVirtualMetricInDB($vmetric_id = null) {
		if (!$vmetric_id) return;
		global $pearDB;
		$v_ena = array();		
		$v_ena = enableVirtualMetric($vmetric_id);
		foreach($v_ena as $pkey => $v_id) {
//			if ( checkRRDGraphData($v_id) == 0 )
				$p_qy  = $pearDB->query("UPDATE `virtual_metrics` SET `vmetric_activate` = '1' WHERE `vmetric_id` ='".$v_id."';");
//			else
//				break;
		}
	}
	
	function disableVirtualMetricInDB($vmetric_id = null, $force = 0) {
		if (!$vmetric_id) return;
		global $pearDB;
		$v_dis= array();
		$v_dis = disableVirtualMetric($vmetric_id, $force);
		foreach($v_dis as $pkey => $vm)
			$p_qy  = $pearDB->query("UPDATE `virtual_metrics` SET `vmetric_activate` = '0' WHERE `vmetric_id` ='".$vm."';");
	}

	function insertVirtualMetric()	{
		global $form, $pearDB;
		$h_id = NULL;
		$s_id = NULL;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "INSERT INTO `virtual_metrics` ( `vmetric_id` , `index_id`, `vmetric_name`, `def_type` , `rpn_function`, `unit_name` , `hidden` , `comment` , `vmetric_activate`, `ck_state`) ";
		$rq .= "VALUES ( NULL, ";
                isset($ret["index_id"]) && $ret["index_id"] != NULL ? $rq .= "'".$ret["index_id"]."', ": $rq .= "NULL, ";
		isset($ret["vmetric_name"]) && $ret["vmetric_name"] != NULL ? $rq .= "'".htmlentities($ret["vmetric_name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["def_type"]) && $ret["def_type"] != NULL ? $rq .= "'".$ret["def_type"]."', ": $rq .= "NULL, ";
		isset($ret["rpn_function"]) && $ret["rpn_function"] != NULL ? $rq .= "'".$ret["rpn_function"]."', ": $rq .= "NULL, ";
		isset($ret["unit_name"]) && $ret["unit_name"] != NULL ? $rq .= "'".$ret["unit_name"]."', ": $rq .= "NULL, ";
		isset($ret["vhidden"]) && $ret["vhidden"] != NULL ? $rq .= "'".$ret["vhidden"]."', ": $rq .= "NULL, ";
		isset($ret["comment"]) && $ret["comment"] != NULL ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES, "UTF-8")."'": $rq .= "NULL, ";
		$rq .= "NULL, NULL";
		$rq .= ")";
		$DBRESULT = $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo();
		$DBRESULT = $pearDB->query("SELECT MAX(vmetric_id) FROM virtual_metrics");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo();
		$vmetric_id = $DBRESULT->fetchRow();
		return ($vmetric_id["MAX(vmetric_id)"]);
	}
	
	function updateVirtualMetric($vmetric_id = null)	{
		if (!$vmetric_id) return;
		global $form, $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE virtual_metrics ";
		$rq .= "SET `index_id` = ";
                isset($ret["index_id"]) && $ret["index_id"] != NULL ? $rq .= "'".$ret["index_id"]."', ": $rq .= "NULL, ";
		$rq .=  "vmetric_name = ";
		isset($ret["vmetric_name"]) && $ret["vmetric_name"] != NULL ? $rq .= "'".htmlentities($ret["vmetric_name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .=  "def_type = ";
		isset($ret["def_type"]) && $ret["def_type"] != NULL ? $rq .= "'".$ret["def_type"]."', ": $rq .= "NULL, ";
		$rq .=  "rpn_function = ";
		isset($ret["rpn_function"]) && $ret["rpn_function"] != NULL ? $rq .= "'".$ret["rpn_function"]."', ": $rq .= "NULL, ";
		$rq .=  "unit_name = ";
		isset($ret["unit_name"]) && $ret["unit_name"] != NULL ? $rq .= "'".$ret["unit_name"]."', ": $rq .= "NULL, ";
		$rq .=  "hidden = ";
		isset($ret["vhidden"]) && $ret["vhidden"] != NULL ? $rq .= "'".$ret["vhidden"]."', ": $rq .= "NULL, ";
		$rq .=  "comment = ";
		isset($ret["comment"]) && $ret["comment"] != NULL ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "vmetric_activate = NULL, ck_state = NULL ";
		$rq .= "WHERE vmetric_id = '".$vmetric_id."'";
		$DBRESULT = $pearDB->query($rq);

//		if ( checkRRDGraphData($vmetric_id) == 0 )
			enableVirtualMetricInDB($vmetric_id);
//		else
//			disableVirtualMetricInDB($vmetric_id, 1);
		
	}		

	/* recursive function */
	function &disableVirtualMetric($v_id = null, $force = 0) {
		global $pearDB;

		$repA = array("*", "+", "-", "?", "^", "$");
		$repB = array("\\\\*", "\\\\+", "\\\\-", "\\\\?", "\\\\^", "\\\\$");
		$l_where = "";
		if ($force == 0)
			$l_where = " AND `vmetric_activate` = '1'";

		$l_pqy = $pearDB->query("SELECT index_id, vmetric_name FROM `virtual_metrics` WHERE `vmetric_id`='".$v_id."'".$l_where.";");
		if ( $l_pqy->numRows() == 1 ) {		
			$vmetric = $l_pqy->fetchRow();
			$l_pqy->free();
			$l_pqy = $pearDB->query("SELECT vmetric_id FROM `virtual_metrics` WHERE `index_id`='".$vmetric["index_id"]."' AND `vmetric_activate` = '1' AND `rpn_function` REGEXP '(^|,)".str_replace($repA, $repB, $vmetric["vmetric_name"])."(,|$)';");
			while ($d_vmetric = $l_pqy->fetchRow()) {
				$lv_dis = disableVirtualMetric($d_vmetric["vmetric_id"]);		
				if ( is_array($lv_dis))
					foreach($lv_dis as $pkey => $vm)
						$v_dis[] = $vm;	
			}
			$l_pqy->free();
			if ($force == 0)
				$v_dis[] = $v_id;
			return $v_dis;
		}
	}

	/* recursive function */
	function &enableVirtualMetric($v_id, $v_name = null, $index_id = null) {
		global $pearDB;

		if ( is_null($v_id) )
			if ( is_null($v_name) || is_null($index_id) )
			    return;
			else
			    $l_where = "vmetric_name = '".$v_name."' AND index_id ='".$index_id."'";
		else
			$l_where = "vmetric_id = '".$v_id."'";
		
		$l_pqy = $pearDB->query("SELECT vmetric_id, index_id, rpn_function FROM virtual_metrics WHERE ".$l_where." AND (vmetric_activate = '0' OR vmetric_activate IS NULL);");	
		if ( $l_pqy->numRows() == 1 ) {
			$p_vmetric = $l_pqy->fetchRow();
			$l_pqy->free();
			$l_mlist = split(",",$p_vmetric["rpn_function"]);
			foreach ( $l_mlist as $l_mnane ){
				$lv_ena = enableVirtualMetric(NULL, $l_mnane, $p_vmetric["index_id"]);
				if ( is_array($lv_ena))
					foreach($lv_ena as $pkey => $vm)
						$v_ena[] = $vm;
			}
			$v_ena[] = $p_vmetric["vmetric_id"];
			return $v_ena;
		} else 
			$l_pqy->free();
	}

?>
