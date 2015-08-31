<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */
if (!isset($oreon))
    exit;

function _TestRPNInfinityLoop () {
    global $form;
    $gsvs = NULL;
    if (isset($form))
        $gsvs = $form->getSubmitValues();
    
    if ( $gsvs["vmetric_name"] != NULL && preg_match("/".$gsvs["vmetric_name"]."/i",$gsvs["rpn_function"]) ) {
        return false;
    } else {
        return true;
    }
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

function deleteVirtualMetricInDB ($vmetrics = array()) {
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

function insertVirtualMetric() {
    global $form, $pearDB, $centreon;
    $h_id = NULL;
    $s_id = NULL;
    $ret = array();
    $ret = $form->getSubmitValues();
    $rq = "INSERT INTO `virtual_metrics` ( `vmetric_id` , `index_id`, `vmetric_name`, `def_type` , `rpn_function`, `unit_name` , `warn`, `crit`, `hidden` , `comment` , `vmetric_activate`, `ck_state`) ";
    $rq .= "VALUES ( NULL, ";
    isset($ret["index_id"]) && $ret["index_id"] != NULL ? $rq .= "'".$ret["index_id"]."', ": $rq .= "NULL, ";
    isset($ret["vmetric_name"]) && $ret["vmetric_name"] != NULL ? $rq .= "'".htmlentities($ret["vmetric_name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    isset($ret["def_type"]) && $ret["def_type"] != NULL ? $rq .= "'".$ret["def_type"]."', ": $rq .= "NULL, ";
    isset($ret["rpn_function"]) && $ret["rpn_function"] != NULL ? $rq .= "'".$ret["rpn_function"]."', ": $rq .= "NULL, ";
    isset($ret["unit_name"]) && $ret["unit_name"] != NULL ? $rq .= "'".$ret["unit_name"]."', ": $rq .= "NULL, ";
    isset($ret["warn"]) && $ret["warn"] != NULL ? $rq .= "'".$ret["warn"]."', ": $rq .= "NULL, ";
    isset($ret["crit"]) && $ret["crit"] != NULL ? $rq .= "'".$ret["crit"]."', ": $rq .= "NULL, ";
    isset($ret["vhidden"]) && $ret["vhidden"] != NULL ? $rq .= "'".$ret["vhidden"]."', ": $rq .= "NULL, ";
    isset($ret["comment"]) && $ret["comment"] != NULL ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    $rq .= "NULL, NULL";
    $rq .= ")";
    if ($centreon->optGen["debug_rrdtool"] == "1") {
        $debug_path = realpath($centreon->optGen["debug_path"]);
        error_log("[" . date("d/m/Y H:s") ."] VIRTUAL METRIC : $rq \n", 3, $debug_path . "/rrdtool.log");
    }
    $DBRESULT = $pearDB->query($rq);
    $DBRESULT = $pearDB->query("SELECT MAX(vmetric_id) FROM virtual_metrics");
    $vmetric_id = $DBRESULT->fetchRow();
    $vmetric_id = $vmetric_id["MAX(vmetric_id)"];
    return $vmetric_id;
}

function updateVirtualMetric($vmetric_id = null) {
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
    $rq .=  "warn = ";
    isset($ret["warn"]) && $ret["warn"] != NULL ? $rq .= "'".$ret["warn"]."', ": $rq .= "NULL, ";
    $rq .=  "crit = ";
    isset($ret["crit"]) && $ret["crit"] != NULL ? $rq .= "'".$ret["crit"]."', ": $rq .= "NULL, ";
    $rq .=  "hidden = ";
    isset($ret["vhidden"]) && $ret["vhidden"] != NULL ? $rq .= "'".$ret["vhidden"]."', ": $rq .= "NULL, ";
    $rq .=  "comment = ";
    isset($ret["comment"]) && $ret["comment"] != NULL ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    $rq .= "vmetric_activate = NULL, ck_state = NULL ";
    $rq .= "WHERE vmetric_id = '".$vmetric_id."'";
    $DBRESULT = $pearDB->query($rq);

    if (!enableVirtualMetricInDB($vmetric_id)) {
        disableVirtualMetricInDB($vmetric_id, 1);
    }
}

function disableVirtualMetricInDB($vmetric_id = null, $force = 0) {
    if (!$vmetric_id)
        return 0;
    global $pearDB;
    
    $v_dis = disableVirtualMetric($vmetric_id, $force);
    if (!count($v_dis)) {
        return 0;
    }
    foreach ($v_dis as $pkey => $vm) {
        $pearDB->query("UPDATE `virtual_metrics` SET `vmetric_activate` = '0' WHERE `vmetric_id` ='$vm';");
    }
    return 1;
}

function &disableVirtualMetric($v_id = null, $force = 0) {
    global $pearDB;
    $v_dis = array();
    
    $repA = array("*", "+", "-", "?", "^", "$");
    $repB = array("\\\\*", "\\\\+", "\\\\-", "\\\\?", "\\\\^", "\\\\$");
    $l_where = ($force == 0) ? " AND `vmetric_activate` = '1'" : "";
    $l_pqy = $pearDB->query("SELECT index_id, vmetric_name FROM `virtual_metrics` WHERE `vmetric_id`='$v_id'$l_where;");
    if ($l_pqy->numRows() == 1) {
        $vmetric = $l_pqy->fetchRow();
        $l_pqy->free();
        $l_pqy = $pearDB->query("SELECT vmetric_id FROM `virtual_metrics` WHERE `index_id`='".$vmetric["index_id"]."' AND `vmetric_activate` = '1' AND `rpn_function` REGEXP '(^|,)".str_replace($repA, $repB, $vmetric["vmetric_name"])."(,|$)';");
        while ($d_vmetric = $l_pqy->fetchRow()) {
            $lv_dis = disableVirtualMetric($d_vmetric["vmetric_id"]);
            if (is_array($lv_dis))
                foreach($lv_dis as $pkey => $vm)
                    $v_dis[] = $vm;
        }
        $l_pqy->free();
        if (!$force) {
            $v_dis[] = $v_id;
        }
    }
    return $v_dis;
}

function enableVirtualMetricInDB($vmetric_id = null) {
    if (!$vmetric_id)
        return 0;

    global $pearDB;

    $v_ena = enableVirtualMetric($vmetric_id);
    if (!count($v_ena)) {
        return 0;
    }
    foreach ($v_ena as $pkey => $v_id) {
        list($rc, $output) = checkRRDGraphData($v_id);
        if ($rc) {
            $error = preg_replace('/^ERROR:\s*/', '', $output);
            throw new Exception("Wrong RPN syntax (RRDtool said: $error)");
        }
        $pearDB->query("UPDATE `virtual_metrics` SET `vmetric_activate` = '1' WHERE `vmetric_id` ='$v_id';");
    }
    return 1;
}

function enableVirtualMetric($v_id, $v_name = null, $index_id = null) {
    global $pearDB;
    $v_ena = array();

    $l_where = "vmetric_id = '$v_id'";
    if (is_null($v_id)) {
        $l_where = "vmetric_name = '$v_name' AND index_id ='$index_id'";
    }

    $l_pqy = $pearDB->query("SELECT vmetric_id, index_id, rpn_function FROM virtual_metrics WHERE $l_where AND (vmetric_activate = '0' OR vmetric_activate IS NULL);");
    if ($l_pqy->numRows() == 1) {
        $p_vmetric = $l_pqy->fetchRow();
        $l_mlist = preg_split("/\,/", $p_vmetric["rpn_function"]);
        foreach ($l_mlist as $l_mnane) {
            $lv_ena = enableVirtualMetric(NULL, $l_mnane, $p_vmetric["index_id"]);
            if (is_array($lv_ena))
                foreach ($lv_ena as $pkey => $vm)
                    $v_ena[] = $vm;
        }
        $v_ena[] = $p_vmetric["vmetric_id"];
    }
    $l_pqy->free();
    return $v_ena;
}

?>
