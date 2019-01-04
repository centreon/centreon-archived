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


/**
 * Retrieve the next available suffixes for this virtual metric name from database
 *
 * @global CentreonDB $pearDB DB connector
 * @param string $virtualMetricName Virtual metric name to process
 * @param int $numberOf Number of suffix requested
 * @param int $indexId Id of the record in centreon_storage.index_data
 * @param string $separator Character used to separate the virtual metric name and suffix
 * @return array Return the next available suffixes
 * @throws Exception
 */
function getAvailableSuffixIds($virtualMetricName, $numberOf, $indexId, $separator = '_')
{
    if (!is_int($numberOf) || $numberOf < 0) {
        return array();
    }

    global $pearDB;
    /**
     * To avoid that this column value will be interpreted like regular
     * expression in the database query
     */
    $virtualMetricName = preg_quote($virtualMetricName);

    // Get list of suffix already used
    $virtualMetricName = CentreonDB::escape($virtualMetricName);
    $query = "SELECT CAST(SUBSTRING_INDEX(vmetric_name,'_',-1) AS INT) AS suffix "
        . "FROM virtual_metrics WHERE vmetric_name REGEXP '^"
        . $virtualMetricName . $separator . "[0-9]+$' "
        . 'AND index_id = ' . (int) $indexId . ' '
        . 'ORDER BY suffix';
    $results = $pearDB->query($query);

    $notAvailableSuffixes = array();

    while ($result = $results->fetchRow()) {
        $suffix = (int)$result['suffix'];
        if (!in_array($suffix, $notAvailableSuffixes)) {
            $notAvailableSuffixes[] = $suffix;
        }
    }

    /**
     * Search for available suffixes taking into account those found in the database
     */
    $nextAvailableSuffixes = array();
    $counter = 1;
    while (count($nextAvailableSuffixes) < $numberOf) {
        if (!in_array($counter, $notAvailableSuffixes)) {
            $nextAvailableSuffixes[] = $counter;
        }
        $counter++;
    }

    return $nextAvailableSuffixes;
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

/**
 * Delete a list of virtual metrics identified by their id
 *
 * @param int[] $vmetricsIds Id of virtual metrics to delete
 * @global CentreonDB $pearDB DB Connector
 * @throws Exception
 */
function deleteVirtualMetricInDB(array $vmetricsIds)
{
    global $pearDB;
    foreach (array_keys($vmetricsIds) as $id) {
        $DBRESULT = $pearDB->query(
            'DELETE FROM virtual_metrics WHERE vmetric_id = ' . (int) $id
        );
        if (PEAR::isError($DBRESULT)) {
            print "DB Error : ".$DBRESULT->getDebugInfo();
        }
    }
}

/**
 * Duplicate a list of virtual metric
 *
 * @param int[] $vmetricsIds Id of virtual metrics to duplicate
 * @param array $nbrDup Number of copy per virtual metrics id
 * @global CentreonDB $pearDB DB Connector
 * @throws Exception
 */
function duplicateVirtualMetric(array $vmetricsIds, array $nbrDup)
{
    global $pearDB;
    foreach (array_keys($vmetricsIds) as $id) {
        if (!is_int($id)) {
            $id = (int) $id;
        }
        $DBRESULT = $pearDB->query(
            'SELECT * FROM virtual_metrics WHERE vmetric_id = ' . $id
            . ' LIMIT 1'
        );

        if (PEAR::isError($DBRESULT)) {
            print "DB Error : ".$DBRESULT->getDebugInfo();
        }
        $virtualMetricInfos = $DBRESULT->fetchRow();
        $virtualMetricInfos["vmetric_id"] = '';
        $availableVirtualMetricSuffix = getAvailableSuffixIds(
            $virtualMetricInfos['vmetric_name'],
            $nbrDup[$id],
            (int) $virtualMetricInfos['index_id']
        );
        foreach ($availableVirtualMetricSuffix as $suffix) {
            $queryValues = null;
            $virtualMetricName = null;
            $indexId = null;

            foreach ($virtualMetricInfos as $columnName => $columnValue) {
                if ($columnName == 'vmetric_name') {
                    $columnValue .= '_' . $suffix;
                    $virtualMetricName = $columnValue;
                } elseif ($columnName == 'index_id') {
                    $indexId = (int) $columnValue;
                    $columnValue = $indexId;
                }
                if (is_null($queryValues)) {
                    $queryValues .= $columnValue != null
                        ? ("'" . $columnValue . "'")
                        : "NULL";
                } else {
                    $queryValues .= $columnValue != null
                        ? (", '" . $columnValue . "'")
                        : ", NULL";
                }
            }
            if (!is_null($queryValues) && NameTestExistence($virtualMetricName, $indexId)) {
                $DBRESULT2 = $pearDB->query(
                    'INSERT INTO virtual_metrics VALUES (' . $queryValues . ')'
                );
                if (PEAR::isError($DBRESULT2)) {
                    print "DB Error : ".$DBRESULT2->getDebugInfo();
                }
            }
        }
    }
}

/**
 * Insert a new virtual metric
 *
 * @param HTML_QuickForm $formData Data from HTML form
 * @global CentreonDB $pearDB DB Connector to centreon database
 * @global CentreonDB $pearDBO DB Connector to centreon_storage database
 * @global Centreon $centreon
 * @return mixed
 * @throws Exception
 */
function insertVirtualMetric($formData)
{
    global $pearDB, $pearDBO, $centreon;
    $h_id = null;

    $rq =
        'INSERT INTO `virtual_metrics` '
        . '( `vmetric_id` , `index_id`, `vmetric_name`, `def_type` , `rpn_function`, `unit_name` , '
        . '`warn`, `crit`, `hidden` , `comment` , `vmetric_activate`, `ck_state`) '
        . 'VALUES ( NULL, ';

    if (isset($formData["host_id"]) && preg_match('/\d+\-\d+/', $formData["host_id"])) {
        # Get index_id
        list($host_id, $service_id) = explode('-', $formData["host_id"]);
        $result = $pearDBO->query(
            'SELECT id FROM index_data WHERE host_id = ' . (int) $host_id . ' AND service_id = ' . (int) $service_id
        );
        if ($row = $result->fetchRow()) {
            $rq .= "'" . $row['id'] . "', ";
        } else {
            $rq .= "NULL, ";
        }
    } else {
        $rq .= "NULL, ";
    }
    isset($formData["vmetric_name"]) && $formData["vmetric_name"] != null
        ? $rq .= "'".htmlentities($formData["vmetric_name"], ENT_QUOTES, "UTF-8")."', "
        : $rq .= "NULL, ";
    isset($formData["def_type"]) && $formData["def_type"] != null
        ? $rq .= "'".$formData["def_type"]."', "
        : $rq .= "NULL, ";
    isset($formData["rpn_function"]) && $formData["rpn_function"] != null
        ? $rq .= "'".$formData["rpn_function"]."', "
        : $rq .= "NULL, ";
    isset($formData["unit_name"]) && $formData["unit_name"] != null
        ? $rq .= "'".$formData["unit_name"]."', "
        : $rq .= "NULL, ";
    isset($formData["warn"]) && $formData["warn"] != null
        ? $rq .= "'".$formData["warn"]."', "
        : $rq .= "NULL, ";
    isset($formData["crit"]) && $formData["crit"] != null
        ? $rq .= "'".$formData["crit"]."', "
        : $rq .= "NULL, ";
    isset($formData["vhidden"]) && $formData["vhidden"] != null
        ? $rq .= "'".$formData["vhidden"]."', "
        : $rq .= "NULL, ";
    isset($formData["comment"]) && $formData["comment"] != null
        ? $rq .= "'".htmlentities($formData["comment"], ENT_QUOTES, "UTF-8")."', "
        : $rq .= "NULL, ";

    $rq .= "NULL, NULL";
    $rq .= ")";
    if ($centreon->optGen["debug_rrdtool"] == "1") {
        $debug_path = realpath($centreon->optGen["debug_path"]);
        error_log("[" . date("d/m/Y H:s") ."] VIRTUAL METRIC : $rq \n", 3, $debug_path . "/rrdtool.log");
    }
    $pearDB->query($rq);

    $DBRESULT = $pearDB->query(
        'SELECT MAX(vmetric_id) AS vmetric_id FROM virtual_metrics'
    );
    $result = $DBRESULT->fetchRow();

    return (int) $result['vmetric_id'];
}

/**
 * Update a virtual metric
 *
 * @param int $vmetric_id Id of virtual metric to update
 * @param HTML_QuickForm $data New data of virtual metric
 * @global CentreonDB $pearDB DB connector in centreon database
 * @global CentreonDB $pearDBO DB connector in centreon_storage database
 * @throws Exception
 */
function updateVirtualMetric($vmetric_id, $data)
{
    if (is_null($vmetric_id)) {
        return;
    }
    if (!is_int($vmetric_id)) {
        $vmetric_id = (int) $vmetric_id;
    }
    global $pearDB, $pearDBO;

    $rq = "UPDATE virtual_metrics ";
    $rq .= "SET `index_id` = ";
    if (isset($data["host_id"]) && preg_match('/\d+\-\d+/', $data["host_id"])) {
        # Get index_id
        list($host_id, $service_id) = explode('-', $data["host_id"]);
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
    isset($data["vmetric_name"]) && $data["vmetric_name"] != null
        ? $rq .= "'".htmlentities($data["vmetric_name"], ENT_QUOTES, "UTF-8")."', "
        : $rq .= "NULL, ";
    $rq .=  "def_type = ";
    isset($data["def_type"]) && $data["def_type"] != null
        ? $rq .= "'".$data["def_type"]."', "
        : $rq .= "NULL, ";
    $rq .=  "rpn_function = ";
    isset($data["rpn_function"]) && $data["rpn_function"] != null
        ? $rq .= "'".$data["rpn_function"]."', "
        : $rq .= "NULL, ";
    $rq .=  "unit_name = ";
    isset($data["unit_name"]) && $data["unit_name"] != null
        ? $rq .= "'".$data["unit_name"]."', "
        : $rq .= "NULL, ";
    $rq .=  "warn = ";
    isset($data["warn"]) && $data["warn"] != null
        ? $rq .= "'".$data["warn"]."', "
        : $rq .= "NULL, ";
    $rq .=  "crit = ";
    isset($data["crit"]) && $data["crit"] != null
        ? $rq .= "'".$data["crit"]."', "
        : $rq .= "NULL, ";
    $rq .=  "hidden = ";
    isset($data["vhidden"]) && $data["vhidden"] != null
        ? $rq .= "'".$data["vhidden"]."', "
        : $rq .= "NULL, ";
    $rq .=  "comment = ";
    isset($data["comment"]) && $data["comment"] != null
        ? $rq .= "'".htmlentities($data["comment"], ENT_QUOTES, "UTF-8")."', "
        : $rq .= "NULL, ";
    $rq .= "vmetric_activate = NULL, ck_state = NULL ";
    $rq .= "WHERE vmetric_id = '".$vmetric_id."'";
    $pearDB->query($rq);

    if (!enableVirtualMetricInDB($vmetric_id)) {
        disableVirtualMetricInDB($vmetric_id, true);
    }
}

/**
 * Disable a virtual metric
 *
 * @param int $vmetric_id Id of the virtual metric to disable
 * @param bool $force Indicates whether or not you can force the deactivation
 * @global CentreonDB $pearDB Db connector
 * @return bool Indicates whether or not the deactivation has been done
 * @throws Exception
 */
function disableVirtualMetricInDB($virtualMetricId, $force = false)
{
    if (is_null($virtualMetricId)) {
        return false;
    }
    if (!is_int($virtualMetricId)) {
        $virtualMetricId = (int) $virtualMetricId;
    }
    global $pearDB;
    
    $virtualMetricIds = disableVirtualMetric($virtualMetricId, $force);
    if (!count($virtualMetricIds)) {
        return false;
    }
    foreach (array_values($virtualMetricIds) as $id) {
        $pearDB->query(
            "UPDATE `virtual_metrics` SET `vmetric_activate` = '0' "
            . 'WHERE `vmetric_id` = ' . $id
        );
    }
    return true;
}

/**
 * Disable a virtual metric
 *
 * @param int $virtualMetricId Id of the virtual metric to disable
 * @param bool $force Indicates whether or not you can force the deactivation
 * @return int[] Returns a virtual metric id list that has been disabled
 * @throws Exception
 */
function &disableVirtualMetric($virtualMetricId = null, $force = false)
{
    global $pearDB;
    $virtualMetricIds = array();
    
    $repA = array("*", "+", "-", "?", "^", "$");
    $repB = array("\\\\*", "\\\\+", "\\\\-", "\\\\?", "\\\\^", "\\\\$");

    $request =
        'SELECT index_id, vmetric_name FROM `virtual_metrics` '
        . 'WHERE `vmetric_id` = ' . $virtualMetricId;
    if (!$force) {
        $request .= " AND `vmetric_activate` = '1'";
    }
    $result = $pearDB->query($request);
    if ($result->numRows() == 1) {
        $vmetric = $result->fetchRow();
        $result->free();
        $virtualMetricName = str_replace($repA, $repB, $vmetric["vmetric_name"]);
        $result = $pearDB->query(
            "SELECT vmetric_id FROM `virtual_metrics` "
            . "WHERE `index_id`='" . (int) $vmetric["index_id"] . "' "
            . "AND `vmetric_activate` = '1' "
            . "AND `rpn_function` REGEXP '(^|,)" . $virtualMetricName . "(,|$)'");

        while ($d_vmetric = $result->fetchRow()) {
            $lv_dis = disableVirtualMetric((int) $d_vmetric["vmetric_id"]);
            if (is_array($lv_dis)) {
                foreach (array_values($lv_dis) as $id) {
                    $virtualMetricIds[] = $id;
                }
            }
        }
        $result->free();
        if (!$force) {
            $virtualMetricIds[] = $virtualMetricId;
        }
    }
    return $virtualMetricIds;
}

/**
 * Enables a virtual metric
 *
 * @param int $virtualMetricId Id of the virtual metric to enable
 * @global CentreonDB $pearDB DB connector
 * @return bool Indicates whether or not the activation has been done
 * @throws Exception
 */
function enableVirtualMetricInDB($virtualMetricId)
{
    if (is_null($virtualMetricId)) {
        return false;
    }

    if (!is_int($virtualMetricId)) {
        $virtualMetricId = (int) $virtualMetricId;
    }

    global $pearDB;

    $virtualMetricIds = enableVirtualMetric($virtualMetricId);
    if (!count($virtualMetricIds)) {
        return false;
    }
    foreach (array_values($virtualMetricIds) as $id) {
        list($rc, $output) = checkRRDGraphData($id);
        if ($rc) {
            $error = preg_replace('/^ERROR:\s*/', '', $output);
            throw new Exception("Wrong RPN syntax (RRDtool said: $error)");
        }
        $pearDB->query(
            "UPDATE `virtual_metrics` SET `vmetric_activate` = '1' "
            . "WHERE `vmetric_id` = " . $id
        );
    }
    return true;
}

/**
 * Enables a virtual metric
 *
 * @param int $virtualMetricId Id of the virtual metric to enable
 * @param string $virtualMetricName
 * @param int $indexId Index of the virtual metric
 * @global CentreonDB $pearDB DB connector
 * @return int[] Returns a virtual metric id list that has been enabled
 * @throws Exception
 */
function enableVirtualMetric($virtualMetricId, $virtualMetricName = null, $indexId = null)
{
    global $pearDB;

    if (!is_null($virtualMetricId) && !is_int($virtualMetricId)) {
        $virtualMetricId = (int) $virtualMetricId;
    }

    $virtualMetricIds = array();

    $subrequest = 'vmetric_id = ' . $virtualMetricId;
    if (is_null($virtualMetricId) && !is_null($indexId)) {
        if (!is_int($indexId)) {
            $indexId = (int) $indexId;
        }
        $virtualMetricName = CentreonDB::escape($virtualMetricName);
        $subrequest = "vmetric_name = '$virtualMetricName' AND index_id = " . $indexId;
    }

    $result = $pearDB->query(
        'SELECT vmetric_id, index_id, rpn_function FROM virtual_metrics '
        . "WHERE $subrequest AND (vmetric_activate = '0' OR vmetric_activate IS NULL)"
    );
    if ($result->numRows() == 1) {
        $virtualMetric = $result->fetchRow();
        $virtualMetricNames = preg_split("/\,/", $virtualMetric["rpn_function"]);
        foreach ($virtualMetricNames as $name) {
            $lv_ena = enableVirtualMetric(null, $name, $virtualMetric["index_id"]);
            if (is_array($lv_ena)) {
                foreach (array_values($lv_ena) as $id) {
                    $virtualMetricIds[] = $id;
                }
            }
        }
        $virtualMetricIds[] = (int) $virtualMetric["vmetric_id"];
    }
    $result->free();
    return $virtualMetricIds;
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
