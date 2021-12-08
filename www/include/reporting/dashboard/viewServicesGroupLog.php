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
    exit;
}

/*
 * Required files
 */
require_once './include/reporting/dashboard/initReport.php';

/*
 *  Getting service group to report
 */
$id = filter_var($_GET['itemElement'] ?? $_POST['itemElement'] ?? false, FILTER_VALIDATE_INT);
/*
 * FORMS
 */

$serviceGroupForm = new HTML_QuickFormCustom('formServiceGroup', 'post', "?p=" . $p);
$redirect = $serviceGroupForm->addElement('hidden', 'o');
$redirect->setValue($o);

$serviceGroupRoute = array(
    'datasourceOrigin' => 'ajax',
    'multiple' => false,
    'linkedObject' => 'centreonServicegroups',
    'availableDatasetRoute' =>
        './api/internal.php?object=centreon_configuration_servicegroup&action=list',
    'defaultDatasetRoute' =>
        './api/internal.php?object=centreon_configuration_servicegroup'
        . '&action=defaultValues&target=service&field=service_sgs&id=' . $id,
);
$serviceGroupSelectBox = $serviceGroupForm->addElement(
    'select2',
    'item',
    _("Service Group"),
    [],
    $serviceGroupRoute
);
$serviceGroupSelectBox->addJsCallback(
    'change',
    'this.form.submit();'
);
$serviceGroupForm->addElement(
    'hidden',
    'period',
    $period
);
$serviceGroupForm->addElement(
    'hidden',
    'StartDate',
    $get_date_start
);
$serviceGroupForm->addElement(
    'hidden',
    'EndDate',
    $get_date_end
);

if (isset($id)) {
    $serviceGroupForm->setDefaults(array('item' => $id));
}

/*
* Set servicegroup id with period selection form
*/
if ($id !== false) {
    $formPeriod->addElement(
        'hidden',
        'item',
        $id
    );

    /*
     * Stats Display for selected services group
     * Getting periods values
     */
    $dates = getPeriodToReport("alternate");
    $startDate = $dates[0];
    $endDate = $dates[1];

    /*
     * Getting servicegroups logs
     */
    $servicesgroupStats = getLogInDbForServicesGroup($id, $startDate, $endDate, $reportingTimePeriod);

    /*
     * Chart datas
     */
    $tpl->assign('servicegroup_ok', $servicesgroupStats["average"]["OK_TP"]);
    $tpl->assign('servicegroup_warning', $servicesgroupStats["average"]["WARNING_TP"]);
    $tpl->assign('servicegroup_critical', $servicesgroupStats["average"]["CRITICAL_TP"]);
    $tpl->assign('servicegroup_unknown', $servicesgroupStats["average"]["UNKNOWN_TP"]);
    $tpl->assign('servicegroup_undetermined', $servicesgroupStats["average"]["UNDETERMINED_TP"]);
    $tpl->assign('servicegroup_maintenance', $servicesgroupStats["average"]["MAINTENANCE_TP"]);

    /*
     * Exporting variables for ihtml
     */
    $tpl->assign('totalAlert', $servicesgroupStats["average"]["TOTAL_ALERTS"]);
    $tpl->assign('summary', $servicesgroupStats["average"]);

    /*
     * Removing average infos from table
     */
    $servicesgroupFinalStats = array();
    foreach ($servicesgroupStats as $key => $value) {
        if ($key != "average") {
            $servicesgroupFinalStats[$key] = $value;
        }
    }

    $tpl->assign("components", $servicesgroupFinalStats);
    $tpl->assign('period_name', _("From"));
    $tpl->assign('date_start', $startDate);
    $tpl->assign('to', _("to"));
    $tpl->assign('date_end', $endDate);
    $tpl->assign('period', $period);
    $formPeriod->setDefaults(array('period' => $period));
    $tpl->assign('id', $id);
    $tpl->assign('Alert', _("Alert"));

    /*
     * Ajax timeline and CSV export initialization
     * CSV export
     */
    $tpl->assign(
        "link_csv_url",
        "./include/reporting/dashboard/csvExport/csv_ServiceGroupLogs.php?servicegroup="
        . $id . "&start=" . $startDate . "&end=" . $endDate
    );
    $tpl->assign(
        "link_csv_name",
        _("Export in CSV format")
    );

    /*
     * Status colors
     */
    $color = substr($colors["up"], 1)
        . ':' . substr($colors["down"], 1)
        . ':' . substr($colors["unreachable"], 1)
        . ':' . substr($colors["maintenance"], 1)
        . ':' . substr($colors["undetermined"], 1);

    /*
     * Ajax timeline
     */
    $type = 'ServiceGroup';
    include("./include/reporting/dashboard/ajaxReporting_js.php");
} else {
    ?><script type="text/javascript"> function initTimeline() {;} </script> <?php
}
$tpl->assign('resumeTitle', _("Service group state"));
$tpl->assign('p', $p);

/*
 * Rendering forms
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$formPeriod->accept($renderer);
$tpl->assign('formPeriod', $renderer->toArray());

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$serviceGroupForm->accept($renderer);
$tpl->assign('serviceGroupForm', $renderer->toArray());

if (
    !$formPeriod->isSubmitted()
    || ($formPeriod->isSubmitted() && $formPeriod->validate())
) {
    $tpl->display("template/viewServicesGroupLog.ihtml");
}
