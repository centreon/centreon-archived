<?php
/*
 * Copyright 2005-2018 Centreon
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
    exit;
}

/*
 * Required files
 */
require_once './include/reporting/dashboard/initReport.php';

/*
 *  Getting service to report
 */
$host_id = filter_var($_GET['host_id'] ?? $_POST['host_id'] ?? false, FILTER_VALIDATE_INT);
$service_id = filter_var($_GET['item'] ?? $_POST['item'] ?? false, FILTER_VALIDATE_INT);

/*
 * FORMS
 */
$form = new HTML_QuickFormCustom('formItem', 'post', "?p=" . $p);

$host_name = getMyHostName($host_id);
$items = $centreon->user->access->getHostServices($pearDBO, $host_id);

$itemsForUrl = array();
foreach ($items as $key => $value) {
    $itemsForUrl[str_replace(":", "%3A", $key)] = str_replace(":", "%3A", $value);
}
$service_name = $itemsForUrl[$service_id];

$select = $form->addElement(
    'select',
    'item',
    _("Service"),
    $items,
    array(
        "onChange" =>"this.form.submit();"
    )
);
$form->addElement(
    'hidden',
    'period',
    $period
);
$form->addElement(
    'hidden',
    'StartDate',
    $get_date_start
);
$form->addElement(
    'hidden',
    'EndDate',
    $get_date_end
);
$form->addElement('hidden', 'p', $p);
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/* adding hidden fields to get the result of datepicker in an unlocalized format */
$formPeriod->addElement(
    'hidden',
    'alternativeDateStartDate',
    '',
    array(
        'size' => 10,
        'class' => 'alternativeDate'
    )
);
$formPeriod->addElement(
    'hidden',
    'alternativeDateEndDate',
    '',
    array(
        'size' => 10,
        'class' => 'alternativeDate'
    )
);

/*
 * Set service id with period selection form
 */
if ($service_id !== false && $host_id !== false) {
    $formPeriod->addElement(
        'hidden',
        'item',
        $service_id
    );
    $formPeriod->addElement(
        'hidden',
        'host_id',
        $host_id
    );
    $form->addElement(
        'hidden',
        'host_id',
        $host_id
    );
    $form->setDefaults(array('item' => $service_id));

    /*
     * Getting periods values
     */
    $dates = getPeriodToReport("alternate");
    $start_date = $dates[0];
    $end_date = $dates[1];

    /*
     * Getting hostgroup and his hosts stats
     */
    $serviceStats = array();
    $serviceStats = getLogInDbForOneSVC($host_id, $service_id, $start_date, $end_date, $reportingTimePeriod);

    /*
     * Chart datas
     */
    $tpl->assign('service_ok', $serviceStats["OK_TP"]);
    $tpl->assign('service_warning', $serviceStats["WARNING_TP"]);
    $tpl->assign('service_critical', $serviceStats["CRITICAL_TP"]);
    $tpl->assign('service_unknown', $serviceStats["UNKNOWN_TP"]);
    $tpl->assign('service_undetermined', $serviceStats["UNDETERMINED_TP"]);
    $tpl->assign('service_maintenance', $serviceStats["MAINTENANCE_TP"]);

    /*
     * Exporting variables for ihtml
     */
    $tpl->assign('host_name', $host_name);
    $tpl->assign('name', $itemsForUrl[$service_id]);
    $tpl->assign('totalAlert', $serviceStats["TOTAL_ALERTS"]);
    $tpl->assign('totalTime', $serviceStats["TOTAL_TIME_F"]);
    $tpl->assign('summary', $serviceStats);
    $tpl->assign('from', _("From"));
    $tpl->assign('date_start', $start_date);
    $tpl->assign('to', _("to"));
    $tpl->assign('date_end', $end_date);
    $formPeriod->setDefaults(array('period' => $period));
    $tpl->assign('id', $service_id);

    /*
     * Ajax timeline and CSV export initialization
     * CSV Export
     */
    $tpl->assign(
        "link_csv_url",
        "./include/reporting/dashboard/csvExport/csv_ServiceLogs.php?host="
        . $host_id . "&service=" . $service_id . "&start=" . $start_date . "&end=" . $end_date
    );
    $tpl->assign("link_csv_name", _("Export in CSV format"));

    /*
     * status colors
     */
    $color = substr($colors["up"], 1)
        . ':' . substr($colors["down"], 1)
        . ':' . substr($colors["unreachable"], 1)
        . ':' . substr($colors["undetermined"], 1)
        . ':' . substr($colors["maintenance"], 1);

    /*
     * Ajax timeline
     */
    $type = 'Service';
    include("./include/reporting/dashboard/ajaxReporting_js.php");
} else {
    ?><script type="text/javascript"> function initTimeline() {;} </script> <?php
}
$tpl->assign('resumeTitle', _("Service state"));
$tpl->assign('p', $p);

/*
 * Rendering forms
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$formPeriod->accept($renderer);
$tpl->assign('formPeriod', $renderer->toArray());

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('formItem', $renderer->toArray());

if (
    !$formPeriod->isSubmitted()
    || ($formPeriod->isSubmitted() && $formPeriod->validate())
) {
    $tpl->display("template/viewServicesLog.ihtml");
}
