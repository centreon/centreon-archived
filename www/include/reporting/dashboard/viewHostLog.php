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
 *  Getting host to report
 */
$id = filter_var($_GET['host'] ?? $_POST['host'] ?? false, FILTER_VALIDATE_INT);

/*
 * Formulary
 */

/*
 * Host Selection
 */
$formHost = new HTML_QuickFormCustom('formHost', 'post', "?p=" . $p);
$redirect = $formHost->addElement('hidden', 'o');
$redirect->setValue($o);

$hostsRoute = array(
    'datasourceOrigin' => 'ajax',
    'multiple' => false,
    'linkedObject' => 'centreonHost',
    'availableDatasetRoute' =>
        './api/internal.php?object=centreon_configuration_host&action=list',
    'defaultDatasetRoute' =>
        './api/internal.php?object=centreon_configuration_host
        &action=defaultValues&target=host&field=host_id&id=' . $id,
);
$selHost = $formHost->addElement(
    'select2',
    'host',
    _("Host"),
    [],
    $hostsRoute
);
$selHost->addJsCallback(
    'change',
    'this.form.submit();'
);
$formHost->addElement(
    'hidden',
    'period',
    $period
);
$formHost->addElement(
    'hidden',
    'StartDate',
    $get_date_start
);
$formHost->addElement(
    'hidden',
    'EndDate',
    $get_date_end
);

if (isset($id)) {
    $formHost->setDefaults(['host' => $id]);
}

/*
 * Set host id with period selection form
 */
if ($id !== false) {
    $formPeriod->addElement(
        'hidden',
        'host',
        $id
    );

    /*
     * Stats Display for selected host
     * Getting periods values
     */
    $dates = getPeriodToReport("alternate");
    $startDate = $dates[0];
    $endDate = $dates[1];
    //$formPeriod->setDefaults(array('period' => $period));

    /*
     * Getting host and his services stats
     */
    $hostStats = [];
    $hostStats = getLogInDbForHost($id, $startDate, $endDate, $reportingTimePeriod);
    $hostServicesStats = [];
    $hostServicesStats = getLogInDbForHostSVC($id, $startDate, $endDate, $reportingTimePeriod);

    /*
     * Chart datas
     */
    $tpl->assign('host_up', $hostStats["UP_TP"]);
    $tpl->assign('host_down', $hostStats["DOWN_TP"]);
    $tpl->assign('host_unreachable', $hostStats["UNREACHABLE_TP"]);
    $tpl->assign('host_undetermined', $hostStats["UNDETERMINED_TP"]);
    $tpl->assign('host_maintenance', $hostStats["MAINTENANCE_TP"]);

    /*
     * Exporting variables for ihtml
     */
    $tpl->assign("totalAlert", $hostStats["TOTAL_ALERTS"]);
    $tpl->assign("totalTime", $hostStats["TOTAL_TIME_F"]);
    $tpl->assign("summary", $hostStats);
    $tpl->assign("components_avg", array_pop($hostServicesStats));
    $tpl->assign("components", $hostServicesStats);
    $tpl->assign("period_name", _("From"));
    $tpl->assign("date_start", $startDate);
    $tpl->assign("to", _("to"));
    $tpl->assign("date_end", $endDate);
    $tpl->assign("period", $period);
    $tpl->assign("host_id", $id);
    $tpl->assign("Alert", _("Alert"));

    /*
     * Ajax TimeLine and CSV export initialization
     * CSV export
     */
    $tpl->assign(
        "link_csv_url",
        "./include/reporting/dashboard/csvExport/csv_HostLogs.php?host=" .
            $id . "&start=" . $startDate . "&end=" . $endDate
    );
    $tpl->assign("link_csv_name", _("Export in CSV format"));

    /*
     * Status colors
     */
    $color = substr($colors["up"], 1)
        . ':' . substr($colors["down"], 1)
        . ':' . substr($colors["unreachable"], 1)
        . ':' . substr($colors["undetermined"], 1)
        . ':' . substr($colors["maintenance"], 1);

    /*
     * Ajax timeline
     */
    $type = 'Host';
    include("./include/reporting/dashboard/ajaxReporting_js.php");
} else {
    ?><script type="text/javascript"> function initTimeline() {;} </script> <?php
}
$tpl->assign("resumeTitle", _("Host state"));

/*
 * Rendering Forms
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$formPeriod->accept($renderer);
$tpl->assign('formPeriod', $renderer->toArray());

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$formHost->accept($renderer);
$tpl->assign('formHost', $renderer->toArray());

if (
    !$formPeriod->isSubmitted()
    || ($formPeriod->isSubmitted() && $formPeriod->validate())
) {
    $tpl->display("template/viewHostLog.ihtml");
}
