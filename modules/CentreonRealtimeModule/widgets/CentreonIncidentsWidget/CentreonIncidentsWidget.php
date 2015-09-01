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
$tpl->addCss("centreon.status.css");

use Centreon\Internal\Utils\Status;
use Centreon\Internal\Utils\Datetime;

/* Get Param */
$params = $this->getWidgetParams();

/* Get DB */
$dbM = $this->getMonitoringDb();
$db = $this->getConfigurationDb();

/* Init Params */
$data = array();

/* Get Data */
$query = "select h.name, NULL AS description, i.*, st.state FROM rt_issues i, rt_hosts h, rt_hoststateevents st WHERE i.host_id = h.host_id AND i.service_id IS NULL AND st.host_id = h.host_id AND st.start_time = st.start_time AND i.end_time IS NULL AND i.ack_time = 0 AND st.state < 3 UNION select h.name, s.description AS description, i.*, st.state FROM rt_issues i, rt_hosts h, rt_services s, rt_servicestateevents st WHERE i.host_id = h.host_id AND i.host_id = s.host_id AND s.service_id IS NOT NULL AND st.host_id = h.host_id AND st.service_id = s.service_id AND i.start_time = st.start_time AND i.end_time IS NULL AND i.ack_time = 0 AND st.state < 3 ORDER BY state DESC, start_time DESC LIMIT 10";
$stmt = $dbM->prepare($query);
$stmt->execute();
$state_id = array("success" => 0, "warning" => 1, "danger" => 2, "default" => 3, "info" => 4);
while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
  $row['duration'] = Datetime::humanReadable(time() - $row['start_time']);
  $status = 'success';
  if (isset($row["description"])) {
    if ($row["state"] == 1) {
      $status = 'warning';
    } else if ($row["state"] == 2) {
      $status = 'danger';
    } else if ($row["state"] == 3) {
      $status = 'default';
    } else if ($row["state"] == 4) {
      $status = 'info';
    } 
  } else {
    if ($row["state"] == 1) {
      $status = 'danger';
    } else if ($row["state"] == 2) {
      $status = 'default';
    } else if ($row["state"] == 3) {
      $status = 'default';
    } else if ($row["state"] == 4) {
      $status = 'info';
    } 
  }
  $row["state_id"] = $state_id[$status];
  $row['status'] = "<span class='label label-$status'>&nbsp;</span>";
  $data[] = $row;
}

/* Assign infos */
$tpl->assign("data", $data);

/* Display */
$tpl->display('file:[CentreonIncidentsWidget]console.tpl');
