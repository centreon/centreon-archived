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

    if ($gsvs["vmetric_name"] != null
        && preg_match("/" . $gsvs["vmetric_name"] . "/i", $gsvs["rpn_function"])
    ) {
        return false;
    } else {
        return true;
    }
}

/**
 * Indicates if a virtual metric name has already been used
 *
 * @global CentreonDB $pearDB
 * @global CentreonDB $pearDBO
 * @global HTML_QuickFormCustom $form
 * @param string $vmetricName
 * @param int $indexId
 * @return boolean Return false if the virtual metric name has already been used
 */
function hasVirtualNameNeverUsed($vmetricName = null, $indexId = null)
{
    global $pearDB, $pearDBO, $form;
    $gsvs = null;
    if (isset($form)) {
        $gsvs = $form->getSubmitValues();
    }
    if (is_null($vmetricName) && isset($gsvs["vmetric_name"])) {
        $vmetricName = htmlentities($gsvs["vmetric_name"], ENT_QUOTES, 'UTF-8');
    }
    if (is_null($indexId) && isset($gsvs["index_id"])) {
        $indexId = $gsvs["index_id"];
    }
    
    $prepareVirtualM = $pearDB->prepare(
        "SELECT vmetric_id FROM virtual_metrics WHERE "
        . "vmetric_name = :metric_name AND index_id = :index_id"
    );
    
    $prepareVirtualM->bindValue(':metric_name', $vmetricName, \PDO::PARAM_STR);
    $prepareVirtualM->bindValue(':index_id', $indexId, \PDO::PARAM_INT);
    
    try {
        $prepareVirtualM->execute();
    } catch (\PDOException $e) {
        print "DB Error : " . $e->getMessage();
    }
    
    $vmetric = $prepareVirtualM->fetch();
    $numberOfVirtualMetric = $prepareVirtualM->rowCount();
    $prepareVirtualM->closeCursor();

    $prepareMetric = $pearDBO->prepare(
        "SELECT metric_id FROM metrics WHERE "
        . "metric_name = :metric_name AND index_id = :index_id"
    );
    
    $prepareMetric->bindValue(':metric_name', $vmetricName, \PDO::PARAM_STR);
    $prepareMetric->bindValue(':index_id', $indexId, \PDO::PARAM_INT);
    
    try {
        $prepareMetric->execute();
    } catch (\PDOException $e) {
        print "DB Error : " . $e->getMessage();
    }
    
    $metric = $prepareMetric->fetch();
    $numberOfVirtualMetric += $prepareMetric->rowCount();
    $prepareMetric->closeCursor();
    
    if (($numberOfVirtualMetric >= 1
        && $vmetric["vmetric_id"] != $gsvs["vmetric_id"])
        || isset($metric["metric_id"])
    ) {
        return false;
    } else {
        return true;
    }
}

/**
 * Delete a list of virtual metric
 *
 * @global CentreonDB $pearDB
 * @param int[] $vmetrics List of virtual metric id to delete
 */
function deleteVirtualMetricInDB($vmetrics = array())
{
    global $pearDB;
    foreach (array_keys($vmetrics) as $vmetricId) {
        try {
            $prepareStatement = $pearDB->prepare(
                "DELETE FROM virtual_metrics WHERE vmetric_id = :vmetric_id"
            );
            $prepareStatement->bindValue(':vmetric_id', $vmetricId, \PDO::PARAM_INT);
            $prepareStatement->execute();
        } catch (\PDOException $e) {
            print "DB Error : " . $e->getMessage();
        }
    }
}

/**
 * Duplicates a list of virtual metric
 *
 * @global CentreonDB $pearDB
 * @param int[] $vmetrics List of virtual metric id to duplicate
 * @param int[] $nbrDup Number of copy
 */
function multipleVirtualMetricInDB($vmetrics = array(), $nbrDup = array())
{
    global $pearDB;
    foreach (array_keys($vmetrics) as $vmetricId) {
        $prepareStatement = $pearDB->prepare(
            "SELECT * FROM virtual_metrics WHERE vmetric_id = :vmetric_id LIMIT 1"
        );
        $prepareStatement->bindValue(':vmetric_id', $vmetricId, \PDO::PARAM_INT);
        
        try {
            $prepareStatement->execute();
        } catch (\PDOException $e) {
            print "DB Error : " . $e->getMessage();
        }
        
        $vmConfiguration = $prepareStatement->fetch();
        $vmConfiguration["vmetric_id"] = '';
        
        for ($newIndex= 1; $newIndex <= $nbrDup[$vmetricId]; $newIndex++) {
            $val = null;
            $virtualMetricName = null;
            foreach ($vmConfiguration as $cfgName => $cfgValue) {
                if ($cfgName == "vmetric_name") {
                    $indexId = (int) $vmConfiguration['index_id'];
                    $count = 1;
                    $virtualMetricName = $cfgValue . "_" . $count;
                    while (!hasVirtualNameNeverUsed($virtualMetricName, $indexId)) {
                        $count++;
                        $virtualMetricName = $cfgValue . "_" . $count;
                    }
                    $cfgValue = $virtualMetricName;
                }
                
                if (is_null($val)) {
                    $val .= ($cfgValue == null)
                        ? 'NULL'
                        : "'" . $pearDB->escape($cfgValue) . "'";
                } else {
                    $val .= ($cfgValue == null)
                        ? ', NULL'
                        : ", '" . $pearDB->escape($cfgValue) . "'";
                }
            }
            if (!is_null($val)) {
                try {
                    $pearDB->query("INSERT INTO virtual_metrics VALUES ($val)");
                } catch (\PDOException $e) {
                    print "DB Error : " . $e->getMessage();
                }
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

/**
 * Insert a virtual metric
 *
 * @global HTML_QuickFormCustom $form
 * @global CentreonDB $pearDB
 * @global CentreonDB $pearDBO
 * @global Centreon $centreon
 * @return int New virtual metric id
 */
function insertVirtualMetric()
{
    global $form, $pearDB, $pearDBO, $centreon;
    $h_id = null;
    $s_id = null;
    $ret = array();
    $ret = $form->getSubmitValues();

    $rq = "INSERT INTO `virtual_metrics` ( `vmetric_id` , `index_id`, `vmetric_name`, `def_type` , `rpn_function`, " .
        "`unit_name` , `warn`, `crit`, `hidden` , `comment` , `vmetric_activate`, `ck_state`) ";
    $rq .= "VALUES ( NULL, ";

    if (isset($ret["host_id"]) && preg_match('/\d+\-\d+/', $ret["host_id"])) {
        # Get index_id
        list($host_id, $service_id) = explode('-', $ret["host_id"]);
        
        $prepare = $pearDBO->prepare(
            "SELECT id FROM index_data "
            . "WHERE host_id = :host_id "
            . "AND service_id = :service_id "
        );
        $prepare->bindValue(':host_id', $host_id, \PDO::PARAM_INT);
        $prepare->bindValue(':service_id', $service_id, \PDO::PARAM_INT);
        $prepare->execute();
        
        $result = $prepare->fetch(\PDO::FETCH_ASSOC);
        if (isset($result['id'])) {
            $rq .= "'" . $result['id'] . "', ";
        } else {
            $rq .= "NULL, ";
        }
    } else {
        $rq .= "NULL, ";
    }
    isset($ret["vmetric_name"]) && $ret["vmetric_name"] != null
        ? $rq .= "'" . htmlentities($ret["vmetric_name"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["def_type"]) && $ret["def_type"] != null ? $rq .= "'" . $ret["def_type"] . "', " : $rq .= "NULL, ";
    isset($ret["rpn_function"]) && $ret["rpn_function"] != null
        ? $rq .= "'" . $ret["rpn_function"] . "', "
        : $rq .= "NULL, ";
    isset($ret["unit_name"]) && $ret["unit_name"] != null ? $rq .= "'" . $ret["unit_name"] . "', " : $rq .= "NULL, ";
    isset($ret["warn"]) && $ret["warn"] != null ? $rq .= "'" . $ret["warn"] . "', " : $rq .= "NULL, ";
    isset($ret["crit"]) && $ret["crit"] != null ? $rq .= "'" . $ret["crit"] . "', " : $rq .= "NULL, ";
    isset($ret["vhidden"]) && $ret["vhidden"] != null ? $rq .= "'" . $ret["vhidden"] . "', " : $rq .= "NULL, ";
    isset($ret["comment"]) && $ret["comment"] != null
        ? $rq .= "'" . htmlentities($ret["comment"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    $rq .= "NULL, NULL";
    $rq .= ")";
    if ($centreon->optGen["debug_rrdtool"] == "1") {
        $debug_path = realpath($centreon->optGen["debug_path"]);
        error_log("[" . date("d/m/Y H:s") . "] VIRTUAL METRIC : $rq \n", 3, $debug_path . "/rrdtool.log");
    }
    $dbResult = $pearDB->query($rq);
    $dbResult = $pearDB->query("SELECT MAX(vmetric_id) FROM virtual_metrics");
    $vmetric_id = $dbResult->fetch();
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
        
        $prepare = $pearDBO->prepare(
            "SELECT id FROM index_data "
            . "WHERE host_id = :host_id "
            . "AND service_id = :service_id"
        );
        $prepare->bindValue(':host_id', $host_id, \PDO::PARAM_INT);
        $prepare->bindValue(':service_id', $service_id, \PDO::PARAM_INT);
        $prepare->execute();
        
        $result = $prepare->fetch(\PDO::FETCH_ASSOC);
        if (isset($result['id'])) {
            $rq .= "'" . $result['id'] . "', ";
        } else {
            $rq .= "NULL, ";
        }
    } else {
        $rq .= "NULL, ";
    }
    $rq .= "vmetric_name = ";
    isset($ret["vmetric_name"]) && $ret["vmetric_name"] != null
        ? $rq .= "'" . htmlentities($ret["vmetric_name"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    $rq .= "def_type = ";
    isset($ret["def_type"]) && $ret["def_type"] != null ? $rq .= "'" . $ret["def_type"] . "', " : $rq .= "NULL, ";
    $rq .= "rpn_function = ";
    isset($ret["rpn_function"]) && $ret["rpn_function"] != null
        ? $rq .= "'" . $ret["rpn_function"] . "', "
        : $rq .= "NULL, ";
    $rq .= "unit_name = ";
    isset($ret["unit_name"]) && $ret["unit_name"] != null ? $rq .= "'" . $ret["unit_name"] . "', " : $rq .= "NULL, ";
    $rq .= "warn = ";
    isset($ret["warn"]) && $ret["warn"] != null ? $rq .= "'" . $ret["warn"] . "', " : $rq .= "NULL, ";
    $rq .= "crit = ";
    isset($ret["crit"]) && $ret["crit"] != null ? $rq .= "'" . $ret["crit"] . "', " : $rq .= "NULL, ";
    $rq .= "hidden = ";
    isset($ret["vhidden"]) && $ret["vhidden"] != null
        ? $rq .= "'" . $ret["vhidden"] . "', "
        : $rq .= "NULL, ";
    $rq .= "comment = ";
    isset($ret["comment"]) && $ret["comment"] != null
        ? $rq .= "'" . htmlentities($ret["comment"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    $rq .= "vmetric_activate = NULL, ck_state = NULL ";
    $rq .= "WHERE vmetric_id = '" . $vmetric_id . "'";
    $dbResult = $pearDB->query($rq);

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
    foreach ($v_dis as $vm) {
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
    if ($l_pqy->rowCount() == 1) {
        $vmetric = $l_pqy->fetch();
        $l_pqy->closeCursor();
        $query = "SELECT vmetric_id FROM `virtual_metrics` WHERE `index_id`='" . $vmetric["index_id"] .
            "' AND `vmetric_activate` = '1' " .
            "AND `rpn_function` REGEXP '(^|,)" . str_replace($repA, $repB, $vmetric["vmetric_name"]) . "(,|$)';";
        $l_pqy = $pearDB->query($query);
        while ($d_vmetric = $l_pqy->fetch()) {
            $lv_dis = disableVirtualMetric($d_vmetric["vmetric_id"]);
            if (is_array($lv_dis)) {
                foreach ($lv_dis as $pkey => $vm) {
                    $v_dis[] = $vm;
                }
            }
        }
        $l_pqy->closeCursor();
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
    foreach ($v_ena as $v_id) {
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

    $query = "SELECT vmetric_id, index_id, rpn_function FROM virtual_metrics " .
        "WHERE $l_where AND (vmetric_activate = '0' OR vmetric_activate IS NULL);";
    $l_pqy = $pearDB->query($query);
    if ($l_pqy->rowCount() == 1) {
        $p_vmetric = $l_pqy->fetch();
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
    $l_pqy->closeCursor();
    return $v_ena;
}

function checkRRDGraphData($v_id = null, $force = 0)
{
    global $pearDB, $oreon;
    if (!isset($v_id)) {
        null;
    }

    /* Check if already Valid */
    $query = "SELECT vmetric_id, def_type FROM virtual_metrics " .
        "WHERE vmetric_id = '$v_id' AND ( ck_state <> '1' OR ck_state IS NULL );";
    $l_pqy = $pearDB->query($query);
    if ($l_pqy->rowCount() == 1) {
        /**
         * Create XML Request Objects
         */
        $centreon = &$_SESSION["centreon"];
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
