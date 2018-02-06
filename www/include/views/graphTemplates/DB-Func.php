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
*/

if (!isset($centreon)) {
    exit();
}

function testExistence($name = null)
{
    global $pearDB, $form;

    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('graph_id');
    }
    $res = $pearDB->query("SELECT graph_id, name FROM giv_graphs_template WHERE name = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
    $graph = $res->fetchRow();
    /*
	 * Modif case
	 */
    if ($res->numRows() >= 1 && $graph["graph_id"] == $id) {
        return true;
    } /*
	 * Duplicate entry
	 */
    elseif ($res->numRows() >= 1 && $graph["graph_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

function deleteGraphTemplateInDB($graphs = array())
{
    global $pearDB;

    foreach ($graphs as $key => $value) {
        $pearDB->query("DELETE FROM giv_graphs_template WHERE graph_id = '".$key."'");
    }
    defaultOreonGraph();
}

function multipleGraphTemplateInDB($graphs = array(), $nbrDup = array())
{
    foreach ($graphs as $key => $value) {
        global $pearDB;
        $res = $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$key."' LIMIT 1");
        $row = $res->fetchRow();
        $row["graph_id"] = '';
        $row["default_tpl1"] = '0';
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "name" ? ($name = $value2 = $value2."_".$i) : null;
                $val ? $val .= ($value2!=null?(", '".$value2."'"):", NULL") : $val .= ($value2!=null?("'".$value2."'"):"NULL");
            }
            if (testExistence($name)) {
                $val ? $rq = "INSERT INTO giv_graphs_template VALUES (".$val.")" : $rq = null;
                $pearDB->query($rq);
            }
        }
    }
}

function defaultOreonGraph()
{
    global $pearDB;
    $rq = "SELECT DISTINCT graph_id FROM giv_graphs_template WHERE default_tpl1 = '1'";
    $res = $pearDB->query($rq);
    if (!$res->numRows()) {
        $rq = "UPDATE giv_graphs_template SET default_tpl1 = '1' WHERE graph_id = (SELECT MIN(graph_id) FROM giv_graphs_template)";
        $pearDB->query($rq);
    }
}

function noDefaultOreonGraph()
{
    global $pearDB;
    $rq = "UPDATE giv_graphs_template SET default_tpl1 = '0'";
    $pearDB->query($rq);
}


function updateGraphTemplateInDB($graph_id = null)
{
    if (!$graph_id) {
        return;
    }
    updateGraphTemplate($graph_id);
}

function insertGraphTemplateInDB()
{
    $graph_id = insertGraphTemplate();
    return ($graph_id);
}

function insertGraphTemplate()
{
    global $form;
    global $pearDB;
    $ret = array();
    $ret = $form->getSubmitValues();
    if (isset($ret["default_tpl1"]) && $ret["default_tpl1"]) {
        noDefaultOreonGraph();
    }
    $rq = "INSERT INTO `giv_graphs_template` ( `graph_id` , `name` , " .
            "`vertical_label` , `width` , `height` , `base` , `lower_limit`, `upper_limit` , `size_to_max`, `default_tpl1` , `split_component` , `scaled`, `stacked` , `comment`) ";
    $rq .= "VALUES (";
    $rq .= "NULL, ";
    isset($ret["name"]) && $ret["name"] != null ? $rq .= "'".htmlentities($ret["name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    isset($ret["vertical_label"]) && $ret["vertical_label"] != null ? $rq .= "'".htmlentities($ret["vertical_label"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    isset($ret["width"]) && $ret["width"] != null ? $rq .= "'".htmlentities($ret["width"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    isset($ret["height"]) && $ret["height"] != null ? $rq .= "'".htmlentities($ret["height"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    isset($ret["base"]) && $ret["base"] != null ? $rq .= "'".htmlentities($ret["base"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    isset($ret["lower_limit"]) && $ret["lower_limit"] != null ? $rq .= "'".$ret["lower_limit"]."', ": $rq .= "NULL, ";
    isset($ret["upper_limit"]) && $ret["upper_limit"] != null ? $rq .= "'".$ret["upper_limit"]."', ": $rq .= "NULL, ";
    isset($ret["size_to_max"]) && $ret["size_to_max"] != null ? $rq .= "'".$ret["size_to_max"]."', ": $rq .= "0, ";
    isset($ret["default_tpl1"]) && $ret["default_tpl1"] != null ? $rq .= "'".$ret["default_tpl1"]."', ": $rq .= "NULL, ";
    $rq .= "NULL, "; // Column split chart (removed options)
    isset($ret["scaled"]) && $ret["scaled"] != null ? $rq .= "'".$ret["scaled"]."', ": $rq .= "'0', ";
    isset($ret["stacked"]) && $ret["stacked"] != null ? $rq .= "'".htmlentities($ret["stacked"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    isset($ret["comment"]) && $ret["comment"] != null ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES, "UTF-8")."'": $rq .= "NULL";
    $rq .= ")";
    $pearDB->query($rq);
    defaultOreonGraph();
    $res = $pearDB->query("SELECT MAX(graph_id) FROM giv_graphs_template");
    $graph_id = $res->fetchRow();
    return ($graph_id["MAX(graph_id)"]);
}

function updateGraphTemplate($graph_id = null)
{
    global $form, $pearDB;

    if (!$graph_id) {
        return;
    }
    $ret = array();
    $ret = $form->getSubmitValues();
    if (isset($ret["default_tpl1"]) && $ret["default_tpl1"]) {
        noDefaultOreonGraph();
    }
    $rq = "UPDATE giv_graphs_template ";
    $rq .= "SET name = ";
    isset($ret["name"]) && $ret["name"] != null ? $rq .= "'".htmlentities($ret["name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    $rq .=  "vertical_label = ";
    isset($ret["vertical_label"]) && $ret["vertical_label"] != null ? $rq .= "'".htmlentities($ret["vertical_label"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    $rq .= "width = ";
    isset($ret["width"]) && $ret["width"] != null ? $rq .= "'".$ret["width"]."', ": $rq .= "NULL, ";
    $rq .= "height = ";
    isset($ret["height"]) && $ret["height"] != null ? $rq .= "'".$ret["height"]."', ": $rq .= "NULL, ";
    $rq .= "base = ";
    isset($ret["base"]) && $ret["base"] != null ? $rq .= "'".$ret["base"]."', ": $rq .= "NULL, ";
    $rq .= "lower_limit = ";
    isset($ret["lower_limit"]) && $ret["lower_limit"] != null ? $rq .= "'".$ret["lower_limit"]."', ": $rq .= "NULL, ";
    $rq .= "upper_limit = ";
    isset($ret["upper_limit"]) && $ret["upper_limit"] != null ? $rq .= "'".$ret["upper_limit"]."', ": $rq .= "NULL, ";
    $rq .= "size_to_max = ";
    isset($ret["size_to_max"]) && $ret["size_to_max"] != null ? $rq .= "'".$ret["size_to_max"]."', ": $rq .= "0, ";
    $rq .= "default_tpl1 = ";
    isset($ret["default_tpl1"]) && $ret["default_tpl1"] != null ? $rq .= "'".$ret["default_tpl1"]."', ": $rq .= "NULL, ";
    $rq .= "split_component = ";
    isset($ret["split_component"]) && $ret["split_component"] != null ? $rq .= "'".$ret["split_component"]."', ": $rq .= "NULL, ";
    $rq .= "scaled = ";
    isset($ret["scaled"]) && $ret["scaled"] != null ? $rq .= "'".$ret["scaled"]."', ": $rq .= "'0', ";
    $rq .= "stacked = ";
    isset($ret["stacked"]) && $ret["stacked"] != null ? $rq .= "'".$ret["stacked"]."', ": $rq .= "NULL, ";
    $rq .= "comment = ";
    isset($ret["comment"]) && $ret["comment"] != null ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES, "UTF-8")."' ": $rq .= "NULL ";
    $rq .= "WHERE graph_id = '".$graph_id."'";
    $pearDB->query($rq);
    defaultOreonGraph();
}
