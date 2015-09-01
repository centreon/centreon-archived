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

$dbstorage = $this->getMonitoringDb();

/* Get host percent OK */
$query = "SELECT count(host_id) as nb_host, state FROM rt_hosts WHERE enabled = 1 GROUP BY state";
$stmt = $dbstorage->query($query);
$nbHostOk = 0;
$nbHostTotal = 0;
while ($row = $stmt->fetch()) {
    $nbHostTotal += $row['nb_host'];
    if ($row['state'] == 0) {
        $nbHostOk = $row['nb_host'];
    }
}
$hostPercent = $nbHostOk / $nbHostTotal;

/* Get service percent OK */
$query = "SELECT count(service_id) as nb_service, state FROM rt_services WHERE enabled = 1 GROUP BY state";
$stmt = $dbstorage->query($query);
$nbServiceOk = 0;
$nbServiceTotal = 0;
while ($row = $stmt->fetch()) {
    $nbServiceTotal += $row['nb_service'];
    if ($row['state'] == 0) {
        $nbServiceOk = $row['nb_service'];
    }
}
$servicePercent = $nbServiceOk / $nbServiceTotal;

$tmpl = $this->getTemplate();
$tmpl->addJs("d3.min.js");
$tmpl->addJs("centreon.gauge-simple.js");
$tmpl->addCss("centreon.gauge-simple.css");
$tmpl->assign("hostPercent", $hostPercent);
$tmpl->assign("nbHostOk", $nbHostOk);
$tmpl->assign("nbHostTotal", $nbHostTotal);
$tmpl->assign("servicePercent", $servicePercent);
$tmpl->assign("nbServiceOk", $nbServiceOk);
$tmpl->assign("nbServiceTotal", $nbServiceTotal);
$tmpl->display("file:[MonitoringStatusWidget]monitoringstatus.tpl");
