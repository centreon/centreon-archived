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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

if (!isset($oreon)) {
    exit();
}

    require_once "./include/monitoring/common-Func.php";

    require_once 'HTML/QuickForm.php';
    require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

    unset($tpl);
    unset($path);

    /*
	 * Time period select
	 */
    $form = new HTML_QuickForm('form', 'post', "?p=".$p);

    /*
	 * Get Poller List
	 */
    $pollerList = array();
    $defaultPoller = array();
    $DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = 1 ORDER BY `name`");

    while ($data = $DBRESULT->fetchRow()) {
        if ($data['localhost']) {
            $defaultPoller[$data['name']] = $data['id'];
            $pollerId = $data['id'];
        }
    }
    $DBRESULT->free();

    isset($_POST['pollers']) && $_POST['pollers'] != "" ? $selectedPoller = $_POST['pollers'] : $selectedPoller = $defaultPoller;

    $attrPollers = array(
        'datasourceOrigin' => 'ajax',
        'allowClear' => false,
        'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_monitoring_poller&action=list',
        'multiple' => false,
        'defaultDataset' => $defaultPoller,
        'linkedObject' => 'centreonInstance'
    );
    $form->addElement('select2', 'pollers', _("Poller"), array(), $attrPollers);

    /*
	 * Get Period
	 */
    $time_period = array(
        "last3hours"  => _("Last 3 hours"),
        "today" => _("Today"),
        "yesterday" => _("Yesterday"),
        "last4days" => _("Last 4 days"),
        "lastweek" => _("Last week"),
        "lastmonth" => _("Last month"),
        "last6month" => _("Last 6 months"),
        "lastyear" => _("Last year")
    );

    $defaultPeriod = array();
    $currentPeriod = '';
    if (isset($_POST['start']) && ($_POST != '')) {
        $defaultPeriod[$time_period[$_POST['start']]] = $_POST['start'];
        $currentPeriod .= $_POST['start'];
    } else {
        $defaultPeriod[$time_period['today']] = 'today';
        $currentPeriod .= 'today';
    }

    switch ($currentPeriod) {
        case "last3hours":
            $start = time() - (60*60*3);
            break;
        case "today":
            $start = time() - (60*60*24);
            break;
        case "yesterday":
            $start = time() - (60*60*48);
            break;
        case "last4days":
            $start = time() - (60*60*96);
            break;
        case "lastweek":
            $start = time() - (60*60*168);
            break;
        case "lastmonth":
            $start = time() - (60*60*24*30);
            break;
        case "last6month":
            $start = time() - (60*60*24*30*6);
            break;
        case "lastyear":
            $start = time() - (60*60*24*30*12);
            break;
    }

    /*
    * Get end values
    */
    $end = time();



    $periodSelect = array(
        'allowClear' => false,
        'multiple' => false,
        'defaultDataset' => $defaultPeriod
    );

    $selTP = $form->addElement('select2', 'start', _("Period"), $time_period, $periodSelect);

    $options = array(   "active_host_check" => "nagios_active_host_execution.rrd",
                        "active_service_check" => "nagios_active_service_execution.rrd",
                        "active_host_last" => "nagios_active_host_last.rrd",
                        "active_service_last" => "nagios_active_service_last.rrd",
                        "host_latency" => "nagios_active_host_latency.rrd",
                        "service_latency" => "nagios_active_service_latency.rrd",
                        "host_states" => "nagios_hosts_states.rrd",
                        "service_states" => "nagios_services_states.rrd",
                        "cmd_buffer" => "nagios_cmd_buffer.rrd");
    
    $title = array(
            "active_host_check" => _("Host Check Execution Time"),
            "active_host_last" => _("Hosts Actively Checked"),
            "host_latency" => _("Host check latency"),
            "active_service_check" => _("Service Check Execution Time"),
            "active_service_last" => _("Services Actively Checked"),
            "service_latency" => _("Service check latency"),
            "cmd_buffer" => _("Commands in buffer"),
            "host_states" => _("Host status"),
            "service_states" => _("Service status")
        );

    $path = "./include/Administration/corePerformance/";

    /*
	 * Smarty template Init
	 */
    $tpl = new Smarty();
    $tpl = initSmartyTpl($path, $tpl, "./");

    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $form->accept($renderer);

    /*
	 * Assign values
	 */

    $tpl->assign('form', $renderer->toArray());

    if (isset($_POST["start"])) {
        $tpl->assign('startPeriod', $_POST["start"]);
    } else {
        $tpl->assign('startPeriod', 'today');
    }
    if (isset($host_list) && $host_list) {
        $tpl->assign('host_list', $host_list);
    }
    if (isset($tab_server) && $tab_server) {
        $tpl->assign('tab_server', $tab_server);
    }

    $tpl->assign("p", $p);
    if (isset($pollerName)) {
        $tpl->assign("pollerName", $pollerName);
    }
    $tpl->assign("options", $options);
    $tpl->assign("startTime", $start);
    $tpl->assign("endTime", $end);
    $tpl->assign("pollerId", $pollerId);
    $tpl->assign("title", $title);
    $tpl->assign("session_id", session_id());
    $tpl->display("nagiosStats.html");
