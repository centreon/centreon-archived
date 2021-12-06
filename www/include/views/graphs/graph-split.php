<?php

/*
* Copyright 2005-2021 Centreon
* Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$chartId = filter_var($_GET['chartId'] ?? null, FILTER_SANITIZE_STRING);

if (preg_match('/([0-9]+)_([0-9]+)/', $chartId, $matches)) {
    $hostId = (int)$matches[1];
    $serviceId = (int)$matches[2];
} else {
    throw new \InvalidArgumentException('chartId must be a combination of integers');
}

$metrics = array();

/* Get list metrics */
$query = 'SELECT m.metric_id, m.metric_name, i.host_name, i.service_description
    FROM metrics m
    INNER JOIN index_data i
    ON i.id = m.index_id AND i.service_id = :serviceId
    AND i.host_id = :hostId';

$stmt = $pearDBO->prepare($query);
$stmt->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);
$stmt->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
$stmt->execute();

while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
    $metrics[] = array(
        'id' => $chartId . '_' . $row['metric_id'],
        'title' => $row['host_name'] . ' - ' . $row['service_description'] . ' : ' . $row['metric_name']
    );
}

if (isset($_GET['start'])) {
    $period_start = filter_var($_GET['start'], FILTER_VALIDATE_INT);
} else {
    $period_start = 'undefined';
}

if (isset($_GET['end'])) {
    $period_end = filter_var($_GET['end'], FILTER_VALIDATE_INT);
} else {
    $period_end = 'undefined';
}

if ($period_start === false) {
    $period_start = 'undefined';
}

if ($period_end === false) {
    $period_end = 'undefined';
}

/*
 * Form begin
 */
$form = new HTML_QuickFormCustom('FormPeriod', 'get', '?p=' . $p);

$periods = [
    '' => '',
    '3h' => _('Last 3 Hours'),
    '6h' => _('Last 6 Hours'),
    '12h' => _('Last 12 Hours'),
    '1d' => _('Last 24 Hours'),
    '2d' => _('Last 2 Days'),
    '3d' => _('Last 3 Days'),
    '4d' => _('Last 4 Days'),
    '5d' => _('Last 5 Days'),
    '7d' => _('Last 7 Days'),
    '14d' => _('Last 14 Days'),
    '28d' => _('Last 28 Days'),
    '30d' => _('Last 30 Days'),
    '31d' => _('Last 31 Days'),
    '2M' => _('Last 2 Months'),
    '4M' => _('Last 4 Months'),
    '6M' => _('Last 6 Months'),
    '1y' => _('Last Year')
];
$sel = $form->addElement(
    'select',
    'period',
    _('Graph Period'),
    $periods,
    [ 'onchange' => 'changeInterval()' ]
);
$form->addElement(
    'text',
    'StartDate',
    '',
    [
        'id' => 'StartDate',
        'class' => 'datepicker-iso',
        'size' => 10,
        'onchange' => 'changePeriod()'
    ]
);
$form->addElement(
    'text',
    'StartTime',
    '',
    [
        'id' => 'StartTime',
        'class' => 'timepicker',
        'size' => 5,
        'onchange' => 'changePeriod()'
    ]
);
$form->addElement(
    'text',
    'EndDate',
    '',
    [
        'id' => 'EndDate',
        'class' => 'datepicker-iso',
        'size' => 10,
        'onchange' => 'changePeriod()'
    ]
);
$form->addElement(
    'text',
    'EndTime',
    '',
    [
        'id' => 'EndTime',
        'class' => 'timepicker',
        'size' => 5,
        'onchange' => 'changePeriod()'
    ]
);

/* adding hidden fields to get the result of datepicker in an unlocalized format */
$form->addElement(
    'hidden',
    'alternativeDateStartDate',
    '',
    [
        'size' => 10,
        'class' => 'alternativeDate'
    ]
);
$form->addElement(
    'hidden',
    'alternativeDateEndDate',
    '',
    [
        'size' => 10,
        'class' => 'alternativeDate'
    ]
);

if (
    $period_start != 'undefined' &&
    $period_end != 'undefined'
) {
    $startDay = date('Y-m-d', $period_start);
    $startTime = date('H:i', $period_start);
    $endDay = date('Y-m-d', $period_end);
    $endTime = date('H:i', $period_end);
    $form->setDefaults(
        [
            'alternativeDateStartDate' => $startDay,
            'StartTime' => $startTime,
            'alternativeDateEndDate' => $endDay,
            'EndTime' => $endTime
        ]
    );
} else {
    $form->setDefaults(
        [
            'period' => '3h'
        ]
    );
}

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);

$tpl->assign('form', $renderer->toArray());
$tpl->assign('metrics', $metrics);

$tpl->display('graph-split.html');
