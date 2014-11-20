<?php
/**
 * Copyright 2005-2011 MERETHIS
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
