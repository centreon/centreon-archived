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
 * SVN : $URL: $
 * SVN : $Id: $
 *
 */
if (!isset($oreon))
    exit;

function checkRRDGraphData($v_id = null, $force = 0) {
    if (!isset($v_id)) null;
    
    global $pearDB;
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
        $lastline = exec($obj->displayImageFlow() . " 2>&1", $result, $rc);
        $ckstate = (!$rc) ? '1' : '2';
        $pearDB->query("UPDATE `virtual_metrics` SET `ck_state` = '$ckstate' WHERE `vmetric_id` ='$v_id';");
        return array($rc, $lastline);
    }
    return null;
}
?>
