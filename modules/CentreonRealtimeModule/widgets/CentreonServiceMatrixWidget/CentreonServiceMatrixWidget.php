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
