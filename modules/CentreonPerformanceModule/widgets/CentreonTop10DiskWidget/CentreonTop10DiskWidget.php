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

/* Get Data */
$query = "SELECT host_name, service_description, service_id, host_id, size.current_value AS size, used.current_value AS used, (used.current_value/size.current_value*100) AS percent FROM index_data, metrics used, metrics size WHERE service_description LIKE 'Disk%' AND used.index_id = id AND size.index_id = id AND size.metric_name = 'size' AND used.metric_name = 'used' ORDER BY percent DESC LIMIT 10";
$stmt = $dbM->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
    $row["percent"] = round($row["percent"]);
    $unit = getUnit($row['size']);

    $row['used'] = formatData($row['used'], $unit);
    $row['size'] = formatData($row['size'], $unit);

    $data[] = $row;
}


function getUnit($value) {
    $unit = array('o', 'Ko', 'Mo', 'Go', 'To', 'Po');
    $i = 0;
    while ($value > 1024) {
        $value = $value / 1024;
        $i++;
    }
    return array($i, $unit[$i]);
}

function formatData($value, $unit) {
    for ($i = 0; $i != $unit[0]; $i++) {
        $value /= 1024;
    }
    return round($value, 1)." ".$unit[1];
}

/* Assign infos */
$tpl->assign("data", $data);

/* Display */
$tpl->display('file:[CentreonTop10DiskWidget]console.tpl');
