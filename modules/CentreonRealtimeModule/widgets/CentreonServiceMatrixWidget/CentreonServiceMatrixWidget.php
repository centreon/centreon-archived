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

$tpl = $this->getTemplate();

/* Load css */
$tpl->addCss('angled-headers.css');

/* Get Param */
$params = $this->getWidgetParams();

/* Get DB */
$dbM = $this->getMonitoringDb();
$db = $this->getConfigurationDb();

/* Init Params */
$data = array();
$topLine = array();
$leftCol = array();
$tabHostID = array();

/* Get Data */
$query = "SELECT s.host_id, h.name, s.service_id, s.description, s.state, s.output "
    . "FROM services s, hosts h "
    . "WHERE h.host_id = s.host_id "
    . "AND h.enabled = '1' "
    . "AND s.enabled = '1' "
    . "ORDER BY name, description";
$stmt = $dbM->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
    if (!isset($leftCol[$row["name"]])) {
        $leftCol[$row["name"]] = $row["name"];
    }
    if (!isset($topLine[$row["description"]])) {
        $topLine[$row["description"]] = $row["description"];
    }
    if (!isset($data[$row["name"]])) {
        $data[$row["name"]] = array();
    }
    if (!isset($data[$row["name"]][$row["description"]])) {
        $data[$row["name"]][$row["description"]] = array();
    }
    $data[$row["name"]][$row["description"]] = array('state' => $row["state"], 'output' => $row['output']);
    $tabHostID[$row["name"]] = $row["host_id"];
}

/* organise informations */
foreach ($leftCol as $key => $value) {
    foreach ($topLine as $k => $v) {
        if (!isset($data[$key][$k])) {
            $data[$key][$k] = -1;
        }
    }
    ksort($data[$key]);
}

/* Sort TopLine */
ksort($topLine);

/* Table in order to convert status and color (id to name) */
$convert = array("ok" => 0, 'warning' => 1, "critical" => 2, "unknown" => 3, "pending" => 4);

/* Get colors */
$query = "select * FROM options WHERE `key` LIKE 'color_%'";
$stmt = $db->query($query);
while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
    if (isset($convert[str_replace('color_', '', $row['key'])])) {
        $statusColor[$convert[str_replace('color_', '', $row['key'])]] = $row["value"];
    }
}

/* Assign infos */
$tpl->assign("data", $data);
$tpl->assign("leftCol", $leftCol);
$tpl->assign("topLine", $topLine);
$tpl->assign("hostID", $tabHostID);
$tpl->assign("status", $statusColor);

/* Display */
$tpl->display('file:[CentreonServiceMatrixWidget]console.tpl');
