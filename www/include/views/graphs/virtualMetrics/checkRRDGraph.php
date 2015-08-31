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

function checkRRDGraphData($v_id = null, $force = 0) {
    if (!isset($v_id)) null;
    
    global $pearDB, $oreon;
    /* Check if already Valid */
    
    $l_pqy = $pearDB->query("SELECT vmetric_id, def_type FROM virtual_metrics WHERE vmetric_id = '$v_id' AND ( ck_state <> '1' OR ck_state IS NULL );");
    if ($l_pqy->numRows() == 1) {
        /**
         * Create XML Request Objects
         */
        $obj = new CentreonGraph(session_id(), NULL, 0, 1);

        /**
         * We check only one curve
         **/
        $obj->onecurve = true;
        $obj->checkcurve = true;

        $obj->init();
        /**
         * Init Curve list
         */
        $obj->setMetricList("v$v_id");
        $obj->initCurveList();

        /**
         * Create Legend
         */
        $obj->createLegend();

        /**
         * Display Images Binary Data
         */
        $lastline = exec($oreon->optGen["rrdtool_path_bin"] . $obj->displayImageFlow() . " 2>&1", $result, $rc);
        $ckstate = (!$rc) ? '1' : '2';
        $pearDB->query("UPDATE `virtual_metrics` SET `ck_state` = '$ckstate' WHERE `vmetric_id` ='$v_id';");
        return array($rc, $lastline);
    }
    return null;
}
?>
