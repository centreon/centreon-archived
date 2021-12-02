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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

if (!isset($oreon)) {
    exit;
}

/*
 * Required files
 */
require_once './include/reporting/dashboard/initReport.php';

/*
 *  Getting hostgroup to report
 */
$id = filter_var($_GET['item'] ?? $_POST['item'] ?? false, FILTER_VALIDATE_INT);
/*
 * Formulary
 *
 * Hostgroup Selection
 *
 */

$formHostGroup = new HTML_QuickFormCustom('formHostGroup', 'post', "?p=" . $p);
$redirect = $formHostGroup->addElement('hidden', 'o');
$redirect->setValue($o);

$hostsGroupRoute = array(
    'datasourceOrigin' => 'ajax',
    'multiple' => false,
    'linkedObject' => 'centreonHostgroups',
    'availableDatasetRoute' =>
        './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup&action=list',
    'defaultDatasetRoute' =>
        './api/internal.php?object=centreon_configuration_hostgroup'
        . '&action=defaultValues&target=service&field=service_hgPars&id=' . $id,
);
$hostGroupSelectBox = $formHostGroup->addElement(
    'select2',
    'item',
    _("Host Group"),
    [],
    $hostsGroupRoute
);
$hostGroupSelectBox->addJsCallback(
    'change',
    'this.form.submit();'
);
$formHostGroup->addElement(
    'hidden',
    'period',
    $period
);
$formHostGroup->addElement(
    'hidden',
    'StartDate',
    $get_date_start
);
$formHostGroup->addElement(
    'hidden',
    'EndDate',
    $get_date_end
);

if (isset($id)) {
    $formHostGroup->setDefaults(array('item' => $id));
}

/*
 * Set hostgroup id with period selection form
 */
if ($id !== false) {
    $formPeriod->addElement('hidden', 'item', $id);

    /*
     * Stats Display for selected hostgroup
     * Getting periods values
     */
    $dates = getPeriodToReport("alternate");
    $start_date = $dates[0];
    $end_date = $dates[1];

    /*
     * Getting hostgroup and his hosts stats
     */
    $hostgroupStats = array();
    $hostgroupStats = getLogInDbForHostGroup($id, $start_date, $end_date, $reportingTimePeriod);

    /*
     * Chart datas
     */
    $tpl->assign('hostgroup_up', $hostgroupStats["average"]["UP_TP"]);
    $tpl->assign('hostgroup_down', $hostgroupStats["average"]["DOWN_TP"]);
    $tpl->assign('hostgroup_unreachable', $hostgroupStats["average"]["UNREACHABLE_TP"]);
    $tpl->assign('hostgroup_undetermined', $hostgroupStats["average"]["UNDETERMINED_TP"]);
    $tpl->assign('hostgroup_maintenance', $hostgroupStats["average"]["MAINTENANCE_TP"]);

    /*
     * Exporting variables for ihtml
     */
    $tpl->assign('totalAlert', $hostgroupStats["average"]["TOTAL_ALERTS"]);
    $tpl->assign('summary', $hostgroupStats["average"]);

    /*
     * removing average infos from table
     */
    $hostgroupFinalStats = array();
    foreach ($hostgroupStats as $key => $value) {
        if ($key != "average") {
            $hostgroupFinalStats[$key] = $value;
        }
    }

    $tpl->assign("components", $hostgroupFinalStats);
    $tpl->assign('period_name', _("From"));
    $tpl->assign('date_start', $start_date);
    $tpl->assign('to', _("to"));
    $tpl->assign('date_end', $end_date);
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
        "./include/reporting/dashboard/csvExport/csv_HostGroupLogs.php?hostgroup="
        . $id . "&start=" . $start_date . "&end=" . $end_date
    );
    $tpl->assign("link_csv_name", _("Export in CSV format"));

    /*
     * Status colors
     */
    $color = substr($colors["up"], 1) .
            ':' . substr($colors["down"], 1) .
            ':' . substr($colors["unreachable"], 1) .
            ':' . substr($colors["maintenance"], 1) .
            ':' . substr($colors["undetermined"], 1);

    /*
     * Ajax timeline
     */
    $type = 'HostGroup';
    include("./include/reporting/dashboard/ajaxReporting_js.php");
} else {
    ?><script type="text/javascript"> function initTimeline() {;} </script><?php
}
$tpl->assign('resumeTitle', _("Hosts group state"));

/*
 * Rendering Forms
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$formPeriod->accept($renderer);
$tpl->assign('formPeriod', $renderer->toArray());

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$formHostGroup->accept($renderer);
$tpl->assign('formHostGroup', $renderer->toArray());

if (
    !$formPeriod->isSubmitted()
    || ($formPeriod->isSubmitted() && $formPeriod->validate())
) {
    $tpl->display("template/viewHostGroupLog.ihtml");
}
