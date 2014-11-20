<?php
/*
 * Copyright 2005-2014 MERETHIS
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
