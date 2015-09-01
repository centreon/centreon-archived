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

/* Get Param */
$params = $this->getWidgetParams();

/* Get DB */
$dbM = $this->getMonitoringDb();
$db = $this->getConfigurationDb();

/* Init Params */
$data = array();

/* Get Data */
$query = "SELECT host_name, service_description, service_id, host_id, cpu.current_value  FROM rt_index_data, rt_metrics cpu WHERE service_description LIKE 'cpu' AND cpu.index_id = id AND cpu.metric_name LIKE '%cpu%' ORDER BY current_value DESC LIMIT 10";
$stmt = $dbM->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
    $row["percent"] = round($row["current_value"]);
    $row["unit"] = " %";
    $data[] = $row;
}

/* Assign infos */
$tpl->assign("data", $data);

/* Display */
$tpl->display('file:[CentreonTop10CPUWidget]console.tpl');
