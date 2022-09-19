<?php

/*
* Copyright 2005-2021 Centreon
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

/*
 * Path to the configuration dir
 */
$path = "./include/views/graphs/";

/*
 * Include Pear Lib
 */

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$chartId = \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['chartId'] ?? null);

if (preg_match('/([0-9]+)_([0-9]+)/', $chartId, $matches)) {
    $hostId = (int)$matches[1];
    $serviceId = (int)$matches[2];
} else {
    throw new \InvalidArgumentException('chartId must be a combination of integers');
}

/* Get host and service name */
$serviceName = '';

$query = 'SELECT h.name, s.description FROM hosts h, services s
    WHERE h.host_id = :hostId AND s.service_id = :serviceId AND h.host_id = s.host_id';

$stmt = $pearDBO->prepare($query);
$stmt->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);
$stmt->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
$stmt->execute();

while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
    $serviceName = $row['name'] . ' - ' . $row['description'];
}

$periods = [
    [
        'short' => '1d',
        'long' => _('last day'),
    ],
    [
        'short' => '7d',
        'long' => _('last week'),
    ],
    [
        'short' => '31d',
        'long' => _('last month'),
    ],
    [
        'short' => '1y',
        'long' => _('last year'),
    ],
];

$tpl->assign('periods', $periods);
$tpl->assign('svc_id', $chartId);
$tpl->assign('srv_name', $serviceName);

$tpl->display('graph-periods.html');
