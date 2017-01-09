<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
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
if (!isset($oreon)) {
    exit;
}

function _TestRPNInfinityLoop()
{
    global $form;
    $gsvs = null;
    if (isset($form)) {
        $gsvs = $form->getSubmitValues();
    }
    
    if ($gsvs["vmetric_name"] != null && preg_match("/".$gsvs["vmetric_name"]."/i", $gsvs["rpn_function"])) {
        return false;
    } else {
        return true;
    }
}


function NameTestExistence($vmetric_name = null, $index_id = null)
{
    global $pearDB, $pearDBO, $form;
    $gsvs = null;
    if (isset($form)) {
        $gsvs = $form->getSubmitValues();
    }
    
    $sql = "SELECT vmetric_id FROM virtual_metrics WHERE ";
    $sql .= "vmetric_name = '".($vmetric_name == null ? $gsvs["vmetric_name"] : $vmetric_name)."' ";
    $sql .= "AND index_id = '".($index_id == null ? $gsvs["index_id"] : $index_id)."'";
    $DBRESULT = $pearDB->query($sql);
    if (PEAR::isError($DBRESULT)) {
        print "DB Error : ".$DBRESULT->getDebugInfo();
    }
    $vmetric = $DBRESULT->fetchRow();
    $nR = $DBRESULT->numRows();
    $DBRESULT->free();

    $sql = "SELECT metric_id FROM metrics WHERE ";
    $sql .= "metric_name = '".($vmetric_name == null ? $gsvs["vmetric_name"] : $vmetric_name)."' ";
    $sql .= "AND index_id = '".($index_id == null ? $gsvs["index_id"] : $index_id)."'";
    $DBRESULT = $pearDBO->query($sql);
    if (PEAR::isError($DBRESULT)) {
        print "DB Error : ".$DBRESULT->getDebugInfo();
    }
    $metric = $DBRESULT->fetchRow();
    $nR = $nR + $DBRESULT->numRows();
    $DBRESULT->free();

    if ($nR >= 1 && $vmetric["vmetric_id"] != $gsvs["vmetric_id"] || isset($metric["metric_id"])) {
        return false;
    } else {
        return true;
    }
}

function deleteVirtualMetricInDB($vmetrics = array())
{
    global $pearDB;
    foreach ($vmetrics as $key => $value) {
        $DBRESULT = $pearDB->query("DELETE FROM virtual_metrics WHERE vmetric_id = '".$key."'");
        if (PEAR::isError($DBRESULT)) {
            print "DB Error : ".$DBRESULT->getDebugInfo();
        }
    }
}

function multipleVirtualMetricInDB($vmetrics = array(), $nbrDup = array())
{
    global $pearDB;
    foreach ($vmetrics as $key => $value) {
        $DBRESULT = $pearDB->query("SELECT * FROM virtual_metrics WHERE vmetric_id = '".$key."' LIMIT 1");

        if (PEAR::isError($DBRESULT)) {
            print "DB Error : ".$DBRESULT->getDebugInfo();
        }
        $row = $DBRESULT->fetchRow();
        $row["vmetric_id"] = '';
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "index_id" ? $i_id = $value2 : null;
                if ($key2 == "vmetric_name") {
                    $count = 1;
                    $v_name = $value2."_".$count;
                    while (!NameTestExistence($v_name, $i_id)) {
                        $count++;
                        $v_name = $value2."_".$count;
                    }
                    $value2 = $v_name;
                }
                $val ? $val .= ($value2!=null?(", '".$value2."'"):", NULL") : $val .= ($value2!=null?("'".$value2."'"):"NULL");
            }
            $val ? $rq = "INSERT INTO virtual_metrics VALUES (".$val.")" : $rq = null;
            $DBRESULT2 = $pearDB->query($rq);
            if (PEAR::isError($DBRESULT2)) {
                print "DB Error : ".$DBRESULT2->getDebugInfo();
            }
        }
    }
}

function updateVirtualMetricInDB($vmetric_id = null)
{
    if (!$vmetric_id) {
        return;
    }
    updateVirtualMetric($vmetric_id);
}

function insertVirtualMetricInDB()
{
    $vmetric_id = insertVirtualMetric();
    return ($vmetric_id);
}

function insertVirtualMetric()
{
    global $form, $pearDB, $pearDBO, $centreon;
    $h_id = null;
    $s_id = null;
    $ret = array();
    $ret = $form->getSubmitValues();
    
    $rq = "INSERT INTO `virtual_metrics` ( `vmetric_id` , `index_id`, `vmetric_name`, `def_type` , `rpn_function`, `unit_name` , `warn`, `crit`, `hidden` , `comment` , `vmetric_activate`, `ck_state`) ";
    $rq .= "VALUES ( NULL, ";

    if (isset($ret["host_id"]) && preg_match('/\d+\-\d+/', $ret["host_id"])) {
        # Get index_id
        list($host_id, $service_id) = explode('-', $ret["host_id"]);
        $query = "SELECT id "
            . "FROM index_data "
            . "WHERE host_id = " . $host_id . " "
            . "AND service_id = " . $service_id . " ";
        $result = $pearDBO->query($query);
        if ($row = $result->fetchRow()) {
            $rq .= "'" . $row['id'] . "', ";
        } else {
            $rq .= "NULL, ";
        }
    } else {
        $rq .= "NULL, ";
    }
    isset($ret["vmetric_name"]) && $ret["vmetric_name"] != null ? $rq .= "'".htmlentities($ret["vmetric_name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    isset($ret["def_type"]) && $ret["def_type"] != null ? $rq .= "'".$ret["def_type"]."', ": $rq .= "NULL, ";
    isset($ret["rpn_function"]) && $ret["rpn_function"] != null ? $rq .= "'".$ret["rpn_function"]."', ": $rq .= "NULL, ";
    isset($ret["unit_name"]) && $ret["unit_name"] != null ? $rq .= "'".$ret["unit_name"]."', ": $rq .= "NULL, ";
    isset($ret["warn"]) && $ret["warn"] != null ? $rq .= "'".$ret["warn"]."', ": $rq .= "NULL, ";
    isset($ret["crit"]) && $ret["crit"] != null ? $rq .= "'".$ret["crit"]."', ": $rq .= "NULL, ";
    isset($ret["vhidden"]) && $ret["vhidden"] != null ? $rq .= "'".$ret["vhidden"]."', ": $rq .= "NULL, ";
    isset($ret["comment"]) && $ret["comment"] != null ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
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

function updateVirtualMetric($vmetric_id = null)
{
    if (!$vmetric_id) {
        return;
    }
    global $form, $pearDB, $pearDBO;
    $ret = array();
    $ret = $form->getSubmitValues();
    $rq = "UPDATE virtual_metrics ";
    $rq .= "SET `index_id` = ";
    if (isset($ret["host_id"]) && preg_match('/\d+\-\d+/', $ret["host_id"])) {
        # Get index_id
        list($host_id, $service_id) = explode('-', $ret["host_id"]);
        $query = "SELECT id "
            . "FROM index_data "
            . "WHERE host_id = " . $host_id . " "
            . "AND service_id = " . $service_id . " ";
        $result = $pearDBO->query($query);
        if ($row = $result->fetchRow()) {
            $rq .= "'" . $row['id'] . "', ";
        } else {
            $rq .= "NULL, ";
        }
    } else {
        $rq .= "NULL, ";
    }
    $rq .=  "vmetric_name = ";
    isset($ret["vmetric_name"]) && $ret["vmetric_name"] != null ? $rq .= "'".htmlentities($ret["vmetric_name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    $rq .=  "def_type = ";
    isset($ret["def_type"]) && $ret["def_type"] != null ? $rq .= "'".$ret["def_type"]."', ": $rq .= "NULL, ";
    $rq .=  "rpn_function = ";
    isset($ret["rpn_function"]) && $ret["rpn_function"] != null ? $rq .= "'".$ret["rpn_function"]."', ": $rq .= "NULL, ";
    $rq .=  "unit_name = ";
    isset($ret["unit_name"]) && $ret["unit_name"] != null ? $rq .= "'".$ret["unit_name"]."', ": $rq .= "NULL, ";
    $rq .=  "warn = ";
    isset($ret["warn"]) && $ret["warn"] != null ? $rq .= "'".$ret["warn"]."', ": $rq .= "NULL, ";
    $rq .=  "crit = ";
    isset($ret["crit"]) && $ret["crit"] != null ? $rq .= "'".$ret["crit"]."', ": $rq .= "NULL, ";
    $rq .=  "hidden = ";
    isset($ret["vhidden"]) && $ret["vhidden"] != null ? $rq .= "'".$ret["vhidden"]."', ": $rq .= "NULL, ";
    $rq .=  "comment = ";
    isset($ret["comment"]) && $ret["comment"] != null ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    $rq .= "vmetric_activate = NULL, ck_state = NULL ";
    $rq .= "WHERE vmetric_id = '".$vmetric_id."'";
    $DBRESULT = $pearDB->query($rq);

    if (!enableVirtualMetricInDB($vmetric_id)) {
        disableVirtualMetricInDB($vmetric_id, 1);
    }
}

function disableVirtualMetricInDB($vmetric_id = null, $force = 0)
{
    if (!$vmetric_id) {
        return 0;
    }
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

function &disableVirtualMetric($v_id = null, $force = 0)
{
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
            if (is_array($lv_dis)) {
                foreach ($lv_dis as $pkey => $vm) {
                    $v_dis[] = $vm;
                }
            }
        }
        $l_pqy->free();
        if (!$force) {
            $v_dis[] = $v_id;
        }
    }
    return $v_dis;
}

function enableVirtualMetricInDB($vmetric_id = null)
{
    if (!$vmetric_id) {
        return 0;
    }

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

function enableVirtualMetric($v_id, $v_name = null, $index_id = null)
{
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
            $lv_ena = enableVirtualMetric(null, $l_mnane, $p_vmetric["index_id"]);
            if (is_array($lv_ena)) {
                foreach ($lv_ena as $pkey => $vm) {
                    $v_ena[] = $vm;
                }
            }
        }
        $v_ena[] = $p_vmetric["vmetric_id"];
    }
    $l_pqy->free();
    return $v_ena;
}

function checkRRDGraphData($v_id = null, $force = 0)
{
    global $pearDB, $oreon;
    if (!isset($v_id)) {
        null;
    }

    /* Check if already Valid */
    $l_pqy = $pearDB->query("SELECT vmetric_id, def_type FROM virtual_metrics WHERE vmetric_id = '$v_id' AND ( ck_state <> '1' OR ck_state IS NULL );");
    if ($l_pqy->numRows() == 1) {
        /**
         * Create XML Request Objects
         */
        $centreon = & $_SESSION["centreon"];
        $obj = new CentreonGraph($centreon->user->get_id(), null, 0, 1);

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
