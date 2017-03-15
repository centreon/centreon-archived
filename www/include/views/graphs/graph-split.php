<?php
/*
* Copyright 2005-2015 Centreon
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

require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

function getGetPostValue($str)
{
    $value = null;
    if (isset($_GET[$str]) && $_GET[$str]) {
        $value = $_GET[$str];
    }
    if (isset($_POST[$str]) && $_POST[$str]) {
        $value = $_POST[$str];
    }
    return urldecode($value);
}

$svc_id = getGetPostValue('chartId');

$metrics = array();
if (isset($svc_id) && $svc_id) {
    list($hostId, $svcId) = explode('_', $svc_id);
    /* Get list metrics */
    $query = 'SELECT m.metric_id, m.metric_name, i.host_name, i.service_description
        FROM metrics m, index_data i
        WHERE i.id = m.index_id AND i.service_id = ' . CentreonDB::escape($svcId) .
        ' AND i.host_id = ' . CentreonDB::escape($hostId);
    $res = $pearDBO->query($query);
    while ($row = $res->fetchRow()) {
        $metrics[] = array(
            'id' => $svc_id . '_' .$row['metric_id'],
            'title' => $row['host_name'] . ' - ' . $row['service_description'] . ' : ' . $row['metric_name']
        );
    }
}

/* Get Period if is in url */
$period_start = 'undefined';
$period_end = 'undefined';
if (isset($_REQUEST['start']) && is_numeric($_REQUEST['start'])) {
    $period_start = $_REQUEST['start'];
}
if (isset($_REQUEST['end']) && is_numeric($_REQUEST['end'])) {
    $period_end = $_REQUEST['end'];
}

/*
 * Form begin
 */
$form = new HTML_QuickForm('FormPeriod', 'get', "?p=".$p);

$periods = array(
    "" => "",
    "3h" => _("Last 3 Hours"),
    "6h" => _("Last 6 Hours"),
    "12h" => _("Last 12 Hours"),
    "1d" => _("Last 24 Hours"),
    "2d" => _("Last 2 Days"),
    "3d" => _("Last 3 Days"),
    "4d" => _("Last 4 Days"),
    "5d" => _("Last 5 Days"),
    "7d" => _("Last 7 Days"),
    "14d" => _("Last 14 Days"),
    "28d" => _("Last 28 Days"),
    "30d" => _("Last 30 Days"),
    "31d" => _("Last 31 Days"),
    "2M" => _("Last 2 Months"),
    "4M" => _("Last 4 Months"),
    "6M" => _("Last 6 Months"),
    "1y" => _("Last Year")
);
$sel = $form->addElement('select', 'period', _("Graph Period"), $periods, array("onchange"=>"changeInterval()"));
$form->addElement(
    'text',
    'StartDate',
    '',
    array(
        "id" => "StartDate",
        "class" => "datepicker-iso",
        "size" => 10,
        "onchange" => "changePeriod()"
    )
);
$form->addElement(
    'text',
    'StartTime',
    '',
    array(
        "id" => "StartTime",
        "class" => "timepicker",
        "size" => 5,
        "onchange" => "changePeriod()"
    )
);
$form->addElement(
    'text',
    'EndDate',
    '',
    array(
        "id" => "EndDate",
        "class" => "datepicker-iso",
        "size" => 10,
        "onchange" => "changePeriod()"
    )
);
$form->addElement(
    'text',
    'EndTime',
    '',
    array(
        "id" => "EndTime",
        "class" => "timepicker",
        "size" => 5,
        "onchange" => "changePeriod()"
    )
);

if ($period_start != 'undefined' && $period_end != 'undefined') {
    $startDay = date('Y-m-d', $period_start);
    $startTime = date('H:i', $period_start);
    $endDay = date('Y-m-d', $period_end);
    $endTime = date('H:i', $period_end);
    $form->setDefaults(array(
        'StartDate' => $startDay,
        'StartTime' => $startTime,
        'EndDate' => $endDay,
        'EndTime' => $endTime
    ));
} else {
    $form->setDefaults(array(
        'period' => '3h'
    ));
}

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);

$tpl->assign('form', $renderer->toArray());
$tpl->assign('metrics', $metrics);

$tpl->display("graph-split.html");
